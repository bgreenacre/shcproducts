shcp_filter_product_grid_form = function(form) {
    var $form = jQuery(form);

    jQuery(':input:not([type="hidden"])').each(function() {
        var $field = jQuery(this);

        if ($field.filter('select').length > 0) {
            fields = jQuery(':selected:first', $field).data('fields');
        } else if ($field.filter('[type="radio"], [type="checkbox"]').length > 0) {
            fields = jQuery(':checked:first', $field).data('fields');
        } else {
            fields = $field.data('fields');
        }

        if (typeof fields == 'string' && fields != '')
            fields = jQuery.parseJSON(fields);
        else if (typeof fields != 'object')
            fields = {};

        for (i in fields) {
            if (fields[i] == false)
                jQuery(':input[name="'+i+'"]', $form).remove();
            else if ($form.find(':input[name="'+i+'"]').length > 0)
                jQuery(':input[name="'+i+'"]', $form).val(fields[i]);
            else
                $form.append('<input type="hidden" name="'+i+'" id="'+i+'" value="'+fields[i]+'" />');
        }
    });
};

shcp_items_init = function(items) {
    var $items = (typeof items == 'undefined') ? jQuery('.shcp-item') : jQuery(items);
    var quickview_modal = jQuery('.shcp-quickview a', $items).overlay({
        left: 'center',
        closeOnClick: true,
        onBeforeLoad: function(e) {
            var id = this.getTrigger().data('post_id'),
                wrap = this.getOverlay();
            wrap.find('form').remove();
            jQuery.ajax({
                url: shcp_ajax.ajaxurl,
                data: {action: 'product_action_quickview', p: id},
                dataType: 'html',
                type: 'POST',
                success: function(data) {
                    wrap.append(data);
                }
            });
        },
        onLoad: function(e) {
            jQuery(this.getOverlay()).find('.close').html('Close');
        }
    });
    jQuery('.addtocart', $items).live('click', function(e) {
        jQuery(this).shcProduct('add');
        jQuery(this).parents("#shcp_quickview_modal").find('.close').click(); 
        e.preventDefault();
    });
    // show quickview button on product hover
    $items.hover(
      function() {
        jQuery(this).find('.shcp-quickview').show();
      },
      function() {
        jQuery(this).find('.shcp-quickview').hide();
      }
    );
}

jQuery(document).ready(function($) {
    //Get the window height and width
    var winH = jQuery(window).height(),
        winW = jQuery(window).width();

    $('form.cart').shcCart();
    $.shcCart.options.endpoint = shcp_ajax.ajaxurl;
    $('.shcp-image-thumbnail').live('click', function(e) {
        var $tank = $(this).closest('.shcp-image-tank');
        $tank
            .find('.shcp-current-image img')
            .attr('src', $(this).data('image'));
        $tank
            .find('.shcp-image-thumbnail')
            .removeClass('selected');
        $(this).addClass('selected');
        e.preventDefault();
    });
    var confirm_modal = $('.addtocart').overlay({
      left: 'center',
      closeOnClick: true,
      onBeforeLoad: function(e) {
        var id = this.getTrigger().data('post_id'),
            wrap = this.getOverlay();
        wrap.find('#shcp-modal-container').remove();
        $('#shcp-cartconfirm').data('active_overlay', this);
        $.ajax({
            url: shcp_ajax.ajaxurl,
            data: {action: 'product_action_cartconfirm', p: id},
            dataType: 'html',
            type: 'POST',
            success: function(data) {
                wrap.append(data);
            }
        });
      },
        onLoad: function(e) {
            $(this.getOverlay()).find('.close').html('Close');
        }
    });  
    $('#continue_shopping').live('click', function(e) {
        $(this).closest('#shcp-cartconfirm').data('active_overlay').close();
        e.preventDefault();
    });
    $('.shcp-update-cart').live('click', function(e) {
        e.preventDefault();
        $(this).closest('form.cart').trigger('submit');
    });
    $('.shcp-overlay').overlay({
        onLoad: function(e) {
            $(this.getOverlay()).find('.close').html('Close');
        }
    });
    $('#shcp_grid_filter').bind('submit', function(e) {
        var $form = $(this);
        e.preventDefault();
        shcp_filter_product_grid_form(this);

        $.ajax({
            url: shcp_ajax.ajaxurl,
            data: $form.serialize(),
            type: 'GET',
            dataType: 'html',
            success: function(data) {
                var $grid = $(data).filter('#shcp_items');

                $('#shcp_items').replaceWith($grid);
                shcp_items_init();
            }
        });
    }).find('select').bind('change', function(e) {
        $(this).closest('#shcp_grid_filter').trigger('submit');
    });

    shcp_items_init();
});
