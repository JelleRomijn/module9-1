# Reactiesectie in YouTube-stijl (PHP + MySQL)

Een nagebouwde YouTube-videopagina met een reactieformulier. Alles gebeurt op
**één pagina**: je ziet de video, je vult het formulier in, en na het verzenden
kom je op dezelfde pagina terug met jouw reactie erbij.

## Bestanden

| Bestand                    | Wat het doet                                                        |
|----------------------------|---------------------------------------------------------------------|
| `index.php`                | De hele pagina: formulier verwerken, gegevens ophalen, alles tonen   |
| `includes/db.php`          | Verbinding met MySQL (PDO), maakt de tabel aan als die er nog niet is |
| `includes/config.php`      | Databasegegevens - hier pas je host, gebruiker en wachtwoord aan      |
| `includes/functies.php`    | Hulpfuncties: schoonmaken, validatie-hulp, tijd, avatar, queries      |
| `includes/formulier.php`   | Het reactieformulier (ook gebruikt voor antwoorden)                   |
| `includes/reactie.php`     | Hoe één reactie met haar antwoorden getoond wordt                     |
| `style.css`                | Vormgeving (donkere YouTube-stijl)                                    |
| `script.js`                | Extraatjes: emoji's, tekstteller. De pagina werkt ook zonder          |
| `database.sql`             | De tabel, met uitleg per kolom                                        |
| `dump.sql`                 | Dump van de database-tabel (zie hieronder)                            |
| `maak-dump.sh`             | Maakt `dump.sql` opnieuw                                              |
| `composer.json` / `vendor/`| De composer-package Carbon                                            |
| `docker-compose.yml`       | Start PHP + MySQL + phpMyAdmin in één commando                        |

## Starten

### Optie 1 - Docker

```bash
docker compose up -d
```

* Site: <http://localhost:8090>
* phpMyAdmin: <http://localhost:8091> (gebruiker `root`, wachtwoord `root`)
* Stoppen: `docker compose down` (met `-v` wis je ook de database)

### Optie 2 - MAMP of XAMPP

1. Zet deze map in de webmap (`htdocs` bij XAMPP, `Sites` bij MAMP).
2. Start Apache en MySQL.
3. Pas zo nodig `includes/config.php` aan:
   * **MAMP**: poort `8889`, gebruiker `root`, wachtwoord `root`
   * **XAMPP**: poort `3306`, gebruiker `root`, wachtwoord leeg
4. Open `index.php` in je browser. De database en tabel worden automatisch aangemaakt.

Liever zelf importeren? Draai dan `database.sql` of `dump.sql` in phpMyAdmin en zet
`'install' => false` in `includes/config.php`.

> Open de pagina via `http://localhost/...` en niet door het bestand te
> dubbelklikken (`file://`), want dan werkt PHP niet.

## De database

Eén tabel: `reacties`.

| Kolom        | Type                | Waarom dit type                                  |
|--------------|---------------------|--------------------------------------------------|
| `id`         | INT UNSIGNED, A_I   | Uniek nummer per reactie                          |
| `parent_id`  | INT UNSIGNED, NULL  | Leeg = hoofdreactie, anders het id van de reactie waarop geantwoord wordt |
| `naam`       | VARCHAR(60)         | Een naam is kort, 60 tekens is ruim genoeg        |
| `email`      | VARCHAR(120)        | E-mailadressen kunnen langer zijn dan een naam    |
| `reactie`    | TEXT                | Mag langer zijn dan een VARCHAR aankan            |
| `likes`      | INT UNSIGNED        | Een aantal kan niet negatief zijn                 |
| `created_at` | DATETIME            | Moment van plaatsen, vult zichzelf (`CURRENT_TIMESTAMP`) |

`parent_id` verwijst naar `id` in dezelfde tabel, met `ON DELETE CASCADE`: verwijder
je een hoofdreactie, dan verdwijnen de antwoorden mee. De tabel gebruikt `utf8mb4`,
zodat emoji's goed opgeslagen worden.

### Dump

`dump.sql` is de dump van de tabel. Heb je zelf reacties geplaatst en wil je die
in de dump hebben? Draai dan:

```bash
./maak-dump.sh
```

## Controle van het formulier (validatie)

De controle gebeurt in **PHP**, dus op de server. Een controle in de browser
(`type="email"`) is handig voor de bezoeker, maar die kan iemand omzeilen - daarom
staat er `novalidate` op het formulier en controleert PHP alles opnieuw:

| Veld    | Controle                                                        |
|---------|-----------------------------------------------------------------|
| naam    | mag niet leeg zijn, maximaal 60 tekens                           |
| email   | mag niet leeg zijn, moet een geldig adres zijn (`filter_var` met `FILTER_VALIDATE_EMAIL`), maximaal 120 tekens |
| reactie | mag niet leeg zijn, maximaal 500 tekens                          |

Klopt er iets niet, dan wordt er **niets opgeslagen** en kom je terug op het
formulier met een rode melding bij het foute veld. Wat je al had ingetypt blijft staan.

### Geen HTML of JavaScript in de reacties

Alle binnenkomende tekst gaat eerst door `schoonmaken()` in `includes/functies.php`:

```php
$data = trim($data);                                      // spaties eraf
$data = stripslashes($data);                              // backslashes eraf
$data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');     // < > " ' & onschadelijk
```

Typ je `<script>alert('hoi')</script>`, dan komt dat als gewone tekst in de database
en op het scherm - het wordt niet uitgevoerd. Bij het tonen gaat de tekst nog een
tweede keer door `htmlspecialchars()` (de functie `e()`).

Alle queries gebruiken **prepared statements**, dus SQL-injectie kan ook niet.

## Composer-package

Deze opdracht gebruikt [Carbon](https://carbon.nesbot.com/) (`nesbot/carbon`).
Daarmee wordt `2026-09-11 09:12:00` uit de database getoond als
**"3 minuten geleden"**, in het Nederlands:

```php
Carbon::setLocale('nl');
Carbon::parse($datum)->diffForHumans();
```

Is de map `vendor/` er niet (bijvoorbeeld na het kopiëren van het project)? Draai dan:

```bash
composer install
```

## Wat je op de pagina kunt

* De video bekijken (YouTube-embed)
* Een reactie plaatsen met naam, e-mailadres en tekst
* Antwoorden op een reactie (`Reageren` onder een reactie)
* Reacties liken; nog een keer klikken haalt je like weer weg
* Sorteren op topreacties of nieuwste eerst
* Emoji's invoegen (extraatje van `script.js`)

Je e-mailadres wordt wel opgeslagen, maar **niet** op de pagina getoond - net als
bij een echte website.
