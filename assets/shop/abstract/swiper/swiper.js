import Swiper, {
  Navigation, Pagination, Autoplay, EffectFade, Mousewheel, Keyboard,
} from 'swiper';

Swiper.use([Navigation, Pagination, Autoplay, EffectFade, Mousewheel, Keyboard]);

// Slider other projects
const cmsSlider = new Swiper('.cms-slider__container', {
  slidesPerView: 'auto',
  spaceBetween: 20,
  centeredSlides: true,
  loop: true,
  mousewheel: {
    forceToAxis: true,
  },
  observeParents: true,
  observer: true,
  breakpoints: {
    720: {
      spaceBetween: 30,
    },
    1000: {
      spaceBetween: 30,
    },
  },
  navigation: {
    nextEl: '.cms-slider__arrow--right',
  },
});

// apply color to a project's text in the cross content
const crossContentColorTag = document.querySelectorAll('.cms-slider__color');
crossContentColorTag.forEach((singleDiv) => {
  const pTag = singleDiv.querySelector('.color_target');
  pTag.style.color = pTag.dataset.color;
});

// Slider plans
const sliderBudgetContainer = document.querySelector('.cms-slider-budget__container');
const sliderBudgetSlides = document.querySelectorAll('.cms-slider-budget__container .swiper-slide');

if (sliderBudgetContainer && sliderBudgetSlides.length > 1) {
  sliderBudgetContainer.classList.add('active');

  const cmsSliderBudget = new Swiper(sliderBudgetContainer, {
    slidesPerView: 'auto',
    spaceBetween: 40,
    centredSlides: false,
    loop: true,
    breakpoints: {
      1028: {
        slidesPerView: 'auto',
        spaceBetween: 40,
      },
      300: {
        slidesPerView: 'auto',
        spaceBetween: 20,
      },
    },
    navigation: {
      nextEl: '.cms-slider__arrow--right',
    },
  });
}

// Slider Header - page projet
const sliderHeaderContainer = document.querySelector('.cms-slider-header__container');
const sliderHeaderSlides = document.querySelectorAll('.cms-slider-header__container .swiper-slide');

if (sliderHeaderContainer && sliderHeaderSlides.length > 1) {
  sliderHeaderContainer.classList.add('active');

  const cmsSliderHeader = new Swiper(sliderHeaderContainer, {
    observer: true,
    spaceBetween: 50,
    observeParents: true,
    centredSlides: false,
    parallax: false,
    loop: true,
    draggable: false,
    breakpoints: {
      1028: {
        slidesPerView: 'auto',
        spaceBetween: 50,
      },
      300: {
        slidesPerView: 'auto',
        spaceBetween: 20,
      },
    },
    navigation: {
      nextEl: '.cms-slider__arrow--right',
    },
  });
}

// Main Slider Home
const cmsSliderHome = new Swiper('.cms-slider-home__container', {
  slidesPerView: 'auto',
  spaceBetween: 20,
  centeredSlides: true,
  loop: true,
  observeParents: true,
  observer: true,
  breakpoints: {
    720: {
      spaceBetween: 30,
    },
    1000: {
      spaceBetween: 50,
    },
  },
  navigation: {
    nextEl: '.cms-slider__arrow--right',
    prevEl: '.cms-slider__arrow--left',
  },
  pagination: {
    el: '.swiper-pagination-two',
    type: 'bullets',
    clickable: true,
  },
});

// Slider Color Palette
const $colorsSlider = $('.colors-slider').data('content');
const $colorNameTag = $('.color-combination-name');
const $colorDescriptionTag = $('.color-combination-desc');
const $colorSliderBack = $('.cms-palette-img__container');
const $colorNameImg = $('.slide-color-name');

$('.swiper-pagination').click((event) => {
  const target = $(event.target);

  $colorNameImg.html(target.data('name'));
  $colorNameTag.text(target.data('name'));
  $colorDescriptionTag.text(target.data('desc'));
  $colorSliderBack.css('background-color', target.data('colorhex'));
});

const cmsSliderColor = new Swiper('.img-slider', {
  spaceBetween: 0,
  effect: 'fade',
  autoplay: {
    delay: 4000,
    disableOnInteraction: false,
  },
  slidesPerView: 'auto',
  draggable: false,
  allowTouchMove: false,
  pagination: {
    el: '.swiper-pagination',
    clickable: true,
    renderBullet(index, className) {
      const $colorObj = $colorsSlider[index];
      return `<div style="background-image: url(${$colorObj.chipImage})" id="span-slide" data-desc="${$colorObj.colorDesc}" data-colorHex="${$colorObj.colorHex}" data-id="${$colorObj.colorCode}" data-name="${$colorObj.colorName}" class="cms-palette__color ${className}"></div>`;
    },
  },
  on: {
    slideChange(swiper) {
      const color = $colorsSlider[swiper.activeIndex];

      $colorNameImg.html(color.colorName);
      $colorNameTag.text(color.colorName);
      $colorDescriptionTag.text(color.colorDesc);
      $colorSliderBack.css('background-color', color.colorHex);
    },
  },
});

