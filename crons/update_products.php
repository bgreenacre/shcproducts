<?php (PHP_SAPI === 'cli') or die('This can only be executed via command line.');
/**
 * This file contains a scheduled task to update products in wordpress.
 * It first attempts to load the wordpress environment and then
 * executes the update.
 */

$wp_folder_root = (isset($_SERVER['WP_ROOT'])) ? $_SERVER['WP_ROOT'] : '';

if ( ! is_dir($wp_folder_root))
{
    throw new Exception('Invalid wordpress root folder set.');
    exit;
}

$wp_folder_root = realpath($wp_folder_root) . DIRECTORY_SEPARATOR;

// Load in wordpress environment.
require_once $wp_folder_root.'wp-load.php';

try {
    $update = new Controller_Crons_Products();
    $update->action_update();
}
catch (Exception $e) {
    throw $e;
}

exit;
