jQuery(document).ready(function($) {
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
    
    $(document).on('click', '.pagination-button', function(e) {
        e.preventDefault();
        
        if ($(this).hasClass('disabled')) {
            return false;
        }
        
        var $form = $('.email-filters');
        var page = $(this).data('page');
        
        $form.find('input[name="paged"]').val(page);
        
        if ($form.find('input[name="filter_action"]').length === 0) {
            $form.append('<input type="hidden" name="filter_action" value="filter_logs">');
        } else {
            $form.find('input[name="filter_action"]').val('filter_logs');
        }
        
        
        $form.submit();
    });
    
    $('.reset-filter').on('click', function(e) {
        e.preventDefault();
        
        var $form = $('.email-filters');
        
        // Reset all filter inputs
        $form.find('.provider-filter').val('');
        $form.find('.status-filter').val('');
        $form.find('input[name="date_from"]').val('');
        $form.find('input[name="date_to"]').val('');
        $form.find('input[name="search"]').val('');
        
        // Reset sorting to default
        $form.find('input[name="orderby"]').val('sent_at');
        $form.find('input[name="order"]').val('desc');
        
        // Reset page to 1
        $form.find('input[name="paged"]').val(1);
        
        // Add filter_action if not present
        if ($form.find('input[name="filter_action"]').length === 0) {
            $form.append('<input type="hidden" name="filter_action" value="filter_logs">');
        }
        
        // Submit the form with reset values
        $form.submit();
    });
});