// Slider recommendations
if (window.innerWidth < 1000 && document.querySelector('.reco-slider')) {
  const recoSlider = new Swiper('.reco-slider', {
    slidesPerView: 'auto',
    spaceBetween: 40,
    loop: true,
    observeParents: true,
    observer: true,
  });
}

// Slider images - Single product - Eshop
const sliderSingleProductContainer = document.querySelector('.single-product-header__left');
const sliderSingleProductSlide = document.querySelectorAll('.single-product-header__left-img.swiper-slide');
const sliderSingleProductArrow = document.querySelectorAll('.single-product-header__left .single-product-arrow');

if (sliderSingleProductContainer && sliderSingleProductSlide.length > 1) {
  const sliderSingleProduct = new Swiper('.single-product-header__left', {
    slidesPerView: 'auto',
    spaceBetween: 20,
    centeredSlides: true,
    loop: true,
    observeParents: true,
    observer: true,
    breakpoints: {
      720: {
        spaceBetween: 30,
      },
      1000: {
        spaceBetween: 50,
      },
    },
    navigation: {
      nextEl: '.single-product-arrow--right',
      prevEl: '.single-product-arrow--left',
    },
  });
} else {
  sliderSingleProductArrow.forEach((elmt) => {
    elmt.style.display = 'none';
  });
}

const thematiqueSlider = document.querySelectorAll('.swiper-thematique');
const thematiqueSliderArrowsRight = document.querySelectorAll('.slider-thematique--arrow--right');
const thematiqueSliderArrowsLeft = document.querySelectorAll('.slider-thematique--arrow--left');

for (let i = 0; i < thematiqueSliderArrowsRight.length; i++) {
  thematiqueSliderArrowsRight[i].classList.add(`slider-thematique--arrow--right-${i}`);
}

for (let i = 0; i < thematiqueSliderArrowsLeft.length; i++) {
  thematiqueSliderArrowsLeft[i].classList.add(`slider-thematique--arrow--left-${i}`);
}

for (let i = 0; i < thematiqueSlider.length; i++) {
  thematiqueSlider[i].classList.add(`swiper-thematique-${i}`);
  const thematiqueSliderArrowsRightClass = `slider-thematique--arrow--right-${i}`;
  const thematiqueSliderArrowsLeftClass = `slider-thematique--arrow--left-${i}`;

  const slider = new Swiper(`.swiper-thematique-${i}`, {
    slidesPerView: 'auto',
    spaceBetween: 10,
    centeredSlides: false,
    loop: false,
    mousewheel: {
      forceToAxis: true,
    },
    observeParents: true,
    observer: true,
    breakpoints: {
      720: {
        spaceBetween: 0,
      },
      1000: {
        spaceBetween: 0,
      },
    },
    navigation: {
      nextEl: '.' + thematiqueSliderArrowsRightClass,
      prevEl: '.' + thematiqueSliderArrowsLeftClass,
    },
  });
}

// Slider Header - page projet
const sliderHomeProjectContainer = document.querySelector('.cms-slider-home-project__container');
const sliderHomeProjectrSlides = document.querySelectorAll('.cms-slider-home-project__container .swiper-slide');

if (sliderHomeProjectContainer && sliderHomeProjectrSlides.length > 1) {
  sliderHomeProjectContainer.classList.add('active');

  const cmsSliderHomeProject = new Swiper(sliderHomeProjectContainer, {
    observer: true,
    spaceBetween: 0,
    observeParents: true,
    centeredSlides: false,
    parallax: false,
    loop: true,
    autoplay: {
      delay: 3000,
    },
    effect: "fade",
    speed: 1000,
    draggable: false,
    breakpoints: {
      1028: {
        slidesPerView: '1',
        spaceBetween: 0,
      },
      300: {
        slidesPerView: '1',
        spaceBetween: 0,
      },
    },

    pagination: {
      el: '.swiper-pagination',
      type: 'bullets',
      clickable: true,
    },
  });
}

// Slider Header - page projet
const sliderHomeProjectPlansContainer = document.querySelector('.cms-slider-home-project-plans__container');
const sliderHomeProjectPlansSlides = document.querySelectorAll('.cms-slider-home-project-plans__container .swiper-slide');

if (sliderHomeProjectPlansContainer && sliderHomeProjectPlansSlides.length > 1) {
  sliderHomeProjectPlansContainer.classList.add('active');

  const cmsSliderHomeProjectPlans = new Swiper(sliderHomeProjectPlansContainer, {
    observer: true,
    spaceBetween: 0,
    observeParents: true,
    centeredSlides: false,
    parallax: false,
    loop: true,
    speed: 1000,
    draggable: false,
    autoplay: true,
    breakpoints: {
      1028: {
        slidesPerView: '1',
        spaceBetween: 0,
      },
      300: {
        slidesPerView: '1',
        spaceBetween: 0,
      },
    },

    pagination: {
      el: '.swiper-pagination',
      type: 'bullets',
      clickable: true,
    },
  });
}
