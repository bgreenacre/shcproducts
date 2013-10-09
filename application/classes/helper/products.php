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
 * @package     shcproducts
 * @subpackage  Helper
 * @since       0.1
 * @author      Brian Greenacre
 */
class Helper_Products {

    /**
     * image 
     * 
     * @param mixed $image 
     * @param array $attrs 
     * @param mixed $disable_url_dimensions 
     * @static
     * @access public
     * @return void
     */
    public static function image($image, array $attrs = array(), $disable_url_dimensions = FALSE)
    {
    	if(empty($image)) return 'No Image';
    
        $attrs['height']  = SHCP::get($attrs, 'height', 140);
        $attrs['width']   = SHCP::get($attrs, 'width', 140);
        $attrs['alt']     = SHCP::get($attrs, 'alt', '');
        $attrs_str        = '';

        $attrs['src'] = self::image_url($image, $attrs['height'], $attrs['width'], $disable_url_dimensions);

        foreach ($attrs as $key => $value)
        {
            $attrs_str .= $key . '="'
                .htmlspecialchars( (string) $value, ENT_QUOTES)
                .'" ';
        }

        $attrs_str = ' ' . rtrim($attrs_str, ' ');

        return '<img'.$attrs_str.' />';
    }

    /**
     * image_url 
     * 
     * @param mixed $image 
     * @param mixed $height 
     * @param mixed $width 
     * @param mixed $disable_url_dimensions 
     * @static
     * @access public
     * @return void
     */
    public static function image_url($image, $height, $width, $disable_url_dimensions = FALSE)
    {
        // $image = urldecode($image);
        // 
        // if(strpos($image, 'http//') !== FALSE || strpos($image, 'http://') !== FALSE)
        // {
        //     $parts = parse_url($image);
        // 
        //     if ($qs = SHCP::get($parts, 'query'))
        //     {
        //         // for marketplace products, which are not available at the above url
        //         // these appear to be normally in the form of the following quite long string: 
        //         //
        //         //    http//c.shld.net/rpx/i/s/pi/mp/8241/2385011303p?src=            --> this part gets removed
        //         //    http://www.pokkadots.com/media/catalog/product/f/l/fl-bp_1.jpg  --> this is the real image
        //         //    &d=787672ad510db48c19b0fcf012e4717c163efa20                     --> this part gets removed
        //         $image = substr($image, (strpos($image, 'src=') + 4));
        //         $image = substr($image, 0, strpos($image, '&d='));
        //     }
        //     else
        //     {
        //         $pos = (int) strrpos($image, '/');
        //         $image = substr($image, $pos+1);
        //     }
        // }
        // 
        // if (strpos($image, 'http://') === FALSE)
        // {
        //     $image = 'http://c.shld.net/rpx/i/s/i/spin/image/' . $image;
        //  
        //     if ($disable_url_dimensions === FALSE)
        //         $image .= '?hei='.$height.'&amp;wid='.$width;
        // }
        // 
        // return $image;
        
        $glue_char = (strpos($image, '?') === false) ? '?' : '&';
		return $image . $glue_char.'hei='.$height.'&wid='.$width."&op_sharpen=1";
    }

	public static function swatch_grabber($image, $height, $width, $disable_url_dimensions = FALSE)
    {
        $image = urldecode($image);
        
        if(strpos($image, 'http//') !== FALSE || strpos($image, 'http://') !== FALSE)
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
                $pos = (int) strrpos($image, '/');
                $image = substr($image, $pos+1);
            }
        }
        
        if (strpos($image, 'http://') === FALSE)
        {
            $image = 'http://c.shld.net/rpx/i/s/i/spin/image/' . $image;
         
            if ($disable_url_dimensions === FALSE)
                $image .= '?hei='.$height.'&amp;wid='.$width;
        }
        
        return $image;
    }

}
