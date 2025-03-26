jQuery(document).ready(function ($) {
    console.log("Free Mail SMTP Settings JS loaded");
  
      // Open modal when delete button is clicked
      $('#free-mail-smtp-delete-data').on('click', function() {
          console.log('Delete data button clicked');
          $('#data-deletion-modal').show();
      });
      
      // Close modal when X is clicked
      $('.modal-close, .modal-cancel').click(function() {
          $('#data-deletion-modal').hide();
          $('#delete-confirmation').val('');
          $('#confirm-delete-data').prop('disabled', true);
      });
      
      // Enable confirm button only when "DELETE" is typed
      $('#delete-confirmation').on('input', function() {
          $('#confirm-delete-data').prop('disabled', $(this).val() !== 'DELETE');
      });
      
      // Handle deletion confirmation
      $('#confirm-delete-data').click(function() {
          if ($('#delete-confirmation').val() === 'DELETE') {
              const $button = $(this);
              $button.text('Deleting...').prop('disabled', true);
              
              $.ajax({
                  url: FreeMailSMTPAdminSettings.ajaxUrl, 
                  type: 'POST',
                  data: {
                      action: 'free_mail_smtp_delete_all_data',
                      nonce: FreeMailSMTPAdminSettings.nonce 
                  },
                  success: function(response) {
                      if (response.success) {
                          $('#data-deletion-modal').hide();
                          alert('All plugin data has been successfully deleted.');
                          window.location.href = FreeMailSMTPAdminSettings.adminUrl || window.location.href;
                      } else {
                          alert('Error: ' + (response.data || 'Unknown error occurred'));
                          $button.text('Permanently Delete All Data').prop('disabled', false);
                      }
                  },
                  error: function(xhr, status, error) { 
                      console.error('Delete data error:', error);
                      alert('Server error occurred. Please try again.');
                      $button.text('Permanently Delete All Data').prop('disabled', false);
                  }
              });
          }
      });
      
      $(window).click(function(e) {
          if ($(e.target).is('#data-deletion-modal')) {
              $('#data-deletion-modal').hide();
              $('#delete-confirmation').val('');
              $('#confirm-delete-data').prop('disabled', true);
          }
      });
  });