global.slimPositioning = function (form) {
  // @see https://pqina.nl/slim/
  return {
    init() {
      const that = this;
      // remove collection when no image loaded
      form.on('click', '.slim_zoning_elements a[data-form-collection="add"]', () => {
        const slim = form.find('.slim:first-child').slim('data');
        if (slim[0].input.name !== null) {
          return true;
        }
        form.find('.slim_zoning_elements').children().first().empty();
        $('#media_zoning_image').find('i.circle').remove();
        return true;
      });
      // eslint-disable-next-line no-undef
      $(document).on('click', '[data-form-collection="delete"]', (event) => {
        const container = $(event.target).parent('div');
        that.circleRemove(container);
      });
      // fill collection item top/left positions
      form.on('click', '.slim_positioning', (e) => {
        // eslint-disable-next-line no-undef
        const element = $(e.target);
        $('form[name="media_zoning_image"]').parent('.form').animate({
          scrollTop: '0px',
        }, 250);
        form
          .off('click', '.field.slim')
          .on('click', '.field.slim', (event) => {
            event.stopImmediatePropagation();
            if (event.isDefaultPrevented.name === 'returnFalse') {
              return false;
            }
            const container = element.parent('div').parent('div').parent('div');
            const offset = $('#media_zoning_image').offset();
            const imageWidth = form.find('#media_zoning_image .slim:first-child').width();
            const imageHeight = form.find('#media_zoning_image .slim:first-child').height();
            // eslint-disable-next-line no-mixed-operators
            const pourcentageLeft = (event.pageX - offset.left) * 100 / imageWidth;
            // eslint-disable-next-line no-mixed-operators
            const pourcentageTop = (event.pageY - offset.top) * 100 / imageHeight;
            this.circle(container, pourcentageTop, pourcentageLeft);
            element.parent('div').nextAll('div').eq(0).find('input')
              .val(pourcentageLeft);
            element.parent('div').nextAll('div').eq(1).find('input')
              .val(pourcentageTop);
            form.off('click', '.field.slim');
            return false;
          });
      });
    },
    circle(container, top, left) {
      this.circleRemove(container);
      this.circleAdd(container, top, left);
    },
    circleIndex(container) {
      return container.data('form-collection-index');
    },
    circleRemove(container) {
      const index = this.circleIndex(container);
      if (index === -1) {
        return;
      }
      $(`#media_zoning_image .slim:first-child i.icon[data-index="${index}"]`).remove();
    },
    circleAdd(container, top, left) {
      const index = this.circleIndex(container);
      if (index === -1) {
        return;
      }
      const icon = $(`<i class="circle outline icon" data-index="${index}"></i>`);
      icon.css({
        position: 'absolute',
        top: `calc(${top}% - 10px)`,
        left: `calc(${left}% - 8px)`,
      });
      icon.appendTo($('#media_zoning_image').children('.slim:first-child'));
    },
    options: {
      // remove collection when remove image
      didRemove() {
        form.find('.slim_zoning_elements').children().first().empty();
        $('#media_zoning_image').find('i.circle').remove();
      },
      // remove collection when new image loaded
      didLoad() {
        form.find('.slim_zoning_elements').children().first().empty();
        $('#media_zoning_image').find('i.circle').remove();
        return true;
      },
      // remove collection when image transformed
      didTransform() {
        form.find('.slim_zoning_elements').children().first().empty();
        $('#media_zoning_image').find('i.circle').remove();
        return true;
      },
    },
  };
};
