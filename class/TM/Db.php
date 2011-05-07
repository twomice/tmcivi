<?php
/**
 * Project:     TwoMice CiviCRM modifications and custom features (for client: GPF)
 * $Id$
 */

/**
 * Drupal module file.
 *
 * @package TM_CiviCRM
 *
 */

define('TM_DB_DATASET_SIZE_LIMIT', 1000);

class TM_Db {

    private $get_rows_keyed = FALSE;
    var $allow_large_dataset = FALSE;
    
    private function __construct() {

    }

    public static function get() {
        static $db = NULL;
        if ($db == NULL ){
            $db = new TM_Db;
        };
        return $db;
    }

    public static function get_value($query) {
        $args = func_get_args();
        $result = call_user_func_array('tm_query', $args);
        $row = db_fetch_array($result);
        if (!$row) {
            return FALSE;
        }
        $rowValues = array_values($row);
        return $rowValues[0];
    }

    public function get_rows($query) {
        if (!$this) {
            TM_Util::trigger_error( __METHOD__ .' cannot be called statically.', TM_ERROR_ERROR);
            return FALSE;
        }

        $args = func_get_args();

        $result = call_user_func_array('tm_query', $args);

        if ($result === FALSE || $result->num_rows == 0) {
            return FALSE;
        }

        if ($result->num_rows > TM_DB_DATASET_SIZE_LIMIT && ! $this->allow_large_dataset ) {
            dsm(debug_backtrace());
            TM_Util::trigger_error('Query exceeded maximum allowed rows.  Please adjust query to preserve system resources.', TM_ERROR_ERROR, TRUE, "rows found: {$result->num_rows}; max limit: ". TM_DB_DATASET_SIZE_LIMIT .";");
            return;
        }

        // return this to FALSE again, so calling function doesn't have to
        $this->allow_large_dataset = FALSE;

        while ($row = db_fetch_array($result)) {
            if ( $this->get_rows_keyed ) {
                $vals = array_values($row);
                $rowKey = $vals[0];
                $rows[$rowKey] = $row;
            } else {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    public function get_rows_keyed($query) {
        if (!$this) {
            TM_Util::trigger_error( __METHOD__ .' cannot be called statically.', TM_ERROR_ERROR);
            return FALSE;
        }
        $this->get_rows_keyed = TRUE;

        $args = func_get_args();
        $rows = call_user_func_array(array($this,'get_rows'), $args);

        $this->get_rows_keyed = FALSE;

        return $rows;
    }

    /**
     * Fetch array of all values in the first column in all matching records of given query
     */
    public function get_column($query) {
        if (!$this) {
            TM_Util::trigger_error( __METHOD__ .' cannot be called statically.', TM_ERROR_ERROR);
            return FALSE;
        }

        $args = func_get_args();
        $rows = call_user_func_array(array($this,'get_rows'), $args);
        if ($rows === FALSE) {
            return FALSE;
        }

        foreach (array_values($rows) as $row) {
            $row = array_values($row);
            $result[] = $row[0];
        }

        return $result;
    }

    public static function get_row($query) {
        $args = func_get_args();

        $result = call_user_func_array('tm_query', $args);

        return db_fetch_array($result);
    }
}
