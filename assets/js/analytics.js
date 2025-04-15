jQuery(document).ready(function($) {
    console.log("Free Mail SMTP Analytics JS loaded");
    var currentPage = 1;
    var perPage = 10;
    var totalPages = 1;

    $('#apply-filters').on('click', function() {
        currentPage = 1; 
        loadAnalyticsData();
    });

    $('#prev-page').on('click', function() {
        if (currentPage > 1) {
            currentPage--;
            loadAnalyticsData();
        }
    });
    $('#next-page').on('click', function() {
            currentPage++;
        loadAnalyticsData();
    });

    function loadAnalyticsData() {
        perPage = parseInt($('#per-page').val()) || perPage;
        $('#loading-overlay').show();
        var tbody = $('.analytics-table tbody');
        tbody.empty();
        tbody.append(`
            <tr>
                <td colspan="8" class="loading-message">Loading...</td>
            </tr>
        `);

        // Make sure perPage is a number and at least 1
        perPage = Math.max(1, parseInt(perPage) || 10);
        
        var filters = {
            provider: $('#provider-filter').val(),
            status: $('#status-filter').val(),
            date_from: $('#date-from').val(),
            date_to: $('#date-to').val(),
            page: currentPage,
            per_page: perPage
        };

        var tbody = $('.analytics-table tbody');
        var thead = $('.analytics-table thead');

        $.ajax({
            url: FreeMailSMTPAnalytics.ajaxUrl,
            method: 'POST',
            data: {
                action: 'free_mail_smtp_fetch_provider_analytics',
                nonce: FreeMailSMTPAnalytics.nonce,
                filters: filters
            },
            success: function(response) {
                if (response.success && response.data) {
                    refreshTable(response.data);
                    if (response.data.total_pages !== undefined) {
                        totalPages = response.data.total_pages;
                        $('#current-page').text(currentPage + ' of ' + totalPages);
                        $('#prev-page').prop('disabled', currentPage <= 1);
                        $('#next-page').prop('disabled', currentPage >= totalPages);
                    } else {
                        $('#current-page').text(currentPage);
                    }
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