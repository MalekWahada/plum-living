import UIComponent from '../../abstract/js-toolbox/ui-component.js';

import Swiper, {Navigation, Pagination, Autoplay, EffectFade, Mousewheel, Keyboard} from 'swiper';
Swiper.use([Navigation, Pagination, Autoplay, EffectFade, Mousewheel, Keyboard]);


class UIModal extends UIComponent {
  constructor ({ elmt, autoInsertCloseButton }) {
    super(elmt);

    this.autoInsertCloseButton = autoInsertCloseButton;
    this.scrollboxElmt = this.elmt.querySelector('.ui-modal__scrollbox');
    this.dialogElmt = this.elmt.querySelector('.ui-modal__dialog');
    this.closeButton = this.elmt.querySelector('.ui-modal__close-button');

    if(this.elmt.classList.contains('ui-modal--visible')) {
      this.show();
    }
    else {
      this.elmt.setAttribute('aria-hidden', true);
    }

    this.elmt.addEventListener('click', onClick.bind(this));
  }

  show () {
    const sourceUrl = this.elmt.dataset.sourceUrl;
    this._promise = {};
    this._promise.instance = new Promise((resolve, reject) => {
      this._promise.resolve = resolve;
      this._promise.reject = reject;
    });
    this.emit('show');

    if(sourceUrl) {
      this.loadContent(sourceUrl);
    }
    else {
      showComplete.call(this);
    }

    return this._promise.instance;
  }

  confirm () {
    this.elmt.classList.remove('ui-modal--loading');
    this.elmt.classList.remove('ui-modal--visible');
    this.elmt.setAttribute('aria-hidden', true);
    looseFocus.call(this);
    if(this._promise) {
      this._promise.resolve(true);
      delete this._promise;
    }
    this.emit('confirm');
    this.emit('hide');
  }

  dismiss () {
    this.elmt.classList.remove('ui-modal--loading');
    this.elmt.classList.remove('ui-modal--visible');
    this.elmt.setAttribute('aria-hidden', true);
    looseFocus.call(this);
    if(this._promise) {
      this._promise.resolve(false);
      delete this._promise;
    }
    this.emit('dismiss');
    this.emit('hide');
  }

  loadContent (url) {
    if(this.dialogElmt) {
      this.dialogElmt.innerHTML = '';
    }

    this.loaderElmt = document.createElement('div');
    this.loaderElmt.className = 'ui loader ui-modal__loader';
    this.elmt.appendChild(this.loaderElmt);

    this.elmt.classList.add('ui-modal--loading');

    fetch(url)
      .then(function (response ) {
        if (response.status === 404) {
          location.reload();
          return;
        }

        return response;
      })
      .then(resp => resp.text())
      .then(onLoadContent.bind(this))
      .catch(onLoadContentError.bind(this));
  }
}


function showComplete () {
  this.autoInsertCloseButton && insertCloseButton.call(this);
  const shouldEmitLoadEvent = this.elmt.classList.contains('ui-modal--loading');
  this.elmt.classList.remove('ui-modal--loading');
  this.elmt.classList.add('ui-modal--visible');
  this.elmt.setAttribute('aria-hidden', false);
  gainFocus.call(this);
  shouldEmitLoadEvent && this.emit('loadContent');
  this.emit('shown');

  // Slider images - Modal products - Eshop
  const sliderTunnelModalContainer = document.querySelector('.tunnel-modal-left');
  const sliderTunnelModalSlide = document.querySelectorAll('.tunnel-modal-left__img.swiper-slide');
  const sliderTunnelModalArrow = document.querySelectorAll('.tunnel-modal-left .tunnel-modal-arrow');

  if (sliderTunnelModalContainer && sliderTunnelModalSlide.length > 1) {
    const sliderTunnelModal = new Swiper('.tunnel-modal-left', {
      slidesPerView: 'auto',
      spaceBetween: 20,
      centeredSlides: true,
      loop: true,
      observeParents: true,
      observer: true,
      breakpoints: {
        720: {
          spaceBetween: 30,
        },
        1000: {
          spaceBetween: 50,
        },
      },
      navigation: {
        nextEl: '.tunnel-modal-arrow--right',
        prevEl: '.tunnel-modal-arrow--left',
      },
    });
  } else {
    sliderTunnelModalArrow.forEach(elmt => {
      elmt.style.display = 'none';
    });
  }
}


