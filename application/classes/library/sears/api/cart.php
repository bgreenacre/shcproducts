<?php defined('SHCP_PATH') OR die('No direct script access.');
/**
 * shcproducts
 *
 * @author Brian Greenacre and Kyla Klein
 * @email bgreenacre42@gmail.com
 * @version $Id$
 * @since Thu 16 Jun 2011 11:34:46 AM
 */

// -----------------------------------------------------------------------------

/**
 * Library_Sears_Api_Cart
 *
 */
class Library_Sears_Api_Cart extends Library_Sears_Api {

    public function __construct($group = NULL)
    {
        parent::__construct($group);

        $this->content_type = 'xml';
    }
}

