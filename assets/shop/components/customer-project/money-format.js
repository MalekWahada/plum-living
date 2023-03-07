// Check parameters:currency_dividing_amount value in services.yaml
const CURRENCY_DIVIDER = 1000;

const locale = document.getElementById('locale');

const moneyFormat = new Intl.NumberFormat(
  locale ? locale.value.replace('_', '-') : 'fr-FR',
  {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  },
);



export default moneyFormat;

export {
  moneyFormat,
  CURRENCY_DIVIDER
};