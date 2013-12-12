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
 * Library_Sears_Api
 *
 * @package     shcproducts
 * @subpackage  API
 * @category    Library
 */
class Library_Sears_Api implements Countable, Iterator, SeekableIterator, ArrayAccess, Serializable {

    /**
     * @static
     * @var string 
     */
    protected static $_session;

    /**
     * @static
     * @var object
     */
    protected static $user;

    /**
     * Contains the absolute URL to the API.
     *
     * @var string
     */
    protected $endpoint;

    /**
     * API Key.
     *
     * @var string
     */
    protected $apikey;

    /**
     * Auth ID.
     *
     * @var string
     */
    protected $authid;

    /**
     * APP ID
     *
     * @var string
     */
    protected $appid;

    /**
     * Flag to state if the request is a success.
     *
     * @var bool
     */
    protected $success = FALSE;

    /**
     * errors 
     * 
     * @var array
     * @access protected
     */
    protected $errors = array();

    /**
     * Content type to get from API.
     *
     * @var string json OR xml
     */
    protected $content_type = 'json';
    
    
    /**
     * Version of the API to use
     * 
     * @var string 'v2'
     */
    protected $apiVersion = 'v2';
    
    /**
     * HTTP Code returned from cURL
     * @var int
     */
    public $http_code;

    /**
     * Array of CURL options to set for the curl request.
     *
     * @var array
     */
    protected $curl_options = array(
        CURLOPT_RETURNTRANSFER  => 1,
        CURLOPT_CONNECTTIMEOUT => 300,          // timeout on connect 
        CURLOPT_TIMEOUT        => 300,          // timeout on response
        //CURLOPT_HTTPHEADER      => array('X-SHCMMR-Client-Id: app_ui'),
    );

    /**
     * Store name. Can be Sears, Kmart, MyGofer, Craftsman.
     *
     * @var string
     */
    protected $store;

    /**
     * Cache enabled or disabled. Default is TRUE.
     *
     * @var bool
     */
    protected $cache;

    /**
     * Names of Sears API classes that will be called and merged into the top
     * call repsonse. This is to maintain some consistency where the API
     * _clearly_ falls apart.
     *
     * @var array
     */
    protected $_with;

    /**
     * Config group name to use.
     *
     * @var string
     */
    protected $_group;

    /**
     * Parameters to pass in the URL.
     *
     * @var array
     */
    protected $_params = array();

    /**
     * Complete url the curl request will use.
     *
     * @var string
     */
    protected $_url;

    /**
     * API method to call.
     *
     * @var string
     */
    protected $_method;

    /**
     * This contains an object to provide context for the magic variables
     * made available in each extend class.
     *
     * @var object
     */
    protected $_parent;

    /**
     * Contents from the request made. Used as the iterator.
     *
     * @var array
     */
    protected $_data;

    /**
     * _object 
     * 
     * @var mixed
     * @access protected
     */
    protected $_object;

    /**
     * Current position within the object.data
     *
     * @access  protected
     * @var     int
     */
    protected $_position = 0;

    /**
     * Total number of rows in the object.data
     *
     * @access  protected
     * @var     int
     */
    protected $_total_rows;

    /**
     * factory - Instantiate objects that are extended from this class.
     *
     * @static
     * @param string $class     Name of Sears API class.
     * @param string $group = NULL  Config group to use.
     * @param mixed $parent = NULL  Any context the object needs.
     * @return object instanceof Library_Sears_Api
     */
    public static function factory($class, $group = NULL, $parent = NULL)
    {
        $class = 'Library_Sears_Api_' . ucfirst($class);

        try
        {
            return new $class($group, $parent);
        }
        catch(Exception $e)
        {
            throw new Exception($e);
        }
    }

    /**
     * session 
     * 
     * @param mixed $key 
     * @static
     * @access public
     * @return void
     */
    public static function session($key = NULL)
    {
        if ($key === NULL)
        {
            return Library_Sears_Api::$_session;
        }

        Library_Sears_Api::$_session = $key;
    }

