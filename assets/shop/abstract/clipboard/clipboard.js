import popHint from '../../components/hint/hint.js';



function copyToClipboard (value) {
  const copyableElmt = createCopyableElmt(value);
  const hadFocus = document.activeElement;

  copyableElmt.focus();
  copyableElmt.selectionStart = 0;
  copyableElmt.selectionEnd = value.length;
  const result = document.execCommand('copy');
  copyableElmt.disabled = true;
  hadFocus && hadFocus.focus();
  copyableElmt.remove();

  document.dispatchEvent(new Event('copyToClipboard'));
  return result;
}

function createCopyableElmt (value) {
  const copyableElmt = document.createElement('textarea');
  
  copyableElmt.style.position = 'fixed';
  copyableElmt.style.clip = 'rect(0,0,0,0)';
  copyableElmt.style.zIndex = -10;
  copyableElmt.style.left = 0;
  copyableElmt.style.top = 0;
  copyableElmt.style.opacity = 0;
  copyableElmt.value = value;
  
  document.body.appendChild(copyableElmt);

  return copyableElmt;
}



function onClick (ev) {
  const value = ev.currentTarget.dataset.clipboard;
  
  if(copyToClipboard(value)) {
    popHint(
      ev.currentTarget.dataset.clipboardCopiedText || 'Copied',
      { left: ev.clientX, top: ev.clientY - 16 }
    );
  }
}



function init (rootElmt) {
  (rootElmt || document).querySelectorAll('[data-clipboard]').forEach(elmt => {
    elmt.addEventListener('click', onClick);
  });
}



export default init;