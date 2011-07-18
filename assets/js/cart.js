/**
 * Sears Holding Company Cart jQuery plugin.
 *
 * Handle cart events and product addtocarts.
 *
 * @author Brian Greenacre
 * @package shcproducts
 * @email bgreenacre42@gmail.com
 * @version $Id$
 * @since Thur 14 Jul 2011 07:32:09 PM
 * @license http://www.opensource.org/licenses/gpl-license.php
 */
(function($) {
var globalMethods = ['add', 'remove', 'update'],
    qsReg = /([^?=&]+)(=([^&]*))?/g,
    fieldNameReplaceReg = /(\[\])?$/,
    privateMethodReg = /^_/;

$.shcCart = {
    eventNames: [],
    options: {
        endpoint: '/wp-admin/admin-ajax.php'
    },
    json: {},
    init: function(el, args) {
        var $el = $(el);
        $el.data('cart:options', $.extend({}, $.shcCart.options, $el.data('cart:options'), args));
    },
    view: function(el, args) {
    },
    add: function() {
        var products = $.shcCart._productData(arguments);
        if (products.length)
            $.shcCart._call('add', products);
        return arguments;
    },
    empty: function(el) {
        $.shcCart._call('empty');
    },
    update: function() {
        var products = $.shcCart._productData(arguments);
        if (products.length)
            $.shcCart._call('update', products);
        return arguments;
    },
    remove: function() {
        var products = $.shcCart._productData(arguments);
        if (products.length)
            $.shcCart._call('remove', products);
        return arguments;
    },
    _call: function(action, data, opts) {
        data = data || [];
        data.push({name: 'action', value: 'cartaction_'+action});
        $.ajax($.extend({
            url: $.shcCart.options.endpoint,
            data: $.param(data),
            dataType: 'jsonp',
            success: function(response, status) {
                
                $.event.trigger('shcCart.'+action, [response]);
            },
            type: 'GET',
        }, opts || {}));
    },
    _productData: function(prods) {
        var products = [];

        $.each(prods, function() {
            var $this = $(this), data = [];
            if ($this.data('productData')) {
                products = $.merge(products, $this.data('productData'));
                return true;
            }
            $this.closest(':input').each(function() {
                $field = $(this);
                data.push({name: $field.attr('name').replace(fieldNameReplaceReg, '[]'), value: $field.val()});
            });
            if ($this.data()) {
                var blockDataKeys = ['handle', 'events'];
                $.each($this.data(), function(index) {
                    if ($.inArray(index, blockDataKeys) == -1)
                        data.push({name: index+'[]', value: this});
                });
            }

            if ($this.attr('href') && (qs = $this.attr('href').match(qsReg)) != null)
                $.each(Array.prototype.slice.call(qs, 1), function() {
                    var field = this.split('=');
                    data.push({name: field[0]+'[]', value: field[1]});
                });

            $this.data('productData', data);
            products = $.merge(products, $this.data('productData'));
        });

        return products;
    }
};
$.fn.shcCart = function(method, options) {
    return this.each(function() {
        if (typeof method === 'string' && method) {
            if (method.match(privateMethodReg))
                $.error('Method '+method+' is a private method which cannot be called from public scope.');
            else if ($.inArray(method, globalMethods))
                $.error('Method '+method+' is a global method which cannot be called within element context.');
            else if ($.shcCart[method])
                $.shcCart[method].apply(this, Array.prototype.slice.call(arguments, 1));
        }
        else if (typeof method === 'object' || ! method)
            $.shcCart.init.apply(this, arguments);
        else
            $.error('Method '+method+' does not exist on jQuery.shcCart');
    });
};

})(jQuery);
