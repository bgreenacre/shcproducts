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
 * SHCP_Library_Api_Cart
 *
 */
class SHCP_Library_Api_Cart extends SHCP_Library_Api {

    public function __construct($group = NULL)
    {
        parent::__construct($group);

        $this->content_type = 'xml';
    }
}

