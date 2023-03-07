const shippingCountryTag = document.getElementById('sylius_checkout_address_shippingAddress_countryCode');
const shippingPostalCodeTag = document.getElementById('sylius_checkout_address_shippingAddress_postcode');

if (shippingCountryTag !== null && shippingPostalCodeTag !== null) {
  // eslint-disable-next-line no-inner-declarations
  function setShippingPostalCodePattern() {
    const pattern = shippingCountryTag.options[shippingCountryTag.selectedIndex].getAttribute('pattern');
    shippingPostalCodeTag.setAttribute('pattern', pattern);
  }

  // init postal code pattern
  if (shippingCountryTag.value !== '') {
    setShippingPostalCodePattern();
  }

  // handle postal code pattern while country onchange is fired
  shippingCountryTag.addEventListener('change', function () {
    setShippingPostalCodePattern();
  });
}
