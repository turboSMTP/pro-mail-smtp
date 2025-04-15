/**
 * Provider Forms Handler
 * 
 * This script handles all functionality for provider forms including:
 * - Filling form fields with existing provider data
 * - Toggling password visibility
 */

(function($) {
    'use strict';

    // Common field mappings across all providers
    const commonFields = ['connection_label', 'priority', 'email_from_overwrite', 'connection_id'];
    
    // Provider-specific field mappings
    const providerFields = {
        'sendgrid': ['api_key'],
        'brevo': ['api_key'],
        'postmark': ['api_key'],
        'mailgun': ['api_key', 'domain', 'region'],
        'smtp2go': ['api_key'],
        'sparkpost': ['api_key', 'region'],
        'gmail': ['client_id', 'client_secret'],
        'outlook': ['client_id', 'client_secret'],
        'turbosmtp': ['consumer_key', 'consumer_secret', 'region'],
        'other': ['smtp_host', 'smtp_user', 'smtp_pw', 'smtp_encryption', 'smtp_port']
    };

    /**
     * Fill form inputs based on provider data
     * 
     * @param {Object} data Provider data object
     */
    window.fillInputs = function(data) {
        console.log('Filling form with data:', data);
        
        // Set the connection ID
        $('#connection_id').val(data.index);
        
        // Fill common fields
        commonFields.forEach(field => {
            if (field === 'connection_id') {
                $('#' + field).val(data.index);
            } else if (field === 'email_from_overwrite' && data.config_keys) {
                $('#' + field).val(data.config_keys[field] || '');
            } else {
                $('#' + field).val(data[field] || '');
            }
        });
        
        // Get the current provider
        const provider = $('#provider').val();
        
        // Fill provider-specific fields
        if (provider && providerFields[provider] && data.config_keys) {
            providerFields[provider].forEach(field => {
                if (data.config_keys[field] !== undefined) {
                    $('#' + field).val(data.config_keys[field]);
                }
            });
        }
        
        // Hide back button for edit mode
        $('.back-step').hide();
    };

    /**
     * Initialize form functionality
     */
    $(document).ready(function() {
        // Use event delegation for toggle password visibility
        // This will catch all dashicons in password toggle wrappers
        $(document).on('click', '.dashicons', function() {
            // Check if this is inside a password wrapper
            if ($(this).closest('.api-key-wrapper, .secret-wrapper, .smtp-pw-wrapper').length) {
                const input = $(this).prev('input');
                
                // Only toggle if we found an input and it's a password field or was a password field
                if (input.length && (input.attr('type') === 'password' || input.attr('type') === 'text')) {
                    const type = input.attr('type') === 'password' ? 'text' : 'password';
                    input.attr('type', type);
                    $(this).toggleClass('dashicons-visibility dashicons-hidden');
                }
            }
        });
    });

})(jQuery);
