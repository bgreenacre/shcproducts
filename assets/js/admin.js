jQuery(document).ready(function($) {

    // displays products from the api via ajax when form is submitted
    $("#submit_keyword").click(function(e) {
        e.preventDefault();
        $('#shcp_categories').html('');
        $('#shcp_subcategories').html('');
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

    // attach loading div functionality
    $("#ajax_loading").bind("ajaxSend", function(){
        $('#ajax_loading_overlay').show();
        $(this).show();
    })
    .bind("ajaxComplete", function(){
        $('#ajax_loading_overlay').hide();
        $(this).hide();
    });
    
    // remove default search text on focus, put it back on blur
    $('.search_terms').live({
        focus: function() {
            if($(this).val() == 'Enter search terms') {
                $(this).val('');
            }
        },
        blur: function() {
            if($(this).val() == '') {
                $(this).val('Enter search terms');
            }
        }
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
  jQuery("#import_all").unbind('change').change(function() {
    var status = jQuery(this).is(":checked") ? true : false;
    jQuery('input[name="import_single[]"]').each(function() {
      jQuery(this).attr('checked', status);
    });
  });
  
    // activate import all products button
  jQuery('#save_all_products').click(function(e) {
      e.preventDefault();
      save_all_products(jQuery(this));
  });  

  // activate save_products button
  jQuery("#save_products").unbind('click').click(function(e) {
    e.preventDefault();
    save_products();
  });

  // activate pagination links
  jQuery(".product_page_link").unbind('click').click(function(e) {

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
  jQuery("#search_categories").unbind('change').change(function(e) {
      e.preventDefault();
      import_products(jQuery(this), 'category', null);
  });

  // displays a list of products when a subcategory is selected
  jQuery("#search_subcategories").unbind('change').change(function(e) {
      e.preventDefault();
      import_products(jQuery(this), 'subcategory', null);
  });
}

function save_products() {

 var import_table = jQuery("#shcp_import_table");
 var items = [];
 var data;
 
 data = jQuery('#shcp_category').serialize();
 data += '&action=action_save';
 
 import_table.find('tbody tr').each(function(index) {
    if(jQuery(this).find("input[name='import_single[]']").is(":checked")) {
      items.push(index);
      // don't send entire form, only values from those rows which are selected
      data += "&" + jQuery('#row_' + index).find('input').serialize();
    }
  });

  jQuery.ajax({
    type: 'post',
    dataType: 'json',
    url: shcp_ajax.ajaxurl,
    data: data,
    success: function(response) {
      for(var i in items) {
        row = jQuery("#row_" + items[i]);
        var show_error = false;
        var show_error_text = '';
        
        var partnumber = row.find('input[name="partnumber[]"]').val();
        
        if(response) {
            jQuery(response.errors).each(function() {
              if(typeof(this.detail) != 'undefined' && this.detail.partnumber == partnumber) {
                show_error_text = this.detail.empty;
                show_error = true;
              }
            });
        }
        
        if(show_error == true) {
          row.css({ background : '#FCCFD4' }).addClass('disable');
          row.find('.partnumber').text(show_error_text);
        } else {
          row.css({ background : '#f1f1f1' }).addClass('disable');
          row.find('.partnumber').text("imported");
        }
        row.find('input[name="is_featured"]').remove();
        row.find('input[name="is_hidden"]').remove();
        row.find('input[name="import_single[]"]').remove();
        row.find('.cutprice').text("");
        row.find('.displayprice').text("");
      }
      
      import_callback(this);
    },
    error:function (xhr, ajaxOptions, thrownError){
        alert(xhr.status);
        alert(thrownError);
    }
  });
}

function save_all_products(el) {

  var product_count = el != null ? el.attr('data-product-count') : 0;
  var method        = el != null ? el.attr('data-method') : 0;
  
  var keyword_terms     = jQuery("#search_terms_keyword").val();
  var vertical_terms    = jQuery("#search_terms_vertical").val();
  var category_terms    = jQuery("#search_categories option:selected").val();
  var subcategory_terms = jQuery("#search_subcategories option:selected").val();
  var assigned_category = jQuery('#shcp_import_form option:selected').val();

  keyword_terms         = keyword_terms != "Enter keywords" ? keyword_terms : '';
  vertical_terms        = vertical_terms != "Enter vertical name" ? vertical_terms : '';
  category_terms        = category_terms != "Choose Category" ? category_terms : '';
  subcategory_terms     = subcategory_terms != "Choose Subategory" ? subcategory_terms : '';
  
  data = {
    "action"            : 'action_save_all',
    "method"            : method,
    "product_count"     : product_count,
    "keyword_terms"     : keyword_terms,
    "vertical_terms"    : vertical_terms,
    "category_terms"    : category_terms,
    "subcategory_terms" : subcategory_terms,
    "assigned_category" : assigned_category
  };
    
  jQuery.ajax({
    type: 'post',
    url: shcp_ajax.ajaxurl,
    data: data,
    dataType: 'json',
    success: function(response) {
      var response_text = '';
      if(response) {
          jQuery(response.errors).each(function() {
            if(typeof(this.detail) != 'undefined') {
              response_text += '<p class="error"><strong>' + this.detail.partnumber + '</strong>' + this.detail.empty + '</p>';
            }
            if(typeof(this.post_title) != 'undefined') {
                response_text += '<p class="error"><strong>' + this.post_title.partnumber + '</strong>' + this.post_title.empty + '</p>';
            }
          });      
      }
      if(response.errors == null) {
          response_text += '<p>All products imported</p>';
      }
      jQuery('#shcp_import_list').html(response_text);
      import_callback(this);
    },
    error:function (xhr, ajaxOptions, thrownError){
        alert(xhr.status);
        alert(thrownError);
    }
  });
}
