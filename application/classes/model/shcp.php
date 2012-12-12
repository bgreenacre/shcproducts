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
 * Model_SHCP
 *
 * @package     shcproducts
 * @subpackage  Model
 * @since       0.1
 * @author      Brian Greenacre
 */
class Model_SHCP implements Countable, Iterator, SeekableIterator, ArrayAccess, Serializable {

    /**
     * factory 
     * 
     * @param mixed $model 
     * @static
     * @access public
     * @return void
     */
    public static function factory($model)
    {
        $model = 'Model_' . ucfirst($model);

        try
        {
            return new $model();
        }
        catch(Exception $e)
        {
            throw new Exception($e);
        }
    }

    /**
     * Use the query_posts function vs direct class instantiation.
     *
     * @access  protected
     * @var     bool
     */
    protected $use_query_posts = FALSE;

    /**
     * merge_wp_query 
     * 
     * @var mixed
     * @access protected
     */
    protected $merge_wp_query = FALSE;

    /**
     * _id 
     * 
     * @var mixed
     * @access protected
     */
    protected $_id;

    /**
     * Name of the table. Default is the posts table.
     *
     * @var string
     */
    protected $_table_name = 'posts';

    /**
     * Parameters to pass in the query_posts call.
     *
     * @var array
     */
    protected $_params = array();

    /**
     * Contains DB columns of the table.
     *
     * @var array
     */
    protected $_fields = array();

    /**
     * Tracks whether the posts have been queried.
     *
     * @var bool
     */
    protected $_executed;
    
    /**
     * Tracks the current page of posts.
     * 
     * @var int
     */
    protected $_current_page;

    /**
     * Contents from the request made. Used as the iterator.
     *
     * @var array
     */
    protected $_data = array();

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
     * Number of posts to be displayed.
     *
     * @var int
     */
    protected $_total_display;

    /**
     * _posts_per_page 
     * 
     * @var mixed
     * @access protected
     */
    protected $_posts_per_page;

    /**
     * _max_num_pages 
     * 
     * @var mixed
     * @access protected
     */
    protected $_max_num_pages;

    /**
     * Array of indexed new values to set to the current post.
     *
     * @var array
     */
    protected $_values = array();

    /**
     * Contains any error messages thrown when saving a post.
     *
     * @var array
     */
    protected $_errors;

    /**
     * __construct - Call the _initialize method to set the object up.
     *
     * @return void
     */
    public function __construct($id = NULL)
    {
        $this->_initialize();
        $this->fields();

        if ($id !== NULL)
        {
            if (is_numeric($id))
            {
                $this->_id = (int) $id;
                $this->param('p', $this->_id);
            }
            elseif (is_object($id))
            {
                $this->_data = array($id);
                $this->_total_rows = 1;
                $this->_position = 0;
                $this->_total_display = 1;
                $this->_posts_per_page = 1;
                $this->_max_num_pages = 1;
                $this->_executed = TRUE;
            }
        }
    }

    /**
     * __get - Magic method to make accessing custom post fields easier.
     *
     *  //Example of getting a custom field "partNumber"
     *  $post = new Model_Product()->param('p', 22)->load();
     *  $post->ID; // the post id.
     *  $post->partNumber; // look up the custom field value for the post.
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        if (array_key_exists($key, $this->_values))
        {
            return $this->_values[$key];
        }
        elseif (is_object($this->current()))
        {
            if (isset($this->current()->{$key}) === TRUE)
            {
                return $this->current()->{$key};
            }
            elseif ($meta = get_post_meta($this->current()->ID, $key, TRUE))
            {
                return $meta;
            }
        }

        return NULL;
    }

    /**
     * __set - Set a property of the current post with a given value.
     *
     * @param string $key
     * @param string $value
     * @return object
     */
    public function __set($key, $value)
    {
        // Simply add this set value into the _values property.
        // Typical the save method would then be called in order to persist
        // the new values for the post.
        $this->_values[$key] = $value;
        $this->param($key, $value);

        return $this;
    }

    /**
     * values - Set the values for the post to be saved later.
     *
     * @param array $values = NULL
     * @return object $this
     */
    public function values(array $values = NULL)
    {
        $this->_values = array_merge($this->_values, $values);

        return $this;
    }

    /**
     * use_query_posts 
     * 
     * @param mixed $use 
     * @access public
     * @return void
     */
    public function use_query_posts($use = FALSE)
    {
        $this->use_query_posts = (bool) $use;
        return $this;
    }

    /**
     * merge_wp_query 
     * 
     * @param mixed $use 
     * @access public
     * @return void
     */
    public function merge_wp_query($use = FALSE)
    {
        $this->merge_wp_query = (bool) $use;
        return $this;
    }

