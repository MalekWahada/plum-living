export default {
  init () {
    // get rid of query params without refreshing the page
    window.history.replaceState(null, null, window.location.pathname);

    const syliusVariantsPricing = document.getElementById('sylius-variants-pricing');

    if(syliusVariantsPricing) {
      const onVariantChange = () => {
        let selector = '';

        document.querySelectorAll('#sylius-product-adding-to-cart select[data-option]').forEach(elmt => {
          const value = elmt.options[elmt.selectedIndex].value;
          selector += `[data-${elmt.dataset.option}="${value}"]`;
        });

        const price = document.getElementById('sylius-variants-pricing').querySelector(selector).dataset.value;
        const originalPrice = document.getElementById('sylius-variants-pricing').querySelector(selector).dataset.originalPrice;
        const priceElmt = document.getElementById('product-price');
        const originalPriceElmt = document.getElementById('product-original-price');

        if(price !== undefined) {
          if(priceElmt) {
            priceElmt.innerText = price;
          }

          document.querySelector('button[type=submit]').disabled = false;

          if(originalPrice !== undefined && originalPriceElmt) {
            originalPriceElmt.style.display = 'inline';
            originalPriceElmt.innerHTML = `<del>${originalPrice}</del>`;
          } else if(originalPriceElmt) {
            originalPriceElmt.style.display = 'none';
          }
        } else {
          if(priceElmt) {
            priceElmt.innerText = document.getElementById('sylius-variants-pricing').dataset.unavailableText;
          }

          document.querySelector('button[type=submit]').disabled = true;
        }
      }

      document.querySelectorAll('[name*="sylius_tunnel_add_to_cart[cartItem][variant]"]').forEach(elmt => {
        elmt.addEventListener('change', onVariantChange);
      });
    }
  },

  finalize() {}
};
