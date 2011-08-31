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
var qsReg = /([^?=&]+)(=([^&]*))?/g,
    fieldNameReplaceReg = /(\[\])?$/,
    privateMethodReg = /^_/;

/**
 * Global shcCart class with jQuery.
 */
$.shcCart = {
    eventNames: [],
    options: {
        endpoint: '/wp-admin/admin-ajax.php',
        autoUpdate: false
    },
    json: {},
    init: function(args) {
        var $el = $(this);
        $el.data('cart:options', $.extend({}, $.shcCart.options, $el.data('cart:options'), args))
            .bind('shcCartUpdate', function() {
                $(this).shcCart('view');
            })
            .bind('submit', function(e) {
                e.preventDefault();
                $('#shcp_loader').show();
                $(this).shcCart('update');
                return false;
            });
        $el.find('.shcp-empty-cart').live('click', function(e) {
            $('#shcp_loader').show();
            $el.shcCart('empty');
            e.preventDefault();
        });
        $el.find('.shcp-remove-item').live('click', function(e) {
            $('#shcp_loader').show();
            $.shcCart.remove([this]);
            e.preventDefault();
        });
        $el.find(':input:not([type="submit"],[type="button"])').live('change blur', function() {
            $.event.trigger('shcCartChange', [$.shcCart.json, this])
            if ($el.data('cart:options').autoUpdate)
                $el.shcCart('update');
        });
    },
    view: function(el, args) {
        location.reload();
    },
    add: function(prods) {
        var products = $.shcCart._productData(prods);
        if (products.length)
            $.shcCart._call('add', products);
        return prods;
    },
    empty: function(args) {
        $.shcCart._call('empty');
    },
    update: function(args) {
        var $el = $(this);
        $.shcCart._call('update', $el.serializeArray());
    },
    remove: function(prods) {
        var products = $.shcCart._productData(prods);
        if (products.length)
            $.shcCart._call('remove', products);
        return prods;
    },
    _call: function(action, data, opts) {
        data = data || [];
        data.push({name: 'action', value: 'cartaction_'+action});
        $.ajax($.extend({
            url: $.shcCart.options.endpoint,
            data: $.param(data),
            dataType: 'json',
            success: function(response, status) {
                $.event.trigger('shcCartUpdate', [action, response]);
            },
            type: 'GET'
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

/**
 * shcCart element function.
 */
$.fn.shcCart = function(method, options) {
    return this.each(function() {
        if (typeof method === 'string' && method) {
            if (method.match(privateMethodReg))
                $.error('Method '+method+' is a private method which cannot be called from public scope.');
            else if ($.shcCart[method])
                $.shcCart[method].apply(this, Array.prototype.slice.call(arguments, 1));
            else
                $.error('Method '+method+' does not exist on jQuery.shcCart');
        }
        else if (typeof method === 'object' || ! method) {
            $.shcCart.init.apply(this, [method]);
        }
    });
};

$.fn.shcProduct = function(method, options) {
    if (method == 'add')
        return $.shcCart.add(this);
    else if (method == 'remove')
        return $.shcCart.remove(this);
    else
        $.error('Method '+method+' does not exist on jQuery.shcProduct');
};

})(jQuery);
