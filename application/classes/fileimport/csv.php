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

class FileImport_Csv extends FileImport {

    public function load(array $data = NULL, $use_header = TRUE)
    {
        $this->_position = 0;
        $this->_total_rows = 0;

        if ($file = @fopen(FileImport::$path.$this->filename, 'r'))
        {
            while ($row = fgetcsv($file, 0, ',', '"'))
            {
                if ($use_header AND $this->_total_rows == 0)
                {
                    $this->cols = $row;
                }
                else
                {
                    $this->_data[] = $row;
                    ++$this->_total_rows;
                }
            }
        }

        return $this;
    }

}
