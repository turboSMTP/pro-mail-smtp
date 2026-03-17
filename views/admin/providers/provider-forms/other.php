<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Resolve saved connection data for edit mode
// $connection_data and $is_edit are passed from Providers::load_provider_form()
$pro_mail_smtp_cd           = isset( $connection_data ) && is_array( $connection_data ) ? $connection_data : [];
$pro_mail_smtp_saved_host   = isset( $pro_mail_smtp_cd['smtp_host'] )           ? esc_attr( $pro_mail_smtp_cd['smtp_host'] )           : '';
$pro_mail_smtp_saved_user   = isset( $pro_mail_smtp_cd['smtp_user'] )           ? esc_attr( $pro_mail_smtp_cd['smtp_user'] )           : '';
$pro_mail_smtp_saved_enc    = isset( $pro_mail_smtp_cd['smtp_encryption'] )     ? $pro_mail_smtp_cd['smtp_encryption']                 : 'tls';
$pro_mail_smtp_saved_port   = isset( $pro_mail_smtp_cd['smtp_port'] )           ? (int) $pro_mail_smtp_cd['smtp_port']                 : '';
$pro_mail_smtp_saved_from   = isset( $pro_mail_smtp_cd['email_from_overwrite'] ) ? esc_attr( $pro_mail_smtp_cd['email_from_overwrite'] ) : '';
// smtp_auth: treat missing key as true (backwards compat), only false when explicitly 0/false
$pro_mail_smtp_saved_auth   = ! isset( $pro_mail_smtp_cd['smtp_auth'] ) || (bool) $pro_mail_smtp_cd['smtp_auth'];
?>
<div class="wizard-step">
    
    <h3><?php echo ( isset( $is_edit ) && $is_edit ) ? 'Edit SMTP Configuration' : 'Add SMTP Provider'; ?></h3>
    <p class="description"><?php esc_html_e( 'Enter your SMTP credentials below.', 'pro-mail-smtp' ); ?></p>

    <form id="provider-form" method="post">
    <?php wp_nonce_field( 'pro_mail_smtp_nonce_providers', 'pro_mail_smtp_nonce_providers' ); ?>
        
        <input type="hidden" name="provider" id="provider" value="other">
        <input type="hidden" name="connection_id" id="connection_id" value="">
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="connection_label"><?php esc_html_e( 'Connection Label', 'pro-mail-smtp' ); ?></label>
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
                    <label for="email_from_overwrite"><?php esc_html_e( 'Email From', 'pro-mail-smtp' ); ?></label>
                </th>
                <td>
                    <input type="email"
                           name="config_keys[email_from_overwrite]"
                           id="email_from_overwrite"
                           class="regular-text"
                           value="<?php echo esc_attr( $pro_mail_smtp_saved_from ); ?>">
                    <p class="description"><?php esc_html_e( '(Optional) Force sender email for this provider', 'pro-mail-smtp' ); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="smtp_host"><?php esc_html_e( 'SMTP Host', 'pro-mail-smtp' ); ?></label>
                </th>
                <td>
                    <input type="text"
                           name="config_keys[smtp_host]"
                           id="smtp_host"
                           class="regular-text"
                           value="<?php echo esc_attr( $pro_mail_smtp_saved_host ); ?>"
                           required>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="smtp_auth_toggle"><?php esc_html_e( 'SMTP Authentication', 'pro-mail-smtp' ); ?></label>
                </th>
                <td>
                    <?php /* Hidden field ensures smtp_auth=0 is sent when checkbox is unchecked */ ?>
                    <input type="hidden" name="config_keys[smtp_auth]" value="0">
                    <label>
                        <input type="checkbox"
                               name="config_keys[smtp_auth]"
                               id="smtp_auth_toggle"
                               value="1"
                               <?php checked( $pro_mail_smtp_saved_auth, true ); ?>>
                        <?php esc_html_e( 'Enable username &amp; password authentication', 'pro-mail-smtp' ); ?>
                    </label>
                    <p class="description"><?php esc_html_e( 'Disable if your SMTP server does not require authentication.', 'pro-mail-smtp' ); ?></p>
                </td>
            </tr>
            <tr id="smtp-user-row">
                <th scope="row">
                    <label for="smtp_user"><?php esc_html_e( 'SMTP Username', 'pro-mail-smtp' ); ?></label>
                </th>
                <td>
                    <input type="text"
                           name="config_keys[smtp_user]"
                           id="smtp_user"
                           class="regular-text"
                           value="<?php echo esc_attr( $pro_mail_smtp_saved_user ); ?>">
                </td>
            </tr>
            <tr id="smtp-pw-row">
                <th scope="row">
                    <label for="smtp_pw"><?php esc_html_e( 'SMTP Password', 'pro-mail-smtp' ); ?></label>
                </th>
                <td>
                    <div class="smtp-pw-wrapper">
                        <input type="password"
                               name="config_keys[smtp_pw]"
                               id="smtp_pw"
                               class="regular-text"
                               placeholder="<?php esc_attr_e( 'Leave blank to keep current password', 'pro-mail-smtp' ); ?>">
                        <span id="toggle_smtp_pw" class="dashicons dashicons-visibility"></span>
                    </div>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="smtp_encryption"><?php esc_html_e( 'Encryption', 'pro-mail-smtp' ); ?></label>
                </th>
                <td>
                    <select name="config_keys[smtp_encryption]" id="smtp_encryption" required>
                        <option value="none" <?php selected( $pro_mail_smtp_saved_enc, 'none' ); ?>><?php esc_html_e( 'None', 'pro-mail-smtp' ); ?></option>
                        <option value="ssl"  <?php selected( $pro_mail_smtp_saved_enc, 'ssl' );  ?>><?php esc_html_e( 'SSL', 'pro-mail-smtp' ); ?></option>
                        <option value="tls"  <?php selected( $pro_mail_smtp_saved_enc, 'tls' );  ?>><?php esc_html_e( 'TLS', 'pro-mail-smtp' ); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="smtp_port"><?php esc_html_e( 'Port', 'pro-mail-smtp' ); ?></label>
                </th>
                <td>
                    <input type="number"
                           name="config_keys[smtp_port]"
                           id="smtp_port"
                           class="small-text"
                           min="1"
                           value="<?php echo esc_attr( $pro_mail_smtp_saved_port ); ?>"
                           required>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="priority"><?php esc_html_e( 'Priority', 'pro-mail-smtp' ); ?></label>
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
            <?php if ( ! ( isset( $is_edit ) && $is_edit ) ) : ?>
                <button type="button" class="button back-step"><?php esc_html_e( 'Back', 'pro-mail-smtp' ); ?></button>
            <?php endif; ?>
            <button type="submit" class="button button-primary save-provider">
                <?php echo ( isset( $is_edit ) && $is_edit ) ? esc_html__( 'Update SMTP', 'pro-mail-smtp' ) : esc_html__( 'Add SMTP', 'pro-mail-smtp' ); ?>
            </button>
        </div>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    function toggleSmtpAuthFields() {
        var enabled = $('#smtp_auth_toggle').is(':checked');
        $('#smtp-user-row, #smtp-pw-row').toggle(enabled);
        $('#smtp_user').prop('required', enabled);
        $('#smtp_pw').prop('required', enabled && $('#smtp_pw').val() === '');
    }
    toggleSmtpAuthFields();
    $('#smtp_auth_toggle').on('change', toggleSmtpAuthFields);
});
</script>