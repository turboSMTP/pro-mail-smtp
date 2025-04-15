<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<table class="wp-list-table widefat fixed striped analytics-table" id="analytics-table">
    <thead>
        <tr>
            <th scope="col"><?php esc_html_e('ID', 'free-mail-smtp'); ?></th>
            <th scope="col"><?php esc_html_e('Subject', 'free-mail-smtp'); ?></th>
            <th scope="col"><?php esc_html_e('Sender', 'free-mail-smtp'); ?></th>
            <th scope="col"><?php esc_html_e('Recipient', 'free-mail-smtp'); ?></th>
            <th scope="col"><?php esc_html_e('Send Time', 'free-mail-smtp'); ?></th>
            <th scope="col"><?php esc_html_e('Status', 'free-mail-smtp'); ?></th>
            <th scope="col"><?php esc_html_e('Domain', 'free-mail-smtp'); ?></th>
            <th scope="col"><?php esc_html_e('Provider Message', 'free-mail-smtp'); ?></th>
        </tr>
    </thead>
    <tbody>
            <tr>
                <td colspan="8"><?php esc_html_e('No data found.', 'free-mail-smtp'); ?></td>
            </tr>
    </tbody>
</table>
<div id="pagination">
    <button id="prev-page">Prev</button>
    <span id="current-page">1</span>
    <button id="next-page">Next</button>
</div>