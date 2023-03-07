function init (rootElmt) {
  (rootElmt || document).querySelectorAll('.steps-nav').forEach(elmt => {
    const activeStepElmt = elmt.querySelector('.steps-nav__step--active');

    if(!activeStepElmt) {
      return;
    }

    elmt.scrollTo({
      left: activeStepElmt.offsetLeft - elmt.querySelector('.steps-nav__step').offsetLeft
    });

    elmt.classList.add('steps-nav--ready');
  });
}



export default init;