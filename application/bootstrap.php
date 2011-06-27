<?php defined('SHCP_PATH') OR die('No direct script access.');
/**
 * shcproducts
 *
 * @author Brian Greenacre and Kyla Klein
 * @email bgreenacre42@gmail.com
 * @version $Id$
 * @since Wed 15 Jun 2011 07:32:09 PM
 */

// -----------------------------------------------------------------------------

// Require the core class file.
require_once SHCP_CLASS . '/shcp.php';

// Register the auloader for SHCP plugin
spl_autoload_register(array('SHCP', 'autoload'));
SHCP::init();
