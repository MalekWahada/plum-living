const initHandlers = {
  'date-field': function (nativeWidgetElmt) {
    this.querySelector('.field__widget').addEventListener('click', (ev) => {
      const forwardedEvent = {};
      for (const prop in ev) {
        forwardedEvent[prop] = ev[prop];
      }
      forwardedEvent.target = nativeWidgetElmt;

      onGainFocus.call(this, forwardedEvent);
    });
  },

  'number-field': function (nativeWidgetElmt) {
    this.querySelectorAll('[data-field-action]').forEach((button) => {
      button.tabIndex = 0;
    });

    this.addEventListener('click', (ev) => {
      const actionButton = ev.target.closest('[data-field-action]');

      if (actionButton) {
        const currentValue = parseInt(nativeWidgetElmt.value);
        const step = parseInt(nativeWidgetElmt.step) || 1;

        switch (actionButton.dataset.fieldAction) {
          case 'decrement':
            let min = parseInt(nativeWidgetElmt.min);
            min = isNaN(min) ? Number.NEGATIVE_INFINITY : min;
            nativeWidgetElmt.value = Math.max(currentValue - step, min);
            this.dispatchEvent(new CustomEvent('decrement', {
              bubbles: true,
              detail: { nativeWidgetElmt },
            }));
            nativeWidgetElmt.dispatchEvent(new InputEvent('change', { bubbles: true }));
            break;
          case 'increment':
            let max = parseInt(nativeWidgetElmt.max);
            max = isNaN(max) ? Number.POSITIVE_INFINITY : max;
            nativeWidgetElmt.value = Math.min(currentValue + step, max);
            this.dispatchEvent(new CustomEvent('increment', {
              bubbles: true,
              detail: { nativeWidgetElmt },
            }));
            nativeWidgetElmt.dispatchEvent(new InputEvent('change', { bubbles: true }));
            break;
        }
      }
    });
  },

  'color-select-field': function (nativeWidgetElmt) {
    const cancel = (ev) => {
      ev.preventDefault();
      ev.stopImmediatePropagation();
    };

    nativeWidgetElmt.addEventListener('focus', cancel);
    nativeWidgetElmt.addEventListener('blur', cancel);
    nativeWidgetElmt.addEventListener('focusin', cancel);
    nativeWidgetElmt.addEventListener('focusout', cancel);

    const proxiSelect = this.querySelector('select');
    proxiSelect.addEventListener('change', cancel);
    proxiSelect.addEventListener('focus', cancel);
    proxiSelect.addEventListener('blur', cancel);
    proxiSelect.addEventListener('focusin', cancel);
    proxiSelect.addEventListener('focusout', cancel);


    const selectedOptionElmt = this.querySelector('.color-select-field__selected-option');
    const optionsElmt = this.querySelector('.color-select-field__options');
    const tunnalFormBtnValid = document.querySelector('.tunnel-modal-form__button .btn-validate');

    selectedOptionElmt.addEventListener('click', (ev) => {
      !this.hasAttribute('data-field-disabled') && optionsElmt.classList.add('color-select-field__options--open');
      tunnalFormBtnValid && tunnalFormBtnValid.classList.remove('validate');
      window.addEventListener('click', onClickOutsideSelf);
    });

    const onChangeSelf = (ev) => {
      const requestedOptionElmt = optionsElmt.querySelector(`[data-value="${nativeWidgetElmt.value}"]`) || null;
      selectedOptionElmt.dataset.value = nativeWidgetElmt.value;
      selectedOptionElmt.innerHTML = requestedOptionElmt ? requestedOptionElmt.innerHTML : `<span>${this.dataset.emptyLabel}</span>`;
      optionsElmt.classList.remove('color-select-field__options--open');
      proxiSelect.value = nativeWidgetElmt.value;
    };

    const onClickSelf = (ev) => {
      const clickedOptionElmt = ev.target.closest('[data-value]:not(.color-select-field__selected-option)');
      if (clickedOptionElmt) {
        let value = clickedOptionElmt.dataset.value;

        if (value.trim().length === 0) {
          value = null;
        }

        if (nativeWidgetElmt.value !== value) {
          nativeWidgetElmt.value = value;
          nativeWidgetElmt.dispatchEvent(new InputEvent('change', { bubbles: true }));
        } else {
          optionsElmt.classList.remove('color-select-field__options--open');
        }

        window.removeEventListener('click', onClickOutsideSelf);
      }
    };

    const onClickOutsideSelf = (ev) => {
      if (!this.contains(ev.target)) {
        optionsElmt.classList.remove('color-select-field__options--open');
        window.removeEventListener('click', onClickOutsideSelf);
      }
    };

    let touch = {};

    const onMouseDownOnSelf = (ev) => {
      touch = {
        x: ev.clientX,
        y: ev.clientY,
        target: ev.target
      };
      window.addEventListener('mouseup', onMouseUpOutsideSelf);
    }

    const onMouseUpOutsideSelf = (ev) => {
      if(Math.sqrt(Math.pow(ev.clientX - touch.x, 2) + Math.pow(ev.clientY - touch.y, 2)) < 5) {
        onClickSelf(touch);
      }
      window.removeEventListener('mouseup', onMouseUpOutsideSelf);
    }

    nativeWidgetElmt.addEventListener('change', onChangeSelf);
    optionsElmt.addEventListener('mousedown', onMouseDownOnSelf);

    onChangeSelf.call(nativeWidgetElmt);
  },
  'focus-placeholder-field': function () {
    this.querySelector('.field__widget').addEventListener('focus', (ev) => {
      if ('focusPlaceholder' in ev.target.dataset) {
        ev.target.placeholder = ev.target.dataset.focusPlaceholder;
      }
    });
    this.querySelector('.field__widget').addEventListener('focusout', (ev) => {
      if ('focusPlaceholder' in ev.target.dataset) {
        ev.target.placeholder = '';
      }
    });
  },
};

