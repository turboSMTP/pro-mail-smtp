<table class="form-table">
    <tr>
        <th scope="row"><label for="api_key">API Key</label></th>
        <td>
            <input type="password" name="api_key" id="api_key" class="regular-text" required>
            <button type="button" class="button toggle-password">
                <span class="dashicons dashicons-visibility"></span>
            </button>
            <p class="description">Enter your API key</p>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="priority">Priority</label></th>
        <td>
            <input type="number" name="priority" id="priority" value="1" class="small-text" min="1" required>
            <p class="description">Lower number = higher priority</p>
        </td>
    </tr>
</table>