<?php defined('ABSPATH') || exit; ?>

<div class="wrap free-mail-smtp-welcome">
    <div class="plugin-header">
        <img src="<?php echo esc_url(plugins_url('assets/img/icon-svg.svg', dirname(dirname(dirname(__FILE__))))); ?>" alt="Free Mail SMTP" class="plugin-logo">
        <h1>Welcome to FREE MAIL <span>SMTP</span></h1>
    </div>

    <div class="welcome-container">
        <div class="welcome-section consent-section">
            <h2><?php esc_html_e('Data Collection Consent', 'free-mail-smtp'); ?></h2>
            <p><?php esc_html_e('To improve your experience with Free Mail SMTP, we collect anonymous usage data. This helps us understand how you use our plugin and improve it accordingly.', 'free-mail-smtp'); ?></p>
            
            <div class="consent-options">
                <form method="post" action="" id="consent-form">
                    <?php wp_nonce_field('free_mail_smtp_welcome_consent', 'free_mail_smtp_welcome_nonce'); ?>
                    
                    <label>
                        <input type="radio" name="data_collection_consent" value="yes" class="consent-radio">
                        <?php esc_html_e('Yes, I allow Free Mail SMTP to collect anonymous usage data', 'free-mail-smtp'); ?>
                    </label>
                    
                    <label>
                        <input type="radio" name="data_collection_consent" value="no" class="consent-radio">
                        <?php esc_html_e('No, I don\'t want to share anonymous usage data', 'free-mail-smtp'); ?>
                    </label>
                    
                    <div class="welcome-buttons">
                        <button type="submit" name="save_consent" id="continue-btn" class="button button-primary" disabled><?php esc_html_e('Continue', 'free-mail-smtp'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .free-mail-smtp-welcome .welcome-container {
        display: flex;
        flex-direction: column;
        gap: 30px;
        max-width: 800px;
        margin: 20px auto;
    }
    
    .free-mail-smtp-welcome .welcome-section {
        background: #fff;
        border-radius: 8px;
        padding: 25px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .free-mail-smtp-welcome .consent-options {
        margin-top: 20px;
    }
    
    .free-mail-smtp-welcome .consent-options label {
        display: block;
        margin-bottom: 12px;
        padding: 10px;
        border-radius: 4px;
        background: #f8f9fa;
    }
    
    .free-mail-smtp-welcome .welcome-buttons {
        margin-top: 25px;
        display: flex;
        gap: 10px;
    }
    
    .free-mail-smtp-welcome .provider-card {
        background: #f8f9fa;
        border-left: 4px solid #ff6e79;
        padding: 15px;
        margin-bottom: 15px;
        border-radius: 4px;
    }
    
    .free-mail-smtp-welcome h3 {
        margin-top: 0;
        color: #333;
    }
    
    .button-primary {
        background: #ff6e79 !important;
        border-color: #ff6e79 !important;
    }
    
    .button-primary:hover {
        background: #ff5252 !important;
        border-color: #ff5252 !important;
    }

    .button-primary:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const radioButtons = document.querySelectorAll('.consent-radio');
        const continueButton = document.getElementById('continue-btn');
        
        radioButtons.forEach(function(radio) {
            radio.addEventListener('change', function() {
                continueButton.disabled = false;
            });
        });
    });
</script>