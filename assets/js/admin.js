jQuery(document).ready(function($) {
    $('.shcp_product').draggable({
        scope: 'shcp',
        containment: '#shcproducts_related .inside',
        cursor: 'move',
        revert: true,
        connectToSortable: '#shcp_related_tank',
        helper: 'clone'
    });
    $('#shcp_related_tank').droppable({
        scope: 'shcp',
        over: function(e, ui) {
            var $el = $(this),
                $sender = $(ui.draggable),
                curHeight = $el.height(),
                addHeight = $sender.height();
            $el.height(curHeight+addHeight);
        }
    }).sortable({
        receive: function(e, ui) {
        },
        change: function() {console.log('change');},
        create: function() {console.log('create');},
        out: function() {console.log('out');}
    });
});

jQuery.noConflict();
		
jQuery(document).ready(function(){
  //jQuery(".chooseCategory").change(function() { showSelectedProducts(); });
  //jQuery("#choosePartNumber").click(function() { showProductDetail(); });
  
  init_import_form();
  
  // displays products from the api via ajax when form is submitted
  jQuery("#submit").click(function(e) { 
    e.preventDefault();
    import_products(); 
  });

});

function init_import_form() {
  
  // toggles the subcategory textbox to show or hide when category search method is selected or deselected
  jQuery('input[name="search_method"]').change(function() {

    var selected_method = jQuery("input[name='search_method']").filter(":checked").val();
    var subcategory_option = jQuery('.subcategory_option');
    
    if(selected_method == 'category' || selected_method == 'subcategory') {
      subcategory_option.show();
    } else {
      subcategory_option.hide();
    }
  });

}

function import_products(el) {
  
  // var product_count = el.attr('data-product-count');
  // var page_number = el.attr('data-page-number');
  // var category = jQuery(".chooseCategory").val();
  var method      = jQuery("input[name='search_method']").filter(":checked").val();
  var subcategory = jQuery("#search_term_subcategory").val();
  var terms       = jQuery("#search_terms").val();
  
  subcategory = subcategory != "Enter Subcategory" ? subcategory : null;
  terms = terms != "Enter Search Term(s)" ? terms : '';
  
  jQuery.post(
    shcp_ajax.ajaxurl,
    {
      action        : "action_list", 
      method        : method,
      subcategory   : subcategory,
      search_terms  : terms
      //'category'      : category,
      // 'page_number'   : page_number,
      // 'product_count' : product_count
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
    });
  
// 
//   // event handlers for response html
//   jQuery(".catSelect").change(function() {
//    rowColor = jQuery(this).attr('value') != "-1" ? "#d6f0d6" : "transparent";    
//     jQuery(this).parents("tr").css({ background : rowColor });
//   });
//   
//   // assign all categories with a single dropdown (still neccessary to save at the bottom)
//   jQuery(".selectAllCategories").change(function() {
//     var category = jQuery(this).val();
//     jQuery(".catSelect").each(function() {
//       jQuery(this).val(category);
//       rowColor = jQuery(this).attr('value') != "-1" ? "#d6f0d6" : "transparent";    
//       jQuery(this).parents("tr").css({ background : rowColor });
//     });
//   });
//   
//   // check all featured
//   jQuery("#selectAllFeatured").change(function() {
//     var status = jQuery(this).is(":checked") ? true : false;
//     jQuery("input[name='isFeatured']").each(function() {
//       jQuery(this).attr('checked', status);
//     });
//   });
// 
//   // check all hidden
//   jQuery("#selectAllHidden").change(function() {
//     var status = jQuery(el).is(":checked") ? true : false;
//     jQuery("input[name='isHidden']").each(function() {
//       jQuery(this).attr('checked', status);
//     });
//   });
// 
  jQuery("#save_products").click(function(e) { 
    e.preventDefault();
    save_products(); 
  });
//   jQuery("#addProductSubmit").click(function() { submitProductDetail(); });
//   jQuery(".product_page_link").click(function() { loadProductPage(jQuery(this)); });  
}
 
function save_products(){
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
// 
// function showProductDetail(){
// 
//  /* Get the data from the recent questions form */
//  var partNumber = jQuery("#partNumber").val();
// 
//   jQuery.post(
//     SearsAjax.ajaxurl,
//     {
//       action        : "show_import_product_detail", 
//       'partNumber'  : partNumber
//     },
//      function(response) {
//       jQuery('#load_product_detail').html(response);
//       jQuery("#addProductSubmit").click(function() { submitProductDetail(); });
//     }
//   );
// }
// 
// function submitProductDetail() {
// 
//  var items = [];
// 
//  items.push({
//    "title"         : jQuery('#title').val(), 
//    "imageId"       : jQuery('#imageId').val(), 
//    "partNumber"    : jQuery('#partNumber').val(), 
//    "category"      : jQuery('.chooseSingleCategory').val(), 
//    "isFeatured"    : jQuery('#isFeatured').is(':checked'),
//    "isHidden"      : jQuery('#isHidden').is(':checked'),
//    "numReview"     : jQuery('#numReview').val(), 
//    "rating"        : jQuery('#rating').val(), 
//    "cutPrice"      : jQuery('#cutPrice').val(),
//    "displayPrice"  : jQuery('#displayPrice').val()
//  });
//  
//   jQuery.post(
//     SearsAjax.ajaxurl,
//     {
//       action      : "add_product_content", 
//       'cookie'    : encodeURIComponent(document.cookie), 
//       'products'  : items
//     },
//      function() {
//        jQuery('#message').addClass('updated').html("<p>Product Imported</p>");
//     }
//   );
// }
