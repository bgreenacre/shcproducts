<?php defined('SHCP_PATH') OR die('No direct script access.');
/**
 * shcproducts
 *
 * @author Brian Greenacre and Kyla Klein
 * @email bgreenacre42@gmail.com
 * @version $Id$
 * @since Wed 15 Jun 2011 07:32:09 PM
 */

// ----------------------------------------------------------------------------

/**
 * Controller_Widget_Related
 *
 */
class Controller_Widget_Products extends Controller_Widget {

    /**
     * @access  protected
     * @var string
     */
    protected $content = 'widget/products/list';

    public function form($values = NULL)
    {
        $data = array(
            'values'    => $values,
        );

        echo SHCP::view('widget/products/form', $data);
    }

}
