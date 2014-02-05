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

class Controller_Crons_Products {
	
	/**
	 * _activity_log - contains update and delete activity
	 * 
	 * @var array 
	 * @access protected
	 * @since Wednesday, October 24 2012
	 */
	protected $_activity_log = null;
	
	/**
	 * _profile_log - contains profile activity
	 * 
	 * @var array
	 */
	protected $_profile_log = null;
	
	/**
	 * _log_dir
	 * 
	 * @var string - Log Directory name
	 * @access protected 
	 * @since Wednesday, October 24 2012
	 */
	protected $_log_dir;
	
	/**
	 * _log_root_dir_path - the root directory where log files are kept
	 *  
	 * @var protected
	 * @access protected
	 * @since Wednesday, October 24 2012
	 */
	protected $_log_root_dir_path = '/appl/wordpress/log/';
	
	/**
	 * _log_dir_path
	 * 
	 * @var string
	 * @access protected
	 * @since Wednesday, October 24 2012
	 */
	protected $_log_dir_path;
	
	/**
	 * _log_file - log file name
	 * 
	 * @var string
	 * @access protected
	 * @since Wednesday, October 24 2012
	 */
	protected $_log_file;
	
	/**
	 * _blog_name - file/directory safe name used in log directory name and file name
	 * 
	 * @var string
	 * @access protected
	 * @since Wednesday, October 24 2012
	 */
	protected $_blog_name;
	
	/**
	 * _old_logs - array of files (full path) to be deleted
	 * 
	 * @var array
	 * @access protected
	 * @since Wednesday, October 24 2012
	 */
	protected $_old_logs = null;
	
	/**
	 * _num_deleted
	 * 
	 * @var int
	 * @access protected
	 * @since Wednesday, October 24 2012
	 */
	protected $_num_deleted = 0;
	
	/**
	 * _num_updated
	 * 
	 * @var int
	 */
	protected $_num_updated = 0;
	
	/**
	 * _num_draft
	 * 
	 * @var int
	 */
	protected $_num_draft = 0;
	
	/**
	 * _num_no_action
	 * 
	 * @var int
	 */
	protected $_num_no_action = 0;
	
	/**
	 * _num_posts - total number of products
	 * 
	 * @var int
	 */
	protected $_num_posts;
	
	/**
	 * _status
	 * 
	 * @var int 
	 * @access protected
	 * @since Wednesday, October 24 2012
	 */
	protected $_status = 'Success';
	
	/**
	 * _fail_threshold_pct - percent of total posts to 
	 * use as fail threchold for 'deleted products'
	 * 
	 * @var float
	 * @access protected
	 * @since Wednesday, October 24 2012
	 */
	protected $_fail_threshold_pct = .75;
	
	/**
	 * _fail_threshold_cnt - the threshold number of 'deleted' 
	 * posts to trigger the halting of updates
	 * 
	 * @var bool|int
	 */
	protected $_fail_threshold_cnt = false;
	
	/**
	 * _force_update - Ignore threshold and force updates to run.
	 * 
	 * @var bool
	 */
	protected $_force_update = false;
	
	/**
	 * _profile_mode - If job fails due to hitting delete threshold, this
	 * gets set to true and job will finish logging, but no updates/deletes will be 
	 * performed on products.
	 * 
	 * @var bool
	 * 
	 */
	protected $_profile_mode = false;
	
	
	/**
	 * _is_manual_update - Keep track of whether update began via cron job or the admin panel.
	 * 
	 * @var bool
	 */
	protected $_is_manual_update = false;
	
	/**
	*
	* @var int
	*/
	protected $notice_count = 0;
	protected $warning_count = 0;
	protected $total_category_count = 0;
	protected $total_products_imported = 0;
	protected $total_already_imported = 0;
	protected $total_categorized = 0;
	protected $total_skipped_invalid = 0;
	
	protected $total_product_count = 0;
	protected $total_products_updated = 0;
	protected $total_products_deleted = 0;
	protected $total_products_no_action = 0;
	protected $total_products_set_to_draft = 0;
	
