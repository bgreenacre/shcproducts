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

/**
 * Controller_Admin_FileImport 
 * 
 * @package shcproducts
 * @subpackage Admin
 * @category Controller
 */
class Controller_Admin_FileImport {

    public $errors = array();

    public function __construct()
    {
        add_action('admin_menu', array(&$this, 'action_admin_menu'));
    }

    public function action_admin_menu()
    {
        add_submenu_page('edit.php?post_type=shcproduct', __('Import Products from File'), __('File Import Products'), 'edit_posts', 'fileimport', array(&$this, 'action_index'));
    }

    public function action_index()
    {
        $method = 'action_' . SHCP::get($_GET, 'part', 'upload_form');

        if (method_exists($this, $method))
        {
            $this->{$method}();
        }
        else
        {
        }
    }

    public function action_upload_form()
    {
        $data = array(
            'upload_size'   => NULL,
            'unit'          => 'KB',
            'errors'        => $this->errors,
        );

        $upload_size_unit = $max_upload_size =  wp_max_upload_size();
        $sizes = array( 'KB', 'MB', 'GB' );

        for ( $u = -1; $upload_size_unit > 1024 && $u < count( $sizes ) - 1; $u++ )
        {
            $upload_size_unit /= 1024;
        }

        if ( $u < 0 ) {
            $upload_size_unit = 0;
            $u = 0;
        } else {
            $upload_size_unit = (int) $upload_size_unit;
        }

        $data['upload_size'] = $upload_size_unit;
        $data['unit'] = $sizes[$u];

        echo SHCP::view('admin/fileimport/index', $data);
    }

    public function action_review()
    {
        if ( ! isset($_GET['filename']))
        {
            $this->errors[] = __('There is no file uploaded. Please upload an import file.');
        }

        $import = FileImport::factory($_GET['filename']);

        $data = array(
            'rows'  => $import->load(),
        );

        echo SHCP::view('admin/fileimport/review', $data);
    }

    public function action_upload()
    {
        if ($_FILES)
        {
            $upload_dir = realpath(wp_upload_dir()) . DIRECTORY_SEPARATOR . 'import' . DIRECTORY_SEPARATOR;

            if ( ! is_dir($upload_dir))
            {
                mkdir($upload_dir, '0644');
                @chmod($upload_dir, '0644')
            }

            // Is there file uploaded and without errors?
            $not_empty = (isset($_FILES['upload_file']['error'])
                AND isset($_FILES['upload_file']['tmp_name'])
                AND is_uploaded_file($_FILES['upload_file']['tmp_name']));

            if ( ! $not_empty)
            {
                $this->errors[] = __('No file was uploaded or an error occured during upload.');
                $this->action_upload_form();
                return;
            }

            if ($_FILES['upload_file']['error'] === UPLOAD_ERR_INI_SIZE)
            {
                $this->errors[] = __('The uploaded file is too large.');
                // Upload is larger than PHP allowed size (upload_max_filesize)
                $this->action_upload_form();
                return;
            }

            if ($_FILES['upload_file']['error'] !== UPLOAD_ERR_OK)
            {
                $this->errors[] = __('An error occured during upload.');
                // The upload failed, no size to check
                $this->action_upload_form();
                return;
            }

            // Test that the file is greater than the max size
            if ($_FILES['upload_file']['size'] > wp_max_upload_size())
            {
                $this->errors[] = __('The uploaded file is too large.');
                $this->action_upload_form();
                return;
            }

            $ext = strtolower(pathinfo($_FILES['upload_file']['name'], PATHINFO_EXTENSION));

            if ( ! in_array($ext, array('csv', 'xml', 'json')))
            {
                $this->errors[] = __('Invalid file type uploaded. Only csv, xml, or json files are allowed.');
                $this->action_upload_form();
                return;
            }

            $filename = uniqid().$_FILES['upload_file']['name'];
            $filename = preg_replace('/\s+/u', '_', $filename);

            if (is_file($upload_dir.$filename))
            {
                @unlink($upload_dir.$filename);
            }

            if (move_uploaded_file($_FILES['upload_file']['tmp_name'], $upload_dir.$filename))
            {
                // Set permissions on filename
                chmod($upload_dir.$filename, '0644');

                // File has been uploaded and now needs to be reviewed.
                wp_redirect(admin_url('edit.php?post_type=shcproduct&page=fileimport&part=review&filename='.urlencode($filename)));
            }
            else
            {
                $this->errors[] = __('Error writing uploaded file to disk. Please try again.');
            }
        }
        else
        {
            $this->errors[] = __('No file was uploaded.');
        }

        $this->action_upload_form();
    }

}
