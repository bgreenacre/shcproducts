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
 * Installer controller.
 *
 * @package		shcproducts
 * @category	Controller
 * @subpackage  Installer
 * @version		0.1
 * @author		Kyla Klein
 */

class Controller_Installer {

    public function __construct()
    {
        register_activation_hook(SHCP_PATH.'/shcproducts.php', array(&$this, 'action_install'));
        register_deactivation_hook(SHCP_PATH.'/shcproducts.php', array(&$this, 'action_uninstall'));
    }

    public function action_install()
    {
        add_option(SHCP::prefix('options'), array());

        $pages = array();
        $page = new Model_Shcp();

        $page->post_type = 'page';
        $page->post_title = 'Products';
        $page->post_status = 'publish';
        $page->post_content = '[shcp_products]';

        if ($page->check())
        {
            $page->save();
            $pages[] = $page->ID;
        }

        $page = new Model_Shcp();

        $page->post_type = 'page';
        $page->post_title = 'Product';
        $page->post_status = 'publish';
        $page->post_content = '[shcp_product]';

        if ($page->check())
        {
            $page->save();
            $pages[] = $page->ID;
        }

        $page = new Model_Shcp();

        $page->post_type = 'page';
        $page->post_title = 'Cart';
        $page->post_status = 'publish';
        $page->post_content = '[shcp_cart]';

        if ($page->check())
        {
            $page->save();
            $pages[] = $page->ID;
        }

        SHCP::set_option('pages', $pages);
        unset($pages);
        unset($page);
    }

    public function action_uninstall()
    {
        foreach ( (array) SHCP::get_option('pages') as $page)
        {
            wp_delete_post($page, TRUE);
        }

        delete_option(SHCP::prefix('options'), array());
    }

}
