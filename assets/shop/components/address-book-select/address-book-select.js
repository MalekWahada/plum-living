class AdressBookSelect {
  constructor (elmt) {
    this.elmt = elmt;

    this.elmt.querySelector('select').addEventListener('change', onChange.bind(this));
  }
}


function updateField (nativeWidget, newValue) {
  if(!nativeWidget || newValue === undefined) {
    return;
  }

  const ev = new InputEvent('change', { bubbles: true });
  nativeWidget.value = newValue;
  nativeWidget.dispatchEvent(ev);
}


function onChange (ev) {
  const choiceElmt = ev.target.selectedOptions[0];
  const formFieldsContainer = document.getElementById(this.elmt.dataset.targetForm);

  if(!choiceElmt || !formFieldsContainer) {
    return;
  }

  const firstNameFieldWidget = formFieldsContainer.querySelector('[name*="[firstName]"]');
  const lastNameFieldWidget = formFieldsContainer.querySelector('[name*="lastName"]');
  const companyFieldWidget = formFieldsContainer.querySelector('[name*="company"]');
  const streetFieldWidget = formFieldsContainer.querySelector('[name*="street"]');
  const countryCodeFieldWidget = formFieldsContainer.querySelector('[name*="countryCode"]');
  const provinceCodeFieldWidget = formFieldsContainer.querySelector('[name*="provinceCode"]');
  const provinceNameFieldWidget = formFieldsContainer.querySelector('[name*="provinceName"]');
  const cityFieldWidget = formFieldsContainer.querySelector('[name*="city"]');
  const postcodeFieldWidget = formFieldsContainer.querySelector('[name*="postcode"]');
  const phoneNumberCountryFieldWidget = formFieldsContainer.querySelector('[name*="[phoneNumber][country]"]');
  const phoneNumberFieldWidget = formFieldsContainer.querySelector('[name*="[phoneNumber][number]"]');
  const shippingNotesWidget = formFieldsContainer.querySelector('[name*="shippingNotes"]');

  if(choiceElmt.value == 'new') {
    updateField(firstNameFieldWidget, null);
    updateField(lastNameFieldWidget, null);
    updateField(companyFieldWidget, null);
    updateField(streetFieldWidget, null);
    updateField(countryCodeFieldWidget, null);
    updateField(provinceCodeFieldWidget, null);
    updateField(provinceNameFieldWidget, null);
    updateField(cityFieldWidget, null);
    updateField(postcodeFieldWidget, null);
    updateField(phoneNumberCountryFieldWidget, null);
    updateField(phoneNumberFieldWidget, null);
    updateField(shippingNotesWidget, null);
  }
  else {
    updateField(firstNameFieldWidget, choiceElmt.dataset.firstName);
    updateField(lastNameFieldWidget, choiceElmt.dataset.lastName);
    updateField(companyFieldWidget, choiceElmt.dataset.company);
    updateField(streetFieldWidget, choiceElmt.dataset.street);
    updateField(countryCodeFieldWidget, choiceElmt.dataset.countryCode);
    updateField(provinceCodeFieldWidget, choiceElmt.dataset.provinceCode);
    updateField(provinceNameFieldWidget, choiceElmt.dataset.provinceName);
    updateField(cityFieldWidget, choiceElmt.dataset.city);
    updateField(postcodeFieldWidget, choiceElmt.dataset.postcode);
    updateField(phoneNumberCountryFieldWidget, choiceElmt.dataset.phoneNumberCountry);
    updateField(phoneNumberFieldWidget, choiceElmt.dataset.phoneNumber);
    updateField(phoneNumberFieldWidget, choiceElmt.dataset.phoneNumber);
    updateField(shippingNotesWidget, choiceElmt.dataset.shippingNotes);
  }
}



function init (rootElmt) {
  (rootElmt || document).querySelectorAll('.address-book-select').forEach(elmt => {
    new AdressBookSelect(elmt);
  });
}



export default init;
