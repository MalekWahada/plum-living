import getSymbolFromCurrency from 'currency-symbol-map';
import { createPrompt, getComponent as getModal } from '../components/modal/modal';


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
    appPlumScannerProjectStatus (rootElmt) {
      const BACKGROUND_CYCLING_DELAY = 3000;
      const TIMEOUT_DELAY = 5 * 60 * 1000;

      this.refreshIterationCount = 0;
      this.applyRefreshDelay();
      this.backgroundImages = document.querySelectorAll('.plum-scanner-background');
      this.currentBackgroundIndex = 0;

      this.backgroundImages[0].style.zIndex = 1;
      this.backgroundImages[0].style.opacity = 1;

      setTimeout(() => {
        this.nextBackgroundImage(BACKGROUND_CYCLING_DELAY)
      }, BACKGROUND_CYCLING_DELAY);

      const setTimeoutCallback = () => {
        setTimeout(() => {
          const timeoutUrl = document.getElementById('scan-timeout-url').value;

          $.ajax({
            url: timeoutUrl,
            type: 'GET',
            complete: () => {
              const timeoutPrompt = createPrompt({
                title: document.getElementById('scan-timeout-modal-title').value,
                message: document.getElementById('scan-timeout-modal-message').value,
                confirmLabel: document.getElementById('scan-timeout-modal-confirm').value,
                dismissLabel: document.getElementById('scan-timeout-modal-dismiss').value
              });

              // Wait a couple of frames so that the browser does not 'optimize' the modal enter transition
              setTimeout(async () => {
                const shouldRedirect = await timeoutPrompt.show();

                if(shouldRedirect) {
                  window.history.back();
                }
                else {
                  this.getProjectProgress();
                  setTimeoutCallback();
                }
              }, 32);
            }
          });
        }, TIMEOUT_DELAY);
      }

      setTimeoutCallback();
    },

    appPlumScannerSharePlan (rootElmt) {
      const PROGRESS_POLLING_RATE = 10000; // 10 seconds

      this.getProjectProgress();

      document.getElementById('IKP-versions-modal-trigger').addEventListener('click', () => {
        const modal = getModal(document.getElementById('IKP-versions-modal'));
        modal && modal.show();
      });
    },
  },



  /*
    • Step 2
    ---------- ---------- ---------- ---------- ----------
  */

  getProjectProgress () {
    let params = new URLSearchParams(window.location.search);
    if (params.get('mf') === '1') {
      let modalDom = document.getElementById('missing-front-modal');
      let modal = getModal(modalDom);
      modal && modal.show();
      document.getElementById('missing-front-modal').addEventListener('dismiss', function() {
        params.delete('mf');
        window.history.replaceState({}, '', `${location.pathname}?${params}`);
      })
      modalDom.querySelector('.button').addEventListener('click', _ => modal.dismiss());
    }

    if (!document.querySelector('.plum-scanner-loading')) {
      return;
    }
    const updateUrl = document.querySelector('.plum-scanner-loading').dataset.contentUrl;

    $.ajax({
      url: updateUrl,
      type: 'GET',
      success: (res) => {
        if (res.progress === 100) {
          gtmPlumScannerToProjectRedirect();
          // eslint-disable-next-line no-undef
          window.location.href = res.redirectUrl;
        }
      },
      error: (res) => {
        // eslint-disable-next-line no-undef
        window.location.href = res.responseJSON.redirectUrl;
      }
    });
    this.applyRefreshDelay();
  },

  loadImage (image) {
    if(image.hasAttribute('data-loaded')) {
      return Promise.resolve();
    }

    return new Promise((resolve, reject) => {
      image.onload = () => {
        image.dataset.loaded = true;
        image.removeAttribute('data-srcset');
        image.removeAttribute('data-src');
        resolve();
      };

      image.onerror = reject;
      image.srcset = image.dataset.srcset;
      image.src = image.dataset.src;
    });
  },

  nextBackgroundImage (delay) {
    const currentBackgroundImage = this.backgroundImages[this.currentBackgroundIndex];

    this.currentBackgroundIndex = this.currentBackgroundIndex < (this.backgroundImages.length - 1)
      ? this.currentBackgroundIndex + 1
      : 0;

    const image = this.backgroundImages[this.currentBackgroundIndex];

    this.loadImage(image)
    .then(() => {
      currentBackgroundImage.style.zIndex = '';
      image.style.zIndex = 1;
      image.style.opacity = 1;

      setTimeout(() => {
        currentBackgroundImage.style.opacity = '';
      }, 1000);

      setTimeout(() => {
        this.nextBackgroundImage(delay);
      }, delay);
    });
  },

  applyRefreshDelay() {
    let time = 2000; // 2 seconds
    switch (true) {
      case (this.refreshIterationCount < 5):
        time = 2000; // 2 seconds
        break;
      case (this.refreshIterationCount < 9):
        time = 5000; // 5 seconds
        break;
      case (this.refreshIterationCount >= 9):
        time = 10000; // 10 seconds
        break;
    }

    setTimeout(function () {
      this.refreshIterationCount++;
      this.getProjectProgress();
    }.bind(this), time);
  },

  /*
    • Utilities
    ---------- ---------- ---------- ---------- ----------
  */

  initStep (sid, rootElmt) {
    if(rootElmt) {
      const commonRoute = getRoute('common');
      commonRoute.initWidgets(rootElmt);
    }

    sid in this.stepsInit && this.stepsInit[sid].call(this, rootElmt);
  }
};
