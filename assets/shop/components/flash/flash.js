const AUTO_DISMISS_DELAY = 5000;
const AUTO_REMOVE_DELAY = 300;


let instance = null;

function reset(flashElmt, startDelay = 300) {
  if (instance !== null && 'timeout' in instance.dataset) {
    clearTimeout(parseInt(instance.dataset.timeout));
  }

  flashElmt.dataset.shouldAutoDismiss = true;
  flashElmt.querySelector('.ui-flash__timer circle').style.transitionDuration = '';
  flashElmt.classList.remove('ui-flash--waiting');
  flashElmt.classList.remove('ui-flash--leaving');

  instance = flashElmt;

  flashElmt.dataset.timeout = setTimeout(() => {
    start(flashElmt);
  }, startDelay);
}

function start(flashElmt) {
  flashElmt.querySelector('.ui-flash__timer circle').style.transitionDuration = AUTO_DISMISS_DELAY + 'ms';
  flashElmt.classList.add('ui-flash--ready');
  flashElmt.classList.add('ui-flash--waiting');

  flashElmt.dataset.timeout = setTimeout(() => {
    flashElmt.dataset.shouldAutoDismiss && dimiss(flashElmt);
  }, AUTO_DISMISS_DELAY);
}

function dimiss(flashElmt) {
  instance = null;
  flashElmt.classList.add('ui-flash--leaving');

  setTimeout(() => {
    flashElmt.remove();
  }, AUTO_REMOVE_DELAY);
}

function stop(flashElmt) {
  if (flashElmt.matches(':hover')) {
    delete flashElmt.dataset.shouldAutoDismiss;
    flashElmt.classList.remove('ui-flash--waiting');
    flashElmt.querySelector('.ui-flash__timer circle').style.transitionDuration = '';
  }
}


function onClick(ev) {
  if (ev.target.closest('.ui-flash__close-button')) {
    dimiss(this);
  }
}

function onScroll() {
  stop(this);
}

function onLoad(flashElmtList) {
  flashElmtList.forEach((flashElmt) => {
    reset(flashElmt);
  });
}

function initSingle(flashElmt) {
  flashElmt.addEventListener('click', onClick);
  flashElmt.querySelector('.ui-flash__list').addEventListener('scroll', onScroll.bind(flashElmt));
}

function init(rootElmt) {
  const containerTemplateElmt = document.getElementById('Flash-container-template');
  const messageTemplateElmt = document.getElementById('Flash-message-template');

  if (containerTemplateElmt && messageTemplateElmt) {
    containerTemplate = containerTemplateElmt.innerHTML;
    messageTemplate = messageTemplateElmt.innerHTML;
  }

  const flashElmtList = (rootElmt || document).querySelectorAll('.ui-flash');

  flashElmtList.forEach(initSingle);

  window.addEventListener('load', () => {
    onLoad(flashElmtList);
  });
}


let containerTemplate = null;
let messageTemplate = null;

function getMessageClassname(type) {
  const classname = 'message u-flex u-start-on-cross u-padding-2 u-padding-r-3';

  switch (type) {
    case 'success':
      return `${classname} bg-green-light c-true-white`;
    case 'error':
      return `${classname} bg-pink-light c-pink`;
    case 'warning':
      return `${classname} bg-beige c-near-black`;
    default:
      return `${classname} bg-beige c-near-black`;
  }
}

function getMessageIcon(type) {
  switch (type) {
    case 'success':
      return 'check';
    case 'error':
      return 'warning';
    case 'warning':
      return 'warning';
    default:
      return 'arrow-right';
  }
}

function getMessageHeader(type) {
  const uiData = document.getElementById('Flash-ui-data');

  switch (type) {
    case 'success':
      return uiData.dataset.success;
    case 'error':
      return uiData.dataset.error;
    case 'warning':
      return uiData.dataset.warning;
    default:
      return uiData.dataset.info;
  }
}

function elementFromHtml(html) {
  const elmt = document.createElement('div');
  elmt.innerHTML = html;
  return elmt.firstElementChild;
}

function createMessage(type, content) {
  return messageTemplate
    .replaceAll('%CLASSNAME%', getMessageClassname(type))
    .replaceAll('%ICON_LIB_URL%', document.getElementById('Flash-ui-data').dataset.iconLibUrl)
    .replaceAll('%ICON%', getMessageIcon(type))
    .replaceAll('%HEADER%', getMessageHeader(type))
    .replaceAll('%CONTENT%', content);
}

function createContainer(type, content) {
  return containerTemplate
    .replaceAll('%MESSAGE%', createMessage(type, content))
    .replaceAll('%ICON_LIB_URL%', document.getElementById('Flash-ui-data').dataset.iconLibUrl);
}

function createFlash(type, content) {
  if (containerTemplate === null || messageTemplate === null) {
    return;
  }

  if (instance !== null) {
    // There's a flash element already, we're just adding a message to it
    // and resetting the auto-dismiss timer.
    const listElmt = instance.querySelector('.ui-flash__list');

    listElmt.appendChild(elementFromHtml(createMessage(type, content)));
    listElmt.scrollTo({ top: listElmt.scrollHeight, behavior: 'smooth' });

    reset(instance);
  } else {
    // There's no flash element, we're making a new one.
    const flashElmt = elementFromHtml(createContainer(type, content));

    document.body.appendChild(flashElmt);

    initSingle(flashElmt);
    reset(flashElmt, 16);
  }
}


export default init;
export { createFlash };
