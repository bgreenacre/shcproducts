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
 * SHCP - Main class for shcproducts plugin. Provides a number of static methods
 * to accomplish tasks like cacheing, views, class autoloading.
 *
 * @package		shcproducts
 * @subpackage	Core
 * @since		0.1
 * @author		Brian Greenacre
 */
class SHCP {

    /**
     * A global delimiter character used for SHCP.
     *
     * @var string
     */
    public static $delimiter = '.';

    /**
     * Is magic quotes enabled?
     *
     * @var bool
     */
    public static $magic_quotes = FALSE;

    /**
     * Contains an array of indexed global data. Useful for cacheing variables
     * during a complete php execution.
     *
     * @var array
     */
    public static $global_data = array();

    public static $profiling = TRUE;
    public static $cache_dir = SHCP_CACHE;
    public static $cache_life = 2000;
    public static $is_ajax = FALSE;
    private static $_init = FALSE;

    /**
  	 * this is a prefix used for class names, options and anything else
  	 * that requires some sort of namespace.
  	 *
  	 * @access	private
  	 * @var		string
  	 */
  	private static $_prefix = 'SHCP_';

  	/**
  	 * cache any data gathered from language files.
  	 *
  	 * @access	private
  	 * @var		array
  	 */
  	private static $_lang = array();

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

