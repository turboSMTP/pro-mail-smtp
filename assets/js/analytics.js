jQuery(document).ready(function($) {
    // Apply filters
    $('#apply-filters').on('click', function() {
        console.log('Applying filters');
        loadAnalyticsData();
    });

    function loadAnalyticsData() {
        console.log('Loading analytics data');
        $('#loading-overlay').show();

        var tbody = $('.analytics-table tbody');
        tbody.empty();
        tbody.append(`
            <tr>
                <td colspan="8" class="loading-message">Loading...</td>
            </tr>
        `);

        var filters = {
            provider: $('#provider-filter').val(),
            status: $('#status-filter').val(),
            date_from: $('#date-from').val(),
            date_to: $('#date-to').val()
        };

        var tbody = $('.analytics-table tbody');
        var thead = $('.analytics-table thead');
       
        $.ajax({
            url: FreeMailSMTPAnalytics.ajaxUrl,
            method: 'POST',
            data: {
                action: 'fetch_provider_analytics',
                nonce: FreeMailSMTPAnalytics.nonce,
                filters: filters
            },
            success: function(response) {
                console.log('Analytics response:', response);
                if (response.success) {
                    refreshTable(response.data);
                } else {
                    alert('Error loading analytics: ' + (response.data || 'Unknown error'));
                    tbody.find('.loading-message').text('Error loading data');
                }
            },
            error: function(xhr, status, error) {
                console.error('Ajax error:', error);
                alert('Error loading analytics data');
                tbody.find('.loading-message').text('Error loading data');
            },
            complete: function() {
                $('#loading-overlay').hide();
            }
        });
    }

    function refreshTable(data) {
        var tbody = $('.analytics-table tbody');
        var thead = $('.analytics-table thead');
        tbody.empty();
        thead.empty();

        if (!data || data.length === 0) {
            tbody.append(`
                <tr>
                    <td colspan="8" class="no-data">No data found</td>
                </tr>
            `);
            return;
        }

        var headers = data.columns.map(function(column) {
            return `<th>${escapeHtml(column)}</th>`;
        }).join('');
        thead.append(`<tr>${headers}</tr>`);

        data.data.forEach(function(row) {
            var rowHtml = data.columns.map(function(column) {
                var cellData = row[column] || '';
                var cellHtml = escapeHtml(cellData.toString());
                if (column === 'status') {
                    var statusClass = cellData.toLowerCase();
                    cellHtml = `<span class="status-badge status-${statusClass}">${cellHtml}</span>`;
                } else if (column === 'provider_message') {
                    var shortErrorText = cellHtml.length > 30 ? cellHtml.substring(0, 30) + '...' : cellHtml;
                    var errorPopup = cellHtml.length > 30 ? `<a href="#" class="see-more" data-error="${cellHtml}">See more</a>` : '';
                    cellHtml = `${shortErrorText} ${errorPopup}`;
                }
                return `<td>${cellHtml}</td>`;
            }).join('');
            tbody.append(`<tr>${rowHtml}</tr>`);
        });

        $('.see-more').on('click', function(e) {
            e.preventDefault();
            var errorText = $(this).data('error');
            alert(errorText);
        });
    }

    function escapeHtml(str) {
        if (!str) return '';
        return str.toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    loadAnalyticsData();
});