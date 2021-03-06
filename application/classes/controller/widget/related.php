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
 * Controller_Widget_Related
 *
 */
class Controller_Widget_Related extends SHCP_Controller_Widget {

    /**
     * @access  protected
     * @var string
     */
    protected $content = 'widget/related/list';

    /**
     * form 
     * 
     * @param mixed $values 
     * @access public
     * @return void
     */
    public function form($values = NULL)
    {
        $data = array(
            'ids'       => array(
                'title'     => $this->get_field_id('title'),
            ),
            'names'       => array(
                'title'     => $this->get_field_name('title'),
            ),
            'values'    => array_merge(SHCP::config('widget/related.options'), $values),
        );

        echo SHCP::view('widget/related/form', $data);
    }

    /**
     * update 
     * 
     * @param mixed $new 
     * @param mixed $old 
     * @access public
     * @return void
     */
    public function update($new, $old)
    {
        $data = array_merge($old, $new);
        $data['title'] = strip_tags($data['title']);
        return $data;
    }

}
