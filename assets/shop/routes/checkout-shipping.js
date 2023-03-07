import $ from 'jquery';

function parsePrice(priceString) {
  let priceValue = priceString ? parseInt(priceString) : null;

  if (priceValue && !isNaN(priceValue)) {
    priceValue = Math.round(priceValue / 10);
  }

  return priceValue;
}

function formatPrice(priceValue) {
  // TODO use better formatting when going international
  const whole = Math.floor(priceValue / 100);
  const decimal = (priceValue + '').substr(-2);
  return whole + ',' + (decimal + '').padEnd(2);
}



export default {
  init() {
    const shippingTotalElmt = document.getElementById('sylius-summary-shipping-total');
    const grandTotalElmt = document.getElementById('sylius-summary-grand-total');
    const form = document.querySelector(`form[name="sylius_checkout_select_shipping"]`);
    const selectedMethodUrl = document.getElementById('shipping-selected-url').dataset.shippingSelectedUrl;

    let initialShippingTotal = parsePrice(shippingTotalElmt.dataset.initialTotal);
    let initialGrandTotal = parsePrice(grandTotalElmt.dataset.initialTotal);

    if (!isNaN(initialShippingTotal)
      && !isNaN(initialGrandTotal)) {
      grandTotalElmt.addEventListener('animationend', () => {
        grandTotalElmt.classList.remove('look-at-me');
      });

      form.addEventListener('change', (ev) => {
        let newShippingTotal = 0;

        form.querySelectorAll('.checkout-shipping-shipment').forEach(shipmentElmt => {
          for (let i = 0; i < shipmentElmt.elements.length; i++) {
            const widgetElmt = shipmentElmt.elements[i];
            const fee = widgetElmt.closest('[data-fee]').dataset.fee;

            if (widgetElmt.checked) {
              $.ajax({
                url: `${selectedMethodUrl}/${widgetElmt.value}`,
                type: 'GET',
                success: response => {
                  newShippingTotal += parsePrice(response);
                  if (isNaN(newShippingTotal)) {
                    return;
                  }

                  const newGrandTotal = initialGrandTotal + (newShippingTotal - initialShippingTotal);
                  shippingTotalElmt.innerText = formatPrice(newShippingTotal) + ' €';
                  grandTotalElmt.innerText = formatPrice(newGrandTotal) + ' €';
                  grandTotalElmt.classList.add('look-at-me');
                }
              });
            }
          }
        });

      });
    }
  },

  finalize() { }
};
