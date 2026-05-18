<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Resolve saved connection data for edit mode.
// $connection_data and $is_edit are passed from Providers::load_provider_form()
$pro_mail_smtp_cd       = isset( $connection_data ) && is_array( $connection_data ) ? $connection_data : [];
$pro_mail_smtp_saved_key_id  = isset( $pro_mail_smtp_cd['access_key_id'] )       ? esc_attr( $pro_mail_smtp_cd['access_key_id'] )        : '';
$pro_mail_smtp_saved_secret  = isset( $pro_mail_smtp_cd['secret_access_key'] )   ? esc_attr( $pro_mail_smtp_cd['secret_access_key'] )     : '';
$pro_mail_smtp_saved_region  = isset( $pro_mail_smtp_cd['region'] )              ? $pro_mail_smtp_cd['region']                           : 'us-east-1';
$pro_mail_smtp_saved_from    = isset( $pro_mail_smtp_cd['email_from_overwrite'] ) ? esc_attr( $pro_mail_smtp_cd['email_from_overwrite'] ) : '';

$pro_mail_smtp_ses_regions = [
    'us-east-1'      => 'US East (N. Virginia)',
    'us-east-2'      => 'US East (Ohio)',
    'us-west-1'      => 'US West (N. California)',
    'us-west-2'      => 'US West (Oregon)',
    'eu-west-1'      => 'EU (Ireland)',
    'eu-west-2'      => 'EU (London)',
    'eu-west-3'      => 'EU (Paris)',
    'eu-central-1'   => 'EU (Frankfurt)',
    'eu-south-1'     => 'EU (Milan)',
    'eu-north-1'     => 'EU (Stockholm)',
    'ap-south-1'     => 'Asia Pacific (Mumbai)',
    'ap-northeast-1' => 'Asia Pacific (Tokyo)',
    'ap-northeast-2' => 'Asia Pacific (Seoul)',
    'ap-northeast-3' => 'Asia Pacific (Osaka)',
    'ap-southeast-1' => 'Asia Pacific (Singapore)',
    'ap-southeast-2' => 'Asia Pacific (Sydney)',
    'ca-central-1'   => 'Canada (Central)',
    'sa-east-1'      => 'South America (São Paulo)',
    'me-south-1'     => 'Middle East (Bahrain)',
    'af-south-1'     => 'Africa (Cape Town)',
    'il-central-1'   => 'Israel (Tel Aviv)',
];
?>
<div class="wizard-step">
    <h3><?php echo ( isset( $is_edit ) && $is_edit ) ? 'Edit Amazon SES Configuration' : 'Add Amazon SES Provider'; ?></h3>
    <p class="description">
        <?php esc_html_e( 'Enter your AWS IAM credentials below. The IAM user must have the following permissions: ', 'pro-mail-smtp' ); ?>
        <code>ses:SendEmail</code>, <code>ses:SendRawEmail</code>, <code>ses:GetAccount</code>.
    </p>

    <form id="provider-form" method="post">
        <?php wp_nonce_field( 'pro_mail_smtp_nonce_providers', 'pro_mail_smtp_nonce_providers' ); ?>

        <input type="hidden" name="provider"      id="provider"      value="amazonses">
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
                    <label for="access_key_id"><?php esc_html_e( 'Access Key ID', 'pro-mail-smtp' ); ?></label>
                </th>
                <td>
                    <div class="api-key-wrapper">
                        <input type="password"
                               name="config_keys[access_key_id]"
                               id="access_key_id"
                               class="regular-text"
                               value="<?php echo esc_attr( $pro_mail_smtp_saved_key_id ); ?>"
                               required>
                        <span id="toggle_access_key_id" class="dashicons dashicons-visibility"></span>
                    </div>
                    <p class="description"><?php esc_html_e( 'Your AWS IAM Access Key ID.', 'pro-mail-smtp' ); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="secret_access_key"><?php esc_html_e( 'Secret Access Key', 'pro-mail-smtp' ); ?></label>
                </th>
                <td>
                    <div class="secret-wrapper">
                        <input type="password"
                               name="config_keys[secret_access_key]"
                               id="secret_access_key"
                               class="regular-text"
                               value="<?php echo esc_attr( $pro_mail_smtp_saved_secret ); ?>"
                               required>
                        <span id="toggle_secret_access_key" class="dashicons dashicons-visibility"></span>
                    </div>
                    <p class="description"><?php esc_html_e( 'Your AWS IAM Secret Access Key.', 'pro-mail-smtp' ); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="region"><?php esc_html_e( 'AWS Region', 'pro-mail-smtp' ); ?></label>
                </th>
                <td>
                    <select name="config_keys[region]" id="region" required>
                        <?php foreach ( $pro_mail_smtp_ses_regions as $pro_mail_smtp_region_key => $pro_mail_smtp_region_label ) : ?>
                            <option value="<?php echo esc_attr( $pro_mail_smtp_region_key ); ?>" <?php selected( $pro_mail_smtp_saved_region, $pro_mail_smtp_region_key ); ?>>
                                <?php echo esc_html( $pro_mail_smtp_region_label ); ?> &mdash; <?php echo esc_html( $pro_mail_smtp_region_key ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description"><?php esc_html_e( 'Select the AWS region where your SES account and verified identities are configured.', 'pro-mail-smtp' ); ?></p>
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
                <?php echo ( isset( $is_edit ) && $is_edit ) ? esc_html__( 'Update Provider', 'pro-mail-smtp' ) : esc_html__( 'Add Provider', 'pro-mail-smtp' ); ?>
            </button>
        </div>
    </form>
</div>
