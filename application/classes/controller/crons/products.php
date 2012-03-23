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

class Controller_Crons_Products {

    public function action_update()
    {
            $posts = new Model_Products();
            $posts->limit(-1);

        foreach ($posts as $post)
        {
            $post = new Model_Products($post->ID);
            $post->sync_from_api();
            unset($post);
        }
    }

}
