import debounce from 'underscore/modules/debounce.js';
import UIComponent from '../../abstract/js-toolbox/ui-component.js';



class PlumCartSummary extends UIComponent {
  constructor (elmt) {
    super(elmt);

    this.emptyTemplate = document.getElementById('plum-cart-summary__empty-template').innerHTML;
    this.itemTemplate = document.getElementById('plum-cart-summary__item-template').innerHTML;
    this.itemOptionsTemplate = document.getElementById('plum-cart-summary__item-options-template').innerHTML;
    this.itemsContainer = this.elmt.querySelector('.js-update-items');

    this.elmt.addEventListener('click', onClick.bind(this));

    let clearCartButton = document.getElementById('ClearCartButton');
    if (clearCartButton) {
      clearCartButton.addEventListener('click', clearCart.bind(this));
    }

    this
      .elmt
      .querySelector('.plum-cart-summary__content')
      .addEventListener('change', debounce(onFieldChange, 300).bind(this));
    // select number inputs from 'templates/bundles/SyliusShopBundle/Cart/Widget/_popup.html.twig'
    // and add on Enter quantity modifier listener
    this.addOnEnterEventListener();
    this.onAutoRefresh();
  }

  onAutoRefresh() {
    const plumScannerPage = document.getElementsByClassName('app_plum_project_show');
    if (plumScannerPage.length > 0) {
      setInterval(() => {
        this.refresh();
      }, 10000);
    }
  }

  addOnEnterEventListener() {
    const quantityTags = this.elmt.querySelectorAll('[name^=sylius_cart]');
    quantityTags.forEach((quantityTag) => {
      quantityTag.addEventListener('keypress', (event) => {
        if (event.which === 13) {
          quantityTag.closest('form').addEventListener('submit', (e) => {
            e.preventDefault();
            return false;
          });
          debounce(onFieldChange, 300).bind(this);
        }
      });
    });
  }

  get cartIdElmt () {
    return this.elmt.querySelector('input[name="cart_id"]');
  }

  show () {
    this.elmt.classList.add('plum-cart-summary--visible');
    this.emit('show');
  }

  dismiss () {
    this.elmt.classList.remove('plum-cart-summary--visible');
    this.emit('dismiss');
  }

  refresh () {
    const refreshUrl = this.elmt.dataset.refreshUrl;
    const controller = new AbortController();

    this.controller && this.controller.abort();
    this.controller = controller;

    fetch(refreshUrl, {
      cache: 'no-store',
      signal: controller.signal
    })
    .then(response => response.text())
    .then(html => {
      const parser = new DOMParser();
      const cartPage = parser.parseFromString(html, "text/html");
      const cartData = JSON.parse(cartPage.getElementById('plum-json-cart').value);

      refreshCartContent.call(this, cartData);

      this.emit('update', {
        cartData,
        itemsContainer: this.itemsContainer
      });
    })
    .catch(err => {
      if(err.name == 'AbortError') {
        console.log('Aborted to debounce cart refresh calls');
      }
    });
  }

  update () {
    const updateUrl = this.elmt.dataset.updateUrl;
    const controller = new AbortController();

    this.controller && this.controller.abort();
    this.controller = controller;
    fetch(updateUrl, {
      method: 'post',
      cache: 'no-store',
      body: new FormData(document.getElementById('plum-cart-summary-form')),
      signal: controller.signal
    })
    .then(response => response.json())
    .then(cartData => {
      refreshCartContent.call(this, cartData);
      updateCartSummary();

      this.emit('update', {
        cartData,
        itemsContainer: this.itemsContainer
      });
    })
    .catch(err => {
      if(err.name == 'AbortError') {
        console.log('Aborted to debounce cart update calls');
      }
    });
  }
}



function refreshCartContent (cartData) {
  if(cartData.itemCount === 0) {
    this.itemsContainer.innerHTML = this.emptyTemplate;
    this.cartIdElmt && this.cartIdElmt.remove();
    document.getElementById('ClearCartToken').remove();
    document.getElementById('ClearCartButton').classList.add('u-hidden');
  }
  else {
    let itemsHtml = '';

    for(let i = 0 ; i < cartData.items.length ; i++) {
      const itemData = cartData.items[i];

      itemsHtml += this.itemTemplate
        .replaceAll('%ID%', itemData.id)
        .replaceAll('%NAME%', itemData.name)
        .replaceAll('%IMAGE%', itemData.image)
        .replaceAll(
          '%OPTIONS%',
          itemData.options === undefined || itemData.options === null
          ? ''
          : this.itemOptionsTemplate.replaceAll(
            '%OPTIONS%',
            '<span>' + itemData.options.join('</span><span>') + '</span>'
          )
        )
        .replaceAll('%QUANTITY%', itemData.quantity)
        .replaceAll('%TOTAL_PRICE%', itemData.totalPrice);
    }

    this.itemsContainer.innerHTML = itemsHtml;

    if(!this.cartIdElmt) {
      const cartIdElmt = document.createElement('input');
      cartIdElmt.type = 'hidden';
      cartIdElmt.name = 'cart_id';
      cartIdElmt.value = cartData.cartId;
      this.itemsContainer.insertAdjacentElement('beforebegin', cartIdElmt);
    }

    let cartTokenField = document.getElementById('ClearCartToken');
    if (!cartTokenField) {
      let clearCartButton = document.getElementById('ClearCartButton');
      let tokenField = document.createElement('input');
      tokenField.type = 'hidden';
      tokenField.name = '_csrf_token';
      tokenField.value = cartData.cartToken;
      tokenField.id = 'ClearCartToken';
      clearCartButton.after(tokenField);
      clearCartButton.classList.remove('u-hidden');
    }
  }

  this.elmt.querySelector('.js-update-total').innerHTML = cartData.total;
}



function onClick (ev) {
  if(ev.target == this.elmt || ev.target.closest('[data-dismiss]')) {
    this.dismiss();
  }
}


function onFieldChange (ev) {
  const itemElmt = ev.target.closest('.plum-cart-summary__item');

  if(itemElmt) {
    const pid = itemElmt.dataset.productId;
    const vid = itemElmt.dataset.variantId;

    this.update();

    this.emit('quantityChange', { productId: pid, variantId: vid });
  }
}


function updateCartSummary() {
  const cartWidgetPopups = document.getElementsByClassName('cart-summary-route-target');
  const cartWidgetPopup = cartWidgetPopups.length > 0 ? cartWidgetPopups.item(0) : null;
  if (cartWidgetPopup !== null) {
    cartWidgetPopup.setAttribute('data-cart-is-updated', 'true');
  }
}

function clearCart() {
  var formData = new FormData();
  formData.set('_csrf_token', document.getElementById('ClearCartToken').value);
  formData.set('_method', 'DELETE');

  fetch(this.elmt.dataset.clearUrl, {
    method: 'post',
    body: formData
  })
    .then(response => response.text())
    .then(html => {
      const parser = new DOMParser();
      const cartPage = parser.parseFromString(html, "text/html");
      const cartData = JSON.parse(cartPage.getElementById('plum-json-cart').value);
      refreshCartContent.call(this, cartData);

      this.emit('update', {
        cartData,
        itemsContainer: this.itemsContainer
      });
  });
}


const getComponent = UIComponent.get;



let singleton = null;

function init(elmt) {
  if (singleton === null) {
    singleton = new PlumCartSummary(elmt);
  }

  return singleton;
}



export default init;

export { getComponent };
