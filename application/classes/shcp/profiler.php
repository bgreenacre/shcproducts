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
 * SHCP_Profiler - Provides a simple profiling mechanism for this plugin.
 *
 * @package shcproducts
 * @subpackage  utility
 * @category    utility
 * @since       0.1
 */
class SHCP_Profiler {

    /**
     * @var  integer   maximium number of application stats to keep
     */
    public static $rollover = 1000;

    /**
     * @var  array  collected benchmarks
     */
    protected static $_marks = array();

    /**
     * Starts a new benchmark and returns a unique token. The returned token
     * _must_ be used when stopping the benchmark.
     *
     *     $token = SHCP_Profiler::start('test', 'profiler');
     *
     * @param   string  group name
     * @param   string  benchmark name
     * @return  string
     */
    public static function start($group, $name)
    {
        static $counter = 0;

        // Create a unique token based on the counter
        $token = 'shcp/'.base_convert($counter++, 10, 32);

        self::$_marks[$token] = array
        (
            'group' => strtolower($group),
            'name'  => (string) $name,

            // Start the benchmark
            'start_time'   => microtime(TRUE),
            'start_memory' => (function_exists('memory_get_usage')) ? memory_get_usage() : 0,

            // Set the stop keys without values
            'stop_time'    => FALSE,
            'stop_memory'  => FALSE,
        );

        return $token;
    }

    /**
     * Stops a benchmark.
     *
     *     SHCP_Profiler::stop($token);
     *
     * @param   string  token
     * @return  void
     */
    public static function stop($token)
    {
        // Stop the benchmark
        self::$_marks[$token]['stop_time']   = microtime(TRUE);
        self::$_marks[$token]['stop_memory'] = (function_exists('memory_get_usage')) ? memory_get_usage() : 0;
    }

    /**
     * Deletes a benchmark. If an error occurs during the benchmark, it is
     * recommended to delete the benchmark to prevent statistics from being
     * adversely affected.
     *
     *     SHCP_Profiler::delete($token);
     *
     * @param   string  token
     * @return  void
     */
    public static function delete($token)
    {
        // Remove the benchmark
        unset(self::$_marks[$token]);
    }

    /**
     * Returns all the benchmark tokens by group and name as an array.
     *
     *     $groups = SHCP_Profiler::groups();
     *
     * @return  array
     */
    public static function groups()
    {
        $groups = array();

        foreach (self::$_marks as $token => $mark)
        {
            // Sort the tokens by the group and name
            $groups[$mark['group']][$mark['name']][] = $token;
        }

        return $groups;
    }
}
