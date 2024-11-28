jQuery(document).ready(function ($) {
  // Copy ID button functionality
  $('.copy-id-button').on('click', function () {
      const id = $(this).data('id');
      navigator.clipboard.writeText(id).then(() => {
          alert('Form ID copied to clipboard!');
      });
  });
});
