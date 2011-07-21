jQuery(document).ready(function($) {
    //Get the window height and width
    var winH = jQuery(window).height(),
        winW = jQuery(window).width();
    
    $('.cart').shcCart();
    $('.addtocart').overlay({
      top: 'center',
      left: 'center',
      closeOnClick: true,
      onBeforeLoad: function(e) {
          var id = this.getTrigger().data('post_id'),
              wrap = this.getOverlay();
          wrap.find('#shcp-modal-container').remove();
          $.ajax({
              url: shcp_ajax.ajaxurl,
              data: {action: 'product_action_cartconfirm', p: id},
              dataType: 'html',
              type: 'POST',
              success: function(data) {
                  wrap.append(data);
              }
          });
      }
    });  
    $('.addtocart').live('click', function() {
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
        onBeforeLoad: function(e) {
            var id = this.getTrigger().data('post_id'),
                wrap = this.getOverlay();
            wrap.find('form').remove();
            $.ajax({
                url: shcp_ajax.ajaxurl,
                data: {action: 'product_action_quickview', p: id},
                dataType: 'html',
                type: 'POST',
                success: function(data) {
                    wrap.append(data);
                }
            });
        }
    });
    $('.close').live('click', function() {
        return false;
    });
    // show quickview button on product hover
    $('.shcp-item').hover(
      function() {
        $(this).find('.shcp-quickview').show();
      },
      function() {
        $(this).find('.shcp-quickview').hide();
      }
    );
});
