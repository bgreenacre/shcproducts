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
 * @since Fri Sep 30, 2011
 */

// -----------------------------------------------------------------------------

class FileImport implements Countable, Iterator, SeekableIterator, ArrayAccess, Serializable {

    /**
     * path 
     * 
     * @var mixed
     * @access protected
     */
    protected $path;

    /**
     * file 
     * 
     * @var mixed
     * @access protected
     */
    protected $file;

    /**
     * cols 
     * 
     * @var array
     * @access protected
     */
    protected $cols = array();

    /**
     * Used as the array to iterate over.
     *
     * @var array
     */
    protected $_data = array();

    /**
     * Current position within the data array.
     *
     * @access  protected
     * @var     int
     */
    protected $_position = 0;

    /**
     * Total number of rows in the data array.
     *
     * @access  protected
     * @var     int
     */
    protected $_total_rows;

    public static function factory($file, $type = NULL)
    {
        if ( ! FileImport::$path)
        {
            FileImport::$path = realpath(wp_upload_dir()) . DIRECTORY_SEPARATOR . 'import' . DIRECTORY_SEPARATOR;
        }

        if ($type === NULL)
        {
            $type = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        }

        $class = 'FileImport_' . ucfirst($type);

        return new $class($file);
    }

    public function load(array $data = NULL, $use_header = TRUE)
    {
        if ($data !== NULL)
        {
            $this->_data = $data;
        }

        if ($user_header)
        {
            $this->cols = array_shift($this->_data);
        }

        $this->_position = 0;
        $this->_total_rows = count($this->_data);

        return $this;
    }

    /**
     * Return the raw array that is being used for this object.
     *
     *     $array = $config->as_array();
     *
     * @return  array
     */
    public function as_array()
    {
        return $this->current();
    }

    public function cols()
    {
        return $this->_cols;
    }

    /**
     * serialize
     *
     * @return void
     */
    public function serialize()
    {
        foreach (array('_object') as $var)
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

        foreach (unserialize($data) as $name => $var)
        {
            $this->{$name} = $var;
        }

        $this->load();
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
