import {getGlobalYof} from "../abstract/js-toolbox/metrics";

export default {
  init() {
    const links = document.querySelectorAll('.cms-anchor');

    links.forEach((link) => {
        link.addEventListener('click', (e) => {
          e.preventDefault();
          this.scrollToElement(document.querySelector('#cms-anchor'));
        });
    });
  },
  finalize() {},

  scrollToElement(elmt) {
    const headerHeight = parseFloat(window.getComputedStyle(document.querySelector('.plum-header')).getPropertyValue('--compact-height')) * 16;

    window.scrollTo({
      top: getGlobalYof(elmt) - (headerHeight + 60),
      behavior: 'smooth',
    });
  },
};

