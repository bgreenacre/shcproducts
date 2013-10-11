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
 * Model_SHCP
 *
 * @package     shcproducts
 * @subpackage  Model
 * @since       0.1
 * @author      Brian Greenacre
 */
class Model_Products extends Model_SHCP {

    /**
     * _detail 
     * 
     * @var mixed
     * @access protected
     */
    protected $_detail;
    
    /**
     * is_updated - was product updated?
     * 
     * @var bool
     * @access public
     */
   public $is_updated = false;
    
    /**
     * is_deleted -  was product deleted?
     * 
     * @var bool
     * @access public
     */
    public $is_deleted = false;
    
    
    /**
     * is_draft -  was product set to draft?
     * 
     * @var bool
     * @access public
     */
    public $is_draft = false;
    
    /**
     * no_action -  was no action taken on this product?
     * 
     * @var bool
     * @access public
     */
    public $no_action = false;
    
    /**
     * cron_msg -  hold data relating to cron outcome
     * 
     * @var mixed
     * @access public
     */
    public $cron_msg;
    
    /**
     * profile_mode - Used during CRON update. If true,
     * no updates/deletes will be performed to Products.
     * 
     * @var bool
     */
    public $profile_mode = false;

    /**
     * __construct 
     * 
     * @param mixed $id save
     * @access public
     * @return void
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        $this->param('post_type', 'shcproduct');
        
    }

    /**
     * __get - Magic method to make accessing custom post fields easier.
     *
     *  //Example of getting a custom field "partNumber"
     *  $post = new Model_Product()->param('p', 22)->load();
     *  $post->ID; // the post id.
     *  $post->partNumber; // look up the custom field value for the post.
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        $value = parent::__get($key);

        if ($key === 'detail' AND is_string($value))
        {
            $value = unserialize($value);
            
            if (is_object($this->current()))
            {
                $this->current()->detail = $value;
            }
        }
        elseif (isset($this->detail) && is_object($this->detail) AND isset($this->detail->current()->{$key}) === TRUE)
        {
            $value = $this->detail->current()->{$key};
        }

        return $value;
    }

    /**
     * related 
     * 
     * @param mixed $id 
     * @access public
     * @return void
     */
    public function related($id)
    {
        $ids = (array) get_post_meta($id, 'shcp_related_products', TRUE);

        if ($ids)
        {
            $this->param('post__in', $ids);
        }
        else
        {
            $this->param('p', -1);
        }

        return $this;
    }

    /**
     * save 
     * 
     * @access public
     * @return void
     */
    public function save()
    {
        $this->post_type = $this->param('post_type');
        $this->post_status = 'publish';

        return parent::save();
    }
    
    
    /**
    	Providing this as an optional, product-specific alternative to parent::save()
    	Useful for when you just want to update an existing product.
    	No possibility of creating new products or performing any other voodoo.
    	- Assumes a WordPress post to already exist ($post->ID)
    	- See $this->sync_from_api() for examples of $post and $post_meta getting set
    	- Use with caution, etc.
    */
    public function update_product($post, $post_meta) {
        
        // Update the post:
        $outcome = wp_update_post($post, true);
        
        // Update the post meta:
        foreach ($post_meta as $key => $value) {
			update_post_meta($this->ID, $key, $value);
		}
				
		if(!is_numeric($outcome) || $outcome == 0) {
			return false;
		} else {
			return true;
		}
    }
    
    
    /**
    	A sanity check on the post and post_meta data for a product.
    	Recommend to use this before submitting to $this->update_product()
    */
    public function check_product($post, $post_meta) {
    	// If any of the below fields are empty, we reject the whole thing.
    	// (Requirements will likely be refined over time, etc.)

		// Necessary for WP to update the post:
    	if(empty($post['ID']) || !is_numeric($post['ID']) ) return false;
    	if(empty($post['post_type'])) return false; 
    	// Require title, image, and price:
    	if(empty($post['post_title']) || $post['post_title'] == ' ') return false; 
    	if(empty($post_meta['imageid'])) return false;
    	if(empty($post_meta['displayprice']) && empty($post_meta['cutprice'])) return false;
    	return true;
    }

