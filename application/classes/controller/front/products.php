<?php defined('SHCP_PATH') OR die('No direct script access.');
/**
 * Sears and Kmart product plugin.
 * Product display controller. This provides generic templates to be used
 * in theme template files.
 *
 *  // Example of template usage
 *  if (defined('SHCP_PATH'))
 *  {
 *      Controller::factory('front_products')->action_grid();
 *  }
 *
 * @package		shcproducts
 * @subpackage	Controller
 * @version		0.1
 * @author		Brian Greenacre
 */

class Controller_Front_Products {

    public function __construct()
    {
    }

    public function action_grid()
    {
        $data = array(
        );

        echo SHCP::view('front/product/grid', $data);
    }

    public function action_detail()
    {
        $data = array();

        echo SHCP::view('front/product/detail', $data);
    }

    public function action_quickview()
    {
        $data = array();

        echo SHCP::view('front/product/quickview', $data);
    }

}
