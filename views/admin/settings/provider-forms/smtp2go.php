<div class="wizard-step">
    <h3><?php echo isset($is_edit) && $is_edit ? 'Edit SMTP2GO Configuration' : 'Add SMTP2GO Provider'; ?></h3>
    <p class="description">Enter your SMTP2GO API credentials below.</p>

    <form id="provider-form" method="post">
        <?php wp_nonce_field('free_mail_smtp_save_providers', 'free_mail_smtp_nonce'); ?>
        
        <input type="hidden" name="provider" id="provider" value="smtp2go">
        <input type="hidden" name="connection_id" id="connection_id" value="">
        
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
                <div class="api-key-wrapper">
                    <input type="password" 
                           name="config_keys[api_key]" 
                           id="api_key" 
                           class="regular-text" 
                           required>
                           <span id="toggle_api_key" class="dashicons dashicons-visibility"></span>
                           </div>
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
            <button type="submit" class="button button-primary save-provider">
                <?php echo isset($is_edit)  ? 'Update Provider' : 'Add Provider'; ?>
            </button>
        </div>
    </form>
</div>
<style>
   .api-key-wrapper {
       position: relative;
       display: inline-block;
   }


   .api-key-wrapper input {
       padding-right: 30px !important;
       width: 28.5em !important;
       max-width: 100% !important;
   }


   .api-key-wrapper .dashicons {
       position: absolute;
       right: 8px;
       top: 50%;
       transform: translateY(-50%);
       cursor: pointer;
       color: #a8a7a8;
   }


   .api-key-wrapper .dashicons:hover {
       color: #a8a7a8;
   }
</style>
<script>
    function fillInputs(data){
        console.log('filled',data);
        jQuery('#connection_label').val(data.connection_label);
        jQuery('#api_key').val(data.config_keys.api_key);
        jQuery('#connection_id').val(data.index);
        jQuery('#priority').val(data.priority);
        jQuery('.back-step').hide();
    }
    jQuery('#toggle_api_key, #toggle_api_key').on('click', function() {
       var input = jQuery(this).prev('input');
       var type = input.attr('type') === 'password' ? 'text' : 'password';
       input.attr('type', type);
       jQuery(this).toggleClass('dashicons-visibility dashicons-hidden');
   });
</script>