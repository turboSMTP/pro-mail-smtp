jQuery(document).ready(function($) {
    console.log("Pro Mail SMTP Analytics JS loaded");
    var currentPage = 1;
    var perPage = 10;
    var totalPages = 1;

    $('#analytics-filter-form').on('submit', function(e) {
        e.preventDefault();
        currentPage = 1; 
        loadAnalyticsData();
    });

    $('#apply-filters').on('click', function() {
        currentPage = 1; 
        loadAnalyticsData();
    });

    // Handle pagination via AJAX
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
    
    function updatePaginationFormFromFilters() {
        $('#pagination-provider-input').val($('#provider-filter').val());
        $('#pagination-status-input').val($('#status-filter').val());
        $('#pagination-date-from-input').val($('#date-from').val());
        $('#pagination-date-to-input').val($('#date-to').val());
        $('#pagination-per-page-input').val($('#per-page').val());
    }
    
    $('.pagination-form-submit').on('click', function() {
        if (!$(this).prop('disabled')) {
            updatePaginationFormFromFilters();
            
            if ($(this).hasClass('prev-page')) {
                var currentPageNum = parseInt($('#current-page').text().split(' ')[0]);
                $('#pagination-page-input').val(Math.max(1, currentPageNum - 1));
            } else {
                var currentPageNum = parseInt($('#current-page').text().split(' ')[0]);
                $('#pagination-page-input').val(currentPageNum + 1);
            }
            
            // Submit the hidden form
            $('#pagination-form').submit();
        }
        return false;
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
            url: ProMailSMTPAnalytics.ajaxUrl,
            method: 'POST',
            data: {
                action: 'pro_mail_smtp_fetch_provider_analytics',
                nonce: ProMailSMTPAnalytics.nonce,
                filters: filters
            },
            success: function(response) {
                if (response.success && response.data) {
                    refreshTable(response.data); 

                    // Default button states
                    let isLastPage = false;
                    let isFirstPage = currentPage <= 1;

                    if (response.data.total_pages !== undefined && !isNaN(parseInt(response.data.total_pages))) {
                        totalPages = parseInt(response.data.total_pages);
                        totalPages = Math.max(1, totalPages); 
                        currentPage = Math.min(currentPage, totalPages); 
                        isLastPage = currentPage >= totalPages;
                        $('#current-page').text(currentPage + ' of ' + totalPages);
                    } else {
                        totalPages = currentPage; 
                        $('#current-page').text(currentPage);
                    }

                    if (response.data.data && response.data.data.length < perPage) {
                        isLastPage = true; 
                        
                        if (response.data.total_pages !== undefined) {
                             totalPages = currentPage;
                             $('#current-page').text(currentPage + ' of ' + totalPages); 
                        }
                    }

                    $('#prev-page').prop('disabled', isFirstPage);
                    $('#next-page').prop('disabled', isLastPage);

                } else {
                    alert('Error loading analytics: ' + (response.data || 'Unknown error'));
                    tbody.find('.loading-message').text('Error loading data');
                    $('#current-page').text('1');
                    $('#prev-page').prop('disabled', true);
                    $('#next-page').prop('disabled', true);
                }
            },
            error: function(xhr, status, error) {
                alert('Error loading analytics data');
                tbody.find('.loading-message').text('Error loading data');
                $('#current-page').text('1');
                $('#prev-page').prop('disabled', true);
                $('#next-page').prop('disabled', true);
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

        if (!data || !data.data || data.data.length === 0) {
            tbody.append(`
                <tr>
                    <td colspan="8" class="no-data">No data found</td>
                </tr>
            `);
             if (data && data.columns) {
                 var headers = data.columns.map(function(column) {
                     return `<th>${escapeHtml(column)}</th>`;
                 }).join('');
                 thead.append(`<tr>${headers}</tr>`);
             } else {
                 thead.append(`<tr><th colspan="8">Columns definition missing</th></tr>`);
             }
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
                    var errorPopup = cellHtml.length > 30 ? `<a href="#" class="see-more" data-error="${escapeHtml(cellHtml)}">See more</a>` : ''; // Ensure error data is escaped for attribute
                    cellHtml = `${shortErrorText} ${errorPopup}`;
                }
                return `<td>${cellHtml}</td>`;
            }).join('');
            tbody.append(`<tr>${rowHtml}</tr>`);
        });

        $('.see-more').off('click').on('click', function(e) { 
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