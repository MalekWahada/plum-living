import initProject from '../components/customer-project/ProjectManager';

/*
  Index
  —
  • Route Events
  • Inits
  • Utilities
  ---------- ---------- ---------- ---------- ----------
*/


export default {

  /*
      • Route Events
      ---------- ---------- ---------- ---------- ----------
    */

  init({ _rid }) {
    // Step init
    this.initStep(_rid);
  },

  finalize() {},


  /*
      • Inits
      ---------- ---------- ---------- ---------- ----------
    */

  stepsInit: {
    appCustomerProjectShow(rootElmt) {
      initProject(document.querySelector('.ps-project'));
    },
  },

  /*
      • Utilities
      ---------- ---------- ---------- ---------- ----------
    */

  initStep(sid, rootElmt) {
    if (rootElmt) {
      const commonRoute = getRoute('common');
      commonRoute.initWidgets(rootElmt);
    }

    sid in this.stepsInit && this.stepsInit[sid].call(this, rootElmt);
  },
};
