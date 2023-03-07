import UIComponent from '../../abstract/js-toolbox/ui-component.js';
import GridSystem from '../../abstract/css-grid/css-grid.js';


class PlumMenu extends UIComponent {
  constructor(elmt) {
    super(elmt);
    this.imageUri = null;

    this.elmt.querySelector('.plum-menu__main').addEventListener('click', onClickMainSection.bind(this));
    this.elmt.querySelectorAll('.plum-menu__main [data-item-image]').forEach(elmt => elmt.addEventListener('mouseenter', onMouseEnterMainItem.bind(this)));

    GridSystem.on('enterlarge', onEnterLargeBreakpoint.bind(this));
  }

  get isOpen() {
    return this.elmt.classList.contains('plum-menu--open');
  }

  toggle() {
    return this.isOpen
      ? this.close()
      : this.open();
  }

  open() {
    if (GridSystem.breakpointMatches('large')) {
      const imageContainer = this.elmt.querySelector('.plum-menu__image');

      if (imageContainer.childElementCount === 0) {
        this.changeImage(this.elmt.querySelector('.plum-menu__main [data-item-image]').dataset.itemImage);
      }
    }

    this.elmt.classList.add('plum-menu--open');
    this.emit('open');
    return true;
  }

  close() {
    this.elmt.classList.remove('plum-menu--open');
    this.emit('close');
    return false;
  }

  toggleSubMenu(parentElmt) {
    parentElmt.classList.contains('plum-menu-main__parent--open')
      ? this.contractSubMenu(parentElmt)
      : this.expandSubMenu(parentElmt);
  }

  expandSubMenu(parentElmt) {
    if (this.expandedSubMenuElmt) {
      this.contractSubMenu(this.expandedSubMenuElmt);
    }

    const menuElmt = parentElmt.querySelector('.plum-menu-main__menu');
    menuElmt.style.height = menuElmt.scrollHeight + 'px';
    parentElmt.classList.add('plum-menu-main__parent--open');
    this.expandedSubMenuElmt = parentElmt;
  }

  contractSubMenu(parentElmt) {
    const menuElmt = parentElmt.querySelector('.plum-menu-main__menu');
    menuElmt.style.height = '';
    parentElmt.classList.remove('plum-menu-main__parent--open');
  }

  changeImage(uri) {
    if (uri === this.imageUri) {
      return;
    }

    const imageContainer = this.elmt.querySelector('.plum-menu__image');
    const image = new Image();

    while (imageContainer.childElementCount > 1) {
      imageContainer.lastElementChild.onload = null;
      imageContainer.lastElementChild.ontransitionend = null;
      imageContainer.lastElementChild.remove();
    }

    image.onload = onImageLoad.bind(imageContainer);
    imageContainer.classList.remove('plum-menu__image--loaded');
    imageContainer.appendChild(image);
    image.src = uri;
    this.imageUri = uri;
  }
}


function onClickMainSection(ev) {
  const parentElmt = ev.target.closest('.plum-menu-main__expander') || ev.target.closest('.plum-nav__unlink')
    ? ev.target.closest('.plum-menu-main__parent')
    : null;

  if (parentElmt) {
    this.toggleSubMenu(parentElmt);
    ev.preventDefault();
  }
}


function onMouseEnterMainItem(ev) {
  if (!GridSystem.breakpointMatches('large')) {
    return;
  }

  if (ev.currentTarget.dataset.navDepth == '1') {
    const uri = ev.currentTarget.dataset.itemImage;
    this.changeImage(uri);
  }
}


function onImageLoad(ev) {
  const imageContainer = this;

  ev.target.onload = null;
  ev.target.ontransitionend = onImageTransitionEnd.bind(imageContainer);

  imageContainer.classList.add('plum-menu__image--loaded');
}


function onImageTransitionEnd(ev) {
  const imageContainer = this;

  ev.target.ontransitionend = null;

  while (imageContainer.childElementCount > 1) {
    imageContainer.firstElementChild.remove();
  }
}


function onEnterLargeBreakpoint(ev) {
  const imageContainer = this.elmt.querySelector('.plum-menu__image');

  if (imageContainer.childElementCount === 0) {
    this.changeImage(this.elmt.querySelector('.plum-menu__main [data-item-image]').dataset.itemImage);
  }
}


let singleton = null;

function init(elmt) {
  if (singleton === null) {
    singleton = new PlumMenu(elmt);
  }

  return singleton;
}


export default init;
