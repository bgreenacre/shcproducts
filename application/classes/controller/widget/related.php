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
class Controller_Widget_Related extends Controller_Widget {

    /**
     * @access  protected
     * @var string
     */
    protected $content = 'widget/related/list';

    public function form($values = NULL)
    {
        $data = array(
            'values'    => $values,
        );

        echo SHCP::view('widget/related/form', $data);
    }

}
