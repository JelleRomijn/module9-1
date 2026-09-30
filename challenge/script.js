/* =========================================================
   Kleine extraatjes voor het formulier.

   De pagina werkt ook zonder JavaScript: het formulier wordt
   gewoon naar index.php gestuurd en PHP doet de rest.
   Dit bestand maakt het alleen wat prettiger in gebruik.
   ========================================================= */

const EMOJIS = ['😀', '😂', '🤣', '😊', '😍', '😎', '🤔', '😭', '😱', '🥳',
                '👍', '👎', '👏', '🙏', '💪', '🔥', '💥', '💣', '🎮', '🏆',
                '❤️', '💯', '⭐', '⚡', '🎉', '🤯', '😅', '🥲', '👀', '🫡'];

/* Elk formulier op de pagina krijgt dezelfde extraatjes. */
document.querySelectorAll('.composer').forEach(maakFormulierSlim);

function maakFormulierSlim(formulier) {
  const tekstvak = formulier.querySelector('textarea');
  const teller   = formulier.querySelector('.composer__counter');
  const emojiKnop = formulier.querySelector('.emoji-btn');

  if (!tekstvak) return;

  /* --- Het tekstvak groeit mee met de tekst --- */
  function pasHoogteAan() {
    tekstvak.style.height = 'auto';
    tekstvak.style.height = tekstvak.scrollHeight + 'px';
  }

  /* --- Teller: 0/500 --- */
  function werkTellerBij() {
    if (teller) teller.textContent = tekstvak.value.length + '/500';
  }

  tekstvak.addEventListener('input', () => {
    pasHoogteAan();
    werkTellerBij();
  });

  pasHoogteAan();
  werkTellerBij();

  /* --- Emoji-keuzelijst --- */
  if (emojiKnop) {
    const lijst = document.createElement('div');
    lijst.className = 'emoji-picker';
    lijst.hidden = true;
    lijst.innerHTML = EMOJIS
      .map((emoji) => `<button type="button" data-emoji="${emoji}">${emoji}</button>`)
      .join('');

    emojiKnop.insertAdjacentElement('afterend', lijst);

    emojiKnop.addEventListener('click', (e) => {
      e.stopPropagation();
      const wasVerborgen = lijst.hidden;
      sluitAlleEmojiLijsten();
      lijst.hidden = !wasVerborgen;
    });

    lijst.addEventListener('click', (e) => {
      e.stopPropagation();               // de lijst blijft open zodat je er meer kunt kiezen

      const knop = e.target.closest('[data-emoji]');
      if (!knop) return;

      // De emoji komt op de plek van de cursor te staan.
      const start = tekstvak.selectionStart ?? tekstvak.value.length;
      const eind  = tekstvak.selectionEnd ?? tekstvak.value.length;
      const emoji = knop.dataset.emoji;

      tekstvak.value = tekstvak.value.slice(0, start) + emoji + tekstvak.value.slice(eind);
      tekstvak.focus();
      tekstvak.setSelectionRange(start + emoji.length, start + emoji.length);

      pasHoogteAan();
      werkTellerBij();
    });
  }

  /* --- Ctrl/Cmd + Enter verstuurt het formulier --- */
  tekstvak.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
      e.preventDefault();
      formulier.requestSubmit();
    }
  });

  /* --- Na "Wissen" ook de teller en de hoogte terugzetten --- */
  formulier.addEventListener('reset', () => {
    setTimeout(() => {
      pasHoogteAan();
      werkTellerBij();
    }, 0);
  });
}

function sluitAlleEmojiLijsten() {
  document.querySelectorAll('.emoji-picker').forEach((lijst) => {
    lijst.hidden = true;
  });
}

/* Klik je ergens anders, of druk je op Escape, dan gaat de lijst dicht. */
document.addEventListener('click', sluitAlleEmojiLijsten);

document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') sluitAlleEmojiLijsten();
});

/* Staat er een antwoordformulier open? Zet de cursor er meteen in. */
const antwoordformulier = document.getElementById('antwoordformulier');
if (antwoordformulier) {
  antwoordformulier.querySelector('textarea').focus();
}
