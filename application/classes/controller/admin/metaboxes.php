<?php defined('SHCP_PATH') OR die('No direct script access.');
/**
 * Sears Holding Company Products Wordpress plugin.
 *
 * Provides the ability to import products via the Sears API and storing in
 * wordpress as custom post type.
 *
 * @author Brian Greenacre and Kyla Klein
 * @package shcproducts
 * @email bgreenacre42@gmail.com
 * @version $Id$
 * @since Wed 15 Jun 2011 07:32:09 PM
 */

// -----------------------------------------------------------------------------

/**
 * Product import controller.
 *
 * @package     shcproducts
 * @subpackage  Controller
 * @since       0.1
 * @author      Brian Greenacre
 */
class Controller_Admin_Metaboxes {

    /**
     * __construct - Setup the actions used by this controller.
     *
     * @return void
     */
    public function __construct()
    {
        add_action('add_meta_boxes', array(&$this, 'metaboxes'));
        add_action('save_post', array(&$this, 'save'));
    }

    /**
     * metabox - Adds the large metabox to the post edit form.
     *
     * Edited by Eddie Moya to include the metabox in pages and products.
     * 
     * @return void
     */
    public function metaboxes()
    {
        $metaboxes = apply_filters('magic_metaboxes', array());
        foreach($metaboxes as $metabox){
            add_meta_box($metabox['metabox_slug'], $metabox['metabox_title'], array(&$this, 'metabox_fields'), $metabox['post_type'], 'side', 'low', $metabox['fields'] );
        }
    }

    /**
     * action_list - Display available products and current related products.
     *
     * @param object $post = NULL
     * @return void
     */
    public function metabox_fields($post, $fields)
    {

      foreach((array)$fields['args'] as $field){

          ?><p>
            <label for="<?php echo $field['id']; ?>">
                <?php echo $field['label']; ?>
            </label>
            <input type="<?php echo $field['type']; ?>" id="<?php echo $field['id']; ?>" name="<?php echo $field['id']; ?>" value="<?php echo get_post_meta($post->ID, $field['id'], true); ?>" size="25"/>
            </p>
          <?php
      }
      
    }
    
    function save( $post_id ) {

      if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
          return;

      if ( 'shcproduct' == $_REQUEST['post_type'] ) 
      {
        if ( !current_user_can( 'edit_shcproduct', $post_id ) )
            return;
      }
      else
      {
        if ( !current_user_can( 'edit_shcproduct', $post_id ) )
            return;
      }
      //print_pre($_POST);

      $metaboxes = apply_filters('magic_metaboxes', array());
      
      foreach((array) $metaboxes as $metabox){
          foreach((array) $metabox['fields'] as $field){
          
          $value = $_POST[$field['id']];
          update_post_meta($post_id, $field['id'], $value); 
          }
          
      }
    }


}


function get_magic_metabox_data($metabox_name, $post_id){
   
    $metaboxes = apply_filters('magic_metaboxes', array());
    
    foreach($metaboxes[$metabox_name]['fields'] as $field){
        
        $data[$field['id']] = get_post_meta($post_id, $field['id'], true);
    }
    
    return $data;
    
}