	/**
	 * Constructor - Sets initial properties
	 * 
	 * @param void
	 * @return void
	 * @since Wednesday, October 24 2012
	 */
	public function __construct() {
		
		$this->_blog_name = str_replace(' ', '_', trim(strtolower(get_bloginfo('name'))));
		$this->_log_file =  $this->_blog_name . '-' . date('Ymd');
		$this->_log_dir = $this->_blog_name . DIRECTORY_SEPARATOR;
		$this->_log_dir_path = $this->_log_root_dir_path . $this->_log_dir;
		
	}
	
	protected function _action_sync_categories() {
		$categories = get_full_categories();
		$category_array = array();
		foreach($categories as $category_id => $category_name) {
			$this->total_category_count++;
		
			$category_name = str_replace('&rarr;', '--', $category_name);
		
			$this->log_message('');
			$this->log_message('ID #'.$category_id.' ('.$category_name.')');
		
			$cat_obj = new Product_Category_Model($category_id);
			$cat_sync_outcome = $cat_obj->sync();
						
			if(is_array($cat_sync_outcome) && !empty($cat_sync_outcome)) {
				$reverse_array = array();
				foreach($cat_sync_outcome as $part_number => $outcome) {
					if(!isset($reverse_array[$outcome])) {
						$reverse_array[$outcome] = array($part_number);
					} else {
						$reverse_array[$outcome][] = $part_number;
					}
					// Increment stats based on outcome:
					if(strpos($outcome,'Already imported and in category') !== false){
						$this->total_already_imported++;
					}
					if(strpos($outcome,'Not imported') !== false){
						$this->total_skipped_invalid++;
					}
					if(strpos($outcome,'adding to category') !== false){
						$this->total_categorized++;
					}
					if(strpos($outcome,'Successfully imported') !== false){
						$this->total_products_imported++;
					}
				}
				foreach($reverse_array as $outcome => $part_numbers) {
					$this->log_message('-- '.trim($outcome,' .').': '.implode(', ', $part_numbers));
				}
			} else {
				$this->log_message('No operations performed.');
			}
			// Process notices & warnings for category sync:
			$notices = $cat_obj->notices;
			if(is_array($notices) && !empty($notices)) {
				$notice_count = count($notices);
				$s = ($notice_count == 1) ? '' : 's';
				$this->log_message($notice_count.' notice'.$s.':');
				foreach($notices as $notice) {
					$this->log_message('-- '.$notice);
					$this->notice_count++;
				}
			}
			$warnings = $cat_obj->warnings;
			if(is_array($warnings) && !empty($warnings)) {
				$warning_count = count($warnings);
				$s = ($warning_count == 1) ? '' : 's';
				$this->log_message($warning_count.' warning'.$s.':');
				foreach($warnings as $warning) {
					$this->log_message('-- '.$warning);
					$this->warning_count++;
				}
			}
		}
	}
	
	protected function _action_update_products($posts) {
		$outcome_array = array(
			'Successfully Updated' => array()
		);
		
		foreach ($posts as $key => $post) {
			$this->total_product_count++;
		
			$prod_obj = new Product_Post_Model($post->ID);
			$outcome = $prod_obj->sync_from_api();

			if($outcome !== true) {
				if(!isset($outcome_array[$outcome])) {
					$outcome_array[$outcome] = array($post->ID);
				} else {
					$outcome_array[$outcome][] = $post->ID;
				}
			} else {
				$outcome_array['Successfully Updated'][] = $post->ID;
			}
			
			if($prod_obj->is_updated) {
				$this->total_products_updated++;
			}
			if($prod_obj->is_deleted) {
				$this->total_products_deleted++;
			}
			if($prod_obj->no_action) {
				$this->total_products_no_action++;
			}
			if($prod_obj->is_draft) {
				$this->total_products_set_to_draft++;
			}
		}
		
		// Log outcomes:
		foreach($outcome_array as $key => $value_array) {
			if(!empty($value_array)) {
				$this->log_message('');
				$this->log_message(trim($key,'. ').': '.implode(', ',$value_array));
			}
		}
	}

    public function action_update($force = false) {
    
		set_time_limit(0);
    	
    	$this->elapsed_time_start = microtime(true);
    	
    	// Grab list of posts before performing any imports:
		global $wpdb;
		$sql = "SELECT ID FROM ".$wpdb->prefix."posts WHERE post_type = 'shcproduct' AND (post_status = 'publish' OR post_status = 'draft') ORDER BY post_modified ASC";
		$posts = $wpdb->get_results($sql);
    	    	
		// Step 1: Loop through categories and import products as needed:
		$this->_action_sync_categories();
		
		// Step 2: Loop through posts and update / delete products as needed:
		$this->_action_update_products($posts);
				
		$this->elapsed_time_end = microtime(true);
		
		// Step 3: Email the report:
		$this->mail_report();
    }
    
