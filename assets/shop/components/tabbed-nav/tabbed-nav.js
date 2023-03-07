function init (rootElmt) {
  (rootElmt || document).querySelectorAll('.tabbed-nav').forEach(elmt => {
    const selectedTabElmt = elmt.querySelector('.tabbed-nav__item--selected');

    if(selectedTabElmt) {
      const marginWidth = elmt.querySelector('.tabbed-nav__item').offsetLeft;
      const remainderWidth = window.innerWidth - selectedTabElmt.offsetWidth - marginWidth * 2;

      elmt.scrollTo({
        left: selectedTabElmt.offsetLeft - marginWidth - remainderWidth / 2
      });
    }

    elmt.classList.add('tabbed-nav--ready');
  });
}



export default init;