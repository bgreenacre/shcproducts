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

/**
 * SHCP
 *
 */
class SHCP {

    public static $delimiter = '.';
    public static $magic_quotes = FALSE;
    public static $global_data = array();
    public static $profiling = TRUE;
    private static $_init = FALSE;

    /**
     * init
     *
     * @static
     * @return void
     */
    public static function init(array $params = array())
    {
        if (self::$_init !== FALSE)
        {
            return;
        }

		// Determine if the extremely evil magic quotes are enabled
		self::$magic_quotes = (bool) get_magic_quotes_gpc();

		// Sanitize all request variables
		$_GET    = self::sanitize($_GET);
		$_POST   = self::sanitize($_POST);
		$_COOKIE = self::sanitize($_COOKIE);

		if (isset($params['profiling']) === TRUE)
        {
            self::$profiling = (bool) $params['profiling'];
		}

		if (self::$profiling)
        {
            $load_fnc = SHCP_Profiler::start('init', 'functions');
        }

		if ($functions = (array) self::config('load.functions'))
        {
            foreach ($functions as $func)
            {
                $func = SHCP_FUNCTIONS . '/' . $func . '.php';

                if (is_file($func))
                {
                    include_once $func;
                }
            }
        }

        if (isset($load_fnc))
        {
            SHCP_Profiler::stop($load_fnc);
            unset($load_fnc);
        }

		if (self::$profiling)
        {
            $load_ctl = SHCP_Profiler::start('init', 'controllers');
        }

		if ($controllers = (array) self::config('load.controllers'))
        {
            foreach ($controllers as $ctl)
            {
                // Merely instantiate the controller.
                // Any action hooks and filters should be set in the
                // Controllers constructor.
                $ctl = new $ctl();
            }
        }

        if (isset($load_ctl))
        {
            SHCP_Profiler::stop($load_ctl);
            unset($load_ctl);
        }
    }

    /**
     * view - Simple view method to get a template file and pass an array
     * of data to it.
     *
     * @static
     * @param unknown $view
     * @param unknown array $data = NULL
     * @param unknown $ret = TRUE
     * @return void
     */
    public static function view($view, array $data = NULL, $ret = TRUE)
    {
        $view = SHCP_VIEW . '/' . $view . '.php';

		// Import the view variables to local namespace
		extract($data, EXTR_SKIP);

        // Extract the global data array.
		if (self::$global_data)
        {
            extract(self::$global_data, EXTR_SKIP);
        }

		// Capture the view output
		ob_start();

		try
		{
			// Load the view within the current scope
			include $view;
		}
		catch (Exception $e)
		{
			// Delete the output buffer
			ob_end_clean();

			// Re-throw the exception
			throw $e;
		}

        if ($ret !== FALSE)
        {
            // Get the captured output and close the buffer
            return ob_get_clean();
        }
        else
        {
            echo ob_get_clean();
        }
    }

    /**
     * set_global - Set a global variable to be used in the views.
     *
     * @static
     * @return void
     */
    public static function set_global($name, $value = NULL)
    {
        if (is_array($name) === TRUE)
        {
            self::$global_data += $name;
        }
        else
        {
            self::$global_data[$name] = $value;
        }
    }

    /**
     * bind_global - Bind a global variable to be used in the views.
     *
     * @static
     * @return void
     */
    public static function bind_global($name, &$value = NULL)
    {
        self::$global_data[$name] =& $value;
    }

    /**
     * get - Get an index from a given array.
     *
     * @static
     * @param array $data
     * @param string $index = NULL
     * @param mixed $default = NULL
     * @return mixed
     */
    public static function get(array $data, $index = NULL, $default = NULL)
    {
        return (isset($data[$index]) === TRUE) ? $data[$index] : $default;
    }

