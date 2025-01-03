<div class="wizard-step">
    <h3><?php echo isset($is_edit) && $is_edit ? 'Edit SendGrid Configuration' : 'Add SendGrid Provider'; ?></h3>
    <p class="description">Enter your SendGrid API credentials below.</p>

    <form id="provider-form" method="post">
        <?php wp_nonce_field('free_mail_smtp_save_providers', 'free_mail_smtp_nonce'); ?>
        
        <!-- Important hidden fields -->
        <input type="hidden" name="provider" id="provider" value="sendgrid">
        <input type="hidden" name="provider_index" id="provider_index" value="">
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="connection_label">Connection Label</label>
                </th>
                <td>
                    <input type="text" 
                           name="connection_label" 
                           id="connection_label" 
                           class="regular-text" 
                           required>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="api_key">API Key</label>
                </th>
                <td>
                    <input type="password" 
                           name="config_keys[api_key]" 
                           id="api_key" 
                           class="regular-text" 
                           required>
                    <button type="button" class="button toggle-password">
                        <span class="dashicons dashicons-visibility"></span>
                    </button>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="priority">Priority</label>
                </th>
                <td>
                    <input type="number" 
                           name="priority" 
                           id="priority" 
                           class="small-text" 
                           min="1" 
                           value="1"
                           required>
                </td>
            </tr>
        </table>

        <div class="submit-wrapper">
            <?php if (!(isset($is_edit) && $is_edit)): ?>
                <button type="button" class="button back-step">Back</button>
            <?php endif; ?>
            <button type="submit" class="button button-primary">
                <?php echo isset($is_edit)  ? 'Update Provider' : 'Add Provider'; ?>
            </button>
        </div>
    </form>
</div>
<script>
    function fillInputs(data){
        console.log('filled',data);
        jQuery('#connection_label').val(data.connection_label);
        jQuery('#api_key').val(data.config_keys.api_key);
        jQuery('#provider_index').val(data.index);
        jQuery('#priority').val(data.priority);
        jQuery('.back-step').hide();
    }
    jQuery('#toggle_api_key').on('click', function() {
        var input = jQuery('#api_key');
        var type = input.attr('type') === 'password' ? 'text' : 'password';
        input.attr('type', type);
        jQuery(this).text(type === 'password' ? 'Show' : 'Hide');
    });
</script>