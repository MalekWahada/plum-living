import { getComponent as getModal } from '../components/modal/modal.js';

export default {
  init() {
    // Recommendations modal
    const recommendationElmt = document.querySelectorAll('.recommendation-trigger');

    recommendationElmt.forEach((Elmt) => {
      Elmt.addEventListener('click', (e) => {
        const recommendationModal = getModal(document.getElementById('recommendation-modal-' + Elmt.dataset.target));
        recommendationModal.show();
        e.preventDefault();
      });
    });
  },

  finalize() {}
};
