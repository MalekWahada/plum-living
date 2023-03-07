import i18n from 'i18next';
import {initReactI18next} from 'react-i18next';
import common_fr from './assets/locale/fr/translation.json';

i18n
  // pass the i18n instance to react-i18next.
  .use(initReactI18next)
  // init i18next
  // for all options read: https://www.i18next.com/overview/configuration-options
  .init({
    fallbackLng: 'fr',
    debug: true,
    resources: {
      fr: {
        common: common_fr // 'common' is our custom namespace
      }
    },
    interpolation: {
      escapeValue: false, // not needed for react as it escapes by default
    },
  });

export default i18n;