    /**
     * __construct - Set config properties.
     *
     * @param string $group = NULL  Config group to use.
     * @param mixed $parent = NULL  Any context the object needs.
     * @return void
     */
    public function __construct($group = NULL, $parent = NULL)
    {
        $this->_initialize();

        if ($group === NULL)
        {
            $this->_group = 'default';
        }
        else
        {
            $this->_group = $group;
        }

        $config = (array) SHCP::config('api.' . $this->_group);

        foreach ($config as $property => $value)
        {
            $this->{$property} = $value;
        }

        $settings = SHCP::get_option();

        foreach ($settings as $setting => $value)
        {
            $this->{$setting} = $value;
        }

        if ($parent)
        {
            $this->_parent = $parent;
        }
    }

    /**
     * __get - Magic method to access properties.
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        if (isset($this->current()->{$key}) === TRUE)
        {
            return $this->current()->{$key};
        }
        elseif (isset($this->_object->{$key}) === TRUE)
        {
            return $this->_object->{$key};
        }

        return NULL;
    }

    /**
     * _initialize - Initialize propertys of this class.
     *
     * @return void
     */
    protected function _initialize()
    {
        $this->_object = NULL;
        $this->_data = array();
        $this->_position = 0;
        $this->_total_rows = 0;
        $this->_params = array();
        $this->_request_made = FALSE;
        $this->_url = NULL;
        $this->_method = NULL;
        $this->success = FALSE;
        $this->errors = array();
    }

    /**
     * with 
     * 
     * @param mixed $with 
     * @access public
     * @return void
     */
    public function with($with)
    {
        $this->_with[] = $with;
    }

    /**
     * detail 
     * 
     * @access public
     * @return void
     */
    public function detail()
    {
        return Library_Sears_Api::factory('product', $this->_group, $this->current())
            ->get()
            ->load();
    }

    /**
     * method - The API method to call.
     *
     *  // As a setter.
     *  $this->method('AddtoCart');
     *
     *  // As a getter.
     *  $this->method();
     *
     * @param string $method = NULL
     * @return object $this
     */
    public function method($method = NULL)
    {
        if ($method === NULL)
        {
            return $this->_method;
        }

        // Force a initialize so each method is standalone.
        $this->_initialize();

        $this->_method = $method;

        return $this;
    }

    /**
     * reload - Reload the response for a specific api call.
     *
     * @param bool $refresh = FALSE
     * @return object $this
     */
    public function reload($refresh = FALSE)
    {
        if ($refresh !== FALSE OR ! $this->_request_made)
        {
            $this->_request();
        }

        $this->load();
        return $this;
    }

    /**
     * load - Public method to load up a API call.
     *
     * @return object $this
     */
    public function load()
    {
        $this->_load();

        if ($this->success() AND $this->_with)
        {
            foreach ($this->_with as $with)
            {
                $with = Library_Sears_Api::factory($with, $this->_group);
            }
        }

        return $this;
    }

    /**
     * success - Getter to check if the API call was successful.
     *
     * @return bool
     */
    public function success()
    {
        return $this->success;
    }

    public function cache($cache = NULL)
    {
        if ($cache === NULL)
        {
            return $this->cache;
        }

        $this->cache = (bool) $cache;

        return $this;
    }

    /**
     * param - Add a parameter which be sent to the API in the form of $_GET
     * variable.
     *
     *  // Single name and value. This method can be chained.
     *  $this->param('store', 'Sears')->param('catalogId', 12605);
     *
     *  // Can take an Array of parameters.
     *  $this->param(array('store' => 'Sears', 'catalogId' => '12605'));
     *
     *  // It can also append to an existing parameter and separates
     *  // the values witha comma ",".
     *  $this->param('partNumber', '3948302P', TRUE);
     *
     * @param string $name
     * @param string $value = NULL
     * @param bool $append = FALSE
     * @return mixed
     */
    public function param($name, $value = NULL, $append = FALSE)
    {
        if (is_array($name))
        {
            $this->_params += $name;
            return $this;
        }

        if ($value === NULL)
        {
            return SHCP::get($this->_params, $name);
        }

        if ($append !== FALSE)
        {
            if (isset($this->_params[$name]) === TRUE)
            {
                $this->_params[$name] .= ',' . $value;
            }
            else
            {
                $this->_params[$name] = $value;
            }
        }
        else
        {
            $this->_params[$name] = $value;
        }

        return $this;
    }

    public function url($method = NULL, array $params = NULL)
    {
        if ( ! $this->_url OR $method !== NULL)
        {
            $this->_method = ($method) ? $method : $this->_method;
            $this->param($params);
            return $this->build_url();
        }
        
        return $this->_url;
    }

