import { getNativeWidgetElmt } from '../components/form-fields/form-fields.js';



export default {
  init() {
    const toggleBillingAddressForm = (show) => {
      const container = document.getElementById('sylius-billing-address');

      container.style.display = show ? 'block' : 'none';

      container.querySelectorAll('.address-form .field[data-field-required]').forEach(fieldElmt => {
        getNativeWidgetElmt(fieldElmt).required = show;
      });
    }

    const differentBillingAddressCheckbox = document.getElementById('sylius_checkout_address_differentBillingAddress');

    differentBillingAddressCheckbox.addEventListener('change', (ev) => {
      toggleBillingAddressForm(differentBillingAddressCheckbox.checked);
    });

    toggleBillingAddressForm(differentBillingAddressCheckbox.checked);
  },

  finalize() {}
};
