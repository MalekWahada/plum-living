import UIComponent from '../../abstract/js-toolbox/ui-component.js';


const MINIMUM_CONTRACT_SCROLL = 80;
const EXPAND_ON_SCROLL_QUICK_TRESHOLD = 30;
const EXPAND_ON_SCROLL_LONG_TRESHOLD = 400;

let lastScrollTop = 0;
let direction = -1;
let anchor = 0;


class PlumHeader extends UIComponent {
  constructor(elmt, options = {}) {
    super(elmt);

    this.elmt = elmt;
    this.compact = false;
    this.options = Object.assign({
      contractOnScroll: true,
      expandOnScrollUp: true,
      expandOnScrollTop: true
    }, options);

    window.addEventListener('scroll', onScroll.bind(this));
    onScroll.call(this);
  }

  contract () {
    this.compact = true;
    this.elmt.classList.add('plum-header--compact');
    this.emit('contract');
  }

  expand () {
    this.compact = false;
    this.elmt.classList.remove('plum-header--compact');
    this.emit('expand');
  }

  update () {
    onScroll.call(this);
  }

  on (type, handler) {
    super.on(type, handler);

    if(type == 'contract' && this.compact) {
      this.contract();
    }
  }
}

function onScroll() {
  const scroll = window.scrollY;
  const delta = scroll - lastScrollTop;

  if(delta > 0 && direction < 0) {
    direction = 1;
    anchor = scroll;
  }
  else if(delta < 0 && direction > 0) {
    direction = -1;
    anchor = scroll;
  }

  const distanceFromAnchor = scroll - anchor;

  if (!this.compact
  && delta >= 0
  && scroll >= MINIMUM_CONTRACT_SCROLL
  && this.options.contractOnScroll) {
    this.contract();
  } else if (this.compact
  && (delta <= -EXPAND_ON_SCROLL_QUICK_TRESHOLD || distanceFromAnchor <= -EXPAND_ON_SCROLL_LONG_TRESHOLD)
  && this.options.expandOnScrollUp) {
    this.expand();
  }
  else if (scroll < MINIMUM_CONTRACT_SCROLL
  && this.options.expandOnScrollTop) {
    this.expand();
  }

  lastScrollTop = scroll;
}


let singleton = null;

function init(elmt, options) {
  if (singleton === null) {
    singleton = new PlumHeader(elmt, options);
  }

  return singleton;
}


export default init;
