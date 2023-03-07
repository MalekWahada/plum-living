
export default {
  init() {
    const aboutLabels = JSON.parse(document.getElementById('About-howYouKnowAboutUs-field').dataset.labels);

    document.getElementById('sylius_customer_registration_howYouKnowAboutUs').addEventListener('change', (ev) => {
      const label = ev.target.value in aboutLabels
        ? aboutLabels[ev.target.value]
        : aboutLabels.default;

      const aboutField = document.getElementById('About-howYouKnowAboutUs-field');
      aboutField.querySelector('.field__label').innerHTML = label;
      aboutField.classList.remove('u-hidden');
    });
  },

  finalize() {},
};