	/**
	 * Gets a value from an Shcpay using a dot separated path.
	 *
	 *     // Get the value of $Shcpay['foo']['bar']
	 *     $value = Shcp::path($Shcpay, 'foo.bar');
	 *
	 * Using a wildcard "*" will search intermediate Shcpays and return an Shcpay.
	 *
	 *     // Get the values of "color" in theme
	 *     $colors = Shcp::path($Shcpay, 'theme.*.color');
	 *
	 *     // Using an array of keys
	 *     $colors = Shcp::path($Shcpay, Shcpay('theme', '*', 'color'));
	 *
	 * @param   array   array to search
	 * @param   mixed   key path string (delimiter separated) or array of keys
	 * @param   mixed   default value if the path is not set
	 * @param   string  key path delimiter
	 * @return  mixed
	 */
	public static function path($array, $path, $default = NULL, $delimiter = NULL)
	{
		if ( ! is_array($array))
		{
			// This is not an Shcpay!
			return $default;
		}

		if (is_array($path))
		{
			// The path has already been separated into keys
			$keys = $path;
		}
		else
		{
			if (array_key_exists($path, $array))
			{
				// No need to do extra processing
				return $array[$path];
			}

			if ($delimiter === NULL)
			{
				// Use the default delimiter
				$delimiter = self::$delimiter;
			}

			// Remove starting delimiters and spaces
			$path = ltrim($path, "{$delimiter} ");

			// Remove ending delimiters, spaces, and wildcards
			$path = rtrim($path, "{$delimiter} *");

			// Split the keys by delimiter
			$keys = explode($delimiter, $path);
		}

		do
		{
			$key = array_shift($keys);

			if (ctype_digit($key))
			{
				// Make the key an integer
				$key = (int) $key;
			}

			if (isset($array[$key]))
			{
				if ($keys)
				{
					if (is_array($array[$key]))
					{
						// Dig down into the next part of the path
						$array = $array[$key];
					}
					else
					{
						// Unable to dig deeper
						break;
					}
				}
				else
				{
					// Found the path requested
					return $array[$key];
				}
			}
			elseif ($key === '*')
			{
				// Handle wildcards

				$values = array();
				foreach ($array as $arr)
				{
					if ($value = self::path($arr, implode('.', $keys)))
					{
						$values[] = $value;
					}
				}

				if ($values)
				{
					// Found the values requested
					return $values;
				}
				else
				{
					// Unable to dig deeper
					break;
				}
			}
			else
			{
				// Unable to dig deeper
				break;
			}
		}
		while ($keys);

		// Unable to find the value requested
		return $default;
	}

	/**
	 * Loads a file within a totally empty scope and returns the output:
	 *
	 *     $foo = Shcp::load('foo.php');
	 *
	 * @param   string
	 * @return  mixed
	 */
	public static function load($file)
	{
		return include $file;
	}

	/**
	 * Returns the configuration Shcpay for the requested group.
	 *
	 *     // Get all the configuration in config/database.php
	 *     $config = Shcp::config('database');
	 *
	 *     // Get only the default connection configuration
	 *     $default = Shcp::config('database.default')
	 *
	 *     // Get only the hostname of the default connection
	 *     $host = Shcp::config('database.default.connection.hostname')
	 *
	 * @param   string   group name
	 * @return  Config
	 */
	public static function config($group)
	{
		static $config;

		if (strpos($group, '.') !== FALSE)
		{
			// Split the config group and path
			list ($group, $path) = explode('.', $group, 2);
		}

		if ( ! isset($config[$group]))
		{
		    $file = SHCP_CONFIG . '/' . $group . '.php';

			// Load the config group into the cache
			$config[$group] = self::load($file);
		}

		if (isset($path))
		{
			return self::path($config[$group], $path, NULL, '.');
		}
		else
		{
			return $config[$group];
		}
	}

	/**
	 * Recursively sanitizes an input variable:
	 *
	 * - Strips slashes if magic quotes are enabled
	 * - Normalizes all newlines to LF
	 *
	 * @param   mixed  any variable
	 * @return  mixed  sanitized variable
	 */
	public static function sanitize($value)
	{
		if (is_array($value) OR is_object($value))
		{
			foreach ($value as $key => $val)
			{
				// Recursively clean each value
				$value[$key] = self::sanitize($val);
			}
		}
		elseif (is_string($value))
		{
			if (self::$magic_quotes === TRUE)
			{
				// Remove slashes added by magic quotes
				$value = stripslashes($value);
			}

			if (strpos($value, "\r") !== FALSE)
			{
				// Standardize newlines
				$value = str_replace(array("\r\n", "\r"), "\n", $value);
			}
		}

		return $value;
	}

}