const gainFocusHandlers = {
  default(nativeWidgetElmt) {
    this.classList.add('field--focus');
  },

  DIV: {
    'custom-color-select': function (nativeWidgetElmt, relatedTarget) {
      if (relatedTarget
      && !this.hasAttribute('data-field-disabled')
      && !this.contains(relatedTarget)
      && this.contains(nativeWidgetElmt)
      && !this.classList.contains('field--focus')) {
        gainFocusHandlers.default.call(this, nativeWidgetElmt);
        this.querySelector('.color-select-field__options').classList.add('color-select-field__options--open');
      }
    },
    'custom-color-select-options': null,
  },

  BUTTON: {
    'custom-color-select-options': function (nativeWidgetElmt, relatedTarget) {
      gainFocusHandlers.DIV['custom-color-select'].call(this, nativeWidgetElmt, relatedTarget);
    },
  },

  INPUT: {
    radio(nativeWidgetElmt) {
      gainFocusHandlers.default.call(this, nativeWidgetElmt);

      document.querySelectorAll(`[name="${nativeWidgetElmt.name}"]`).forEach((elmt) => {
        elmt !== nativeWidgetElmt && loseFocusHandlers.default.call(elmt.closest('.radio-field'), elmt);
      });
    },
  },
};

const loseFocusHandlers = {
  default(nativeWidgetElmt) {
    this.classList.remove('field--focus');
  },

  DIV: {
    'custom-color-select': function (nativeWidgetElmt, relatedTarget) {
      if (relatedTarget
      && !this.contains(relatedTarget)
      && this.contains(nativeWidgetElmt)
      && this.classList.contains('field--focus')) {
        loseFocusHandlers.default.call(this, nativeWidgetElmt);
        this.querySelector('.color-select-field__options').classList.remove('color-select-field__options--open');
      }
    },
    'custom-color-select-options': null,
  },

  BUTTON: {
    'custom-color-select-options': function (nativeWidgetElmt, relatedTarget) {
      loseFocusHandlers.DIV['custom-color-select'].call(this, nativeWidgetElmt, relatedTarget);
    },
  },
};

