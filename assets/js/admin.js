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
    $('#shcp_related_tank').droppable({
        scope: 'shcp',
        tolerance: 'touch',
        over: function(e, ui) {
            var $el = $(this),
                $sender = $(ui.draggable),
                curHeight = $el.height(),
                addHeight = $sender.height();
            $el.height(curHeight+addHeight);
        },
        drop: function(e, ui){
            var $el = $(this),
                $sender = $(ui.draggable);
            if ($('li', $el).length <= 2 && $('#post_id_'+$sender.data('post_id'), $el).length == 0)
                $el.append(
                    $sender.draggable('disable')
                    .append('<input type="hidden" name="shcp_related_products[]" value="'+$sender.data('post_id')+'" />')
                    .append('<a href="#" class="shcp_trash"><img src="/wp-content/plugins/shcproducts/assets/images/trash.png" alt="Remove Product" height="22px" width="20px" /></a>')
                );
            else
                return false;
        },
        deactivate: function(e, ui) {
            var $el = $(this),
                $sender = $(ui.draggable),
                liHeight = parseInt($('li', $el).height()) * $('li', $el).length,
                plHeight = parseInt($sender.height());
            $el.height(liHeight);
        }
    });
    // Click of the trash icon should remove the related product.
    $('#shcp_related_tank li .shcp_trash').live('click', function() {
        $(this).parent().remove();
        return false;
    });
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
    //jQuery(".chooseCategory").change(function() { showSelectedProducts(); });
    //jQuery("#choosePartNumber").click(function() { showProductDetail(); });
    init_import_form();
    // displays products from the api via ajax when form is submitted
    $("#submit").click(function(e) {
        e.preventDefault();
        import_products();
    });
});


function init_import_form() {

  // toggles the subcategory textbox to show or hide when category search method is selected or deselected
  jQuery('input[name="search_method"]').change(function() {

    var selected_method     = jQuery("input[name='search_method']").filter(":checked").val();
    var subcategory_option  = jQuery('.subcategory_option');

    if(selected_method == 'category' || selected_method == 'subcategory') {
      subcategory_option.show();
    } else {
      subcategory_option.hide();
    }
  });

}

function import_products(el) {
  console.log('import_products');

  var product_count = el.attr('data-product-count');
  var page_number   = el.attr('data-page-number');
  var method        = jQuery("input[name='search_method']").filter(":checked").val();
  var subcategory   = jQuery("#search_term_subcategory").val();
  var terms         = jQuery("#search_terms").val();

  subcategory = subcategory != "Enter Subcategory" ? subcategory : null;
  terms = terms != "Enter Search Term(s)" ? terms : '';

  jQuery.post(
    shcp_ajax.ajaxurl,
    {
      action        : "action_list",
      method        : method,
      subcategory   : subcategory,
      search_terms  : terms,
      page_number   : page_number,
      product_count : product_count
    },
     function(response) {
      jQuery('#shcp_import_list').html(response);
      import_callback(this);
    }
  );
}
//
//
// function showSelectedProducts() {
//
//  var category = jQuery(".chooseCategory").val();
//  var product_count = jQuery(".product_count").text();
//
//   jQuery.post(
//     SHCP_ajax.ajaxurl,
//     {
//       action      : "show_import_product_list",
//       'category'  : category,
//       'page_number'   : 1,
//       'product_count' : product_count
//     },
//      function(response) {
//       jQuery('#load_product_list').html(response);
//       import_callback();
//     }
//   );
//
// }
//
function import_callback() {

  console.log("import_callback");

  // check all to import
  jQuery("#import_all").change(function() {
    var status = jQuery(this).is(":checked") ? true : false;
    jQuery("input[name='import_single[]']").each(function() {
      jQuery(this).attr('checked', status);
    });

  // activate save_products button
  jQuery("#save_products").click(function(e) {
    e.preventDefault();
    save_products();
  });
  
//   jQuery("#addProductSubmit").click(function() { submitProductDetail(); });
  
  // activate pagination links
  jQuery(".product_page_link").click(function(e) {
    console.log("import_callback - product_page_link");
    e.preventDefault(); 
    import_products(jQuery(this)); 
  });
}

function save_products() {
  
  console.log("save_products");

 var import_table = jQuery("#shcp_import_table");
 var items = [];

 import_table.find('tbody tr').each(function(index) {
    if(jQuery(this).find("input[name='import_single[]']").is(":checked")) {
      items.push(index);
    }
  });

  data = jQuery('#shcp_import_form').serialize();

  jQuery.ajax({
    type: 'post',
    url: '/wp-admin/admin-ajax.php?action=action_save',
    data: data,
    success: function() {
      for(var i in items) {
        row = jQuery("#row_" + items[i]);

        row.css({ background : '#f1f1f1' }).addClass('disable');
        row.find('input[name="is_featured"]').remove();
        row.find('input[name="is_hidden"]').remove();
        row.find('input[name="import_single"]').remove();
        row.find('.partnumber').text("imported");
        row.find('.cutprice').text("");
        row.find('.displayprice').text("");
      }
    }
  });
}

