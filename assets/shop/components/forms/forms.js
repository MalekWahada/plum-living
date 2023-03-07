import { createPrompt } from '../modal/modal.js';



function prepareConfirmableForm (elmt) {
  elmt.addEventListener('submit', ev => {
    ev.preventDefault();

    const prompt = createPrompt({
      title: ev.target.dataset.confirmationTitle || 'Confirmation',
      message: ev.target.dataset.confirmationMessage || 'Are you sure you want to do this action?',
      dismissLabel: ev.target.dataset.confirmationDismiss || 'No',
      confirmLabel: ev.target.dataset.confirmationConfirm || 'Yes'
    });

    setTimeout(async () => {
      const confirmed = await prompt.show();
      
      confirmed && ev.target.submit();

      setTimeout(() => {
        prompt.remove();
      }, 500);
    }, 50);
  });
}



function init (rootElmt) {
  (rootElmt || document).querySelectorAll('.form').forEach(elmt => {
    elmt.hasAttribute('data-form-requires-confirmation') && prepareConfirmableForm(elmt);
  });
}



export default init;