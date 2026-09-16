<?php
/**
 * Toont één reactie, met de antwoorden eronder.
 *
 * Variabelen die index.php meegeeft:
 *   $reactie    de rij uit de database (met $reactie['antwoorden'])
 *   $reageerOp  het id waarvan het antwoordformulier open staat
 *   $mijnLikes  de reacties die jij geliket hebt
 *   $sorteer    de gekozen sorteervolgorde, zodat links die onthouden
 */

$id      = (int) $reactie['id'];
$geliket = in_array($id, $mijnLikes, true);
?>
<li>
  <div class="comment" id="reactie-<?= $id ?>">
    <span class="avatar" style="background: <?= e(avatarKleur($reactie['naam'])) ?>"><?= e(initiaal($reactie['naam'])) ?></span>

    <div class="comment__body">
      <div class="comment__head">
        <span class="comment__author">@<?= e($reactie['naam']) ?></span>
        <!-- "3 minuten geleden" komt van de composer-package Carbon -->
        <span class="comment__time"><?= e(hoeLangGeleden($reactie['created_at'])) ?></span>
      </div>

      <p class="comment__text"><?= e($reactie['reactie']) ?></p>

      <div class="comment__actions">
        <!-- De like-knop is een klein formulier: hij stuurt het id naar deze pagina. -->
        <form method="post" action="index.php?sorteer=<?= e($sorteer) ?>#reactie-<?= $id ?>" class="like-form">
          <input type="hidden" name="actie" value="like">
          <input type="hidden" name="id" value="<?= $id ?>">
          <button type="submit" class="action <?= $geliket ? 'is-active' : '' ?>"
                  aria-label="<?= $geliket ? 'Like weghalen' : 'Vind ik leuk' ?>">
            <svg viewBox="0 0 24 24"><path d="M18.77 11h-4.23l1.52-4.94C16.38 5.03 15.54 4 14.38 4c-.58 0-1.14.24-1.52.65L7 11H3v10h4h1h9.43c1.06 0 1.98-.67 2.19-1.61l1.34-6C21.23 12.15 20.18 11 18.77 11zM7 20H4v-8h3V20zm12.98-7.83l-1.34 6C18.54 18.65 18.03 19 17.43 19H8v-8.61l5.6-6.06C13.79 4.12 14.08 4 14.38 4c.26 0 .5.11.63.3.07.1.15.26.09.47l-1.52 4.94L13.18 11h1.36h4.23c.41 0 .8.17 1.03.46.12.15.25.4.18.71z"/></svg>
            <span class="action__count"><?= e(korteTelling((int) $reactie['likes'])) ?></span>
          </button>
        </form>

        <a class="action action--reply"
           href="index.php?sorteer=<?= e($sorteer) ?>&reageer_op=<?= $id ?>#reactie-<?= $id ?>">Reageren</a>
      </div>

      <?php /* Het antwoordformulier staat alleen open onder de reactie waarop je klikte. */ ?>
      <?php if ($reageerOp === $id): ?>
        <?php
          $formParent = $id;
          $toonFouten = $fouten && $foutParent === $id;
          include __DIR__ . '/formulier.php';
        ?>
      <?php endif; ?>

      <?php if ($reactie['antwoorden']): ?>
        <p class="replies-count">
          <?= count($reactie['antwoorden']) ?>
          <?= count($reactie['antwoorden']) === 1 ? 'antwoord' : 'antwoorden' ?>
        </p>

        <ul class="replies">
          <?php foreach ($reactie['antwoorden'] as $antwoord): ?>
            <?php $antwoordId = (int) $antwoord['id']; ?>
            <li>
              <div class="comment" id="reactie-<?= $antwoordId ?>">
                <span class="avatar avatar--sm" style="background: <?= e(avatarKleur($antwoord['naam'])) ?>"><?= e(initiaal($antwoord['naam'])) ?></span>

                <div class="comment__body">
                  <div class="comment__head">
                    <span class="comment__author">@<?= e($antwoord['naam']) ?></span>
                    <span class="comment__time"><?= e(hoeLangGeleden($antwoord['created_at'])) ?></span>
                  </div>

                  <p class="comment__text"><?= e($antwoord['reactie']) ?></p>

                  <div class="comment__actions">
                    <form method="post" action="index.php?sorteer=<?= e($sorteer) ?>#reactie-<?= $antwoordId ?>" class="like-form">
                      <input type="hidden" name="actie" value="like">
                      <input type="hidden" name="id" value="<?= $antwoordId ?>">
                      <button type="submit" class="action <?= in_array($antwoordId, $mijnLikes, true) ? 'is-active' : '' ?>"
                              aria-label="Vind ik leuk">
                        <svg viewBox="0 0 24 24"><path d="M18.77 11h-4.23l1.52-4.94C16.38 5.03 15.54 4 14.38 4c-.58 0-1.14.24-1.52.65L7 11H3v10h4h1h9.43c1.06 0 1.98-.67 2.19-1.61l1.34-6C21.23 12.15 20.18 11 18.77 11zM7 20H4v-8h3V20zm12.98-7.83l-1.34 6C18.54 18.65 18.03 19 17.43 19H8v-8.61l5.6-6.06C13.79 4.12 14.08 4 14.38 4c.26 0 .5.11.63.3.07.1.15.26.09.47l-1.52 4.94L13.18 11h1.36h4.23c.41 0 .8.17 1.03.46.12.15.25.4.18.71z"/></svg>
                        <span class="action__count"><?= e(korteTelling((int) $antwoord['likes'])) ?></span>
                      </button>
                    </form>

                    <a class="action action--reply"
                       href="index.php?sorteer=<?= e($sorteer) ?>&reageer_op=<?= $antwoordId ?>#reactie-<?= $antwoordId ?>">Reageren</a>
                  </div>

                  <?php if ($reageerOp === $antwoordId): ?>
                    <?php
                      $formParent = $antwoordId;
                      $toonFouten = $fouten && $foutParent === $antwoordId;
                      include __DIR__ . '/formulier.php';
                    ?>
                  <?php endif; ?>
                </div>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  </div>
</li>
