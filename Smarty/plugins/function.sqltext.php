<?php

/**
 * $Id: function.sqltext.php 417 2010-10-06 04:05:53Z as $
 */


    /*
    * Smarty plugin
    * -------------------------------------------------------------
    * Type:     function
    * Name:     recordsource
    * Purpose:  Execute query given through sql attibute
    * -------------------------------------------------------------
    *
    * Parameters:
    *  $sql: sql statement
    *  $t: encoded string for tablename placeholders, like so: "placeholder:tablename;placeholder2:tablename2"
    *  $p[n] : SPECIAL: parameters named $p1, $p2, etc. are used as parameters to substitute for placeholders in sql. These are read
    *       in this order: p (or p0; these are the same), p1, p2, p3, etc.  Lower numbers must exist for higher numbers to be read
    *       (i.e., if there's no p3, the reading stops and higher numbers are not recognized).
    *
    *    delimiter:    string to join parts
    *    default:    return value if no records are found
    *    name:        if given, the return value will be stored in this named variable,
    *                  rather than being returned as a string.

    */
 function smarty_function_sqltext($args, &$smarty) {
      //extract function parameters

        extract($args);

        if (!$sql) {
            $smarty->trigger_error("sqltext: missing required parameter 'sql'.");
        }
        if (!isset($field)) {
            $field = '0';
        }

        $sql = trim($sql);

        // Replace tablenames in sql

        if (empty($t)) {
            TM_Util::trigger_error('Missing "t" parameter in sqltext plugin call.', TM_ERROR_ERROR);
        }
        $tables = explode(';', rtrim($t, ';'));
        if (!is_array($tables) || !count($tables)) {
            TM_Util::trigger_error('Invalid "t" parameter in sqltext plugin call.', TM_ERROR_WARNING);
        }
        foreach ($tables as $table) {
            list($placeholder, $tableName) = explode(':',$table);
            $sql = str_replace($placeholder, $tableName, $sql);
        }

        // get sql reqplacement parameters
        if (isset($p0)) {
            $params[0] = $p0;
        } elseif (isset($p)) {
            $params[0] = $p;
        }
        $i = 1;
        $varname = "p{$i}";
        while (isset($$varname)) {
            $params[$i] = $$varname;
            $i++;
            $varname = "p{$i}";
        }

        // Run the query

        // If there's a delimiter, we need to support multi-row results.
        if (!empty($delimiter)) {
            $db = TM_Db::get();
            $rows = $db->get_rows($sql, $params);
            if ($rows) {
                $result = implode($delimiter, $rows);
            }
        } else {
            $value = TM_Db::get_value($sql, $params);
            if ($value === FALSE ) {
                $result = $default;
            } else {
                $result = $value;
            }
        }

        if ( isset( $name ) && $name ) {
            $smarty->assign($name, $result);
        } else {
            return $result;
        }

}