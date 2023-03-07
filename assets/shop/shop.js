/*
  Sylius
  ---------- ---------- ---------- ---------- ----------
*/

import 'sylius/bundle/ShopBundle/Resources/private/entry';

/*
  Needs cleaning
  ---------- ---------- ---------- ---------- ----------
*/

import './needs-cleaning/receive-project-plan';

/*
  Listing Filters
  ---------- ---------- ---------- ---------- ----------
*/

import './components/listing-filters/project-inspi-filters';

/*
  Utils
  ---------- ---------- ---------- ---------- ----------
*/

import './utils/rich-editor-specific-shop';
import './utils/address-validation-pattern';
import './utils/shipping-address-validation-pattern';
import './utils/cart-summary';

/*
  Extra components
  ---------- ---------- ---------- ---------- ----------
*/
import './components/product/smallcard';

/*
  Routing
  ---------- ---------- ---------- ---------- ----------
*/

import { getRouter } from './abstract/js-toolbox/router';
import common from './routes/common';
import register from './routes/register';
import cart from './routes/cart';
import checkoutAddress from './routes/checkout-address';
import checkoutShipping from './routes/checkout-shipping';
import eshop from './routes/eshop';
import productPage from './routes/product-page';
import plumScanner from './routes/plum-scanner';
import customerProject from './routes/customer-project';
import anchor from './routes/anchor';
import home from './routes/home';
import designFinish from './routes/design-finish';
import quoteHome from './routes/quote-home';

const router = getRouter({
  common,
  syliusShopRegister: register,
  syliusShopCartSummary: cart,
  syliusShopCheckoutAddress: checkoutAddress,
  syliusShopCheckoutSelectShipping: checkoutShipping,
  syliusShopHomepage: home,
  facadeGetDesigns: eshop,
  facadeGetFinishes: eshop,
  facadeGetColors: eshop,
  facadeGetColorCombination: eshop,
  facadeGetProducts: eshop,

  appPlumScannerGetDesigns: eshop,
  appPlumScannerGetFinishes: eshop,
  appPlumScannerGetColors: eshop,
  appPlumScannerGetColorCombination: eshop,
  appPlumScannerGetProducts: eshop,
  appPlumScannerProjectStatus: plumScanner,
  appPlumScannerSharePlan: plumScanner,
  appCustomerProjectShow: customerProject,

  listingAccessoires: eshop,
  listingSampleFacade: eshop,
  listingSamplePaintMural: eshop,
  listingPaint: eshop,
  listingTap: eshop,

  syliusShopProductShow: productPage,

  cmsPageTypeProject: anchor,
  cmsPageTypeArticle: anchor,

  cmsDesignEtFinition: designFinish,
  cmsDevisHome: quoteHome,
});

window.addEventListener('DOMContentLoaded', () => {
  router.loadEvents();
});

document.addEventListener('routed', (ev) => {
  console.log(`Routed to ${ev.detail.route}, ${ev.detail.fn}`);
});

const radios = document.querySelectorAll('input[type=radio][name="sylius_checkout_select_payment[payments][0][method]"]');
const target = document.getElementById('div-custom-delays');
radios.forEach(
  (radio) => radio.addEventListener(
    'change',
    (e) => {
      if (e.target.value === 'stripe_wire') {
        target.classList.remove('u-hidden');
      } else {
        target.classList.add('u-hidden');
      }
    },
  ),
);

/*
  The end
  ---------- ---------- ---------- ---------- ----------
  It should not be necessary to add anything more from
  this point. Any new code should go either in an abstract
  lib, ui component or route file.
*/
