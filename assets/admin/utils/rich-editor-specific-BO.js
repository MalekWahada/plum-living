// detecting the input name isn't guaranteed because it contains the locale variable
// detect if a form contains a rich editor field [sylius_product_code, combination]
const productFormCodeTag = document.getElementsByName('sylius_product');
const combinationFormCodeTag = document.getElementsByName('app_combination');
const taxonFormCodeTag = document.getElementsByName('sylius_taxon');

const productInfoInput = $("[allowed_infos]");
const allowedNumber = productInfoInput.attr('allowed_infos');

function removeElementsByClass(className) {
  const elements = document.getElementsByClassName(className);
  while (elements.length > 0) {
    elements[0].parentNode.removeChild(elements[0]);
  }
}

if (productFormCodeTag.length > 0 || combinationFormCodeTag.length > 0 || taxonFormCodeTag.length > 0) {
  const send = XMLHttpRequest.prototype.send;
  XMLHttpRequest.prototype.send = function () {
    this.addEventListener('load', function () {
      const length = allowedNumber !== undefined ? allowedNumber : 1;
      if (document.querySelectorAll('.js-uie-container .divider').length > length) {
        removeElementsByClass('divider');
      }
    });
    return send.apply(this, arguments);
  };
}

const productInformationIcon = $('.product-info-icon');

if (productInformationIcon) {
  productInformationIcon.popup({
    on: 'click',
  });
}
