<div class="wrap">
  <h2>Import Products</h2>
  <p>To see products select a search method, enter the search terms and click the Search button.</p>
  <form action="" method="post">
    <div class="shcp_form_labels">
      <label for="search_method">Search Method: </label>
    </div>
    <div class="shcp_form_fields">
      <input type="radio" class="radio" name="search_method" id="search_method_keyword" value="keyword" />
      <label for="search_method_keyword">Keyword</label>
    
      <input type="radio" class="radio" name="search_method" id="search_method_vertical" value="vertical" />
      <label for="search_method_vertical">Vertical</label>
    
      <input type="radio" class="radio" name="search_method" id="search_method_category" value="category" />
      <label for="search_method_category">Category</label>
      
      <div class="subcategory_option">
        <label for="search_term_subcategory">Subcategory (optional): </label>
        <input type="text" name="search_term_subcategory" id="search_term_subcategory" value="Enter Subcategory" />
      </div>
    </div>
    <div class="shcp_form_labels">
      <label for="search_terms">Search Term(s): </label>
    </div>
    <div class="shcp_form_fields">
      <input type="text" name="search_terms" id="search_terms" value="Enter Search Term(s)" />
      <input type="submit" name="submit" id="submit" value="Search" />
    </div>
  </form>  
  <div id="shcp_import_list"></div>  
</div>