		// Determine if this is an ajax request
		self::$is_ajax = ($with = self::get($_SERVER, 'HTTP_X_REQUESTED_WITH') AND 'xmlhttprequest' == strtolower($with));

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
                $ctl = "Controller_" . ucfirst($ctl);

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
     * autoload - Method used to autoload class files.
     *
     * @static
     * @param string $class
     * @return void
     */
    public static function autoload($class)
    {
        try
        {
            $file = str_replace('_', '/', strtolower($class));

            $themepath = get_stylesheet_directory() . '/application/classes/' . $file . '.php';

            if (is_file($themepath))
            {
                require $themepath;
                return TRUE;
            }

            $fullpath = SHCP_CLASS . '/' . $file . '.php';

            if (is_file($fullpath))
            {
                require $fullpath;
                return TRUE;
            }

            return FALSE;
        }
        catch(Exception $e)
        {
            throw Exception($e);
            die;
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
		// Import the view variables to local namespace
		if($data !== NULL)
		{
		  extract($data, EXTR_SKIP);
        }

        // Extract the global data array.
		if (self::$global_data)
        {
            extract(self::$global_data, EXTR_SKIP);
        }

		// Capture the view output
		ob_start();

		try
		{
		    $themepath = get_stylesheet_directory() . '/application/views/' . $view . '.php';

		    if (is_file($themepath))
		    {
		        include $themepath;
		    }
		    else
		    {
                $view = SHCP_VIEW . '/' . $view . '.php';

			    // Load the view within the current scope
			    include $view;
            }
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
	 * Provides simple file-based caching for strings and arrays:
	 *
	 *     // Set the "foo" cache
	 *     SHCP::cache('foo', 'hello, world');
	 *
	 *     // Get the "foo" cache
	 *     $foo = SHCP::cache('foo');
	 *
	 * All caches are stored as PHP code, generated with [var_export][ref-var].
	 * Caching objects may not work as expected. Storing references or an
	 * object or array that has recursion will cause an E_FATAL.
	 *
	 * The cache directory and default cache lifetime is set by [SHCP::init]
	 *
	 * [ref-var]: http://php.net/var_export
	 *
	 * @throws  Exception
	 * @param   string   name of the cache
	 * @param   mixed    data to cache
	 * @param   integer  number of seconds the cache is valid for
	 * @return  mixed    for getting
	 * @return  boolean  for setting
	 */
	public static function cache($name, $data = NULL, $lifetime = NULL)
	{
		// Cache directories are split by keys to prevent filesystem overload
		$dir = self::$cache_dir.DIRECTORY_SEPARATOR;

	    if (($pos = strrpos($name, '/')) !== FALSE)
	    {
	        $dir .= substr($name, 0, $pos);

	        // Cache file is a hash of the name
	        $file = sha1(substr($name, $pos+1)) . '.txt';
	    }
	    else
	    {
	        // Cache file is a hash of the name
	        $file = sha1($name) . '.txt';
	    }

	    $dir .= $file[0].$file[1].DIRECTORY_SEPARATOR;

		if ($lifetime === NULL)
		{
			// Use the default lifetime
			$lifetime = self::$cache_life;
		}

		if ($data === NULL)
		{
			if (is_file($dir.$file))
			{
				if ((time() - filemtime($dir.$file)) < $lifetime)
				{
					// Return the cache
					try
					{
						return unserialize(file_get_contents($dir.$file));
					}
					catch (Exception $e)
					{
						// Cache is corrupt, let return happen normally.
					}
				}
				else
				{
					try
					{
						// Cache has expired
						unlink($dir.$file);
					}
					catch (Exception $e)
					{
						// Cache has mostly likely already been deleted,
						// let return happen normally.
					}
				}
			}

			// Cache not found
			return NULL;
		}

		if ( ! is_dir($dir))
		{
			// Create the cache directory
			mkdir($dir, 0777, TRUE);

			// Set permissions (must be manually set to fix umask issues)
			chmod($dir, 0777);
		}

		// Force the data to be a string
		$data = serialize($data);

		try
		{
			// Write the cache
			return (bool) file_put_contents($dir.$file, $data, LOCK_EX);
		}
		catch (Exception $e)
		{
			// Failed to write cache
			return FALSE;
		}
	}

	/**
	 * get_global - Get a particular global var that's been set.
	 *
	 * @param string $name
	 * @param mixed $default = NULL
	 * @return void
	 */
	public function get_global($name, $default = NULL)
	{
	    return self::get(self::$global_data, $default);
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
  	 * prefix - Prefix a given string with a given string.
  	 *
  	 * @access	public
  	 * @param	string	String to be prefixed.
  	 * @param	string	Prefix string.
  	 * @return	string
  	 */
  	public static function prefix($str = NULL, $prefix = NULL)
  	{
  		if ($prefix === NULL)
  		{
  			$prefix = self::$_prefix;
  		}

  		return $prefix.$str;
  	}

	/**
	 * Gets a value from an array using a dot separated path.
	 *
	 *     // Get the value of $array['foo']['bar']
	 *     $value = Shcp::path($array, 'foo.bar');
	 *
	 * Using a wildcard "*" will search intermediate array and return an array.
	 *
	 *     // Get the values of "color" in theme
	 *     $colors = Shcp::path($array, 'theme.*.color');
	 *
	 *     // Using an array of keys
	 *     $colors = Shcp::path($array, array('theme', '*', 'color'));
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
			// This is not an array!
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
	 * get_option - This a wrapper method for the word press function get_option.
	 * Just makes it easier getting the options specific to this plugin.
	 *
	 * @access	public
	 * @param	string	The path of the option you want.
	 * @param	mixed	Default value to return if option path is not found.
	 * @return	mixed
	 */
	public static function get_option($option = NULL, $default = NULL)
	{
		if ($option === NULL)
		{
			return (array) get_option(self::prefix('options'));
		}

		return self::path(get_option(self::prefix('options')), $option, $default);
	}

	/**
	 * set_option - Save an option into the array of options for SHCP plugin.
	 *
	 * @static
	 * @access public
	 * @param   string|arrays option name or array options and their values.
	 * @param   mixed   The value to set to the option.
	 * @return  bool
	 */
	public static function set_option($option, $value = NULL)
	{
	    $current_options = (array) get_option(self::prefix('options'));

	    if (is_array($option))
        {
            $current_options = array_merge($current_options, $option);
        }
        else
        {
            $current_options[$option] = $value;
        }

        return update_option(self::prefix('options'), $current_options);
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
		    $config[$group] = array();

		    $themeconfig = get_stylesheet_directory() . '/application/config/' . $group . '.php';

		    if (is_file($themeconfig))
		    {
		        $config[$group] = self::load($themeconfig);
		    }

		    $file = SHCP_CONFIG . '/' . $group . '.php';

			// Load the config group into the cache
			$config[$group] = array_merge_recursive(self::load($file), $config[$group]);
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
	 * lang - Fetch a language line.
	 *
	 * @access	public
	 * @param	string	The name of the message file to use.
	 * @param	string	The path to the message line in the array.
	 * @return	string
	 */
	public static function lang($file, $path = NULL)
	{
		if ( ! array_key_exists($file, self::$_lang))
		{
			self::$_lang[$file] = self::load(SHCP_LANG.'/'.$file.'.php');
		}

		return self::path(self::$_lang[$file], $path);
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
