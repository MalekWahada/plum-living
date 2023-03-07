import $ from 'jquery';

function callZoningRoute(element) {
  $.ajax({
    type: 'GET',
    url: element.data('route'),
    success(data) {
      element.parent('div')
        .find('.js-zoning-card')
        .removeClass('u-hidden')
        .children('.body')
        .html(data)
        .on('click', '.zoning-card a', function (e) {
          $(this).attr('disabled', 'disabled');
          let container = $(this).parents('.js-zoning-card').parents('[id]');
          $.ajax({
            type: 'GET',
            url: $(this).attr('href'),
            success(data) {
              let random = (Math.random() + 1).toString(36).substring(7);
              location.href = window.location.pathname + '?r=' + random + '#' + container.attr('id');
            },
          });
          return false;
        });
    },
  });
}

// open modal (route or html)
// eslint-disable-next-line no-undef,func-names
$(document).on('click', '.js-zoning-element', function () {
  if ($(this).data('route')) {
    callZoningRoute($(this));
    return;
  }
  $(this).parent('div')
    .find('.js-zoning-card')
    .removeClass('u-hidden')
    .children('.body')
    .html(
      $(this).next().clone().removeClass('u-hidden'),
    );
});

// close modal
$('.js-zoning-card').on('click', '.close', function () {
  $(this)
    .parent('div')
    .addClass('u-hidden')
    .children('.body')
    .empty();
});
