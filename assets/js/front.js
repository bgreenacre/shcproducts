jQuery(document).ready(function($) {
    //Get the window height and width
    var winH = jQuery(window).height(),
        winW = jQuery(window).width();
    
    $('.cart').shcCart();
    $('.addtocart').overlay({
      left: 'center',
      closeOnClick: true,
      mask: {
    		color: '#fff',
    		loadSpeed: 200,
    		opacity: 0.5
      },
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
        var $tank = $(this).closest('.shcp-image-tank');
        $tank
            .find('.shcp-current-image img')
            .attr('src', $(this).data('image'));
        $tank
            .find('.shcp-image-thumbnail')
            .removeClass('selected');
        $(this).addClass('selected');
        return false;
    });
    $('div.shcp-quickview a').overlay({
        left: 'center',
        closeOnClick: true,
        mask: {
      		color: '#fff',
      		loadSpeed: 200,
      		opacity: 0.5
        },
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
    $('.close').live('click', function(e) {
        e.preventDefault();
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
    // swap current image out for thumbnail when clicked
    $('.shcp-image-thumbnail').bind('click', function() {
      $('.shcp-current-image img').attr('src', $(this).find('img').attr('src'));
      $('.selected').removeClass('selected');
      $(this).addClass('selected');
    });
});
