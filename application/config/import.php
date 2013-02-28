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
 * @since Fri Sep 30, 2011
 */

// -----------------------------------------------------------------------------

return array(
    'field_map' => array(
        'partnumber'    => 'Part Number',
        'category'      => 'Post Category',
        'publish_date'  => 'Post Publish Date',
        'post_status'   => 'Post Status',
        'new_custom_field'  => 'New Custom Field',
    ),
);
