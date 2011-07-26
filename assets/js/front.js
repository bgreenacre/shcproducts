jQuery(document).ready(function($) {
    //Get the window height and width
    var winH = jQuery(window).height(),
        winW = jQuery(window).width();
    
    $('.cart').shcCart();
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
    $('#continue_shopping').live('click', function(e) {
        confirm_modal.data('overlay').close();
        e.preventDefault();
    });
    var quickview_modal = $('.shcp-quickview a').overlay({
        left: 'center',
        closeOnClick: true,
        mask: {
      		color: '#fff',
      		loadSpeed: 200,
      		opacity: 0.5,
      		zIndex: 9000,
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
    $('.addtocart').live('click', function(e) {
        $(this).shcProduct('add');
        //console.log(confirm_modal);
        $(this).parents("#shcp_quickview_modal").find('.close').click(); 
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
});