    /**
     * 
     * log_delete - Adds a delete event to _activity_log
     * 
     * @param object $post
     * @return void
     * @since Wednesday, October 24 2012
     */
    protected function log_delete($post) {
    	
    	$msg = "SET TO DRAFT -- ID: {$post->ID} TITLE: {$post->post_title} \n";
    	
    	if(! $this->_profile_mode) {
    		
    		$this->_activity_log[] = $msg;
    		$this->_num_deleted++;
    		
    	} else {
    		
    		$this->_profile_log[] = $msg;
    	}
    }
    
    /**
     * 
     * log_update - Adds an update event to _activity_log
     * 
     * @param object $post
     * @return void
     * @since Wednesday, October 24 2012$this->_num_posts = count($posts);
    	$this->_fail_threshold_cnt = round($this->_num_posts * $this->_fail_threshold_pct);
     */
    
    protected function log_update($post) {
    	
    	$msg = "UPDATED -- ID: {$post->ID} TITLE: {$post->post_title} \n";
    	
    	if(! $this->_profile_mode) {
    		
    		$this->_activity_log[] = $msg;
    		$this->_num_updated++;
    		
    	} else {
    		
    		$this->_profile_log[] = $msg;
    	}
    	
    }
    
    
    // Log all types of messages:
    protected function log_message($text) {
    	$text = html_entity_decode($text);
    	$this->_activity_log[] = $text."\n";
    }
    
    
    protected function log_job_fail($reason) {
    	
    	$this->_status = 'Fail';
    	
    	$this->_activity_log[] = $reason;
    	
    }
    
    /**
     *
     * create_log - creates log directory
     * 
     * @param void
     * @return void
     */
    protected function create_log() {
    	
    	$file = $this->_log_dir_path . $this->_log_file; 
    	
    	//Check if log directory exists, if not create it...
    	if(! is_dir($this->_log_dir_path)) {
    		
    		mkdir($this->_log_dir_path, 0755);
    	}
    	
    	//Create the log file
    	if(! file_exists($file)) {
    		
    		$f = fopen($file, 'w');
    		
    		$log_body = "Job Status: {$this->_status} \nTotal Products: {$this->_num_posts}\nProducts Updated: {$this->_num_updated} \nProducts Set to Draft: {$this->_num_draft}\nProducts Permanently Deleted: {$this->_num_deleted}\nNo Action Taken: {$this->_num_no_action} \n\n" . $this->log_to_string($this->_activity_log);
    		
    		if($this->_profile_mode) {
    			
    			$log_body .= '\n\n PROFILE MODE -- The following updates were NOT performed: \n\n' . $this->log_to_string($this->_profile_log);
    		}
    		
    		$log_body = strip_tags($log_body);
    		fwrite($f, $log_body);
    		
    		fclose($f);
    	}
    	
    	
    }
    
    /**
     * mail_report
     * 
     * @param void
     * @return void
     */
    protected function mail_report() {
    	
    	$to = 'phpteam@searshc.com';
    	$subject = 'SHC Products Update for ' . $this->_blog_name;
    	
    	$body =  "Product update completed on: " . date('Y-m-d h:i:s a T') ."\n";
    				
		$elapsed_time = $this->elapsed_time_end - $this->elapsed_time_start;
		$elapsed_time_minutes = $elapsed_time / 60;
		$elapsed_time_minutes = number_format($elapsed_time_minutes,2);
		$elapsed_time = number_format($elapsed_time,3);
		$body .= "Elapsed Time: ".$elapsed_time." seconds (".$elapsed_time_minutes." minutes)\n\n";
		
		$body .= "Summary:\n\n";
		
		// Category sync summary:
		$body .= "Total Categories: {$this->total_category_count}\n";
		$body .= "New Products Imported: {$this->total_products_imported}\n";
		$body .= "Existing Products Categorized: {$this->total_categorized}\n";
		$body .= "Already Imported: {$this->total_already_imported}\n";
		$body .= "Skipped / Invalid: {$this->total_skipped_invalid}\n";
		if($this->notice_count > 0) $body .= "Notices: {$this->notice_count}\n";
		if($this->warning_count > 0) $body .= "Warnings: {$this->warning_count}\n";
		
		// Product update summary:
		$body .= "\n";
		$body .= "Total Products: {$this->total_product_count}\n";
		$body .= "Successfully Updated: {$this->total_products_updated}\n";
		$body .= "Permanently Deleted: {$this->total_products_deleted}\n";
		$body .= "Set To Draft: {$this->total_products_set_to_draft}\n";
		$body .= "No Action: {$this->total_products_no_action}\n";
		    		
    	$log_string = $this->log_to_string($this->_activity_log);
    	
    	$body .= "\n".$log_string;	
    	$body = nl2br($body);
    		
		// Make it an HTML message with some light formatting:
		$headers = array();
		$headers[] = 'Content-Type: text/html';
		$body = '<div style="font-size:13px;">'.$body.'</div>';

    	wp_mail($to, $subject, $body, $headers);
    }
    
