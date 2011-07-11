jQuery(document).ready(function($) {
    // default import form js
    init_import_form();

    // displays products from the api via ajax when form is submitted
    $("#submit").click(function(e) {
        e.preventDefault();
        import_products(jQuery(this));
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

  var product_count = el.attr('data-product-count');
  var page_number   = el.attr('data-page-number');
  var method        = jQuery("input[name='search_method']").filter(":checked").val();
  var subcategory   = jQuery("#search_term_subcategory").val();
  var terms         = jQuery("#search_terms").val();

  console.log("import products - " + method);

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
    import_products(jQuery(this));
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
