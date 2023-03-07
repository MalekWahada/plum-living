
function updateDuration(durationElem, minElem, maxElem) {
  const minDate = new Date(minElem.value);
  const maxDate = new Date(maxElem.value);

  if (!isNaN(minDate.getTime()) && !isNaN(maxDate.getTime())) {
    const diff = (maxDate.getTime() - minDate.getTime()) / (24 * 3600 * 1000); // Diff days
    durationElem.value = diff;
  }
}

function updateMaxDateDelivery(durationElem, minElem, maxElem) {
  const minDate = new Date(minElem.value);
  const newDate = new Date(minDate);

  if (!isNaN(minDate.getTime()) && !isNaN(newDate.getTime())) {
    newDate.setDate(newDate.getDate() + parseInt(durationElem.value)); // Increment duration days
    maxElem.value = newDate.toISOString().split('T')[0]; // Format to yyyy-mm-dd for input date
  }
}

function init() {
  const form = document.getElementById('delivery_date_calculation_config_form');
  if (form) {
    const duration = document.getElementById('delivery_date_calculation_config_duration');
    const minDateDelivery = document.getElementById('delivery_date_calculation_config_minDateDelivery');
    const maxDateDelivery = document.getElementById('delivery_date_calculation_config_maxDateDelivery');

    // Initial calculation
    updateDuration(duration, minDateDelivery, maxDateDelivery);

    [duration, minDateDelivery].forEach((item) => {
      item.addEventListener('input', () => {
        updateMaxDateDelivery(duration, minDateDelivery, maxDateDelivery);
      });
    });

    maxDateDelivery.addEventListener('input', () => {
      updateDuration(duration, minDateDelivery, maxDateDelivery);
    });

    // Remove loader
    form.classList.remove("loading");
  }
}


export default init;
