import { breakpointMatches } from '../abstract/css-grid/css-grid.js';
import { getComponent as getModal } from '../components/modal/modal.js';
import { getComponent as getCartSummary } from '../components/plum-cart-summary/plum-cart-summary.js';
import { getRoute } from '../abstract/js-toolbox/router.js';
import { getGlobalYof } from '../abstract/js-toolbox/metrics.js';

/*
    Index
    —
    • Route Events
    • Inits
    • Main Content
    • Sidebar
    • Utilities
    ---------- ---------- ---------- ---------- ----------
  */


export default {

  /*
    • Route Events
    ---------- ---------- ---------- ---------- ----------
  */

  init({ _rid }) {
    const commonRoute = getRoute('common');

    // Sidebar
    this.sidebarElmt = document.querySelector('.tunnel-sidebar');
    window.addEventListener('resize', this.updateStickySidebarThreshold.bind(this));
    window.addEventListener('scroll', this.checkSidebarStickyness.bind(this));
    this.updateStickySidebarThreshold();

    // Main content
    document.querySelector('.tunnel').addEventListener('click', (ev) => {
      const facadeElmt = ev.target.closest('.facade:not([disabled])');
      const facadeSecondBtn = ev.target.closest('.tunnel-second__button:not([disabled]');

      if (facadeElmt !== null) {
        if (this.hijackButtonClick && facadeSecondBtn && facadeSecondBtn.dataset.hasRecommendations) {
          ev.preventDefault();
          this.hijackButtonClick();

          const facadeSecondBtnText = facadeSecondBtn.dataset.secondBtn;
          facadeSecondBtn.innerHTML = facadeSecondBtnText;
        } else if (facadeElmt.dataset.url !== undefined) {
          this.navigateTo({
            url: facadeElmt.dataset.url,
            itemType: facadeElmt.dataset.breadcrumbType,
            itemValue: facadeElmt.dataset.breadcrumbValue,
          });
        }
      }
    });

    // Product modal
    const tunnelModalElmt = document.getElementById('tunnel-modal');

    if (tunnelModalElmt) {
      const tunnelModal = getModal(tunnelModalElmt);

      tunnelModal.on('loadContent', (ev) => {
        // get selected product option color variant image
        this.handleProductModalColorClick(tunnelModal);
        commonRoute.initWidgets(tunnelModal.dialogElmt);
        tunnelModal
          .dialogElmt
          .querySelector('.form-cart-tunnel')
          .addEventListener('submit', this.onProductFormSubmit.bind(tunnelModal.dialogElmt));
      });
    }

    // Step init
    this.initStep(_rid);
  },

  finalize() {},


  /*
    • Inits
    ---------- ---------- ---------- ---------- ----------
  */

  stepsInit: {
    // Eshop steps
    facadeGetDesigns(rootElmt) {
    },

    facadeGetFinishes(rootElmt) {
      setTimeout(() => {
        this.scrollToElement(document.querySelector('.tunnel-second'));
      }, 100);
    },

    facadeGetColors(rootElmt) {
      const recommandtionsElmt = document.querySelector('.recommendations');

      if (recommandtionsElmt) {
        this.hijackButtonClick = () => {
          this.scrollToElement(recommandtionsElmt);
          this.hijackButtonClick = null;
        };
      }

      setTimeout(() => {
        this.scrollToElement(document.querySelector('.tunnel-second .details__img'));
      }, 100);
    },

    facadeGetColorCombination(rootElmt) {
      this.stepsInit.facadeGetColors.call(this, rootElmt);
    },

    facadeGetProducts(rootElmt) {
      rootElmt = rootElmt === undefined ? document.querySelector('.tunnel-third') : rootElmt;

      const nextStepBtn = document.querySelector('.tunnel-second__button');
      if (nextStepBtn !== null) {
        nextStepBtn.remove();
      }
      const categoryBtn = document.querySelector('.tunnel-sidebar__item.category');
      if (categoryBtn !== null) {
        categoryBtn.classList.add('active');
      }

      this.scrollToElement(document.querySelector('.tunnel-third'));

      const tunnelModal = getModal(document.getElementById('tunnel-modal'));

      // Modal
      rootElmt.querySelectorAll('[data-url]').forEach((productElmt) => {
        productElmt.addEventListener('click', (e) => {
          e.preventDefault();
          const modalUrl = productElmt.dataset.url;
          const productObj = productElmt.dataset.object;
          tunnelModal.elmt.dataset.sourceUrl = modalUrl;
          tunnelModal.show();
          gtmModalProductClick(productObj);
        });
      });

      // Product Items
      const productsItemAll = rootElmt.querySelectorAll('.tunnel-products-item');

      productsItemAll.forEach((product) => {
        const optionsContainerAll = rootElmt.querySelectorAll('.select-custom .options');
        const productsItemFooterBtnValidate = product.querySelector('.btn-validate');
        const productsItemFooterTop = product.querySelector('.tunnel-products-item-footer__top');
        const productsItemForm = product.querySelector('.tunnel-products-item-footer-form');
        const formUrl = product.dataset.formUrl;
        let mouseIsOver = false;

        product.addEventListener('mouseenter', (e) => {
          e.preventDefault();
          mouseIsOver = true;

          if (productsItemForm.childElementCount === 0) {
            $.ajax({
              type: 'GET',
              url: formUrl,
            })
              .done((data) => {
                productsItemForm.innerHTML = data;
                const commonRoute = getRoute('common');
                commonRoute.initWidgets(productsItemForm);

                const addToCartForm = product.querySelector('.form-cart-tunnel-card');
                addToCartForm && addToCartForm.addEventListener('submit', this.onProductFormSubmit.bind(product));

                if (mouseIsOver) {
                  product.classList.add('item-purchase');
                  if (productsItemFooterTop !== null) {
                    productsItemFooterTop.classList.remove('block-top');
                  }
                }
              });
          } else {
            product.classList.add('item-purchase');
            if (productsItemFooterTop !== null) {
              productsItemFooterTop.classList.remove('block-top');
            }
          }
        });

        product.addEventListener('mouseleave', (e) => {
          e.preventDefault();
          mouseIsOver = false;

          product.classList.remove('item-purchase');
          productsItemFooterBtnValidate.classList.remove('validate');
          product.classList.remove('validate');

          optionsContainerAll.forEach((option) => {
            if (option.classList.contains('open')) {
              option.classList.remove('open');
            }
          });
        });
      });

      this.setupSidebarProductCategories();
    },

    // Scanner steps

    appPlumScannerGetDesigns(rootElmt) {
      console.log('appPlumScannerGetDesigns', 'using facadeGetDesigns');
      this.initStep('facadeGetDesigns');
    },

    appPlumScannerGetFinishes(rootElmt) {
      console.log('appPlumScannerGetFinishes', 'using facadeGetFinishes');
      this.initStep('facadeGetFinishes');
    },

    appPlumScannerGetColors(rootElmt) {
      console.log('appPlumScannerGetColors', 'using facadeGetColors');
      this.initStep('facadeGetColors');
    },

    appPlumScannerGetColorCombination(rootElmt) {
      console.log('appPlumScannerGetColorCombination', 'using facadeGetColorCombination');
      this.initStep('facadeGetColorCombination');
    },

    // Listing

    listingAccessoires(rootElmt) {
      this.initStep('facadeGetProducts');
    },

    listingSampleFacade(rootElmt) {
      this.initStep('facadeGetProducts');
    },

    listingSamplePaintMural(rootElmt) {
      this.initStep('facadeGetProducts');
    },

    listingPaint(rootElmt) {
      this.initStep('facadeGetProducts');
    },

    listingTap(rootElmt) {
      this.initStep('facadeGetProducts');
    },
  },


  /*
    • Main Content
    ---------- ---------- ---------- ---------- ----------
  */

  navigateTo({ url, itemType, itemValue }) {
    $.ajax({
      url: url,
      cache: false,
      success: (response, status, xhr) => {
        const tunnelMainContent = document.querySelector('.tunnel-main-content');
        const initialSidebarListSelector = document.querySelector('.tunnel-sidebar__list .tunnel-sidebar__item__categories');
        const sidebarList = initialSidebarListSelector !== null ? initialSidebarListSelector : document.querySelector('.tunnel-sidebar__list');
        const sideBarMenu = document.querySelector('.tunnel-sidebar');

        if (typeof response === 'string') {
          tunnelMainContent.innerHTML = response;
          if (sidebarList) {
            sidebarList.innerHTML = '';
          }
        } else {
          tunnelMainContent.innerHTML = response.mainView;
          document.querySelectorAll('.tunnel-sidebar__list .category').forEach((elmt) => { elmt.remove(); });
          sideBarMenu.innerHTML = response.sideBarMenu;

          sidebarList.insertAdjacentHTML('beforeend', response.secondaryView);
        }
        history.pushState('', '', url);


        switch (itemType) {
          case 'design':
            this.updateSidebar(itemType, itemValue, url);
            this.initStep('facadeGetFinishes', document.querySelector('.tunnel-second'));
            break;
          case 'finish':
            this.updateSidebar(itemType, itemValue, url);
            this.initStep('facadeGetColors', document.querySelector('.tunnel-second'));
            break;
          case 'color':
            this.updateSidebar(itemType, itemValue, url);
            this.initStep('facadeGetColorCombination', document.querySelector('.tunnel-second'));
            break;
          case 'product':
            this.initStep('facadeGetProducts', document.querySelector('.tunnel-third'));
            break;
        }
      },
    });
  },

  onProductFormSubmit(ev) {
    const product = this;
    const productsItemFooterBtnValidate = product.querySelector('.btn-validate');
    const productsItemFooterTop = product.querySelector('.tunnel-products-item-footer__top');
    const formElmt = ev.currentTarget;
    const productObj = product.dataset.object;

    ev.preventDefault();
    $.ajax({
      type: 'POST',
      url: formElmt.action,
      data: $(formElmt).serialize(),
    })
      .done((data) => {
        product.classList.remove('item-purchase');
        if (productsItemFooterBtnValidate) {
          const qty = formElmt.querySelector('input[name*="[cartItem][quantity]"]').value;
          productsItemFooterBtnValidate.querySelector('.quantity-added').innerHTML = ` +${qty}`;
          product.classList.remove('item-purchase');
          productsItemFooterBtnValidate.classList.add('validate');
          product.classList.add('validate');

          if (productsItemFooterTop !== null) {
            productsItemFooterTop.classList.add('block-top');
          }

          setTimeout(() => {
            productsItemFooterBtnValidate.classList.remove('validate');
            if (productsItemFooterTop !== null) {
              productsItemFooterTop.classList.remove('block-top');
            }
          }, 2000);
        }


        const cartSummary = getCartSummary(document.querySelector('.plum-cart-summary'));
        gtmAddToCart(productObj);
        cartSummary.refresh();
      });
  },


  /*
    • Sidebar
    ---------- ---------- ---------- ---------- ----------
  */

  sidebarElmt: null,
  sidebarIsStickyAt: 70,

  updateStickySidebarThreshold() {
    this.sidebarIsStickyAt =
      getGlobalYof(this.sidebarElmt) - document.querySelector('.plum-header').offsetHeight;
  },

  checkSidebarStickyness() {
    if (this.sidebarElmt !== null) {
      this.sidebarElmt
        .classList[window.pageYOffset >= this.sidebarIsStickyAt ? 'add' : 'remove']('tunnel-sidebar--sticky');
    }
  },

  updateSidebar(type, value, url) {
    const breadcrumbElmt = document.querySelector(`.${type}-breadcrumb`);
    breadcrumbElmt.innerHTML = value;

    if (type !== 'finish') {
      const sidebarItemElmt = breadcrumbElmt.closest('.tunnel-sidebar__item');
      const sidebarSiblingElmt = sidebarItemElmt ? sidebarItemElmt.nextElementSibling : null;
      sidebarItemElmt && sidebarItemElmt.classList.add('validate');
      sidebarItemElmt && sidebarItemElmt.classList.remove('active');
      sidebarSiblingElmt && sidebarSiblingElmt.classList.add('active');
    }

    breadcrumbElmt.href = url;
  },

  setupSidebarProductCategories() {
    const categoryElmt = document.querySelector('.tunnel-third__container');
    const categories = document.querySelectorAll('.product-category');
    const productCategories = document.querySelectorAll('.tunnel-products');

    if (!this.categoryOnScroll) {
      // On scroll highlight correspondant sidebar category
      const categoryOnScroll = () => {
        let currentCategory = '';
        productCategories.forEach((productCategory) => {
          const productCategoryTop = productCategory.offsetTop;

          if (pageYOffset >= productCategoryTop) {
            currentCategory = productCategory.getAttribute('id');
          }
        });

        categories.forEach((ctg) => {
          ctg.classList.remove('active');

          if (ctg.dataset.link === currentCategory) {
            ctg.classList.add('active');
          }
        });
      };

      window.addEventListener('scroll', categoryOnScroll);

      this.categoryOnScroll = true;
    }

    // On click scroll to correspondant sidebar category
    categories.forEach((category) => {
      category.addEventListener('click', (e) => {
        e.preventDefault();

        const categoryLink = category.dataset.link;
        const categoryTarget = document.querySelector('#' + categoryLink);

        if (categoryLink) {
          this.scrollToElement(categoryTarget);
        } else {
          this.scrollToElement(categoryElmt);
        }
      });
    });

    if (categoryElmt) {
      this.scrollToElement(categoryElmt);
    }
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

  scrollToElement(elmt) {
    const scrollY = window.scrollY;
    const targetY = getGlobalYof(elmt);
    const bannerElmt = document.querySelector('.plum-header__banner');
    const headerHeight = targetY > scrollY
      ? parseFloat(window.getComputedStyle(document.querySelector('.plum-header')).getPropertyValue('--compact-height')) * 16
      : parseFloat(window.getComputedStyle(document.querySelector('.plum-header')).getPropertyValue('--normal-height')) * 16;
    const bannerHeight = !bannerElmt || targetY > scrollY
      ? 0
      : bannerElmt.offsetHeight;
    const sidebarHeight = !breakpointMatches('large') && this.sidebarElmt ? this.sidebarElmt.offsetHeight : 0;

    window.scrollTo({
      top: targetY - (headerHeight + sidebarHeight + bannerHeight + 32),
      behavior: 'smooth',
    });
  },

  handleProductModalColorClick(tunnelModal) {
    const colorsOptions = tunnelModal.dialogElmt.querySelector('.color-select-field__options');
    if (colorsOptions) {
      colorsOptions.addEventListener('click', (event) => {
        const colorButton = event.target.closest('[data-variant-image-url]');
        const imageVariantUrl = colorButton ? colorButton.dataset.variantImageUrl : null;
        if (null !== imageVariantUrl) {
          $.ajax({
            type: 'GET',
            url: imageVariantUrl,
          })
            .done((data) => {
              const imagesContainer = document.getElementById('product-modal-images-container');
              let imageHtml = null;
              if (Object.keys(data || {}).length !== 0 && data !== null) {
                let variantImageTemplate = document.getElementById('plum-product-modal-image__variant-template').innerHTML;
                variantImageTemplate = variantImageTemplate
                  .replaceAll('%IMAGE_SRC%', data.src)
                  .replaceAll('%IMAGE_SRC_SET%', `${data.srcset} 2x`);
                imageHtml = variantImageTemplate;
              } else {
                imageHtml = document.getElementById('plum-product-modal-image__empty-template').innerHTML;
              }
              imagesContainer.innerHTML = imageHtml;
            });
        }
      });
    }
  },
};
