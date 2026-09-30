-- =========================================================
--  Database voor de reactiesectie
--
--  Importeer dit bestand in phpMyAdmin, of laat includes/db.php
--  de tabel automatisch aanmaken bij het eerste bezoek.
-- =========================================================

CREATE DATABASE IF NOT EXISTS `youtube_comments`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `youtube_comments`;

-- utf8mb4 is nodig, anders kun je geen emoji's opslaan.
CREATE TABLE IF NOT EXISTS `reacties` (

  -- Elke reactie krijgt een eigen nummer. AUTO_INCREMENT telt vanzelf op.
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,

  -- Leeg (NULL) = gewone reactie. Staat er een nummer in, dan is het
  -- een antwoord op de reactie met dat id.
  `parent_id`  INT UNSIGNED NULL DEFAULT NULL,

  -- De naam uit het formulier. 60 tekens is ruim genoeg voor een naam.
  `naam`       VARCHAR(60)  NOT NULL,

  -- Het e-mailadres. 120 tekens, want adressen kunnen lang zijn.
  -- Dit wordt NIET op de pagina getoond, net als bij een echte site.
  `email`      VARCHAR(120) NOT NULL,

  -- De reactie zelf. TEXT, want dat mag langer zijn dan VARCHAR.
  `reactie`    TEXT         NOT NULL,

  -- Aantal likes. UNSIGNED = kan niet negatief worden.
  `likes`      INT UNSIGNED NOT NULL DEFAULT 0,

  -- Moment van plaatsen. Wordt automatisch gevuld met de huidige tijd.
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  KEY `idx_parent` (`parent_id`),
  KEY `idx_created` (`created_at`),

  -- Een antwoord hoort bij een bestaande reactie. Verdwijnt de hoofdreactie,
  -- dan verdwijnen de antwoorden automatisch mee (ON DELETE CASCADE).
  CONSTRAINT `fk_reactie_parent`
    FOREIGN KEY (`parent_id`) REFERENCES `reacties` (`id`)
    ON DELETE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
