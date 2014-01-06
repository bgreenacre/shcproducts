<?php
//$path = './wordpress-tests/bootstrap.php';
$path = dirname(__FILE__) . '/wordpress-tests/bootstrap.php';

if( file_exists( $path ) ) {

    require_once $path;
} else {
    exit( "Couldn't find path to wordpress-tests (".$path.")" );
}

date_default_timezone_set('US/Central');
error_log('Beginning tests.... '.date("Y-m-d H:i:s"));

?>