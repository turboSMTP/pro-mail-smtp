jQuery(document).ready(function($) {
    console.log('FreeMailSMTPLogs loaded');
    $('.sort-column').on('click', function(e) {
        e.preventDefault();
        
        var column = $(this).data('column');
        var currentOrderby = $('input[name="orderby"]').val();
        var currentOrder = $('input[name="order"]').val();
        
        var newOrder = (currentOrderby === column && currentOrder === 'desc') ? 'asc' : 'desc';
        
        $('input[name="orderby"]').val(column);
        $('input[name="order"]').val(newOrder);
        
        $('.email-filters').submit();
    });
    
    // Handle pagination
    $(document).on('click', '.pagination-links a', function(e) {
        e.preventDefault();
        
        var href = $(this).attr('href');
        var pageMatch = href.match(/paged=(\d+)/);
        var page = pageMatch ? pageMatch[1] : 1;
        
        $('input[name="paged"]').val(page);
        
        $('.email-filters').submit();
    });
    
    // Reset filters button
    $('.reset-filter').on('click', function(e) {
        e.preventDefault();
        
        // Reset all filter inputs
        $('.provider-filter').val('');
        $('.status-filter').val('');
        $('input[name="date_from"]').val('');
        $('input[name="date_to"]').val('');
        $('input[name="search"]').val('');
        
        // Reset sorting to default
        $('input[name="orderby"]').val('sent_at');
        $('input[name="order"]').val('desc');
        
        // Reset page to 1
        $('input[name="paged"]').val(1);
        
        // Submit the form with reset values
        $('.email-filters').submit();
    });
});