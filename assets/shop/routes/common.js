import Cookies from 'js-cookie';
import '../abstract/swiper/swiper';

import initAddressBookSelects from '../components/address-book-select/address-book-select';
import initClipboard from '../abstract/clipboard/clipboard';
import initFlashes from '../components/flash/flash';
import initForms from '../components/forms/forms';
import initFormFields from '../components/form-fields/form-fields';
import initModals, { getComponent as getModal } from '../components/modal/modal';
import initRippleButtons from '../components/ripple-button/ripple-button';
import initStepsNavs from '../components/steps-nav/steps-nav';
import initTabbedNavs from '../components/tabbed-nav/tabbed-nav';
import initCartSummary from '../components/plum-cart-summary/plum-cart-summary';
import initPlumHeader from '../components/plum-header/plum-header';
import initPlumMenu from '../components/plum-menu/plum-menu';
import initPlumMenuToggles from '../components/plum-menu-toggle/plum-menu-toggle';
import { createFlash } from '../components/flash/flash';
import autosize from '../abstract/autosize/autosize';


export default {
  initWidgets(rootElmt, initHiddenFields = false) {
    initFlashes(rootElmt);
    initForms(rootElmt);
    initFormFields(rootElmt, initHiddenFields);
    initModals(rootElmt);
    initRippleButtons(rootElmt);
    initStepsNavs(rootElmt);
    initTabbedNavs(rootElmt);
    initAddressBookSelects(rootElmt);
    initClipboard(rootElmt);
  },

  init({ _rid }) {
    this.initWidgets(document);

    const plumCartSummaryElmt = document.querySelector('.plum-cart-summary');
    const plumHeaderElmt = document.querySelector('.plum-header');
    const plumMenuElmt = document.querySelector('.plum-menu');
    const plumNewsLetterForm = document.querySelector('.footer-newsletter-form');

    if (plumCartSummaryElmt) {
      const plumCartSummary = initCartSummary(plumCartSummaryElmt);

      plumCartSummary.on('update', (ev) => {
        const headerCartButtonBadge = document.querySelector('.sylius-cart-button .cart-toggle__count');
        headerCartButtonBadge && (headerCartButtonBadge.innerHTML = ev.detail.cartData.itemCount);
        this.initWidgets(ev.detail.itemsContainer);
      });

      document.querySelectorAll('.sylius-cart-button').forEach((elmt) => {
        elmt.addEventListener('click', (e) => {
          e.preventDefault();
          plumCartSummary.show();
        });
      });
    }

    if (plumHeaderElmt) {
      const plumHeader = initPlumHeader(plumHeaderElmt, {
        expandOnScrollUp: !document.body.classList.contains('app_customer_project_show'),
      });
      const tunnelSidebar = document.querySelector('.tunnel-sidebar');

      plumHeader.on('contract', () => {
        document.getElementById('HeaderMenuToggle').classList.add('plum-menu-toggle--when-header-is-compact');
        if (tunnelSidebar) {
          tunnelSidebar.classList.add('tunnel-sidebar--when-header-is-compact');
        }
      });

      plumHeader.on('expand', () => {
        document.getElementById('HeaderMenuToggle').classList.remove('plum-menu-toggle--when-header-is-compact');
        if (tunnelSidebar) {
          tunnelSidebar.classList.remove('tunnel-sidebar--when-header-is-compact');
        }
      });

      // Devis modal
      const devisElmt = document.querySelector('.devis-trigger');

      if(devisElmt) {
        devisElmt.addEventListener('click', (e) => {
          e.preventDefault();

          const devisModal = getModal(document.getElementById('devis-modal'));
          devisModal.show();
        });
      }
    }

    if (plumMenuElmt) {
      const plumMenu = initPlumMenu(plumMenuElmt);

      initPlumMenuToggles({
        plumMenu,
        elmtList: document.querySelectorAll('.plum-menu-toggle'),
      });
    }

    if (plumNewsLetterForm) {
      plumNewsLetterForm.addEventListener('submit', (event) => {
        event.preventDefault();

        $.ajax({
          url: plumNewsLetterForm.action,
          type: plumNewsLetterForm.method,
          data: $('.footer-newsletter-form').serialize(),
        })
          .done((response) => {
            if (response.hasOwnProperty('message')) {
              createFlash('success', response.message);
            }
          })
          .fail((response) => {
            if (response.responseJSON.hasOwnProperty('errors')) {
              const errors = JSON.parse(response.responseJSON.errors);
              let message = '';

              $(errors).each((key, value) => {
                message += `${value}`;
              });
              createFlash('error', message);
            }
          });
      });
    }

    // Samples Pop-in
    const samplesPopin = document.querySelector('.popin__samples');
    const samplesPopinCloseButton = document.querySelector('.popin__samples-close-button');

    function popinShow() {
      samplesPopin.classList.add('popin__samples-active');
    }

    function popinClose() {
      Cookies.set('samplesPopinViewed', Date.now(), { expires: 3 });
      samplesPopin.classList.remove('popin__samples-active');
      setTimeout(() => { samplesPopin.remove(); }, 1000);
    }

    if (Cookies.get('samplesPopinViewed') === undefined) {
      setTimeout(() => {
        popinShow();
      }, 3000);
    }
    else {
      setTimeout(() => { samplesPopin.remove(); }, 1000);
    }

    if (samplesPopinCloseButton) {
      samplesPopinCloseButton.addEventListener('click', popinClose);
    }

    let customerTypeSwitcher = document.querySelector('#customerTypeSwitchContainer select:first-of-type');
    if (customerTypeSwitcher) {
      customerTypeSwitcher.addEventListener('change', ev => {
        let proChoicesContainer = document.getElementById('proChoicesAvailable');
        let proFieldsContainer = document.getElementById('ProFieldsContainer');
        if (!proChoicesContainer || !proFieldsContainer) {
          return;
        }
        let b2bProgramSubscription = document.getElementById('b2b-program-subscription');
        let b2bProgramSubscriptionField = document.querySelector('#b2b-program-subscription > input');

        let proChoices = JSON.parse(proChoicesContainer.dataset.choices);
        if (proChoices.includes(ev.target.value)) {
          proFieldsContainer.classList.remove('u-hidden');
          b2bProgramSubscription.classList.remove('u-hidden');
          b2bProgramSubscriptionField.checked = true;
        } else {
          proFieldsContainer.classList.add('u-hidden');
          b2bProgramSubscription.classList.add('u-hidden');
          b2bProgramSubscriptionField.checked = false;
        }
        initFormFields();
      });
    }
  },

  finalize() {},
};


