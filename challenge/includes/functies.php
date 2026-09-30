<?php
/**
 * Hulpfuncties voor de reactiesectie.
 */
declare(strict_types=1);

use Carbon\Carbon;

/* =========================================================
   Veiligheid
   ========================================================= */

/**
 * Maakt binnenkomende formuliertekst schoon.
 *
 * - trim()            haalt spaties aan het begin en eind weg
 * - stripslashes()    haalt eventuele backslashes weg
 * - htmlspecialchars() verandert < > " ' & in onschadelijke tekens
 *
 * Door die laatste stap kan niemand HTML of JavaScript in de pagina krijgen:
 * <script>alert('hoi')</script> wordt gewoon als tekst opgeslagen en getoond.
 */
function schoonmaken(string $data): string
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');

    return $data;
}

/**
 * Zet tekst veilig op het scherm.
 *
 * De tekst uit de database is al schoongemaakt bij het opslaan. Daarom staat
 * de laatste parameter op false: anders zou je &amp;lt; in beeld krijgen.
 * Deze functie is een tweede slot op de deur.
 */
function e(?string $tekst): string
{
    return htmlspecialchars((string) $tekst, ENT_QUOTES, 'UTF-8', false);
}

/* =========================================================
   Weergave
   ========================================================= */

/**
 * "3 minuten geleden" in plaats van "2026-09-11 09:12:00".
 * Dit doet de composer-package Carbon voor ons (zie composer.json).
 */
function hoeLangGeleden(string $datum): string
{
    Carbon::setLocale('nl');                       // Nederlandse tekst: "geleden"

    $moment = Carbon::parse($datum);

    // Net geplaatst? Dan staat "zojuist" mooier dan "0 seconden geleden".
    if (abs($moment->diffInSeconds()) < 60) {
        return 'zojuist';
    }

    return $moment->diffForHumans();
}

/** De eerste letter van de naam, voor in het rondje van de avatar. */
function initiaal(string $naam): string
{
    $naam = trim($naam);

    return $naam === '' ? '?' : mb_strtoupper(mb_substr($naam, 0, 1));
}

/** Dezelfde naam krijgt altijd dezelfde avatarkleur. */
function avatarKleur(string $naam): string
{
    $getal = 0;
    for ($i = 0; $i < mb_strlen($naam); $i++) {
        $getal = ($getal * 31 + mb_ord(mb_substr($naam, $i, 1))) % 360;
    }

    return 'hsl(' . $getal . ', 45%, 38%)';
}

/** 1234 wordt 1,2K - net als op YouTube. */
function korteTelling(int $aantal): string
{
    if ($aantal >= 1000000) {
        return str_replace('.', ',', (string) round($aantal / 1000000, 1)) . ' mln';
    }
    if ($aantal >= 1000) {
        return str_replace('.', ',', (string) round($aantal / 1000, 1)) . 'K';
    }

    return $aantal > 0 ? (string) $aantal : '';
}

/* =========================================================
   Database
   ========================================================= */

/**
 * Haalt alle reacties op en hangt de antwoorden onder de juiste hoofdreactie.
 *
 * $sorteer = 'top'   -> meeste likes eerst
 * $sorteer = 'nieuw' -> nieuwste eerst
 */
function haalReacties(PDO $pdo, string $sorteer = 'nieuw'): array
{
    $rijen = $pdo->query(
        'SELECT id, parent_id, naam, reactie, likes, created_at
         FROM reacties
         ORDER BY created_at ASC, id ASC'
    )->fetchAll();

    $hoofdreacties = [];
    $antwoorden    = [];

    foreach ($rijen as $rij) {
        if ($rij['parent_id'] === null) {
            $rij['antwoorden'] = [];
            $hoofdreacties[(int) $rij['id']] = $rij;
        } else {
            $antwoorden[] = $rij;
        }
    }

    // Antwoorden onder hun hoofdreactie zetten (oudste eerst).
    foreach ($antwoorden as $antwoord) {
        $ouder = (int) $antwoord['parent_id'];
        if (isset($hoofdreacties[$ouder])) {
            $hoofdreacties[$ouder]['antwoorden'][] = $antwoord;
        }
    }

    $lijst = array_values($hoofdreacties);

    usort($lijst, function ($a, $b) use ($sorteer) {
        if ($sorteer === 'top') {
            // Meeste likes eerst; bij gelijkspel de nieuwste bovenaan.
            return ($b['likes'] <=> $a['likes']) ?: strcmp($b['created_at'], $a['created_at']);
        }

        return strcmp($b['created_at'], $a['created_at']);
    });

    return $lijst;
}

/** Telt hoeveel reacties er in totaal zijn, antwoorden meegerekend. */
function telReacties(array $reacties): int
{
    $totaal = 0;
    foreach ($reacties as $reactie) {
        $totaal += 1 + count($reactie['antwoorden']);
    }

    return $totaal;
}

/** Slaat een nieuwe reactie op en geeft het id ervan terug. */
function plaatsReactie(PDO $pdo, ?int $parentId, string $naam, string $email, string $reactie): int
{
    // De vraagtekens worden pas later door PDO ingevuld. Zo kan iemand geen
    // SQL meesturen in het formulier (SQL-injectie).
    $stmt = $pdo->prepare(
        'INSERT INTO reacties (parent_id, naam, email, reactie) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$parentId, $naam, $email, $reactie]);

    return (int) $pdo->lastInsertId();
}

/**
 * Geeft het id van de hoofdreactie terug.
 * Antwoord je op een antwoord, dan kom je onder dezelfde hoofdreactie te staan,
 * precies zoals op YouTube. Bestaat de reactie niet, dan komt er null terug.
 */
function hoofdReactieId(PDO $pdo, int $id): ?int
{
    $stmt = $pdo->prepare('SELECT id, parent_id FROM reacties WHERE id = ?');
    $stmt->execute([$id]);
    $rij = $stmt->fetch();

    if (!$rij) {
        return null;
    }

    return $rij['parent_id'] === null ? (int) $rij['id'] : (int) $rij['parent_id'];
}

/** Telt er één like bij op. */
function likeReactie(PDO $pdo, int $id): void
{
    $stmt = $pdo->prepare('UPDATE reacties SET likes = likes + 1 WHERE id = ?');
    $stmt->execute([$id]);
}

/** Haalt er één like af (kan niet onder 0 komen, want de kolom is UNSIGNED). */
function verwijderLike(PDO $pdo, int $id): void
{
    $stmt = $pdo->prepare('UPDATE reacties SET likes = likes - 1 WHERE id = ? AND likes > 0');
    $stmt->execute([$id]);
}

/* =========================================================
   Overig
   ========================================================= */

/**
 * Stuurt de bezoeker terug naar de pagina.
 *
 * Dit doen we na het opslaan, zodat je bij het verversen van de pagina
 * niet per ongeluk dezelfde reactie nog een keer plaatst.
 */
function terugNaarPagina(string $anker = ''): void
{
    $url = 'index.php';
    if ($anker !== '') {
        $url .= '#' . $anker;
    }

    header('Location: ' . $url);
    exit;
}
