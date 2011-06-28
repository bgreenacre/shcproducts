<?php defined('SHCP_PATH') OR die('No direct script access.');
/**
 * shcproducts
 *
 * @author Brian Greenacre and Kyla Klein
 * @email bgreenacre42@gmail.com
 * @version $Id$
 * @since Wed 15 Jun 2011 07:32:09 PM
 */

// ----------------------------------------------------------------------------

/**
 * Model_SHCP
 *
 */
class Model_Products extends Model_SHCP {

    public function __construct()
    {
        $this->param('post_type', 'shcproduct');
    }

    public function related($id)
    {
        $ids = (array) get_post_meta($id, 'shcp_related');

        if ($ids)
        {
            $this->param('post__in', $ids);
        }
        else
        {
            $this->param('p', -1);
        }

        return $this;
    }

}
