<?php
/**
 * Reactiesectie in YouTube-stijl
 * ==============================
 *
 * Alles gebeurt op deze ene pagina:
 *   1. het formulier verwerken (controleren en opslaan)
 *   2. de reacties uit de database ophalen
 *   3. de pagina tonen: video, formulier en alle reacties
 *
 * Na het verzenden van het formulier kom je dus weer op deze pagina terug.
 */

session_start();                                 // om naam, e-mail en likes te onthouden

require __DIR__ . '/vendor/autoload.php';        // composer-package Carbon ("3 minuten geleden")
require __DIR__ . '/includes/db.php';            // verbinding met de database
require __DIR__ . '/includes/functies.php';      // eigen hulpfuncties

$pdo = verbinding();

/* =========================================================
   1. HET FORMULIER VERWERKEN
   ========================================================= */

$fouten     = [];     // foutmeldingen per veld
$foutParent = null;   // bij welk formulier de fout hoort (null = het formulier bovenaan)

// Wat er in de invoervelden komt te staan. Naam en e-mail onthouden we in de
// sessie, zodat je die niet bij elke reactie opnieuw hoeft te typen.
$waarden = [
    'naam'    => $_SESSION['naam']  ?? '',
    'email'   => $_SESSION['email'] ?? '',
    'reactie' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $actie = $_POST['actie'] ?? '';

    /* ----- De like-knop ----- */
    if ($actie === 'like') {
        $id        = (int) ($_POST['id'] ?? 0);
        $mijnLikes = $_SESSION['likes'] ?? [];

        if (in_array($id, $mijnLikes, true)) {
            // Je had al geliket: de like gaat er weer af.
            verwijderLike($pdo, $id);
            $_SESSION['likes'] = array_values(array_diff($mijnLikes, [$id]));
        } else {
            likeReactie($pdo, $id);
            $mijnLikes[]       = $id;
            $_SESSION['likes'] = $mijnLikes;
        }

        terugNaarPagina('reactie-' . $id);
    }

    /* ----- Een reactie plaatsen ----- */

    // Eerst alles schoonmaken: spaties eraf en HTML onschadelijk maken.
    $naam    = schoonmaken((string) ($_POST['naam'] ?? ''));
    $email   = schoonmaken((string) ($_POST['email'] ?? ''));
    $reactie = schoonmaken((string) ($_POST['reactie'] ?? ''));

    // Staat hier een nummer in, dan is het een antwoord op die reactie.
    $parentInvoer = ($_POST['parent_id'] ?? '') !== '' ? (int) $_POST['parent_id'] : null;
    $parentId     = $parentInvoer;

    /* ----- Controleren of alles klopt -----
       Dit doen we hier, op de server. Een controle in de browser
       (type="email") is handig, maar die kan iemand omzeilen. */

    if ($naam === '') {
        $fouten['naam'] = 'Vul je naam in.';
    } elseif (mb_strlen($naam) > 60) {
        $fouten['naam'] = 'Je naam mag maximaal 60 tekens lang zijn.';
    }

    if ($email === '') {
        $fouten['email'] = 'Vul je e-mailadres in.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // filter_var kijkt of het echt op een e-mailadres lijkt.
        $fouten['email'] = 'Dit is geen geldig e-mailadres.';
    } elseif (mb_strlen($email) > 120) {
        $fouten['email'] = 'Dit e-mailadres is te lang (maximaal 120 tekens).';
    }

    if ($reactie === '') {
        $fouten['reactie'] = 'Je hebt nog geen reactie geschreven.';
    } elseif (mb_strlen($reactie) > 500) {
        $fouten['reactie'] = 'Je reactie mag maximaal 500 tekens lang zijn.';
    }

    // Antwoord je op een reactie die intussen verwijderd is?
    if ($parentInvoer !== null) {
        $parentId = hoofdReactieId($pdo, $parentInvoer);

        if ($parentId === null) {
            $fouten['reactie'] = 'De reactie waarop je antwoordt bestaat niet meer.';
        }
    }

    if (!$fouten) {
        // Alles klopt: opslaan in de database.
        $nieuwId = plaatsReactie($pdo, $parentId, $naam, $email, $reactie);

        $_SESSION['naam']  = $naam;
        $_SESSION['email'] = $email;

        // Daarna sturen we je terug naar deze pagina. Ververs je dan, dan
        // wordt dezelfde reactie niet nog een keer opgeslagen.
        terugNaarPagina('reactie-' . $nieuwId);
    }

    // Er is iets mis: de ingevulde tekst blijft staan, zodat je niet
    // alles opnieuw hoeft te typen. De foutmeldingen komen in beeld.
    $waarden    = ['naam' => $naam, 'email' => $email, 'reactie' => $reactie];
    $foutParent = $parentInvoer;
}

