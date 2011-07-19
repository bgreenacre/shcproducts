jQuery(document).ready(function($) {
    //Get the window height and width
    var winH = jQuery(window).height(),
        winW = jQuery(window).width();
    
    $('.cart').shcCart({});
    $('.addtocart').bind('click', function() {
        $(this).shcProduct('add');
        return false;
    });
    $('.shcp-image-thumbnail').live('click', function() {
        $(this)
            .parent()
            .find('.shcp-current-image img')
            .attr('src', $('img', this).attr('src'));
        return false;
    });
    $('div.shcp-quickview a').overlay({
        top: 'center',
        left: 'center',
        closeOnClick: true,
        onBeforeLoad: function(e) {
            var id = this.getTrigger().data('post_id'),
                wrap = this.getOverlay();
            wrap.empty().load(shcp_ajax.ajaxurl, {action: 'product_action_quickview', p: id});
        }
    });
    $('.shcp-item').hover(
      function() {
        $(this).find('.shcp-quickview').show();
      },
      function() {
        $(this).find('.shcp-quickview').hide();
      }
    );
});