    /**
     * get_catentryid - This method tries to use the item vs product level rules
     * of the Sears API. _rules_ is a loose term as this particular way of
     * prioritizing entry ids is not well documented. Essentially this method
     * will first check to see if there are ids within the skulist property (the
     * item level) and use the first id in that array. If it does not exist then
     * the catentryid within the detail object is used (product level).
     * If either of those properties does not exist then use the property saved
     * for the post.
     *
     * @access  public
     * @return  string
     */
    public function get_catentryid()
    {
        if (is_object($this->detail))
        {
            if ($this->detail->skulist)
            {
                return $this->detail->skulist->sku[1][0]->catentryid;
            }
            else
            {
                return $this->detail->catentryid;
            }
        }
        else
        {
            return $this->catentryid;
        }
    }

    public function sync_from_api($profile_mode = false)
    {
        if ( ! $this->loaded())
        {
            return $this;
        }
        
		if( strlen( $this->partnumber ) > 0 ) {

			$search = Library_Sears_Api::factory('product')
					->cache(FALSE)
					->get($this->partnumber)
					->param('showSpec', 'true')
					->load();

			if ($search->success())
			{
				$search_detail = $search->get_product_details();
				if(is_array($search_detail)) {
					$search_detail = array_pop($search_detail);
				}
				
				$status_data = null;
				if( isset($search->productdetail->statusdata) ) {
					$status_data = $search->productdetail->statusdata;
				}
				if(empty($status_data) && isset($search_detail->statusdata)) {
					$status_data = $search_detail->statusdata;
				}
				
				// Possible response codes:
				//		0 = Success
				//		1 = Product no longer available
				//	For further details, see: http://developer.sears.com/documentation/product-details-v21-api
				//	Other, secret response codes (shh, don't tell the documentation):
				//		2 = Time out occurred while processing
				if( !empty($status_data) && isset($status_data->responsecode) && $status_data->responsecode == 0 )
				{
				
					$this->post_title = $search_detail->descriptionname;
					if(!empty($search_detail->brandname) && strpos($search_detail->descriptionname, $search_detail->brandname) === false) {
						$this->post_title = $search_detail->brandname.' '.$this->post_title;
					}
			
					// Prepare to save post:
					 $post = array(
						'ID' => $this->ID,
						'post_type' => $this->param('post_type'),
						'post_status' => 'publish',
						'post_title' => $this->post_title,
						'post_modified' => date('Y-m-d H:i:s')
					);
					// Prepare to save post meta:
					$post_meta = array(
						'imageid' => $search_detail->imageurls->imageurl[1][0],
						'numreview' => $search->numreview,
						'catentryid' => $search->catentryid,
						'rating' => $search->rating,
						'displayprice' => $search->regularprice,
						'cutprice' => $search->saleprice,
						'detail' => serialize($search)
					);
													
					 if(!$this->check_product($post, $post_meta) ) {
						// If anything went wrong in the sanity check,
						// set the product to draft.
						$this->cron_msg = 'POST ID #'.$this->ID.' - DRAFT - Part Number '.$this->partnumber.' ('.$this->post_title.') - Data did not pass our sanity check. API URL: '.$search->url();
						if(! $profile_mode) {
							if($this->draft()) {
								$this->is_draft = true;
							 } else {
								global $wpdb;
								$this->cron_msg = 'POST ID #'.$this->ID.' - NO ACTION - Part Number '.$this->partnumber.' ('.$this->post_title.') - An error occurred while trying to set post to DRAFT. ['.print_r($wpdb->last_error,true).']';
								$this->no_action = true;
							 }
						} else {
							$this->cron_msg = '*** profile_mode *** '.$this->cron_msg;
						}
						
					 } else {
						
						if(! $profile_mode) {
							if($this->update_product($post, $post_meta)) {
								$this->is_updated = true;
								$this->cron_msg = 'POST ID #'.$this->ID.' - UPDATE - Part Number '.$this->partnumber.' ('.$this->post_title.') - Successfully updated.';
							} else {
								global $wpdb;
								$this->cron_msg = 'POST ID #'.$this->ID.' - NO ACTION - Part Number '.$this->partnumber.' - An error occurred while trying to UPDATE the post. ['.print_r($wpdb->last_error,true).']';
								$this->no_action = true;
							}
						} else {
							$this->is_updated = true;
							$this->cron_msg = '*** profile_mode *** POST ID #'.$this->ID.' - UPDATE - Part Number '.$this->partnumber.' ('.$this->post_title.') - Successfully updated';
						}
					 }
					
				}
				else if( !empty($status_data) && isset($status_data->responsecode) && $status_data->responsecode == 1 )
				{	// Product is no longer available:
					$this->is_deleted = true;
									
					$this->cron_msg = 'POST ID #'.$this->ID.' - DELETE - Part Number '.$this->partnumber.' ('.$this->post_title.') - No longer available. API URL: '.$search->url();
				
					if(! $profile_mode) {					
						if($this->really_delete_product()) {
							 $this->is_deleted = true;
						} else {
							global $wpdb;
							$this->cron_msg = 'POST ID #'.$this->ID.' - NO ACTION - Part Number '.$this->partnumber.' - An error occurred while trying to DELETE the post. ['.print_r($wpdb->last_error,true).']';
							$this->no_action = true;
						}
					} else {
						$this->cron_msg = '*** profile_mode *** '.$this->cron_msg;
					}
				 
				} else {
					// For some reason, the status data is not as we expected.
					// Don't do anything and log the data for future debugging.
					
					$this->no_action = true;
				
					$this->cron_msg = 'POST ID #'.$this->ID.' - NO ACTION - Part Number '.$this->partnumber.' ('.$this->post_title.') - An unexpected status was encountered. ['.print_r($status_data,true).'] API URL: '.$search->url();
				}
			}
			else
			{
				
							
				$this->cron_msg = 'POST ID #'.$this->ID.' - DRAFT - Part Number '.$this->partnumber.' ('.$this->post_title.') - Failed to look up product. API URL: '.$search->url();
			
				if(! $profile_mode) {
					 if($this->draft()) {
					 	$this->is_draft = true;
					 } else {
					 	global $wpdb;
						$this->cron_msg = 'POST ID #'.$this->ID.' - NO ACTION - Part Number '.$this->partnumber.' ('.$this->post_title.') - An error occurred while trying to set post to DRAFT. ['.print_r($wpdb->last_error,true).']';
					 	$this->no_action = true;
					 }
				} else {
					$this->cron_msg = '*** profile_mode *** '.$this->cron_msg;
				}
			}
        
        } else {
        	// The product is missing a part number in our database.
        	// Something has gone badly wrong at some point;
        	// let's get rid of it instead of keeping it around as a draft.
        	
        	$this->cron_msg = 'POST ID #'.$this->ID.' - DELETE - ('.$this->post_title.') Part Number post meta value was missing.';
                	
        	
					
			if(! $profile_mode) {
				 if($this->really_delete_product()) {
					 $this->is_deleted = true;
				 } else {
				 	global $wpdb;
					$this->cron_msg = 'POST ID #'.$this->ID.' - NO ACTION - Part Number '.$this->partnumber.' - An error occurred while trying to DELETE the post. ['.print_r($wpdb->last_error,true).']';
					$this->no_action = true;
				 }
				 
			} else {
				$this->cron_msg = '*** profile_mode *** '.$this->cron_msg;
			}
        }

        return $this;
    }
    
    // Be careful! This will really delete the product!
    public function really_delete_product() {
    	$outcome = wp_delete_post( $this->ID, TRUE);
    	
    	if($outcome === false) {
    		return false;
    	} else {
    		return true;
    	}
    }
    
    

    public function create_shc_url($isSears=false)
    {
        $baseUrl = ($isSears === true) ? 'http://www.sears.com/shc/s/p_' : 'http://www.kmart.com/shc/s/p_';

        $productCatalog = $this->detail->catalogid;
        $productId = $this->detail->partnumber;
        $productStoreId = $this->storeid;
        $url = $baseUrl.$productStoreId.'_'.$productCatalog.'_'.$productId;

        return $url;
    }
}
