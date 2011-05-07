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

class TM_Custom {

    /**
     * Get system metadata for custom data fields, based on field label or basename
     * (Base name is the column name without the _nn suffix. e.g, basename for
     * column "my_column_99" is "my_column")
     * @param string $type 'label' or 'base'
     * @param string|array $keys the key(s) to search for, of the type given in $type.
     *      Either a single value as a string, or an array of values.
     * @return array|boolean Array of matching columns found, keyed to values given in $keys; or FALSE if no matches were found.
     */
    static function getCustomFields($type, $keys) {
        global $active_db;

        $keys = (array)$keys;
        
        if (empty($keys)) {
            // if there are no keys, just return false;
            return FALSE;
        }

        $db = TM_Db::get();
        if ($type == 'base') {
            $likes = array();
            foreach ($keys as $key) {
                $likes[] = "column_name like '". mysqli_real_escape_string( $active_db, $key) ."_%'";
            }
            $where = '('. implode($likes, ' OR ') .')';
            $rows = $db->get_rows(
                "select cf.*, cg.table_name from {civicrm_custom_field} cf
                INNER JOIN {civicrm_custom_group} cg on cg.id = cf.custom_group_id
                WHERE $where
                AND cf.is_active = 1 and cg.is_active = 1"
            );
            if (!is_array($rows)) {
                return FALSE;
            }
            // Cycle through and key the rows.
            foreach ($rows as $row) {
                // Have to calculate the basename, by stripping _nn suffix
                $key = substr($row['column_name'], 0, strrpos($row['column_name'], '_'));
                $newRows[$key] = $row;
            }
            // replace $rows with keyed rows
            $rows = $newRows;

        } elseif ($type == 'label') {
            $rows = $db->get_rows_keyed(
                "select label as key, cf.*, cg.table_name from {civicrm_custom_field} cf
                INNER JOIN {civicrm_custom_group} cg on cg.id = cf.custom_group_id
                WHERE label in (". db_placeholders($keys, 'text') .")
                AND cf.is_active = 1 and cg.is_active = 1",
                $keys
            );
            if (!is_array($rows)) {
                return FALSE;
            }
            /* We had to include label as first column, aliased 'key', so now remove
             * that value in each row, so it's not confusing anyone.
             */
            foreach ($rows as &$row) {
                unset($row['key']);
            }
        }
        
        return $rows;
    }
}
