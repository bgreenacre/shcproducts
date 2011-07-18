jQuery(document).ready(function($) {
    $('.cart').shcCart();
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
    $('.shcp-item').hover(
      function() {
        $(this).find('.shcp-quickview').show();
      },
      function() {
        $(this).find('.shcp-quickview').hide();
      }
    );
});