const inputHandlers = {
  INPUT: {
    button: null,
    checkbox: null,
    hidden: null,
    image: null,
    radio: null,
    range: null,
    reset: null,
    submit: null,
    default(nativeWidgetElmt) {
      if (nativeWidgetElmt.value || nativeWidgetElmt.placeholder) {
        this.classList.add('field--fill');
      } else {
        this.classList.remove('field--fill');

        setTimeout(() => {
          try {
            if (nativeWidgetElmt.matches('input:-internal-autofill-selected')) {
              this.classList.add('field--fill');
            }
          } catch(err) {}
        }, 600);
      }
    },
  },

  TEXTAREA(nativeWidgetElmt) {
    inputHandlers.INPUT.default.call(this, nativeWidgetElmt);

    if (this.classList.contains('textarea-field--autogrow')) {
      nativeWidgetElmt.style.minHeight = `${nativeWidgetElmt.scrollHeight}px`;
    }
  },

  SELECT(nativeWidgetElmt) {
    const widgetElmt = this.querySelector('.field__widget');

    if (!widgetElmt) {
      return;
    }

    if (nativeWidgetElmt.selectedIndex === -1 || (!nativeWidgetElmt.selectedOptions[0].value && nativeWidgetElmt.selectedOptions[0].disabled)) {
      if (this.dataset.emptyLabel) {
        this.classList.add('field--fill');
        widgetElmt.innerHTML = `<span>${this.dataset.emptyLabel}</span>`;
      } else {
        this.classList.remove('field--fill');
        widgetElmt.innerHTML = '';
      }
    } else {
      this.classList.add('field--fill');
      widgetElmt.innerHTML = `<span>${nativeWidgetElmt.selectedOptions[0].innerText}</span>`;
    }
  },
};

const validHandlers = {
  default(nativeWidgetElmt, compoundField = false) {
    this.classList.remove('field--invalid');
    this.classList.add('field--valid');

    // compoundField can be false or null
    if(!compoundField) {
      removeMessage(this);
      return;
    }

    if(compoundField.nodeType === Node.ELEMENT_NODE) {
      compoundField.classList.remove('field--invalid');
      compoundField.classList.add('field--valid');
      removeMessage(compoundField);
    }
  },

  INPUT: {
    radio(nativeWidgetElmt) {
      validHandlers.default.call(this, nativeWidgetElmt, this.closest('.compound-field'));

      document.querySelectorAll(`[name="${nativeWidgetElmt.name}"]`).forEach((elmt) => {
        elmt !== nativeWidgetElmt && validHandlers.default.call(elmt.closest('.radio-field'), elmt, true);
      });
    },
  },
};

const invalidHandlers = {
  default(nativeWidgetElmt, compoundField = false) {
    const address = document.getElementById('sylius_checkout_address_billingAddress_postcode');
    if (address){
      let labelPostCode = address.closest('label');
      if (labelPostCode.classList.contains('field--invalid')) {
        document.getElementById('sylius-billing-address').style.display = "block";
      }
    }
    this.classList.add('field--invalid');
    this.classList.remove('field--valid');

    if(compoundField.nodeType === Node.ELEMENT_NODE) {
      compoundField.classList.add('field--invalid');
      compoundField.classList.remove('field--valid');
      addMessge({
        fieldElmt: compoundField,
        nativeWidgetElmt,
      });
    }
    else if(compoundField === false) {
      addMessge({
        fieldElmt: this,
        nativeWidgetElmt,
      });
    }
  },

  INPUT: {
    radio(nativeWidgetElmt) {
      invalidHandlers.default.call(this, nativeWidgetElmt, this.closest('.compound-field'));

      document.querySelectorAll(`[name="${nativeWidgetElmt.name}"]`).forEach((elmt) => {
        elmt !== nativeWidgetElmt && invalidHandlers.default.call(elmt.closest('.radio-field'), elmt, true);
      });
    },
  },
};

const nativeWidgetSelector = 'button, input, meter, progress, select, textarea';

function getNativeWidgetElmt(fieldElmt) {
  return fieldElmt.querySelector(nativeWidgetSelector);
}

const textLikeElmts = {
  INPUT: {
    button: false,
    checkbox: false,
    hidden: false,
    image: false,
    radio: false,
    range: false,
    reset: false,
    submit: false,
  },
  TEXTAREA: true,
};

