jQuery(document).ready(function($) {
    var timer;
    // Make products draggable in post editor.
    $('.shcp_product')
        .live('update', function() {
            $(this).draggable('destroy').draggable({
                scope: 'shcp',
                containment: '#shcproducts_related .inside',
                cursor: 'move',
                revert: true,
                connectToSortable: '#shcp_related_tank',
                helper: 'clone'
            });
        })
        .trigger('update')
        .ajaxSuccess(function() {
            $(this).trigger('update');
        });
    // Make related products tank droppable and sortable.
    $('#shcp_related_tank').sortable({
        stop: function(e, ui) {
            var $sender = $(ui.item);
            if ($('li', this).length > 3)
                $sender.sortable('destroy').remove();
            else
                $sender
                    .append('<input type="hidden" name="shcp_related_products[]" value="'+$sender.data('post_id')+'" />')
                    .append('<a href="#" class="shcp_trash"><img src="'+shcp_ajax.imageurl+'/trash.png" alt="Remove Product" height="22px" width="20px" /></a>');
        }
    });
    // Click of the trash icon should remove the related product.
    $('#shcp_related_tank li .shcp_trash').live('click', function() {
        $(this).parent().sortable('destroy').remove();
        return false;
    });
    $('#shcp_pager a').live('click', function() {
        $('#shcp_products_tank').load($(this).attr('href'));
        return false;
    });
    // Add listeners for the keyword search.
    $keyword = $('#shcp_keyword');
    $keyword
        .val($keyword.data('label'))
        .bind('blur', function() {
            if ( ! $keyword.val())
                $keyword.val($keyword.data('label')).css({color: '#ccc'});
        })
        .bind('focus', function() {
            if ($keyword.val() == $keyword.data('label'))
                $keyword.val('');
            $keyword.css({color: '#666'});
        })
        .bind('keypress', function() {
            if (timer)
                clearTimeout(timer);
            $('#shcp_loader').show();
            timer = setTimeout(function() {
                $('#shcp_products_tank').load(shcp_ajax.ajaxurl,
                    {
                        action: "action_filter_list",
                        s: $keyword.val()
                    }, function() {
                        $('#shcp_loader').hide();
                        $('.shcp_product').trigger('update');
                    });
            }, 1000);
        });
});
