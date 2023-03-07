global.autocompleteProductVariant = function (element) {
  return {
    init() {
      $(element).autocomplete({
        source: function (request, response) {
          $.ajax({
            url: $(element).data('route'),
            data: {
              term: request.term,
            },
            success: function (data) {
              response(
                data.map(function (item) {
                  const label = 'fr' in item.translations
                    ? item.translations.fr.name
                    : item.translations.find(t => t.locale === 'fr').name;
                  return {
                    label: `${item.code} - ${label}`,
                    value: item.code,
                  };
                }),
              );
            },
          });
        },
      });
    },
  };
};
