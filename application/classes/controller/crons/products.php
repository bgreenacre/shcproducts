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

    public function action_update($force = false)
    {set_time_limit(0);
    	//Only proceed if the API server is reachable
    	
    	$this->elapsed_time_start = microtime(true);
    	    	
    	if($this->is_api_available()) {
    		
    		
    		if($force) {
    			
    			$this->_force_update = true;
    		}
    		
    		global $wpdb;
    		$sql = "SELECT ID FROM wp_posts WHERE post_type = 'shcproduct' AND (post_status = 'publish' OR post_status = 'draft') ORDER BY post_modified ASC";
    		$posts = $wpdb->get_results($sql);
    		            
            $this->set_threshold($posts);
           
	        foreach ($posts as $key=>$post)
	        {
	        	//If the number of 'deletes' equals threshold, fail job.
	        	if((!$this->_force_update && !$this->_forceupdate_override) && ($this->_num_deleted >= $this->_fail_threshold_cnt) && (! $this->_profile_mode)) {
	        		
	        		$this->fail_job("\n\n WARNING! -- Maximum number of deletes reached ({$this->_fail_threshold_cnt}). Job aborted.");
	        		
	        	}
	        	
	        	if(isset($post->ID) && !empty($post->ID) && is_numeric($post->ID)) {
	        	
					// Run update:
					$model_post = new Model_Products($post->ID); 
					$model_post->sync_from_api($this->_profile_mode);
				
					// Log the outcome (for email report / log file):
					$this->log_message($model_post);
					   
					unset($model_post);
				
					wp_cache_flush();
	            }
	        }
	        
	        $this->elapsed_time_end = microtime(true);
	        
	        //Create Log file
	        $this->create_log();
	        
	        //Rotate out logs that are older than 30 days /appl/wordpress/log/
	        $this->rotate_logs();
	        
	        //Mail report
	        $this->mail_report();
	        
        
         } else {
    		
    		//Fail Job
    		$this->fail_job('WARNING! -- API Server was NOT available. Job aborted.');
    		
    		$this->create_log();
    		
    		$this->rotate_logs();
    		
    		$this->mail_report();
    
    	}
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
    
    
    // Log all types of messages, depending on what was set in the post object, with optional formatting.
    protected function log_message($post) {
    	$msg = $post->cron_msg."\n";
    	if(empty($msg)) return;
    	if(! $this->_profile_mode) {
    		
    		if($post->is_updated) {
    			$this->_num_updated++;
    		}
    		if($post->is_deleted) {
    			$this->_num_deleted++;
    			$msg = '<span style="color:#FF0000;"><b>'.$msg.'</b></span>';
    		} 
    		if($post->is_draft) {
    			$this->_num_draft++;
    			$msg = '<span style="color:#ff6c00;"><b>'.$msg.'</b></span>';
    		}  
    		if($post->no_action) {
    			$this->_num_no_action++;
    			$msg = '<span style="color:#FF0000;background-color:#FFFF00;"><b>'.$msg.'</b></span>';
    		}  		
    		$this->_activity_log[] = $msg;
    	} else {
    		$this->_profile_log[] = $msg;
    	}
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

    	$memory_usage = memory_get_peak_usage();
		$memory_usage = $memory_usage / 1048576;
		$memory_usage = number_format($memory_usage,3);
		$body .= "Peak Memory Usage: ".$memory_usage." MB \n";
			
		$elapsed_time = $this->elapsed_time_end - $this->elapsed_time_start;
		$elapsed_time = number_format($elapsed_time,3);
		$body .= "Elapsed Time: ".$elapsed_time." seconds \n\n";
		
    	if($this->_activity_log) {
    		$status = "Job Status: <b>{$this->_status}</b> \n";
    		$total = "Total Products: {$this->_num_posts}\n";
    		$updated = "Products Updated: {$this->_num_updated} \n";
    		$draft = "Products Set to Draft: {$this->_num_draft}\n";
    		$deleted = "Products Permanently Deleted: {$this->_num_deleted}\n";
    		$no_action = "No Action Taken: {$this->_num_no_action} \n";
    		
    		if($this->_num_draft > 0) $draft = '<span style="color:#ff6c00;"><b>'.$draft.'</b></span>';
    		if($this->_num_deleted > 0) $deleted = '<span style="color:#ff0000;"><b>'.$deleted.'</b></span>';
    		if($this->_num_no_action > 0) $no_action = '<span style="color:#FF0000;background-color:#FFFF00;"><b>'.$no_action.'</b></span>';
    		
    		$log_string = $this->log_to_string($this->_activity_log);
    		
    		$body .= $status.$total.$updated.$draft.$deleted.$no_action."\n".$log_string;
    		
    		
    		
    	} else {
    		$body .= 'No products were updated or set to draft.';
    	}
    	
    	if($this->_profile_mode) {
    		
    		$body .= "\n CHANGES NEEDED TO BE PERFORMED: \n\n" . $this->log_to_string($this->_profile_log);
    	}

		// Make it an HTML message with some light formatting (highlight certain errrors, etc):
		$headers = array();
		$headers[] = 'Content-Type: text/html';
		$body = '<div style="font-size:13px;">'.$body.'</div>';
    	wp_mail($to, $subject, nl2br($body), $headers);
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