// Mosaic Animation Transle
const ratioImg = 0.1;
const images = document.querySelectorAll('.img-anim');

const optionsImg = {
  root: null,
  rootMargin: '10% 0px',
  threshold: 0.1,
};

function handle(entries) {
  entries.forEach((entry) => {
    const currentY = entry.boundingClientRect.y;
    const { isIntersecting, target: img } = entry;

    if (currentY > ratioImg && !isIntersecting) {
      img.classList.add('is-out');
      img.classList.add('is-out--down');
      img.classList.remove('is-in');
    } else
    if ((currentY > ratioImg && isIntersecting) || (currentY < ratioImg && isIntersecting)) {
      img.classList.add('is-in');
      img.classList.remove('is-out');
      img.classList.remove('is-out--down');
      img.classList.remove('is-out--up');
    } else
    if (currentY < ratioImg && !isIntersecting) {
      img.classList.add('is-out');
      img.classList.add('is-out--up');
      img.classList.remove('is-in');
    }
  });
}

const observerImg = new IntersectionObserver(handle, optionsImg);
images.forEach((image) => {
  observerImg.observe(image);
});


// Slider Animation Translate
const ratioSlider = 0.1;
const sliders = document.querySelectorAll('.cms-slider__container');

const optionsSlider = {
  root: null,
  rootMargin: '0px',
  threshold: 0.1,
};

function sliderTrans(entries) {
  entries.forEach((entry) => {
    if (entry.intersectionRatio > ratioSlider) {
      entry.target.classList.add('slider-in');
      entry.target.classList.remove('slider-out');
    } else {
      entry.target.classList.add('slider-out');
      entry.target.classList.remove('slider-in');
    }
  });
}

const observerSlider = new IntersectionObserver(sliderTrans, optionsSlider);
sliders.forEach((slider) => {
  observerSlider.observe(slider);
});

autosize(document.querySelectorAll('.autosize'));

// Scroll top button

const scrollToTopBtn = document.getElementById('scrollTopBtn');

const rootElement = document.documentElement;

function scrollToTop() {
  rootElement.scrollTo({
    top: 0,
    behavior: 'smooth',
  });
}

if (scrollToTopBtn) {
  scrollToTopBtn.addEventListener('click', scrollToTop);
}

// Home projects - Shopping List - Absolute image hack
const shoppingListBlock = document.getElementById('shoppinglist-container');

if (shoppingListBlock) {
  const shoppingListHeight = document.getElementById('shoppinglist-container').offsetHeight;
  document.getElementById('shoppinglist-left-img').style.height = shoppingListHeight+'px';
}

// Home projects - Budget - Absolute image hack
const budgetBlock = document.getElementById('budget-container');

if (budgetBlock) {
  const budgetHeight = document.getElementById('budget-container').offsetHeight;
  document.getElementById('budget-left-img').style.height = budgetHeight+'px';
}