    /**
     * build_url - Build a complete URL to use for the Curl request to the
     * API.
     *
     * @return string
     */
    protected function build_url()
    {
        $url = rtrim($this->endpoint, '/') . '/' . $this->method();

        if ($this->_params)
        {
            $qs = '?';

            foreach (array_reverse($this->_params) as $param => $value)
            {
                $qs .= $param . '=' . urlencode($value) . '&';
            }

            $url .= rtrim($qs, '&');
            unset($qs);
        }

        return $url;
    }

    /**
     * _request - Execute a curl request and parse the result into the
     * $this->_object for further processing by the _load method.
     *
     * @return void
     */
    protected function _request()
    {
        // Require the endpoint property.
        if ($this->endpoint === NULL AND $this->_url === NULL)
        {
            throw new Exception('No endpoint provided for Sears API request');
        }

        if ($this->_url === NULL)
        {
            // Set global params for all requests to Sears API.
            $this
                ->param('store', $this->store)
                ->param('contentType', $this->content_type)
                ->param('apikey', $this->apikey)
                ->param('authID', $this->authid)
                ->param('appID', $this->appid);

            // Get the complete url.
            $this->_url = $this->build_url();
        }
        
        // Enable the following to see where API calls are going:
         error_log('API URL: '.$this->_url);

        if (SHCP::$profiling)
        {
            $request_token = SHCP_Profiler::start('Request', $this->_url);
        }

        if ($this->cache AND $body = SHCP::cache('api/'.urlencode($this->_url)))
        {
            $this->_request_made = TRUE;

            // parse out the body.
            if ($this->content_type == 'json')
            {
                $this->_object = json_decode($body);
            }
            elseif ($this->content_type == 'xml')
            {
                // Load the xml into a simplexml object
                $this->_object = simplexml_load_string($body);
            }

            // Cleanup
            unset($body);

            return TRUE;
        }

        // Init the curl resource.
        $ch = curl_init($this->_url);

        // Set connection options
        if ( ! curl_setopt_array($ch, $this->curl_options))
        {
            throw new Exception('Failed to set CURL options, check CURL documentation.');
        }

        // Get the response body
        $body = curl_exec($ch);

        // Get the response information
        $this->http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        

        if ($body === FALSE)
        {
            $error = curl_error($ch);
        }

        // Close the connection
        curl_close($ch);

        if (isset($error))
        {
            // The API request failed -- may have timed out, etc.
            // Let's just return false here and move on.
            // error_log('CURL ERROR: '.$error); // Enable this to see what went wrong.
            $this->success = false;
            return false;
        }

        // Check for the posibility of a fault xml repsponse and
        // throw as an error.
        if (strpos($body, '<fault>') !== FALSE)
        {
            $fault = simplexml_load_string($body);

            if (isset($fault[0]) === TRUE)
            {
                $this->success = FALSE;
            }
            elseif (isset($fault['faultstring']) === TRUE)
            {
                $this->success = FALSE;
            }

            return FALSE;
        }

        try
        {
            // parse out the body.
            if ($this->content_type == 'json')
            {
                $this->_object = json_decode($body);
            }
            elseif ($this->content_type == 'xml')
            {
                /**
                 * This, this right here! This is completely ridiculous that this
                 * regex has to be performed on the response xml to strip out, in
                 * some cases, whitespace that is up to 100 characters in length.
                 */
                $body = preg_replace('~\s*(<([^>]*)>[^<\s]*</\2>|<[^>]*>)\s*~', '$1', $body);

                // Load the xml into a simplexml object
                $this->_object = simplexml_load_string($body);
            }
        }
        catch(Exception $e)
        {
            $this->_request_made = TRUE;
            throw new Exception($e);
            return;
        }

        // Make sure the flag to state the request has been made is set.
        $this->_request_made = TRUE;

        if ($this->cache)
        {
            SHCP::cache('api/'.urlencode($this->_url), $body);
        }

        if (isset($request_token))
        {
            SHCP_Profiler::stop($request_token);
        }

        // Cleanup
        unset($body);

        return TRUE;
    }

