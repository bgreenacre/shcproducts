<div class="wrap shcp_wrap">
  <h2>Import Products</h2> 
  <p>To see products select a search method, enter the search terms and click the Search button.</p>
  <form action="" id="keyword_form" method="post">
    <div class="shcp_form_labels">
      <label for="search_terms">Keyword Search: </label>
    </div>
    <div class="shcp_form_fields">
      <input type="text" name="search_terms" class="search_terms" id="search_terms_keyword" value="Enter search terms" />
      <input type="submit" name="submit_keyword" id="submit_keyword" value="Search" />
    </div>
  </form>
  <form action="" id="vertical_form" method="post">  
    <div class="shcp_form_labels">
      <label for="search_terms">Vertical Search: </label>
    </div>
    <div class="shcp_form_fields">
      <input type="text" name="search_terms" class="search_terms" id="search_terms_vertical" value="Enter search terms" />
      <input type="submit" name="submit_vertical" id="submit_vertical" value="Search" />
    </div>
    <div class="shcp_form_fields">
      <div id="shcp_categories"></div>
      <div id="shcp_subcategories"></div>
    </div>  
  </form>  
  <div id="shcp_import_list"></div>
  <div id="ajax_loading_overlay">
    <div id="ajax_loading"></div>
  </div>
</div>