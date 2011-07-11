jQuery(document).ready(function($) {
  
    // displays products from the api via ajax when form is submitted
    $("#submit_keyword").click(function(e) {
        e.preventDefault();
        import_products(jQuery(this), 'keyword', null);
    });
    
    // displays a list of categories when a vertical is selected
    $("#submit_vertical").click(function(e) {
        e.preventDefault();
        import_products(jQuery(this), 'vertical', null);
    });

    // displays a list of subcategories when a category is selected
    $("#search_categories").change(function(e) {
        e.preventDefault();
        import_products(jQuery(this), 'category', null);
    });
    
    // displays a list of products when a subcategory is selected
    $("#search_subcategories").change(function(e) {
        e.preventDefault();
        import_products(jQuery(this), 'subcategory', null);
    });
    
});

function import_products(el, method, page_data) {

  var product_count     = page_data != null ? page_data.attr('data-product-count') : 0;
  var page_number       = page_data != null ? page_data.attr('data-page-number') : 1;
  var keyword_terms     = jQuery("#search_terms_keyword").val();
  var vertical_terms    = jQuery("#search_terms_vertical").val();
  var category_terms    = jQuery("#search_categories option:selected").val();
  var subcategory_terms = jQuery("#search_subcategories option:selected").val();

  keyword_terms         = keyword_terms != "Enter keywords" ? keyword_terms : '';
  vertical_terms        = vertical_terms != "Enter vertical name" ? vertical_terms : '';
  category_terms        = category_terms != "Choose Category" ? category_terms : '';
  subcategory_terms     = subcategory_terms != "Choose Subategory" ? subcategory_terms : '';
  
  if(method == 'keyword') {

    jQuery.post(
      shcp_ajax.ajaxurl,
      {
        action        : "action_list",
        method        : method,
        search_terms  : keyword_terms,
        page_number   : page_number,
        product_count : product_count
      },
       function(response) {
        jQuery('#shcp_import_list').html(response);
        import_callback(this);
      }
    );
  }
  
  if(method == 'vertical') {
    jQuery.post(
      shcp_ajax.ajaxurl,
      {
        action        : "action_categories",
        method        : method,
        search_terms  : vertical_terms
      },
      function(response) {
        jQuery('#shcp_categories').html(response);
        import_callback(this);
      }    
    );  
  }  
  
  if(method == 'category') {    
    jQuery.post(
      shcp_ajax.ajaxurl,
      {
        action          : "action_subcategories",
        method          : method,
        vertical_terms  : vertical_terms,
        search_terms    : category_terms
      },
      function(response) {
        jQuery('#shcp_subcategories').html(response);
        import_callback(this);
      }    
    );  
  }  

  
  if(method == 'subcategory') {    
    jQuery.post(
      shcp_ajax.ajaxurl,
      {
        action            : "action_list",
        method            : method,
        page_number       : page_number,
        product_count     : product_count,
        vertical_terms    : vertical_terms,
        category_terms    : category_terms,
        subcategory_terms : subcategory_terms
      },
      function(response) {
        jQuery('#shcp_import_list').html(response);
        import_callback(this);
      }    
    );  
  }
}

function import_callback() {

  // check all to import
  jQuery("#import_all").change(function() {
    var status = jQuery(this).is(":checked") ? true : false;
    jQuery('input[name="import_single[]"]').each(function() {
      jQuery(this).attr('checked', status);
    });
  });

  // activate save_products button
  jQuery("#save_products").click(function(e) {
    e.preventDefault();
    save_products();
  });

  // activate pagination links
  jQuery(".product_page_link").click(function(e) {
    
    e.preventDefault(); 
    
    var method = jQuery(this).attr('data-method');
    
    if(method == 'keyword') {
      submit_form = jQuery('#keyword_form');
    } else {
      submit_form = jQuery('#vertical_form');
    }
    import_products(submit_form, method, jQuery(this));
  });
  
  // displays a list of subcategories when a category is selected
  jQuery("#search_categories").change(function(e) {
      e.preventDefault();
      import_products(jQuery(this), 'category', null);
  });  
  
  // displays a list of products when a subcategory is selected
  jQuery("#search_subcategories").change(function(e) {
      e.preventDefault();
      import_products(jQuery(this), 'subcategory', null);
  });  
}

function save_products() {

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
