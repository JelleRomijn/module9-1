<?php
/**
 * Verbinding met de MySQL-database.
 *
 * We gebruiken PDO. Dat is de nette manier om met een database te praten,
 * omdat je er "prepared statements" mee kunt gebruiken (zie functies.php).
 */
declare(strict_types=1);

function verbinding(): PDO
{
    // static betekent: de verbinding wordt maar één keer gemaakt,
    // ook als je deze functie meerdere keren aanroept.
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $config = require __DIR__ . '/config.php';

    $opties = [
        // Bij een fout krijgen we een duidelijke melding in plaats van stilte.
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        // Rijen komen terug als array met kolomnamen: $rij['naam']
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // Echte prepared statements, dus geen SQL-injectie.
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    // utf8mb4 zorgt dat emoji's goed opgeslagen worden.
    $dsn = sprintf('mysql:host=%s;port=%s;charset=utf8mb4', $config['host'], $config['port']);

    try {
        if (!empty($config['install'])) {
            // Database en tabel aanmaken als ze er nog niet zijn.
            $pdo = new PDO($dsn, $config['user'], $config['pass'], $opties);
            $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . $config['naam'] . '`
                        DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
            $pdo->exec('USE `' . $config['naam'] . '`');
            $pdo->exec(
                'CREATE TABLE IF NOT EXISTS `reacties` (
                    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `parent_id`  INT UNSIGNED NULL DEFAULT NULL,
                    `naam`       VARCHAR(60)  NOT NULL,
                    `email`      VARCHAR(120) NOT NULL,
                    `reactie`    TEXT         NOT NULL,
                    `likes`      INT UNSIGNED NOT NULL DEFAULT 0,
                    `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    KEY `idx_parent` (`parent_id`),
                    KEY `idx_created` (`created_at`),
                    CONSTRAINT `fk_reactie_parent`
                        FOREIGN KEY (`parent_id`) REFERENCES `reacties` (`id`)
                        ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
            );
        } else {
            $pdo = new PDO($dsn . ';dbname=' . $config['naam'], $config['user'], $config['pass'], $opties);
        }
    } catch (PDOException $e) {
        // Geen verbinding? Laat een begrijpelijke melding zien in plaats van een witte pagina.
        exit('<p style="font-family:sans-serif;color:#c00;padding:24px">
                Geen verbinding met de database. Draait MySQL, en kloppen de gegevens in
                <code>includes/config.php</code>?<br><br>' . htmlspecialchars($e->getMessage()) . '</p>');
    }

    return $pdo;
}
