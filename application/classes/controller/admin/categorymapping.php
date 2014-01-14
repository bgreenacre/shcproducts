<?php

/**
* Contiains functionality for the admin page needed to map Gear categories to SHC categories.
*/

class Controller_Admin_Categorymapping {

	 /**
     * __construct 
     * 
     * @param array $params 
     * @access public
     * @return void
     */
    public function __construct(array $params = NULL)
    {
        add_action('admin_menu', array(&$this, 'admin_init'));
    }


    /**
     * action_index - Call first view file (with nothing loaded)
     *
     * @access  public
     * @return  void
     */
    public function action_index()
    {
		if(isset($_GET['view']) && $_GET['view'] == 'all') {
			echo SHCP::view('admin/category_mapping/category_mapping_listall');
		} else {
			echo SHCP::view('admin/category_mapping/category_mapping_index');
		}
    }

    /**
     * admin_init - Init page
     *
     * @access  public
     * @return  void
     */
    public function admin_init()
    {
        add_submenu_page( 'edit.php?post_type=shcproduct', __('Category Mapping'), __('Category Mapping'), 'edit_posts', 'categorymapping', array(&$this, 'action_index'));
    }
    
    
}