<?php
/**
 * Het reactieformulier.
 *
 * Dit bestand wordt twee keer gebruikt:
 *   - bovenaan, om een nieuwe reactie te plaatsen  ($formParent is null)
 *   - onder een reactie, om te antwoorden          ($formParent is het id van die reactie)
 *
 * Variabelen die index.php meegeeft:
 *   $formParent  null of het id van de reactie waarop je antwoordt
 *   $toonFouten  true als de foutmeldingen bij dit formulier horen
 *   $waarden     wat er in de velden moet komen te staan
 *   $fouten      de foutmeldingen
 */

$isAntwoord = $formParent !== null;

// Alleen het formulier dat is verzonden laat de foutmeldingen en de
// ingetypte tekst zien. Het andere formulier blijft leeg.
$velden = $toonFouten ? $waarden : [
    'naam'    => $_SESSION['naam']  ?? '',
    'email'   => $_SESSION['email'] ?? '',
    'reactie' => '',
];
$formFouten = $toonFouten ? $fouten : [];
?>

<!-- novalidate zet de controle van de browser uit, zodat je goed kunt zien
     dat PHP zelf ook controleert wat er binnenkomt. -->
<form class="composer <?= $isAntwoord ? 'composer--antwoord' : '' ?>"
      method="post"
      action="index.php?sorteer=<?= e($sorteer) ?><?= $isAntwoord ? '&reageer_op=' . (int) $formParent : '' ?>#<?= $isAntwoord ? 'reactie-' . (int) $formParent : 'reactieformulier' ?>"
      id="<?= $isAntwoord ? 'antwoordformulier' : 'reactieformulier' ?>"
      novalidate>

  <input type="hidden" name="actie" value="plaatsen">
  <?php if ($isAntwoord): ?>
    <input type="hidden" name="parent_id" value="<?= (int) $formParent ?>">
  <?php endif; ?>

  <span class="avatar <?= $isAntwoord ? 'avatar--sm' : '' ?>"
        style="background: <?= e(avatarKleur($velden['naam'])) ?>"><?= e(initiaal($velden['naam'])) ?></span>

  <div class="composer__body">

    <?php if ($formFouten): ?>
      <p class="melding melding--fout">Controleer je gegevens: er is nog iets niet goed ingevuld.</p>
    <?php endif; ?>

    <div class="composer__rij">
      <label class="veld">
        <span class="veld__label">Naam</span>
        <input type="text"
               name="naam"
               maxlength="60"
               placeholder="Je naam"
               value="<?= e($velden['naam']) ?>"
               class="veld__input <?= isset($formFouten['naam']) ? 'is-fout' : '' ?>">
        <?php if (isset($formFouten['naam'])): ?>
          <span class="veld__fout"><?= e($formFouten['naam']) ?></span>
        <?php endif; ?>
      </label>

      <label class="veld">
        <span class="veld__label">E-mailadres <span class="veld__uitleg">(wordt niet getoond)</span></span>
        <input type="email"
               name="email"
               maxlength="120"
               placeholder="jij@voorbeeld.nl"
               value="<?= e($velden['email']) ?>"
               class="veld__input <?= isset($formFouten['email']) ? 'is-fout' : '' ?>">
        <?php if (isset($formFouten['email'])): ?>
          <span class="veld__fout"><?= e($formFouten['email']) ?></span>
        <?php endif; ?>
      </label>
    </div>

    <div class="composer__field">
      <textarea name="reactie"
                rows="1"
                maxlength="500"
                class="composer__input <?= isset($formFouten['reactie']) ? 'is-fout' : '' ?>"
                placeholder="<?= $isAntwoord ? 'Antwoorden...' : 'Voeg een reactie toe...' ?>"
                aria-label="Je reactie"><?= e($velden['reactie']) ?></textarea>
    </div>

    <?php if (isset($formFouten['reactie'])): ?>
      <span class="veld__fout"><?= e($formFouten['reactie']) ?></span>
    <?php endif; ?>

    <div class="composer__actions">
      <button type="button" class="emoji-btn" aria-label="Emoji">
        <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 19c-4.96 0-9-4.04-9-9s4.04-9 9-9 9 4.04 9 9-4.04 9-9 9zm-3.5-9.5c.83 0 1.5-.67 1.5-1.5S9.33 8.5 8.5 8.5 7 9.17 7 10s.67 1.5 1.5 1.5zm7 0c.83 0 1.5-.67 1.5-1.5s-.67-1.5-1.5-1.5-1.5.67-1.5 1.5.67 1.5 1.5 1.5zM12 17.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/></svg>
      </button>

      <span class="composer__counter">0/500</span>

      <?php if ($isAntwoord): ?>
        <a class="btn btn--ghost" href="index.php?sorteer=<?= e($sorteer) ?>#reactie-<?= (int) $formParent ?>">Annuleren</a>
      <?php else: ?>
        <button type="reset" class="btn btn--ghost">Wissen</button>
      <?php endif; ?>

      <button type="submit" class="btn btn--primary">Reageren</button>
    </div>
  </div>
</form>
