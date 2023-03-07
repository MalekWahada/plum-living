let menu;


function init ({ plumMenu, elmtList }) {
  elmtList.forEach(elmt => {
    menu = plumMenu;
    elmt.addEventListener('click', togglePlumMenu);
  });
}


function togglePlumMenu (ev) {
  ev.currentTarget.classList[menu.toggle() ?'add' : 'remove']('plum-menu-toggle--close-form');
}



export default init;