function isTextLike(elmt) {
  return textLikeElmts[elmt.tagName] === true
    || (textLikeElmts[elmt.tagName] && textLikeElmts[elmt.tagName][elmt.type] !== false);
}

function init(rootElmt, initHiddenFields = false) {
  (rootElmt || document).querySelectorAll('.field').forEach((elmt) => {
    const nativeWidgetElmt = getNativeWidgetElmt(elmt);

    if (!nativeWidgetElmt || (!initHiddenFields && elmt.offsetParent === null)) { // Don't initialize if not visible
      return;
    }

    elmt.addEventListener('focusin', onGainFocus);
    elmt.addEventListener('focusout', onLoseFocus);
    elmt.addEventListener('input', onInput);
    elmt.addEventListener('change', onChange);
    nativeWidgetElmt.addEventListener('invalid', onInvalid.bind(elmt));

    elmt.hasAttribute('data-field-required') && (nativeWidgetElmt.required = true);

    onInput.call(elmt, {
      target: nativeWidgetElmt,
      currentTarget: elmt,
      preventDefault() {},
    });

    callInitHandler({
      libObject: initHandlers,
      nativeWidgetElmt,
      fieldElmt: elmt,
    });
  });
}

function callInitHandler({ libObject, nativeWidgetElmt, fieldElmt }) {
  for (const type in libObject) {
    if (fieldElmt.classList.contains(type)) {
      initHandlers[type].call(fieldElmt, nativeWidgetElmt);
      return;
    }
  }

  initHandlers.default && initHandlers.default.call(fieldElmt, nativeWidgetElmt);
}

function callEventHandler({
  libObject, originalEvent, fieldElmt, shouldPreventDefault = true,
}) {
  const customWidget = originalEvent.target.closest('[data-custom-type]');

  const tagName = originalEvent.target.tagName in libObject
    ? originalEvent.target.tagName
    : 'default';

  const widgetType = customWidget
    ? customWidget.dataset.customType
    : originalEvent.target.type;

  const type = widgetType && (widgetType in libObject[tagName])
    ? widgetType
    : 'default';

  shouldPreventDefault && originalEvent.preventDefault();

  if (typeof libObject[tagName][type] === 'function') {
    libObject[tagName][type].call(fieldElmt, originalEvent.target, originalEvent.relatedTarget);
  } else if (typeof libObject[tagName] === 'function' && libObject[tagName][type] !== null) {
    libObject[tagName].call(fieldElmt, originalEvent.target, originalEvent.relatedTarget);
  } else if (typeof libObject.default === 'function' && libObject[tagName][type] !== null && libObject[tagName] !== null) {
    libObject.default.call(fieldElmt, originalEvent.target, originalEvent.relatedTarget);
  }
}

function addMessge({ fieldElmt, nativeWidgetElmt }) {
  let newNode = false;
  let messageElmt = fieldElmt.querySelector('.field__message');

  if (!messageElmt) {
    newNode = true;
    messageElmt = document.createElement('div');
    messageElmt.className = 'field__message t-base-small';
  }

  messageElmt.innerHTML = nativeWidgetElmt.validationMessage;

  if (newNode) {
    messageElmt.style.height = '0';
    fieldElmt.appendChild(messageElmt);

    window.requestAnimationFrame(() => {
      messageElmt.ontransitionend = onMessageTransitionEnd;
      messageElmt.classList.add('field__message--in-transition');
      messageElmt.classList.add('field__message--visible');
      messageElmt.style.height = `${messageElmt.scrollHeight}px`;
    });
  }
}

function removeMessage(fieldElmt) {
  const messageElmt = fieldElmt.querySelector('.field__message');

  if (messageElmt) {
    messageElmt.style.height = `${messageElmt.scrollHeight}px`;

    window.requestAnimationFrame(() => {
      messageElmt.ontransitionend = onMessageTransitionEnd;
      messageElmt.classList.add('field__message--in-transition');
      messageElmt.classList.remove('field__message--visible');
      messageElmt.style.height = '0';
    });
  }
}

function enableField(fieldElmt, shallow = false) {
  const nativeWidgetElmt = getNativeWidgetElmt(fieldElmt);

  fieldElmt.removeAttribute('data-field-disabled');
  !shallow && nativeWidgetElmt && (nativeWidgetElmt.disabled = false);
}

