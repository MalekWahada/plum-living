import $ from 'jquery';

const $piecesFilter = $('#listing_project_filter_piece');
const $colorsFilter = $('#listing_project_filter_color');
const $chipsFilter = $('[name="listing_inspiration_filter[chip]"]');

function getFilteredPagesByContent($id) {
  $id.closest('form').trigger('submit');
}

$piecesFilter.change(() => getFilteredPagesByContent($piecesFilter));
$colorsFilter.change(() => getFilteredPagesByContent($colorsFilter));
$chipsFilter.change(() => getFilteredPagesByContent($chipsFilter));
