const countryTag = document.getElementById('sylius_checkout_address_billingAddress_countryCode');
const postalCodeTag = document.getElementById('sylius_checkout_address_billingAddress_postcode');

if (countryTag !== null && postalCodeTag !== null) {
  // eslint-disable-next-line no-inner-declarations
  function setPostalCodePattern() {
    const pattern = countryTag.options[countryTag.selectedIndex].getAttribute('pattern');
    postalCodeTag.setAttribute('pattern', pattern);
  }

  // init postal code pattern
  if (countryTag.value !== '') {
    setPostalCodePattern();
  }

  // handle postal code pattern while country onchange is fired
  countryTag.addEventListener('change', function () {
    setPostalCodePattern();
  });
}
