jQuery(document).ready(function ($) {
  // Use a common selector for all forms that require dynamic action URLs
  $('form[data-dynamic-action]').on('submit', function (e) {
      const form = $(this); // Current form being submitted

      if (!form.attr('action')) {
          e.preventDefault(); // Prevent submission until action is set

          // Fetch the action URL for the form ID
          const formId = form.attr('id'); // Get the form's unique ID
          fetch(`/wp-json/custom-api/v1/get-form-action/${formId}`)
              .then((response) => response.json())
              .then((data) => {
                  if (data.url) {
                      form.attr('action', data.url); // Set the action URL
                      form[0].submit(); // Resubmit the form
                  } else {
                      alert('No action URL found for this form ID.');
                  }
              })
              .catch((err) => {
                  console.error('Error fetching the action URL:', err);
                  alert('Failed to load the form action URL.');
              });
      }
  });
});