function disableField(fieldElmt, shallow = false, type = true) {
  const nativeWidgetElmt = getNativeWidgetElmt(fieldElmt);

  fieldElmt.setAttribute('data-field-disabled', type);
  !shallow && nativeWidgetElmt && (nativeWidgetElmt.disabled = true);
}

function changeSelectValues(fieldElmt, list, { shouldDispatchChangeEvent = true, missingGoesToBlank = false }) {
  const nativeWidgetElmt = getNativeWidgetElmt(fieldElmt);

  const oldSelectedValue = nativeWidgetElmt.value;
  let newSelectedValue = list.length === 1 ? list[0].value : null;

  const options = list.map((item) => {
    if (`${item.value}` === oldSelectedValue) {
      newSelectedValue = oldSelectedValue;
    }

    return `<option value="${item.value}">${item.text}</option>`;
  });

  nativeWidgetElmt.innerHTML = options.join('');

  if (newSelectedValue) {
    nativeWidgetElmt.value = newSelectedValue;
  } else if (missingGoesToBlank) {
    nativeWidgetElmt.value = null;
  } else {
    nativeWidgetElmt.value = list[0] ? list[0].value : null;
  }

  shouldDispatchChangeEvent
    ? nativeWidgetElmt.dispatchEvent(new InputEvent('change', { bubbles: true }))
    : inputHandlers.SELECT.call(fieldElmt, nativeWidgetElmt);

  return oldSelectedValue === newSelectedValue || list.length === 1;
}

function changeSelectSelectedValue(fieldElmt, value, { shouldDispatchChangeEvent = true }) {
  const nativeWidgetElmt = getNativeWidgetElmt(fieldElmt);
  const oldSelectedValue = nativeWidgetElmt.value;

  nativeWidgetElmt.value = value;

  shouldDispatchChangeEvent
    ? nativeWidgetElmt.dispatchEvent(new InputEvent('change', { bubbles: true }))
    : inputHandlers.SELECT.call(fieldElmt, nativeWidgetElmt);
}

function changeColorSelectValues(fieldElmt, list, { shouldDispatchChangeEvent = true, missingGoesToBlank = false }) {
  const nativeWidgetElmt = getNativeWidgetElmt(fieldElmt);
  const itemTemplate = fieldElmt.dataset.itemTemplate;
  const optionsElmt = fieldElmt.querySelector('.color-select-field__options');

  const oldSelectedValue = nativeWidgetElmt.value;
  let newSelectedValue = list.length === 1 ? list[0].value : null;

  const options = list.map((item) => {
    if (`${item.value}` === oldSelectedValue) {
      newSelectedValue = oldSelectedValue;
    }

    return itemTemplate
      .replace('%VALUE%', item.value)
      .replace('%LABEL%', item.text)
      .replace('%IMAGE_URL%', item.thumbnail || '');
  });

  optionsElmt.innerHTML = options.join('');

  if (newSelectedValue) {
    nativeWidgetElmt.value = newSelectedValue;
  } else if (missingGoesToBlank) {
    nativeWidgetElmt.value = null;
  } else {
    nativeWidgetElmt.value = list[0] ? list[0].value : null;
  }

  if (shouldDispatchChangeEvent) {
    nativeWidgetElmt.dispatchEvent(new InputEvent('change', { bubbles: true }));
  } else {
    const selectedOptionElmt = fieldElmt.querySelector('.color-select-field__selected-option');
    const requestedOptionElmt = optionsElmt.querySelector(`[data-value="${nativeWidgetElmt.value}"]`) || null;
    selectedOptionElmt.dataset.value = nativeWidgetElmt.value;
    selectedOptionElmt.innerHTML = requestedOptionElmt ? requestedOptionElmt.innerHTML : `<span>${fieldElmt.dataset.emptyLabel}</span>`;
    optionsElmt.classList.remove('color-select-field__options--open');
  }

  return oldSelectedValue === newSelectedValue || list.length === 1;
}

