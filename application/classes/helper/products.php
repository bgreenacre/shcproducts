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
 *
 * Helper for product API work.
 *
 * @package		shcproducts
 * @subpackage	Helper
 * @since		0.1
 * @author		Brian Greenacre
 */
class Helper_Products {

    public static function image($image, array $attrs = array())
    {
        $attrs['height'] = SHCP::get($attrs, 'height', 100);
        $attrs['width'] = SHCP::get($attrs, 'width', 100);
        $attrs_str = '';
        
        $image = urldecode($image);

        if (strpos($image, 'http//') === FALSE)
        {
            $image = 'http://s.shld.net/is/image/Sears/'.$image
                .'?hei='.$attrs['height'].'&wid='.$attrs['width'];
        }
        else 
        { 
          $image = substr($image, (strpos($image, 'src=') + 4));
        }

        $attrs['src'] = $image;

        foreach ($attrs as $key => $value)
        {
            $attrs_str .= $key . '='
                .htmlspecialchars( (string) $value, ENT_QUOTES)
                .' ';
        }

        $attrs_str = ' ' . rtrim($attrs_str, ' ');

        return '<img'.$attrs_str.' />';
    }

}