    /**
     * _load - Load the response from the API call.
     *
     * * Request the API call if needed.
     * * Check the $this->_object and look for any errors.
     * * Each extended should overload this method to do additional validation.
     *
     * @return bool
     */
    protected function _load()
    {
        if ($this->_request_made !== TRUE)
        {
            $this->_request();
        }

        if ($this->_object)
        {
            if (isset($this->_object->StatusData))
            {
                if ($this->_object->StatusData->ResponseCode > 0)
                {
                    $this->success = FALSE;
                    $this->errors[] = 'Error in API call ' . $this->method() . ' [code ' . $this->_object->StatusData->ResponseCode . '] ' . $this->_object->StatusData->RespMessage;
                }
                else
                {
                    $this->success = TRUE;
                }
            }
        }
        else
        {
            $this->success = FALSE;
            return FALSE;
        }

        $this->success = TRUE;
        return TRUE;
    }

    /**
     * sort
     *
     * @param string $sort
     * @return void
     */
    public function sort($sort)
    {
        $this->param('sortBy', $sort);
    }

    /**
     * limit - Limit the results from the api by start and end index.
     *
     * @param int $start
     * @param int $end = NULL
     * @return void
     */
    public function limit($start, $end = NULL)
    {
        $this->param('startIndex', $start);

        if ($end !== NULL)
        {
            $this->param('endIndex', $end);
        }

        return $this;
    }

    /**
     * serialize
     *
     * @return void
     */
    public function serialize()
    {
        $this->load();

        foreach (array('endpoint', 'authid', 'appid', 'apikey', 'content_type', 'store', '_object', '_url', '_params', '_request_made') as $var)
        {
            $data[$var] = $this->{$var};
        }

        return serialize($data);
    }

    /**
     * unserialize
     *
     * @param array $data
     * @return void
     */
    public function unserialize($data)
    {
        $this->_initialize();
        
        $unserialized = unserialize($data);
        if(!$unserialized) {
        	return false;
        }

        foreach (unserialize($data) as $name => $var)
        {
            $this->{$name} = $var;
        }

        $this->reload();
    }

    /**
     * rewind 
     * 
     * @access public
     * @return void
     */
    public function rewind()
    {
        $this->_position = 0;
    }

    /**
     * count 
     * 
     * @access public
     * @return void
     */
    public function count()
    {
        if ( ! $this->_request_made)
        {
            $this->_load();
        }

        return $this->_total_rows;
    }

    /**
     * key 
     * 
     * @access public
     * @return void
     */
    public function key()
    {
        return $this->_position;
    }

    /**
     * prev 
     * 
     * @access public
     * @return void
     */
    public function prev()
    {
        --$this->_position;
        return $this;
    }

    /**
     * next 
     * 
     * @access public
     * @return void
     */
    public function next()
    {
        ++$this->_position;
        return $this;
    }

    /**
     * seek 
     * 
     * @param mixed $offset 
     * @access public
     * @return void
     */
    public function seek($offset)
    {
        if ($this->offsetExists($offset))
        {
            $this->_position = $offset;
            return TRUE;
        }

        return FALSE;
    }

    /**
     * current 
     * 
     * @access public
     * @return void
     */
    public function current()
    {
        if ( ! $this->seek($this->_position))
        {
            return NULL;
        }

        return $this->_data[$this->_position];
    }

    /**
     * valid 
     * 
     * @access public
     * @return void
     */
    public function valid()
    {
        if ( ! $this->_request_made)
        {
            $this->_load();
        }

        return isset($this->_data[$this->_position]);
    }

    /**
     * offsetExists 
     * 
     * @param mixed $offset 
     * @access public
     * @return void
     */
    public function offsetExists($offset)
    {
    	// The following was causing exceptions to be thrown,
    	// and does not appear to cause any ill effects when removed.
    	// Keeping the code commented out below for reference.
    	
//         if ( ! $this->_request_made)
//         {
//             $this->_load();
//         }

        return ($offset >= 0 AND $offset < $this->_total_rows);
    }

    /**
     * offsetGet 
     * 
     * @param mixed $offset 
     * @access public
     * @return void
     */
    public function offsetGet($offset)
    {
        if ( ! $this->seek($offset))
            return NULL;

        return $this->current();
    }

    /**
     * offsetSet 
     * 
     * @param mixed $offset 
     * @param mixed $value 
     * @access public
     * @return void
     */
    public function offsetSet($offset, $value)
    {
    }

    /**
     * offsetUnset 
     * 
     * @param mixed $offset 
     * @access public
     * @return void
     */
    public function offsetUnset($offset)
    {
    }

}
