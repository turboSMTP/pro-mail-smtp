<div class="wizard-step">
    
    <h3><?php echo isset($_POST['is_edit']) ? 'Edit Gmail Configuration' : 'Add Gmail Provider'; ?></h3>
    <p class="description">Enter your Gmail API credentials below.</p>

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
                    <input type="text" 
                           name="config_keys[client_secret]" 
                           id="client_secret" 
                           class="regular-text" 
                           required>
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
            <?php if (!isset($_POST['is_edit'])): ?>
                <button type="button" class="button back-step">Back</button>
            <?php endif; ?>
            <button type="submit" class="button button-primary">
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
</script>