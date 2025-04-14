/**
 * Free Mail SMTP OAuth Handler
 * This script runs on all WordPress admin pages to catch OAuth redirects
 */
(function() {
    console.log('Free Mail SMTP OAuth Handler loaded');
    // Execute immediately to capture parameters as early as possible
    function handleOAuthCallback() {
        console.log('Free Mail SMTP OAuth Handler initialized');
        
        try {
            // Get parameters from both the URL query string and hash fragment
            var queryParams = new URLSearchParams(window.location.search);
            var hashParams = new URLSearchParams(window.location.hash.substring(1));
            console.log('Query Params:', queryParams.toString());
            console.log('Hash Params:', hashParams.toString());
            var code = queryParams.get('code') || hashParams.get('code');
            var state = queryParams.get('state') || hashParams.get('state');
            console.log('OAuth Code:', code);
            console.log('OAuth State:', state);
            console.log('valid state:', isOAuthProvider(state));
            // Only proceed if we have oauth parameters and state is one of our supported providers
            if (code && state && isOAuthProvider(state)) {
                console.log('OAuth callback detected for provider:', state);
                
                // Show processing notification
                var notificationDiv = document.createElement('div');
                notificationDiv.style.cssText = 'position:fixed;top:32px;right:20px;background:#fff;padding:10px 20px;border-left:4px solid #46b450;box-shadow:0 1px 1px rgba(0,0,0,.04);z-index:999999';
                notificationDiv.innerHTML = 'Processing ' + state + ' authentication...';
                document.body.appendChild(notificationDiv);
                
                // Process the OAuth callback
                processOAuthCallback(code, state, notificationDiv);
            }
        } catch (error) {
            console.error('Error in Free Mail SMTP OAuth handler:', error);
        }
    }
    
    // Check if the state parameter matches one of our supported OAuth providers
    function isOAuthProvider(state) {
        // Add all your supported OAuth providers here
        var supportedProviders = ['gmail', 'outlook'];
        return supportedProviders.includes(state.toLowerCase());
    }
    
    // Process the OAuth callback
    function processOAuthCallback(code, state, notificationDiv) {
        // Get ajaxUrl from the localized script data
        var ajaxUrl = (typeof FreeMailSMTPOAuth !== 'undefined' && FreeMailSMTPOAuth.ajaxUrl) 
            ? FreeMailSMTPOAuth.ajaxUrl 
            : window.ajaxurl;
        
        // Get nonce from global variable if available
        var nonce = '';
        if (typeof FreeMailSMTPOAuth !== 'undefined' && FreeMailSMTPOAuth.nonce) {
            nonce = FreeMailSMTPOAuth.nonce;
        }
        
        console.log('Processing OAuth callback for ' + state + ' provider');
        
        jQuery.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'free_mail_smtp_set_oauth_token',
                code: code,
                nonce: nonce,
                provider_type: state
            },
            success: function(response) {
                if (response.success) {
                    console.log(state + ' connected successfully');
                    notificationDiv.innerHTML = state + ' connected successfully! Redirecting...';
                    notificationDiv.style.borderLeftColor = '#46b450';
                    
                    // Redirect to plugin page or remove parameters
                    if (typeof FreeMailSMTPOAuth !== 'undefined' && FreeMailSMTPOAuth.redirectUrl) {
                        window.location.href = FreeMailSMTPOAuth.redirectUrl;
                    } else {
                        // Remove code and state from URL without refreshing
                        var newUrl = window.location.protocol + '//' + window.location.host + window.location.pathname;
                        window.history.replaceState({}, document.title, newUrl);
                        // Reload after a short delay to ensure settings are updated
                        setTimeout(function() {
                            window.location.reload();
                        }, 1000);
                    }
                } else {
                    console.error('Failed to connect ' + state + ':', response.data);
                    notificationDiv.innerHTML = 'Failed to connect ' + state + ': ' + (response.data || 'Unknown error');
                    notificationDiv.style.borderLeftColor = '#dc3232';
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX request failed:', textStatus, errorThrown);
                notificationDiv.innerHTML = 'Connection error: ' + textStatus;
                notificationDiv.style.borderLeftColor = '#dc3232';
            }
        });
    }

    // Run the handler function immediately
    handleOAuthCallback();
    
    // Also attach to DOMContentLoaded in case body isn't ready yet
    document.addEventListener('DOMContentLoaded', handleOAuthCallback);
})();
