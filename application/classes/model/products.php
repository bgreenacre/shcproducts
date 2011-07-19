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
 * @package		shcproducts
 * @subpackage	Model
 * @since		0.1
 * @author		Brian Greenacre
 */
class Model_Products extends Model_SHCP {

    protected $_detail;

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

        if ($key === 'detail')
        {
            $value = unserialize($value);
        }

        return $value;
    }

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

    public function save()
    {
        $this->post_type = $this->param('post_type');
        $this->post_status = 'publish';

        return parent::save();
    }

}