    /**
     * fields - Get the columns for the current table.
     *
     * @return array
     */
    public function fields()
    {
        global $wpdb;

        // First check to see if the global has been set.
        if ($this->_fields = SHCP::get_global($this->_table_name.'_table_fields'))
        {
            return $this->_fields;
        }

        // Query for the columns of the table
        $columns = $wpdb->get_results(
            'SHOW COLUMNS FROM `' . $wpdb->prefix . $this->_table_name. '`;'
        );

        // Dump the result into an array.
        if ($columns)
        {
            foreach ($columns as $column)
            {
                $this->_fields[$column->Field] = $column;
            }
        }

        // Set the global so this query isn't executed more then once per table.
        SHCP::set_global($this->_table_name.'_table_fields', $this->_fields);

        return $this->_fields;
    }

    /**
     * _initialize - Initialize the object properties.
     *
     * @return void
     */
    protected function _initialize()
    {
        $this->_id = 0;
        $this->_data = array();
        $this->_position = 0;
        $this->_total_rows = 0;
        $this->_total_display = 0;
        $this->_posts_per_page = 0;
        $this->_max_num_pages = 0;
        $this->_params = array();
        $this->_values = array();
        $this->_errors = array();
        $this->_fields = array();
        $this->_executed = FALSE;
    }

    /**
     * _load - Load the results from the query.
     *
     * @return bool
     */
    protected function _load()
    {
        global $wp_query;

        if ($this->_executed !== TRUE)
        {
            if ($this->merge_wp_query)
            {
                if ( ! $wp_query->query)
                {
                    $data = $_GET;

                    if ($_POST)
                    {
                        $data = array_merge($data, $_POST);
                    }

                    $wp_query->parse_query(http_build_query($data));
                }

                if (is_array($wp_query->query))
                {
                    $this->_params = array_merge($wp_query->query, $this->_params);
                }
            }

            if ($this->use_query_posts)
            {
                $this->_data = query_posts($this->_params);
                $this->_position = 0;
                $this->_total_rows = $GLOBALS['wp_query']->found_posts;
                $this->_total_display = $GLOBALS['wp_query']->post_count;
                $this->_posts_per_page = $GLOBALS['wp_query']->posts_per_page;
                $this->_max_num_pages = $GLOBALS['wp_query']->max_num_pages;
            }
            else
            {
                // Instantiate the query object to get posts.
                $query = new WP_Query($this->_params);

                // Dump the posts into this object and set the iterator props.
                $this->_data = $query->posts;
                $this->_position = 0;
                $this->_total_rows = count($this->_data);
                $this->_total_display = $query->post_count;
                $this->_posts_per_page = $query->posts_per_page;
                $this->_max_num_pages = $query->max_num_pages;

                // Destroy the query object.
                unset($query);
            }

            $this->_executed = TRUE;
        }



        if ( ! $this->_data)
        {
            $this->_position = 0;
            $this->_total_rows = 0;
            $this->_total_display = 0;
            return FALSE;
        }

        return TRUE;
    }

    /**
     * _save - Internal method to save the current post to the database.
     *
     * This method will merge $this->_values array into the current post
     * of this object. Then it will figure out if this is an update or a
     * creation of a new post. Execute the save and then ensure the current
     * post object is updated with the saved data.
     *
     * @return void
     */
    protected function _save()
    {
        // Nothing to save so just return.
        if ( ! $this->_values)
        {
            return;
        }

        $post = (array) SHCP::get($this->_data, $this->_position);

        foreach (array_keys($this->fields()) as $field)
        {
            if (isset($this->_values[$field]) === TRUE)
            {
                $post[$field] = $this->_values[$field];
            }
        }

        if (isset($post['ID']) && $post['ID'] > 0)
        {
            $id = wp_update_post($post);
        }
        else
        {
            $id = wp_insert_post($post, FALSE);
        }

        if ($id > 0)
        {
            $post['ID'] = $id;

            // Set the current post to the updated data and reset the total_rows
            // property since this could be an addition rather then an update.
            $this->_data[$this->_position] = (object) $post;
            $this->_total_rows = count($this->_data);

            // Now that the post has been saved, save any values that are not
            // post fields as meta tags in the post.
            if ($meta = array_diff_key($this->_values, (array) $this->_data[$this->_position]))
            {
                foreach ($meta as $key => $value)
                {
                    update_post_meta($id, $key, $value);
                }
            }
        }
    }

