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
 * Sears and Kmart product plugin.
 * Product display controller. This provides generic templates to be used
 * in theme template files.
 *
 * @package		shcproducts
 * @subpackage	Controller
 * @since		0.1
 * @author		Brian Greenacre
 */
class Controller_Front_Thematic {

    public $product;

    public function __construct()
    {
        add_filter('thematic_postheader_posttitle', array(&$this, 'filter_postheader_posttitle'));
        add_filter('thematic_postheader_postmeta', array(&$this, 'filter_postheader_postmeta'));
        add_filter('thematic_content', array(&$this, 'filter_content'));
        add_filter('post_class', array(&$this, 'filter_post_class'));
    }

    public function filter_postheader_posttitle($title)
    {
        if ($this->product())
        {
            $data = array(
                'product'   => $this->product,
            );

            $title = SHCP::view('thematic/postheader/posttitle', $data);
        }

        return $title;
    }

    public function filter_postheader_postmeta($meta)
    {
        if ($this->product())
        {
            $data = array(
                'product'   => $this->product,
            );

            $title = SHCP::view('thematic/postheader/postmeta', $data);
        }

        return $title;
    }

    public function filter_content($content)
    {
        return $content;
    }

    public function filter_post_class($class)
    {
        return $class;
    }

    protected function product()
    {
        global $post;

        if ($post->post_type == 'shcproduct')
        {
            $this->product = new Model_Products($post);
            return TRUE;
        }

        return FALSE;
    }

}
