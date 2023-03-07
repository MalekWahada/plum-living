import $ from 'jquery';
// import 'semantic-ui-css';
// Semantic UI is already imported by Sylius in
// either /vendor/sylius/sylius/src/Sylius/Bundle/AdminBundle/Resources/private/entry.js
// or /vendor/sylius/sylius/src/Sylius/Bundle/ShopBundle/Resources/private/entry.js

const $body = $('body');

$body.on('click', '.js-toggle-modal', (event) => {
  event.preventDefault();

  const $link = $(event.target);
  const url = $link.data('content-url');
  const $modal = $($link.data('modal'));

  $.ajax({
    url,
    type: 'GET',
    success(res) {
      $modal.html(res);

      $('.ui.checkbox').checkbox();
      $('.ui.radio.checkbox').checkbox();
      $('.ui.dropdown').dropdown();
    },
  });

  $modal.modal({ onApprove() { return false; } }).modal('show');
});

// enable/disable proceed button
$body.on('change', '#app_cart_validation_validateItemsCount', (event) => {
  const $proceedButton = $('#order-proceed');

  if ($(event.currentTarget).is(':checked')) {
    $proceedButton.removeClass('disabled');
    return;
  }

  $proceedButton.addClass('disabled');
});

// show/hide order original input
$body.on('change', 'input[type=radio][name="app_cart_validation[hasOriginalOrder]"]', (event) => {
  const $originalOrderInput = $('#app_cart_validation_originalOrderNumber');

  if (parseInt(event.currentTarget.value, 10) === 1) {
    $originalOrderInput.prop('required', true);
    $originalOrderInput.show();
    return;
  }

  $originalOrderInput.removeAttr('required');
  $originalOrderInput.hide();
});