    /**
     * array_to_string - converts array to string for printing
     * 
     * @param void
     * @return void
     */
    protected function log_to_string($log) {
    	
    	$out = '';
    	
    	if(!is_array($log) || empty($log)) return '';
    	
    	foreach($log as $entry) {
    		
    		$out .= $entry;
    	}
    	
    	return $out;
    }
   
    /**
     * rotate_logs - Deletes logs greater than 30 days old
     * 
     * @param void
     * @return void
     */
    protected function rotate_logs() {
    	
    	if(is_dir($this->_log_dir_path)) {
    		
    		if($dir = opendir($this->_log_dir_path)) {
    			
    			$this->get_old_logs($dir);
    			
    			$this->delete_old_logs();
    			
    		}
    	}
    }
    
    /**
     * get_old_logs -- populates _old_logs with path to logs greater than 30 days old
     * 
     * @param resource $dir_handle
     * @return void
     */
    protected function get_old_logs($dir_handle) {
    	
    	$sec_thirtydays = (time() - (30 * 86400));
    	
    	if(is_resource($dir_handle)) {
    		
    		if($files = scandir($this->_log_dir_path)) {
    			
    			foreach($files as $file) {
    				
    				$curr_file = $this->_log_dir_path . $file;
    				
    				if(is_file($curr_file)) {
    					
    					if(($ftime = filectime($curr_file)) && ($ftime <= $sec_thirtydays)) {
    						
    						$this->_old_logs[] = $curr_file;
    					}
    				}
    			}
    		}
    	}
    	
    }
    
    /**
     * delete_old_logs - loops thru _old_logs and deletes them 
     * 
     * @param void
     * @return void
     */
    protected function delete_old_logs() {
    	
    	if($this->_old_logs) {
    		
    		foreach($this->_old_logs as $file) {
    			
    			unlink($file);
    		}
    	}
    }
    
    /**
     * is_api_available -- Pings API server to check for a HTTP 200
     * 
     * @param void
     * @return bool
     */
    protected function is_api_available() { 
    	
    	$search = Library_Sears_Api::factory('search')
						            ->cache(FALSE)
						            ->keyword('test')
						            ->load();
						            
       return ($search->http_code == '200') ? true : false;
    }
    
    
    /**
     * is_manual_update -- Set flag to indicate that update began in the admin panel.
     * 
     * @param void
     * @return bool
     */
    public function is_manual_update() { 
    	$this->_is_manual_update = true;
    }
    
    
    /**
     * fail_job -- steps to fail job, create log and email notice
     * 
     * @param string $reason
     * @return void
     */
    protected function fail_job($reason) {
    	
    	//Turn on profile mode
    	$this->_profile_mode = true;
    	
    	$this->log_job_fail($reason);
    		
    	
    }
    
    /**
     * set_threshold - Sets _fail_threshold_cnt 
     * 
     * @param array $posts
     * @return void
     */
    protected function set_threshold($posts) {
    	
    	if(! $this->_force_update){
    		
    		$this->_num_posts = count($posts);
    		$this->_fail_threshold_cnt = round($this->_num_posts * $this->_fail_threshold_pct);
    	} else {
    		// Prevent blank value for Total Products in mail report when initiated via the admin (force update):
    		$this->_num_posts = count($posts);
    	}
    	
    }
    
}