    /**
     * check - By default only trivial things are validated for a post save.
     * Extend this method to add in additional specific validation routines.
     *
     * For storing errors, the field name should be the index and the value
     * an array of messages. Each message index should be the name of the
     * validation routine and the value should be a generic message describing
     * the reason the routine failed.
     *
     *  // Example array of errors.
     *  array(
     *      'post_title'    => array(
     *          'empty'         => 'Empty title',
     *          'max_length'    => 'title too long',
     *          'min_length'    => 'Title too short',
     *      ),
     *      'post_status'   => array(
     *          'empty'     => 'A post status must be set',
     *      ),
     *  );
     *
     * @return bool TRUE is validation routines pass else FALSE.
     */
    public function check()
    {
        if ($this->post_title == '')
        {
            $this->_errors['post_title']['partnumber'] = $this->partnumber;
            $this->_errors['post_title']['empty'] = 'There is no post title set';
        }

        if ($this->detail == NULL || $this->detail->current() == NULL)
        {
            $this->_errors['detail']['partnumber'] = $this->partnumber;
            $this->_errors['detail']['empty'] = 'There is no detail for this product.';
        }

        return ( ! $this->_errors) ? TRUE : FALSE;
    }

    /**
     * errors - Accessor for the errors array.
     *
     * @return array
     */
    public function errors()
    {
        return $this->_errors;
    }

    /**
     * save - Save the current post to the database.
     *
     * @return object
     */
    public function save()
    {
        $this->_save();

        // Reset the _values property to empty array
        $this->_values = array();
        return $this;
    }

    /**
     * save_all - Save all posts to the database.
     *
     * @return object
     */
    public function save_all()
    {
        // Rewind the iteratable array to position 0
        $this->rewind();

        // Blast through the array of posts and save the data for them
        while ($this->valid())
        {
            $this->_save();
            $this->next();
        }

        // Rewind to begining of the posts array.
        $this->rewind();

        // Reset the values array.
        $this->_values = array();

        return $this;
    }

    public function delete($id = NULL)
    {
        if ($id === NULL AND $this->loaded())
        {
            $id = $this->ID;
        }
        else
        {
            return $this;
        }

        //wp_delete_post( (int) $id, TRUE);
        
        //DO NOT delete posts, set them to status 'draft'
        $args = array('ID' => $id, 'post_status' => 'draft');

        wp_update_post($args);
    }

    
    public function trash($id = NULL)
    {
        if ($id === NULL AND $this->loaded())
        {
            $id = $this->ID;
        }
        else
        {
            return $this;
        }

        //wp_delete_post( (int) $id, FALSE);
        
        //DO NOT delete posts, set them to status 'draft'
        $args = array('ID' => $id, 'post_status' => 'draft');
        wp_update_post($args);
    }
    /**
     * as_array - Get the current post as an array rather than an object.
     *
     * @return array
     */
    public function as_array()
    {
        return (array) $this->current();
    }

    /**
     * load - Public method to load up a API call.
     *
     * @return object $this
     */
    public function load()
    {
        $this->_load();

        return $this;
    }

    /**
     * loaded - Check whether a result has been loaded from the DB.
     *
     * @return bool TRUE when there are results else FALSE on empty results.
     */
    public function loaded()
    {
        $this->load();

        return ($this->_total_rows > 0);
    }

    /**
     * param - Add a parameter which will passed to the WP_Query object.
     *
     *  // Single name and value. This method can be chained.
     *  $this->param('store', 'Sears')->param('catalogId', 12605);
     *
     *  // Can take an Array of parameters.
     *  $this->param(array('store' => 'Sears', 'catalogId' => '12605'));
     *
     * @param string $name
     * @param string $value = NULL
     * @param bool $append = FALSE
     * @return mixed
     */
    public function param($name, $value = NULL)
    {
        if (is_array($name))
        {
            $this->_params = array_merge_recursive($this->_params, $name);
            return $this;
        }

        if ($value === NULL)
        {
            return SHCP::get($this->_params, $name);
        }

        $this->_params[$name] = $value;

        return $this;
    }

    /**
     * author - set the author for the query.
     *
     * @param string|int $author
     * @return object
     */
    public function author($author)
    {
        if (is_numeric($author))
        {
            $this->param('author', (int) $author);
        }
        else
        {
            $this->param('author_name', $author);
        }

        return $this;
    }

    /**
     * category - Filter post results by category.
     *
     * @param string|int $cat
     * @param string $compare = 'AND'
     * @return void
     */
    public function category($cat, $compare = 'AND')
    {
        $ids = array();
        $slugs = array();

        if ($current_cat = $this->param('cat') OR $current_cat = $this->param('category_name'))
        {
            unset($this->_params['cat']);
            unset($this->_params['category_name']);

            if (is_numeric($current_cat))
            {
                $ids[] = $current_cat;
            }
            else
            {
                $slugs[] = $current_cat;
            }
        }

        if (is_numeric($cat))
        {
            $ids[] = (int) $cat;
        }
        else
        {
            $slugs[] = $cat;
        }

        if ($ids)
        {
            if (count($ids) > 1)
            {
            }
            else
            {
                $this->param('cat', $ids[0]);
            }
        }
        elseif ($slugs)
        {
            if (count($slugs) > 1)
            {
            }
            else
            {
                $this->param('category_name', $slugs[0]);
            }
        }

        return $this;
    }

