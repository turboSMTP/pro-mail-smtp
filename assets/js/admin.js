jQuery(document).ready(function ($) {
  console.log("Free Mail SMTP Admin JS loaded");

  var modal = $("#provider-modal");
  console.log("Modal element:", modal.length ? "found" : "not found");

  $('.add-provider, #add-provider-button').on('click', function() {
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
$('.test-provider').on('click', function(e) {
    e.preventDefault();
    var button = $(this);
    button.prop('disabled', true).text('Testing...');
    $.ajax({
        url: FreeMailSMTPAdmin.ajaxUrl,
        method: 'POST',
        data: {
            action: 'test_provider_connection',
            nonce: FreeMailSMTPAdmin.nonce,
            connection_id: button.data('connection_id')
        },
        success: function(response) {
            if (response.success) {
                alert('Connection test completed successfully!');
            } else {
                alert('Connection failed: ' + (response.data || 'Unknown error'));
            }
        },
        error: function(xhr, status, error) {
            console.error('Test Error:', error);
            alert('Error testing connection.');
        },
        complete: function() {
            button.prop('disabled', false).text('Test');
        }
    });
});

  // Delete Provider
  $(".delete-provider").on("click", function () {
    if (!confirm("Are you sure you want to delete this provider?")) {
      return;
    }

    var button = $(this);
    var connection_id = button.data("connection_id");
    $.ajax({
      url: FreeMailSMTPAdmin.ajaxUrl,
      method: "POST",
      data: {
        action: "delete_provider",
        connection_id: connection_id,
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
    var connection_id = $(this).data('connection_id');
    var config = $(this).data('config');
    
    $('#step-config').html('<div class="loading">Loading...</div>').show();
    $('#step-provider').hide();
    modal.show();
    $.ajax({
        url: FreeMailSMTPAdmin.ajaxUrl,
        method: 'POST',
        data: {
            action: 'load_provider_form',
            provider: config.provider,
            nonce: FreeMailSMTPAdmin.nonce,
            connection_id: connection_id
        },
        success: function(response) {
            if (response.success) {
                $('#step-config').html(response.data.html);
                $('#provider-form .button-primary .save-provider').text('Update Provider');
                var data = config;
                data.index = connection_id;

                fillInputs(data);
            } else {
                alert('Error loading provider form');
            }
        }
    });
});
});


function handleOAuth() {
    var params = Object.assign(
        {},
        Object.fromEntries(new URLSearchParams(window.location.search)),
        Object.fromEntries(new URLSearchParams(window.location.hash.substring(1)))
    );
    
    if (params.code && params.state) {
        console.log('OAuth callback:', params);
        var code = params.code;
        var provider = params.state; 
        jQuery.ajax({
            url: FreeMailSMTPOAuth.ajaxUrl,
            type: 'POST',
            data: {
                action: 'free_mail_smtp_set_oauth_token',
                code: code,
                nonce: FreeMailSMTPOAuth.nonce,
                provider_type: provider
            },
            success: function(response) {
                if (response.success) {
                    console.log(provider + ' connected successfully');
                    window.location.href = FreeMailSMTPOAuth.redirectUrl;
                } else {
                    console.error('Failed to connect ' + provider + ':', response.data);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX request failed:', textStatus, errorThrown);
            }
        });
    }
}

window.addEventListener('load', handleOAuth);