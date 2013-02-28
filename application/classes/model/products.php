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
        elseif (is_object($this->detail) AND isset($this->detail->current()->{$key}) === TRUE)
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

        $search = Library_Sears_Api::factory('search')
            ->cache(FALSE)
            ->keyword($this->partnumber)
            ->load();

        if ($search->success())
        {
            $search = $search->current();
            $this->post_title = $search->name;
            $this->imageid = $search->imageid;
            $this->numreview = $search->numreview;
            $this->catentryid = $search->catentryid;
            $this->rating = $search->rating;
            $this->displayprice = $search->displayprice;
            $this->cutprice = $search->cutprice;

            $detail = Library_Sears_Api::factory('product')
                ->cache(FALSE)
                ->get($this->partnumber)
                ->param('showSpec', 'true')
                ->load();

            if ($detail->success())
            {
                $this->detail = serialize($detail);
            }
            else
            {
            	$this->is_deleted = true;
            	
            	if(! $profile_mode) {
            		
            		 $this->delete();
            	}
              	 
            }

            if ($this->check())
            {
            	$this->is_updated = true;
            	
            	if(! $profile_mode) {
            		
               		 $this->save();
            	}
            }
        }
        else
        {
        	$this->is_deleted = true;
        	
        	if(! $profile_mode) {
        		
           		 $this->trash();
        	}
        }

        return $this;
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