    /**
     * tag - Filter post results by tags.
     *
     * @param string|int $tag
     * @param string $compare = 'AND'
     * @return object
     */
    public function tag($tag, $compare = 'AND')
    {
        $ids = array();
        $slugs = array();

        if ($current_tag = $this->param('tag') OR $current_tag = $this->param('tag_id'))
        {
            unset($this->_params['tag']);
            unset($this->_params['tag_id']);

            if (is_numeric($current_tag))
            {
                $ids[] = $current_tag;
            }
            else
            {
                $slugs[] = $current_tag;
            }
        }

        if (is_numeric($tag))
        {
            $ids[] = (int) $tag;
        }
        else
        {
            $slugs[] = $tag;
        }

        if ($ids)
        {
            if (count($ids) > 1)
            {
            }
            else
            {
                $this->param('tag_id', $ids[0]);
            }
        }
        elseif ($slugs)
        {
            if (count($slugs) > 1)
            {
            }
            else
            {
                $this->param('tag', $slugs[0]);
            }
        }

        return $this;
    }

    /**
     * tax_relation - Set the relationship between multiple taxonomy filters.
     *
     * @param string $rel = 'AND'
     * @return object
     */
    public function tax_relation($rel = 'AND')
    {
        if ($query = $this->param('tax_query'))
        {
            $query['relation'] = $rel;

            $this->param('tax_query', $query);
        }

        return $this;
    }

    /**
     * tax - Filter results by taxonomy.
     *
     * @param string|array $terms
     * @param string $tax
     * @param string $field = 'slug'
     * @param string $op = 'AND'
     * @return object
     */
    public function tax($terms, $tax, $field = 'slug', $op = 'AND')
    {
        // Get any meta query already existing.
        $query = (array) $this->param('tax_query');

        // Append an array with the parameters.
        $query[] = array(
            'taxonomy'  => $tax,
            'terms'     => $terms,
            'field'     => $field,
            'operator'  => $op,
        );

        // Set the meta_query parameter to pass onto WP_Query.
        $this->param('tax_query', $query);

        return $this;
    }

    /**
     * meta - Filter results by custom field values.
     *
     * @param string $key
     * @param string $compare = '='
     * @param string $value = NULL
     * @param string $type = 'CHAR'
     * @return void
     */
    public function meta($key, $compare = '=', $value = NULL, $type = 'CHAR')
    {
        // Get any meta query already existing.
        $query = (array) $this->param('meta_query');

        // Append an array with the parameters.
        $query[] = array(
            'key'       => $key,
            'value'     => $value,
            'compare'   => $compare,
            'type'      => $type,
        );

        // Set the meta_query parameter to pass onto WP_Query.
        $this->param('meta_query', $query);

        return $this;
    }

    /**
     * limit - Only allow certain amount of posts to show in the results pf
     * the query executed.
     *
     * @param unknown $limit = -1
     * @param unknown $paged = NULL
     * @return void
     */
    public function limit($limit = -1, $paged = NULL)
    {
        $this->param('posts_per_page', (int) $limit);

        if ($paged !== NULL)
        {
            $this->param('paged', (int) $paged);
        }

        return $this;
    }

    /**
     * orderby - Order results by a specified field.
     *
     * @param string $orderby = 'none'
     * @param string $order = 'ASC'
     * @return void
     */
    public function orderby($orderby = 'none', $order = 'ASC')
    {
        $this->param(array('orderby' => $orderby, 'order' => $order));

        return $this;
    }

    /**
     * total_pages 
     * 
     * @access public
     * @return void
     */
    public function total_pages()
    {
        $this->load();

        return (int)$this->_max_num_pages;
    }

    public function posts_per_page()
    {
        $this->load();

        return (int)$this->_posts_per_page;
    }

    /**
     * current_page 
     * 
     * @access public
     * @return void
     */
    public function current_page()
    {
        $this->load();

        return (int)$this->_current_page;
    }

    /**
     * serialize
     *
     * @return void
     */
    public function serialize()
    {
        $this->load();

        foreach (array('_data', '_executed') as $var)
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
        if ( ! $this->_executed)
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
        if ($this->use_query_posts)
            the_post();

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
        if ( ! $this->_executed)
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
        if ( ! $this->_executed)
        {
            $this->_load();
        }

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
