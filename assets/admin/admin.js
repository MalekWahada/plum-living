/*
  Sylius
  ---------- ---------- ---------- ---------- ----------
*/

import 'sylius/bundle/AdminBundle/Resources/private/entry';

/*
  Needs cleaning
  ---------- ---------- ---------- ---------- ----------
*/

// Uncomment to use Swiper carousels on admin pages.
// import '../abstract/swiper/swiper.js';

import './utils/rich-editor-specific-BO';
import './utils/slim.jquery.min';
import './components/media/autocomplete';
import './components/media/slim';

import $ from 'jquery';
import './utils/jquery-ui.min';
import deliveryDateCalculation from './components/delivery-date-calculation/delivery-date-calculation';

$.fn.extend({
  requireConfirmationMessage() {
    this.each((idx, el) => {
      $(el).on('click', (evt) => {
        evt.preventDefault();
        const message = $(el).data('requires-confirmation-message');
        const messageContainer = $('#confirmation-modal > .content > p');
        const previousMessage = messageContainer.text();
        const actionButton = $(evt.currentTarget);

        if (actionButton.is('a')) {
          $('#confirmation-button').attr('href', actionButton.attr('href'));
        }

        if (actionButton.is('button')) {
          $('#confirmation-button').on('click', (event) => {
            event.preventDefault();

            actionButton.closest('form').submit();
          });
        }

        // Listen to modal events to override the message and restore it on modal closed.
        $('#confirmation-modal').modal({
          onShow() {
            if (message) {
              messageContainer.text(message);
            }
          },
          onHidden() {
            messageContainer.text(previousMessage);
          },
        }).modal('show');
      });
    });
  },

  // override sylius-check-all.js to also disable .button class
  checkAll() {
    this.each((idx, el) => {
      const $checkboxAll = $(el);
      const $checkboxes = $($checkboxAll.attr('data-js-bulk-checkboxes'));
      const $buttons = $($checkboxAll.attr('data-js-bulk-buttons'));

      const isAnyChecked = () => {
        let checked = false;
        $checkboxes.each((i, checkbox) => {
          if (checkbox.checked) checked = true;
        });
        return checked;
      };

      const buttonsPropRefresh = () => {
        $buttons.find('button').prop('disabled', !isAnyChecked());
        if (!isAnyChecked()) {
          $buttons.find('.button').addClass('disabled');
        } else {
          $buttons.find('.button').removeClass('disabled');
        }
      };

      $checkboxAll.on('change', () => {
        $checkboxes.prop('checked', $(this).is(':checked'));
        buttonsPropRefresh();
      });

      $checkboxes.on('change', () => {
        $checkboxAll.prop('checked', isAnyChecked());
        buttonsPropRefresh();
      });

      buttonsPropRefresh();
    });
  },
});

window.addEventListener('DOMContentLoaded', () => {
  deliveryDateCalculation();
  $('[data-requires-confirmation-message]').requireConfirmationMessage();
});
