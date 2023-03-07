if (document.body.classList.contains('sylius_shop_cart_summary')) {
  const cartWidgetPopups = document.getElementsByClassName('cart-summary-route-target');
  const cartWidgetPopup = cartWidgetPopups.item(0);

  document.addEventListener('click', function(event) {
    if (cartWidgetPopup !== null) {
      const isClickInside = cartWidgetPopup.contains(event.target);
      // on close cart popup or on click outside the popup we reload the page
      if (cartWidgetPopup.dataset.cartIsUpdated === 'true' && (event.target.id === 'CloseCartButton' || !isClickInside)) {
        window.location.reload();
        // re-disable refresh
        cartWidgetPopup.setAttribute('data-cart-is-updated', 'false');
      }
    }
  });
}
