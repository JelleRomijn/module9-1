#!/bin/bash
# Maakt een dump van de database-tabel in dump.sql
#
# Gebruik:  ./maak-dump.sh
#
# Draai je met Docker, dan gebruikt dit script de container.
# Heb je MAMP/XAMPP, pas dan MYSQLDUMP hieronder aan naar het juiste pad, bijv:
#   MAMP:  /Applications/MAMP/Library/bin/mysqldump --port=8889 -uroot -proot
#   XAMPP: /Applications/XAMPP/xamppfiles/bin/mysqldump -uroot

set -e
cd "$(dirname "$0")"

if docker compose ps db >/dev/null 2>&1 && [ -n "$(docker compose ps -q db)" ]; then
    echo "Dump maken via Docker..."
    docker compose exec -T db mysqldump -uroot -proot --databases youtube_comments \
        --no-tablespaces 2>/dev/null > dump.sql
else
    MYSQLDUMP="mysqldump -uroot -proot"
    echo "Dump maken via $MYSQLDUMP..."
    $MYSQLDUMP --databases youtube_comments --no-tablespaces > dump.sql
fi

echo "Klaar: dump.sql ($(wc -l < dump.sql) regels)"
