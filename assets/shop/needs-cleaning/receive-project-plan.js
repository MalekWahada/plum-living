import $ from 'jquery';
import { createFlash } from '../components/flash/flash.js';

const $receiveBtn = $('#receive_plan_btn');
const $mailInput = $('#mail_receive_plan_input');
const $csrf = $('#receive_plan_btn').prev('input[name="_csrf_token"]');

function inputValidate() {
  const val = $mailInput.val();
  const pattern = /^\w+([-+.'][^\s]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/;

  if (!pattern.test(val) || val === '') {
    $receiveBtn.attr('disabled', true);
    $mailInput.trigger('focus');
    return false;
  }
  $receiveBtn.attr('disabled', false);
  return true;
}

function sendProjectPlanMail() {
  if (!inputValidate()) {
    return;
  }
  $.ajax({
    url: $receiveBtn.data('href'),
    data: {
      mail: $mailInput.val(),
      page: $receiveBtn.data('page'),
      _csrf_token: $csrf.val(),
    },
    type: 'POST',
    success: function (res) {
      createFlash(res.type, res.message);
    },
    error: function () {
      createFlash('error', $receiveBtn.data('errorMessage'));
    },
  });
}

// receiving the mail can be done either on enter key press or by clicking on the 'ok' button
$mailInput.keypress(function onEnterPress(event) {
  if (!$receiveBtn.attr('disabled') && event.which === 13) {
    sendProjectPlanMail();
    return false;
  }
});

$receiveBtn.click(() => sendProjectPlanMail());
