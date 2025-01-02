jQuery(document).ready(function ($) {
  console.log("Free Mail SMTP Admin JS loaded");

  var modal = $("#provider-modal");
  console.log("Modal element:", modal.length ? "found" : "not found");

  $('.add-provider').on('click', function() {
    $('#step-provider').show();
    $('#step-config').hide();
    modal.show();
});

  // Close modal
  $(".modal-close").on("click", function (e) {
    e.preventDefault();
    modal.hide();
  });

  var modal = $("#provider-modal");

  // Provider card selection
  $('.provider-card').on('click', function() {
    var provider = $(this).data('provider');
    
    $.ajax({
        url: FreeMailSMTPAdmin.ajaxUrl,
        method: 'POST',
        data: {
            action: 'load_provider_form',
            provider: provider,
            nonce: FreeMailSMTPAdmin.nonce
        },
        success: function(response) {
            if (response.success) {
                $('#step-config').html(response.data.html);
                $('#step-provider').hide();
                $('#step-config').show();

                // Set submit button text for new provider
                $('#provider-form .button-primary').text('Add Provider');
            } else {
                alert('Error loading provider form');
            }
        }
    });
});


  // Back button handler
  $(document).on("click", ".back-step", function () {
    $("#step-config").hide();
    $("#step-provider").show();
  });

 // Form submission handler
$(document).on('submit', '#provider-form', function(e) {
    e.preventDefault();    
    $.ajax({
        url: FreeMailSMTPAdmin.ajaxUrl,
        method: 'POST',
        data: {
            action: 'save_provider',
            formData: $(this).serialize(),
            nonce: FreeMailSMTPAdmin.nonce
        },
        success: function(response) {
            
            if (response.success) {
                location.reload();
            } else {
                alert('Error saving provider: ' + (response.data || 'Unknown error'));
            }
        },
        error: function(xhr, status, error) {
            console.error('Save Error:', error);
            alert('Network error while saving provider');
        }
    });
});

  // Add basic modal styles
  $("head").append(`
        <style>
            .modal {
                display: none;
                position: fixed;
                z-index: 100000;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0,0,0,0.4);
            }
            .modal-content {
                background-color: #fefefe;
                margin: 5% auto;
                padding: 20px;
                border: 1px solid #888;
                width: 80%;
                max-width: 600px;
                position: relative;
            }
            .provider-card {
                cursor: pointer;
                padding: 15px;
                border: 1px solid #ddd;
                margin-bottom: 10px;
            }
            .provider-card:hover {
                background-color: #f0f0f0;
            }
        </style>
    `);
// Test Provider
$('.test-provider').on('click', function(e) {
    e.preventDefault();
    var button = $(this);
    
    button.prop('disabled', true)
          .text('Testing...');

    $.ajax({
        url: FreeMailSMTPAdmin.ajaxUrl,
        method: 'POST',
        data: {
            action: 'test_provider_connection',
            nonce: FreeMailSMTPAdmin.nonce,
            provider: button.data('provider'),
            config_keys: button.data('config_keys'),
            index: button.data('index')
        },
        success: function(response) {
            if (response.success) {
                alert('Connection test completed successfully!');
            } else {
                alert('Connection failed: ' + (response.data || 'Unknown error'));
            }
        },
        error: function(xhr, status, error) {
            console.error('Test Error:', {
                status: status,
                error: error,
                xhr: xhr
            });
            alert('Error testing connection. Check console for details.');
        },
        complete: function() {
            button.prop('disabled', false)
                  .text('Test');
        }
    });
});

  // Delete Provider
  $(".delete-provider").on("click", function () {
    if (!confirm("Are you sure you want to delete this provider?")) {
      return;
    }

    var button = $(this);
    var index = button.data("index");

    $.ajax({
      url: FreeMailSMTPAdmin.ajaxUrl,
      method: "POST",
      data: {
        action: "delete_provider",
        index: index,
        nonce: FreeMailSMTPAdmin.nonce,
      },
      success: function (response) {
        if (response.success) {
          location.reload();
        } else {
          alert("Error deleting provider");
        }
      },
    });
  });

  $('.edit-provider').on('click', function() {
    var index = $(this).data('index');
    var config = $(this).data('config');
    
    // Skip to configuration step directly
    $('#step-config').html('<div class="loading">Loading...</div>').show();
    $('#step-provider').hide();
    modal.show();
    // Load provider form
    $.ajax({
        url: FreeMailSMTPAdmin.ajaxUrl,
        method: 'POST',
        data: {
            action: 'load_provider_form',
            provider: config.provider,
            nonce: FreeMailSMTPAdmin.nonce,
            index: index
        },
        success: function(response) {
            if (response.success) {
                $('#step-config').html(response.data.html);
                $('#provider-form .button-primary .save-provider').text('Update Provider');
                var data = config;
                data.index = index;

                fillInputs(data);
            } else {
                alert('Error loading provider form');
            }
        }
    });
});
});


function handleGoogleAuth() {
    const urlParams = new URLSearchParams(window.location.search);
    const code = urlParams.get('code');
    console.log('Google Auth code:', code);
    if (code) {
        jQuery.ajax({
            url: FreeMailSMTPGoogleAuth.ajaxUrl,
            type: 'POST',
            data: {
                action: 'free_mail_smtp_set_gmail_token',
                code: code,
                nonce: FreeMailSMTPGoogleAuth.nonce,
            },
            success: function(response) {
                if (response.success) {
                    console.log('Gmail connected successfully');
                    window.location.href = FreeMailSMTPGoogleAuth.redirectUrl;
                } else {
                    console.error('Failed to connect Gmail:', response.data);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX request failed:', textStatus, errorThrown);
            }
        });
    } else {
        console.error('No code parameter found in the URL');
    }
}

// Listen for the Google Auth callback
window.addEventListener('load', handleGoogleAuth);