/* =========================================================
   2. DE GEGEVENS OPHALEN
   ========================================================= */

// Sorteervolgorde uit de adresbalk: index.php?sorteer=top
$sorteer = ($_GET['sorteer'] ?? '') === 'top' ? 'top' : 'nieuw';

$reacties = haalReacties($pdo, $sorteer);
$totaal   = telReacties($reacties);

// Onder welke reactie staat het antwoordformulier open?
$reageerOp = isset($_GET['reageer_op']) ? (int) $_GET['reageer_op'] : null;
if ($fouten && $foutParent !== null) {
    $reageerOp = $foutParent;                    // fout in een antwoord: dat formulier open laten
}

$mijnLikes = $_SESSION['likes'] ?? [];           // welke reacties jij geliket hebt
?>
<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hoe detonate ik zo'n c4 - YouTube</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <!-- ===== Balk bovenaan ===== -->
  <header class="topbar">
    <div class="topbar__left">
      <button class="icon-btn" aria-label="Menu">
        <svg viewBox="0 0 24 24"><path d="M21 6H3V5h18v1zm0 5H3v1h18v-1zm0 6H3v1h18v-1z"/></svg>
      </button>
      <a class="logo" href="index.php">
        <svg class="logo__mark" viewBox="0 0 28 20">
          <path fill="#ff0000" d="M27.4 3.1s-.3-1.9-1.1-2.7c-1-1.1-2.2-1.1-2.7-1.2C19.9 0 14 0 14 0h-.01S8.1 0 4.4.2c-.5.1-1.7.1-2.7 1.2C.9 2.2.6 4.1.6 4.1S.3 6.3.3 8.5v2.1c0 2.2.3 4.4.3 4.4s.3 1.9 1.1 2.7c1 1.1 2.3 1 2.9 1.1 2.1.2 9 .2 9 .2s5.9 0 9.6-.3c.5-.1 1.7-.1 2.7-1.2.8-.8 1.1-2.7 1.1-2.7s.3-2.2.3-4.4V8.5c0-2.2-.3-4.4-.3-4.4z"/>
          <path fill="#fff" d="M11.2 13.2V6.1l6.1 3.6-6.1 3.5z"/>
        </svg>
        <span class="logo__text">YouTube<sup>NL</sup></span>
      </a>
    </div>

    <div class="topbar__center">
      <div class="search">
        <input type="text" class="search__input" placeholder="Zoeken" aria-label="Zoeken">
        <button class="search__btn" aria-label="Zoeken">
          <svg viewBox="0 0 24 24"><path d="M20.87 20.17l-5.59-5.59C16.35 13.35 17 11.75 17 10c0-3.87-3.13-7-7-7s-7 3.13-7 7 3.13 7 7 7c1.75 0 3.35-.65 4.58-1.72l5.59 5.59.7-.7zM10 16c-3.31 0-6-2.69-6-6s2.69-6 6-6 6 2.69 6 6-2.69 6-6 6z"/></svg>
        </button>
      </div>
    </div>

    <div class="topbar__right">
      <button class="icon-btn" aria-label="Meldingen">
        <svg viewBox="0 0 24 24"><path d="M10 20h4c0 1.1-.9 2-2 2s-2-.9-2-2zm10-2.65V19H4v-1.65l2-1.88v-5.15C6 7.4 7.56 5.1 10 4.34v-.38c0-1.42 1.49-2.5 2.99-1.76.65.32 1.01 1.03 1.01 1.76v.38c2.44.75 4 3.06 4 5.98v5.15l2 1.88zm-1 .42l-2-1.88v-5.47c0-2.47-1.19-4.36-3.13-5.1-1.26-.53-2.64-.5-3.84.03C8.15 6.11 7 8 7 10.42v5.47l-2 1.88V18h14v-.23z"/></svg>
      </button>
      <span class="avatar" style="background: <?= e(avatarKleur($waarden['naam'])) ?>">
        <?= e(initiaal($waarden['naam'])) ?>
      </span>
    </div>
  </header>

  <main class="page">

    <!-- ===== De video (een embed van YouTube) ===== -->
    <section class="watch">
      <div class="player">
        <iframe
          src="https://www.youtube.com/embed/Q-AdTMMSh1o"
          title="Hoe detonate ik zo'n c4"
          frameborder="0"
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
          referrerpolicy="strict-origin-when-cross-origin"
          allowfullscreen></iframe>
      </div>

      <h1 class="video-title">Hoe detonate ik zo'n c4</h1>

      <div class="video-bar">
        <div class="channel">
          <span class="avatar avatar--channel">R</span>
          <div class="channel__info">
            <a href="https://www.youtube.com/watch?v=Q-AdTMMSh1o" class="channel__name" target="_blank" rel="noopener">
              Royalistiq Shorts
              <svg class="verified" viewBox="0 0 24 24" aria-label="Geverifieerd"><path d="M12 2l2.4 1.8 3-.3 1 2.8 2.6 1.5-.9 2.9.9 2.9-2.6 1.5-1 2.8-3-.3L12 22l-2.4-1.8-3 .3-1-2.8-2.6-1.5.9-2.9-.9-2.9 2.6-1.5 1-2.8 3 .3L12 2zm-1.1 13.4l5.6-5.6-1.1-1.1-4.5 4.5-2.2-2.2-1.1 1.1 3.3 3.3z"/></svg>
            </a>
          </div>
        </div>

        <!-- Deze knoppen horen bij de YouTube-vormgeving en doen verder niets. -->
        <div class="video-actions">
          <a class="pill" href="https://www.youtube.com/watch?v=Q-AdTMMSh1o" target="_blank" rel="noopener">
            <svg viewBox="0 0 24 24"><path d="M15 5.63L20.66 12 15 18.37V15v-1h-1c-3.96 0-7.14 1-9.75 3.09 1.84-4.07 5.11-6.4 9.89-7.1l.86-.13V9V5.63M14 3v6C6.22 10.13 3.11 15.33 2 21c2.78-3.97 6.44-6 12-6v6l8-9-8-9z"/></svg>
            Delen
          </a>
          <span class="pill">
            <svg viewBox="0 0 24 24"><path d="M17 18v1H6v-1h11zm-.5-6.6l-.7-.7-3.8 3.7V4h-1v10.4l-3.8-3.8-.7.7 5 5 5-4.9z"/></svg>
            Downloaden
          </span>
        </div>
      </div>
    </section>

    <!-- ===== Reacties ===== -->
    <section class="comments">

      <div class="comments__head">
        <h2 class="comments__count" id="reacties">
          <?= $totaal ?> <?= $totaal === 1 ? 'Reactie' : 'Reacties' ?>
        </h2>

        <!-- Sorteren gebeurt met gewone links, dus ook zonder JavaScript. -->
        <details class="sort">
          <summary class="sort__btn">
            <svg viewBox="0 0 24 24"><path d="M21 6H3V5h18v1zm-6 5H3v1h12v-1zm-6 6H3v1h6v-1z"/></svg>
            Sorteren op
          </summary>
          <div class="sort__menu">
            <a class="sort__option <?= $sorteer === 'top' ? 'is-active' : '' ?>"
               href="index.php?sorteer=top#reacties">Topreacties</a>
            <a class="sort__option <?= $sorteer === 'nieuw' ? 'is-active' : '' ?>"
               href="index.php?sorteer=nieuw#reacties">Nieuwste eerst</a>
          </div>
        </details>
      </div>

      <!-- Het formulier om een reactie te plaatsen -->
      <?php
        $formParent = null;                                    // geen antwoord, maar een nieuwe reactie
        $toonFouten = $fouten && $foutParent === null;         // horen de foutmeldingen bij dit formulier?
        include __DIR__ . '/includes/formulier.php';
      ?>

      <!-- Alle reacties uit de database -->
      <?php if ($totaal === 0): ?>
        <p class="comments__state">Nog geen reacties. Wees de eerste die iets plaatst!</p>
      <?php else: ?>
        <ul class="comment-list">
          <?php foreach ($reacties as $reactie): ?>
            <?php include __DIR__ . '/includes/reactie.php'; ?>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>

    </section>
  </main>

  <script src="script.js"></script>
</body>
</html>