function changeColorSelectSelectedValue(fieldElmt, value, { shouldDispatchChangeEvent = true }) {
  const nativeWidgetElmt = getNativeWidgetElmt(fieldElmt);
  const optionsElmt = fieldElmt.querySelector('.color-select-field__options');
  const oldSelectedValue = nativeWidgetElmt.value;

  nativeWidgetElmt.value = value;

  if (shouldDispatchChangeEvent) {
    nativeWidgetElmt.dispatchEvent(new InputEvent('change', { bubbles: true }));
  } else {
    const selectedOptionElmt = fieldElmt.querySelector('.color-select-field__selected-option');
    const requestedOptionElmt = optionsElmt.querySelector(`[data-value="${nativeWidgetElmt.value}"]`) || null;
    selectedOptionElmt.dataset.value = nativeWidgetElmt.value;
    selectedOptionElmt.innerHTML = requestedOptionElmt ? requestedOptionElmt.innerHTML : `<span>${fieldElmt.dataset.emptyLabel}</span>`;
    optionsElmt.classList.remove('color-select-field__options--open');
  }
}

function onMessageTransitionEnd(ev) {
  const shouldBeRemoved = ev.target.offsetHeight < ev.target.scrollHeight;

  ev.target.ontransitionend = null;
  ev.target.style.height = '';
  ev.target.classList.remove('field__message--in-transition');

  shouldBeRemoved && ev.target.remove();
}

function onGainFocus(ev) {
  callEventHandler({
    libObject: gainFocusHandlers,
    originalEvent: ev,
    fieldElmt: this,
    shouldPreventDefault: false,
  });
}

function onLoseFocus(ev) {
  callEventHandler({
    libObject: loseFocusHandlers,
    originalEvent: ev,
    fieldElmt: this,
    shouldPreventDefault: false,
  });

  ev.target.checkValidity && ev.target.checkValidity() && callEventHandler({
    libObject: validHandlers,
    originalEvent: ev,
    fieldElmt: this,
  });
}

function onInput(ev) {
  callEventHandler({
    libObject: inputHandlers,
    originalEvent: ev,
    fieldElmt: this,
  });

  if (this.classList.contains('field--has-initial-error')) {
    this.classList.remove('field--has-initial-error');
  } else if (this.classList.contains('field--invalid') && ev.target.validity.valid) {
    callEventHandler({
      libObject: validHandlers,
      originalEvent: ev,
      fieldElmt: this,
    });
  } else if (isTextLike(ev.target) && this.classList.contains('field--focus') && ev.target.value.length == 0) {
    ev.target.checkValidity() && callEventHandler({
      libObject: validHandlers,
      originalEvent: ev,
      fieldElmt: this,
    });
  }
}

function onChange(ev) {
  callEventHandler({
    libObject: inputHandlers,
    originalEvent: ev,
    fieldElmt: this,
  });

  ev.target.checkValidity() && callEventHandler({
    libObject: validHandlers,
    originalEvent: ev,
    fieldElmt: this,
  });
}

function onInvalid(ev) {
  callEventHandler({
    libObject: invalidHandlers,
    originalEvent: ev,
    fieldElmt: this,
  });
}

const showPwIcon = document.querySelectorAll('.show-password-icon');
const passwordField = document.querySelectorAll('.password-field');

for (let i = 0; i < showPwIcon.length; i++) {
  showPwIcon[i].classList.add(`show-password-icon-${i}`);
}

for (let i = 0; i < passwordField.length; i++) {
  passwordField[i].classList.add(`password-field-${i}`);
  const showPwIconClass = `.show-password-icon-${i}`;
  const passwordFieldClass = `.password-field-${i}`;
  const passwordFieldThis = document.querySelector(passwordFieldClass)

  document.querySelector(showPwIconClass).addEventListener('click', (e) => {
    if (passwordFieldThis.type === "password") {
      passwordFieldThis.type = "text";
      showPwIcon[i].classList.add('password-visible');
    } else {
      passwordFieldThis.type = "password";
      showPwIcon[i].classList.remove('password-visible');
    }
  });
}

export default init;

export {
  getNativeWidgetElmt,
  enableField,
  disableField,
  changeSelectValues,
  changeSelectSelectedValue,
  changeColorSelectValues,
  changeColorSelectSelectedValue,
};
