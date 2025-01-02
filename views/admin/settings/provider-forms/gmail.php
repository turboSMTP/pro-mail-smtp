<div class="wizard-step">
    
    <h3><?php echo isset($is_edit) && $is_edit ? 'Edit Gmail Configuration' : 'Add Gmail Provider'; ?></h3>
    <p class="description">Enter your Gmail API credentials below.</p>
    <p class="description">Note: Ensure your redirect URL is set to <code><?php echo site_url('wp-admin/admin.php?page=free_mail_smtp-settings'); ?></code></p>

    <form id="provider-form" method="post">
        <?php wp_nonce_field('free_mail_smtp_save_providers', 'free_mail_smtp_nonce'); ?>
        
        <!-- Important hidden fields -->
        <input type="hidden" name="provider" id="provider" value="gmail">
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
                    <label for="client_id">Client ID</label>
                </th>
                <td>
                    <input type="text" 
                           name="config_keys[client_id]" 
                           id="client_id" 
                           class="regular-text" 
                           required>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="client_secret">Client Secret</label>
                </th>
                <td>
                    <input type="password" 
                           name="config_keys[client_secret]" 
                           id="client_secret" 
                           class="regular-text" 
                           required>
                    <button type="button" id="toggle_client_secret" class="button">Show</button>
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
            <button type="submit" class="button add-provider">
                <?php echo isset($_POST['is_edit']) ? 'Update Provider' : 'Add Provider'; ?>
            </button>
        </div>
    </form>
</div>

<script>
    function fillInputs(data){
        console.log('filled',data);
        jQuery('#connection_label').val(data.connection_label);
        jQuery('#client_id').val(data.config_keys.client_id);
        jQuery('#client_secret').val(data.config_keys.client_secret);
        jQuery('#provider_index').val(data.index);
        jQuery('#priority').val(data.priority);
        jQuery('.back-step').hide();
    }
    jQuery('#toggle_client_secret').on('click', function() {
        var input = jQuery('#client_secret');
        var type = input.attr('type') === 'password' ? 'text' : 'password';
        input.attr('type', type);
        jQuery(this).text(type === 'password' ? 'Show' : 'Hide');
    });
</script>