import UIComponent from '../../abstract/js-toolbox/ui-component';
import { getGlobalXof, getGlobalYof } from '../../abstract/js-toolbox/metrics.js';



class RippleButton extends UIComponent {
  constructor(elmt) {
    super(elmt);

    this.elmt.addEventListener('click', onClick);
    this.elmt.addEventListener('animationend', onAnimationEnd);
  }
}


const createRipple = {
  html (buttonElmt, x, y) {
    const ripple = document.createElement('span');
    const size = Math.max(buttonElmt.offsetWidth, buttonElmt.offsetHeight);

    ripple.className = 'ripple-button__ripple';
    ripple.style.width = size + 'px';
    ripple.style.height = size + 'px';
    ripple.style.left = (x - size / 2) + 'px';
    ripple.style.top = (y - size / 2) + 'px';

    return ripple;
  },

  svg (buttonElmt, x, y) {
    const ripple = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
    const size = Math.max(buttonElmt.clientWidth, buttonElmt.clientHeight);

    ripple.setAttribute('class', 'ripple-button__ripple');
    ripple.style.r = size + 'px';
    ripple.style.cx = x + 'px';
    ripple.style.cy = y + 'px';

    return ripple;
  }
}


function onClick(ev) {
  const ripple = createRipple[ev.currentTarget.tagName.toLowerCase() == 'svg' ? 'svg' : 'html'](
    ev.currentTarget,
    ev.pageX - getGlobalXof(ev.currentTarget),
    ev.pageY - getGlobalYof(ev.currentTarget)
  );

  ev.currentTarget.appendChild(ripple);
}


function onAnimationEnd(ev) {
  ev.currentTarget.querySelector('.ripple-button__ripple').remove();
}


function init(rootElmt) {
  (rootElmt || document).querySelectorAll('.ripple-button').forEach(elmt => {
    new RippleButton(elmt)
  });
}



export default init;
