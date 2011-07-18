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

    public static function image($image, array $attrs = array(), $disable_url_dimensions = FALSE)
    {
        $attrs['height'] = SHCP::get($attrs, 'height', 100);
        $attrs['width'] = SHCP::get($attrs, 'width', 100);
        $attrs_str = '';
        
        $image = urldecode($image);

        if (strpos($image, 'http//') !== FALSE)
        {
            $parts = parse_url($image);
            
            if ($qs = SHCP::get($parts, 'query'))
            {
                // for marketplace products, which are not available at the above url
                // these appear to be normally in the form of the following quite long string: 
                //
                //    http//c.shld.net/rpx/i/s/pi/mp/8241/2385011303p?src=            --> this part gets removed
                //    http://www.pokkadots.com/media/catalog/product/f/l/fl-bp_1.jpg  --> this is the real image
                //    &d=787672ad510db48c19b0fcf012e4717c163efa20                     --> this part gets removed
                $image = substr($image, (strpos($image, 'src=') + 4));
                $image = substr($image, 0, strpos($image, '&d='));
            }
            else
            {
                $image = preg_replace('/[^0-9_-]+/', '', $image);
            }
        }
        
        if (strpos($image, 'http://') === FALSE)
        {
            $image = 'http://s.shld.net/is/image/Sears/'.$image;
            
            if ($disable_url_dimensions === FALSE)
                $image .= '?hei='.$attrs['height'].'&wid='.$attrs['width'];
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
