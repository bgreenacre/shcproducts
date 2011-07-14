jQuery(document).ready(function($) {
    $('.cart').shcCart();
    $('.addtocart').bind('click', function() {
        $.shcCart.add(this);
        return false;
    });
});
