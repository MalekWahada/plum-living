const FRAME_SKIP_DELAY = 50;
const TRANSITION_DURATION = 200;



function popHint (text, { left, top, removeDelay = 500 }) {
  const hintElmt = document.createElement('span');
  
  hintElmt.className = 'ui-hint t-base-small';
  hintElmt.innerHTML = text;

  if(left !== undefined) {
    hintElmt.style.left = left + 'px';
  }

  if(top !== undefined) {
    hintElmt.style.top = top + 'px';
  }

  document.body.appendChild(hintElmt);

  setTimeout(() => {
    hintElmt.classList.add('ui-hint--visible');
  }, FRAME_SKIP_DELAY);

  setTimeout(() => {
    hintElmt.classList.remove('ui-hint--visible');
  }, FRAME_SKIP_DELAY + removeDelay + TRANSITION_DURATION);

  setTimeout(() => {
    hintElmt.remove();
  }, FRAME_SKIP_DELAY + removeDelay + TRANSITION_DURATION * 2);
}



export default popHint;