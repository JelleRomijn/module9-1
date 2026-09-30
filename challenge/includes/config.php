<?php
/**
 * Databasegegevens.
 *
 * Pas de waarden hieronder aan als jouw MySQL anders draait:
 *
 *   MAMP   -> host 127.0.0.1, port 8889, user root, pass root
 *   XAMPP  -> host 127.0.0.1, port 3306, user root, pass ''
 *   Docker -> deze waarden komen uit docker-compose.yml
 */

return [
    'host' => getenv('DB_HOST') ?: '127.0.0.1',
    'port' => getenv('DB_PORT') ?: '3306',
    'naam' => getenv('DB_NAME') ?: 'youtube_comments',
    'user' => getenv('DB_USER') ?: 'root',
    'pass' => getenv('DB_PASS') !== false ? getenv('DB_PASS') : '',

    // Database en tabel automatisch aanmaken als ze nog niet bestaan.
    // Zet op false zodra je database.sql zelf hebt geïmporteerd.
    'install' => true,
];
