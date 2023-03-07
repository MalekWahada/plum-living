import { getComponent as getModal } from '../components/modal/modal.js';
import { getRoute } from '../abstract/js-toolbox/router.js';


export default {
  init() {
    const commonRoute = getRoute('common');
    const modalTogglerElmt = document.querySelector('.js-toggle-modal');

    if(modalTogglerElmt) {
      const modalRootElmt = document.getElementById('plum-cart-confirmation-modal');
      const confirmationModal = getModal(modalRootElmt);

      const showOriginalNumberField = ctxElmt => {
        const fieldElmt = ctxElmt.querySelector('.original-order-number-field');
        const widgetElmt = document.getElementById('app_cart_validation_originalOrderNumber');

        widgetElmt.required = true;

        fieldElmt.ontransitionend = (ev) => {
          if(ev.target == ev.currentTarget && ev.propertyName == 'height') {
            fieldElmt.style.height = '';
            fieldElmt.style.opacity = '';
            fieldElmt.ontransitionend = null;
          }
        };

        fieldElmt.style.height = fieldElmt.scrollHeight + 'px';
        fieldElmt.style.opacity = 1;
      }

      const hideOriginalNumberField = ctxElmt => {
        const fieldElmt = ctxElmt.querySelector('.original-order-number-field');
        const widgetElmt = document.getElementById('app_cart_validation_originalOrderNumber');

        widgetElmt.required = false;

        fieldElmt.ontransitionend = (ev) => {
          if(ev.target == ev.currentTarget && ev.propertyName == 'height') {
            fieldElmt.ontransitionend = null;
          }
        };

        fieldElmt.style.height = fieldElmt.offsetHeight + 'px';
        fieldElmt.style.opacity = 1;

        setTimeout(() => {
          fieldElmt.style.height = '0px';
          fieldElmt.style.opacity = 0;
        }, 50);
      }

      modalRootElmt.addEventListener('change', ev => {
        if(ev.target.id == 'app_cart_validation_validateItemsCount') { // It's the checkbox
          document.getElementById('order-proceed').disabled = !ev.target.checked;
        }
        else if(ev.target.name == 'app_cart_validation[hasOriginalOrder]') { // It's a radio
          const relatedToPreviousOrder = parseInt(ev.target.value) === 1;
          relatedToPreviousOrder
            ? showOriginalNumberField(modalRootElmt)
            : hideOriginalNumberField(modalRootElmt);
        }
      });

      confirmationModal.on('loadContent', (ev) => {
        commonRoute.initWidgets(ev.target.querySelector('.ui-modal__dialog'));
        document.getElementById('order-proceed').checked = false;
        document.getElementById('app_cart_validation_hasOriginalOrder_0').checked = true;
      });

      modalTogglerElmt.addEventListener('click', (ev) => {
        ev.preventDefault();
        confirmationModal.show();
      });
    }

    //Modal share cart
    const shareCartModalTrigger = document.querySelector('.cart__email-sharing-button');
    shareCartModalTrigger && shareCartModalTrigger.addEventListener('click', onClickOpenshareModal);

    function onClickOpenshareModal (ev) {
      const shareModal = getModal(document.querySelector('.cart-share-modal'));

      if(shareModal) {
        shareModal.on('loadContent', () => {
          commonRoute.initWidgets(shareModal.dialogElmt);
        });
      }

      shareModal.elmt.dataset.sourceUrl = ev.currentTarget.dataset.modalSourceUrl;
      shareModal.show();
    }
  },

  finalize() {},
};