function gainFocus (elmt) {
  const focusElmt = this.dialogElmt.querySelector('.ui-modal__title') || this.dialogElmt || this.elmt;
  focusElmt.tabIndex = 0;

  setTimeout(() => {
    focusElmt.focus();
  }, 500);
}


function looseFocus (elmt) {
  const focusElmt = this.dialogElmt.querySelector('.ui-modal__title') || this.dialogElmt || this.elmt;
  focusElmt.tabIndex = -1;
}


function insertCloseButton () {
  if(!this.closeButton) {
    this.closeButton = document.createElement('button');
    this.closeButton.className = 'ui-modal__close-button close-button func-button';
    this.closeButton.setAttribute('data-dismiss', true);
    this.closeButton.innerHTML = '<span class="close-button__bar"></span><span class="close-button__bar"></span>';
  }

  this.dialogElmt.insertAdjacentElement('afterbegin', this.closeButton);
}


function insertLoadedContent (html) {
  if(this.scrollboxElmt) {
    this.scrollboxElmt.classList.add('u-y-scrollable');
  }
  else {
    this.scrollboxElmt = document.createElement('div');
    this.scrollboxElmt.className = 'ui-modal__scrollbox u-y-scrollable';
    this.elmt.appendChild(this.scrollboxElmt);
  }

  if(!this.dialogElmt) {
    this.dialogElmt = document.createElement('div');
    this.dialogElmt.className = 'ui-modal__dialog';
    this.scrollboxElmt.appendChild(this.dialogElmt);
  }

  this.dialogElmt.innerHTML = html;

  if(this.loaderElmt) {
    this.loaderElmt.remove();
    this.loaderElmt = null;
  }
}


function onClick (ev) {
  if(ev.target == this.scrollboxElmt || ev.target == this.elmt) {
    this.dismiss();
    return;
  }

  const actionElmt = ev.target.closest('[data-confirm], [data-dismiss]');

  if(actionElmt && this.elmt.contains(actionElmt)) {
    if(actionElmt.hasAttribute('data-confirm')) {
      this.confirm();
      return;
    }

    if(actionElmt.hasAttribute('data-dismiss')) {
      this.dismiss();
      return;
    }
  }
}


function onLoadContent(html) {
  insertLoadedContent.call(this, html);
  setTimeout(showComplete.bind(this), 50);
}


function onLoadContentError(err) {
  console.log(err);
}


function init (rootElmt) {
  (rootElmt || document).querySelectorAll('.ui-modal').forEach(elmt => {
    new UIModal({ elmt, autoInsertCloseButton: true });
  });
}

const getComponent = UIComponent.get;



const promptHtml = `
<div class="ui-modal__scrollbox u-y-scrollable">
  <div class="ui-modal__dialog">
    %TITLE%
    %MESSAGE%
    <div class="ui-modal__footer u-cross-centered-row u-end-on-main">
      <button class="prompt-modal__dismiss-button link-button u-margin-r-5" data-dismiss>%DISMISS_LABEL%</button>
      <button class="prompt-modal__confirm-button button button--inversed" data-confirm>
        <svg class="o-icon-24 o-icon--left"><use xlink:href="%ICON_LIB_URL%#SVG-icon-check"></svg>
        %CONFIRM_LABEL%
      </button>
    </div>
  </div>
</div>
`;

function createPrompt ({ id, className, title, message, dismissLabel, confirmLabel }) {
  const elmt = document.createElement('div');
  elmt.className = 'prompt-modal ui-modal ui-modal--dark ' + (className ? className : '');
  id && (elmt.id = id);

  const iconLibUrl = document.getElementById('icon-lib-url');

  const html = promptHtml
    .replace('%TITLE%', title ? `<div class="ui-modal__title t-header-medium">${title}</div>`: '')
    .replace('%MESSAGE%', message ? `<div class="ui-modal__content t-base-medium">${message}</div>`: '')
    .replace('%DISMISS_LABEL%', dismissLabel ? dismissLabel: 'Cancel')
    .replace('%CONFIRM_LABEL%', confirmLabel ? confirmLabel: 'OK')
    .replace('%ICON_LIB_URL%', iconLibUrl && iconLibUrl.value ? iconLibUrl.value : '');

  elmt.innerHTML = html;
  document.body.appendChild(elmt);

  return new UIModal({ elmt });
}



export default init;
export { getComponent, createPrompt };
