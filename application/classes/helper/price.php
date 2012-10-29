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
 * Helper for price formatting in views/templates.
 *
 * @package     shcproducts
 * @subpackage  Helper
 * @since       0.1
 * @author      Brian Greenacre
 */
class Helper_Price {

    /**
     * currency - Format a number into a complete price with currency symbol.
     * In addition to the method arguments, it use the current locale
     * information to figure out the currency symbol and placement of that
     * symbol.
     *
     *  //Below will return $1.05 for the US locale.
     *  echo Helper_Price::currency(1.05);
     *
     * @access  public
     * @param   double|float    The number to be formatted.
     * @param   int             Number of decimal places.
     * @param   string          Currency symbol.
     * @return  string          Formatted price with currency symbol.
     * @uses    Helper_Price::format()
     */
    public static function currency($number = 0, $places = 2, $symbol = NULL)
    {
        $currency = '';
        $number = Helper_Price::format($number, $places, TRUE);
        $info = localeconv();

        if ($symbol === NULL)
        {
            $symbol = $info['currency_symbol'];
        }

        if ($number < 0)
        {
            $currency = ((bool) $info['n_cs_precedes']) ? $symbol.$number : $number.$symbol;
        }
        else
        {
            $currency = ((bool) $info['p_cs_precedes']) ? $symbol.$number : $number.$symbol;
        }

        return $currency;
    }

    /**
     * format - Format a number to the current locale settings.
     *
     *  //Below will return 1,000.000 for the US locale.
     *  echo Helper_Price::format(1000, 3);
     *
     * @access  public
     * @param   double|float    The number to be formatted.
     * @param   int             Number of decimal places.
     * @param   bool            Is monetary number.
     * @return  string          Formatted number.
     */
    public static function format($number, $places, $monetary = FALSE)
    {
        $info = localeconv();

        if ($monetary)
        {
            $decimal   = $info['mon_decimal_point'];
            $thousands = $info['mon_thousands_sep'];
        }
        else
        {
            $decimal   = $info['decimal_point'];
            $thousands = $info['thousands_sep'];
        }

        return number_format($number, $places, $decimal, $thousands);
    }

}
