<div class="wizard-step">
    <h3><?php echo isset($is_edit) && $is_edit ? 'Edit Turbo SMTP Configuration' : 'Add Turbo SMTP Provider'; ?></h3>

    <p class="description">Enter your Turbo SMTP API credentials below.</p>
    <form id="provider-form" method="post">
        <?php wp_nonce_field('free_mail_smtp_save_providers', 'free_mail_smtp_nonce'); ?>

        <input type="hidden" name="provider" id="provider" value="turbosmtp">
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
                    <label for="consumer_key">Consumer Key</label>
                </th>
                <td>
                    <input type="text"
                        name="config_keys[consumer_key]"
                        id="consumer_key"
                        class="regular-text"
                        required>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="consumer_secret">Consumer Secret</label>
                </th>
                <td>
                    <div class="secret-wrapper">
                        <input type="password"
                            name="config_keys[consumer_secret]"
                            id="consumer_secret"
                            class="regular-text"
                            required>
                        <span id="toggle_ssecret" class="dashicons dashicons-visibility"></span>
                    </div>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="region">Region</label>
                </th>
                <td>
                    <select name="config_keys[region]" id="region" required>
                        <option value="us">US</option>
                        <option value="eu">EU</option>
                    </select>
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
                <?php echo isset($is_edit) ? 'Update Provider' : 'Add Provider'; ?>
            </button>
        </div>
    </form>
</div>


<style>
    .secret-wrapper {
        position: relative;
        display: inline-block;
    }

    .secret-wrapper input {
        padding-right: 30px !important;
        width: 28.5em !important;
        max-width: 100% !important;
    }

    .secret-wrapper .dashicons {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #a8a7a8;
    }

    .secret-wrapper .dashicons:hover {
        color: #a8a7a8;
    }
</style>
<script>
    function fillInputs(data) {
        console.log('filled', data);
        jQuery('#connection_label').val(data.connection_label);
        jQuery('#consumer_key').val(data.config_keys.consumer_key);
        jQuery('#consumer_secret').val(data.config_keys.consumer_secret);
        jQuery('#connection_id').val(data.index);
        jQuery('#priority').val(data.priority);
        jQuery('#region').val(data.config_keys.region);
        jQuery('.back-step').hide();
    }
    jQuery('#toggle_ssecret, #toggle_ssecret').on('click', function() {
        var input = jQuery(this).prev('input');
        var type = input.attr('type') === 'password' ? 'text' : 'password';
        input.attr('type', type);
        jQuery(this).toggleClass('dashicons-visibility dashicons-hidden');
    });
</script>