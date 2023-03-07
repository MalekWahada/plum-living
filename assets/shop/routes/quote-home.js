import {getComponent as getModal} from "../components/modal/modal";

export default {
  init() {
    function fixAlignment () {
      const cards = Array.from(document.querySelectorAll('.routing-card')).map(cardElmt => {
        return {
          cardElmt: cardElmt,
          hintElmt: cardElmt.querySelector('.routing-card__hint')
        };
      });

      const refHeight = cards.reduce((max, card) => {
        card.hintHeight = card.hintElmt ? card.hintElmt.offsetHeight : 0;
        return Math.max(max, card.hintHeight);
      }, 0);

      for(let i = 0 ; i < cards.length ; i++) {
        cards[i].cardElmt.style.setProperty('--padder-height', `${refHeight - cards[i].hintHeight}px`);
      }
    }

    window.addEventListener('resize', fixAlignment);

    fixAlignment();

    document.querySelector('.trigger-front-modal > a').addEventListener('click', function(e) {
      e.preventDefault();
      let frontModal = document.getElementById('dont-forget-front-modal');
      let modal = getModal(frontModal);
      frontModal.querySelector('.button').setAttribute('href', e.currentTarget.getAttribute('href'))
      modal && modal.show();
    });
  }
};
