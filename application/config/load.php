<?php defined('SHCP_PATH') OR die('No direct script access.');
/**
 * shcproducts
 *
 * @author Brian Greenacre and Kyla Klein
 * @email bgreenacre42@gmail.com
 * @version $Id$
 * @since Wed 15 Jun 2011 07:32:09 PM
 */

return array(
    'functions'     => array('register_post_types', 'register_taxonomy'),
    'controllers'   => array(
        'app', 'installer', 'admin_options', 'admin_import', 'admin_related',
        'front_products', 'front_cart', 'front_thematic',
        ),
);
