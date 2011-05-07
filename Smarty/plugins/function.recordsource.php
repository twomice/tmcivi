<?php

/**
 * $Id$
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
    *  $sql: sql statement upon which dataset array will be based, must use placeholders for tablenames
    *  $t: encoded string for tablename placeholders, like so: "placeholder:tablename;placeholder2:tablename2"
    *  $multi_delim : delimiter of compounded sql statements. if given, $sql is exploded on the delimeter, and the parts exectued as
    *       separate sql statements in order; the final sql statement is processed for values just as a single sql statement would be.
    *  $name : if given, name of array added to template vars, containing query recordset data (if null, name="recordsource")
    *  $p[n] : SPECIAL: parameters named $p1, $p2, etc. are used as parameters to substitute for placeholders in sql. These are read
    *       in this order: p (or p0; these are the same), p1, p2, p3, etc.  Lower numbers must exist for higher numbers to be read
    *       (i.e., if there's no p3, the reading stops and higher numbers are not recognized).
    *
    *  SQL PARAMETERS
    *  $orderby: $sql .= 'order by ' . $orderby
    *  $groupby: $sql .= 'group by ' . $groupby (unless there's a value for $smarty->orderby)
    *  $offset (requires $rowcount): mysql select-syntax "offset" parameter (select ... limit [$offset,] $rowcount)
    *  $rowcount: mysql select-syntax "rowcount" parameter (select ... limt [$offset,] $rowcount)
    *
    *  GENERAL PARAMETERS:
    *  $multiple (true/false)
    *  $datasetsizelimit (int) Max number of records to return. If more than this number found, query fails with error. Default = 1000
    *  $allowlargedataset: if true, do not bail on datasets larger than $datasetsizelimit
    */
    function smarty_function_recordsource($args, &$smarty) {


        extract($args);

        $sql = trim($sql);

        // Replace tablenames in sql

        if (empty($t)) {
            TM_Util::trigger_error('Missing "t" parameter in recordsource plugin call.', TM_ERROR_ERROR);
        }
        $tables = explode(';', rtrim($t, ';'));
        if (!is_array($tables) || !count($tables)) {
            TM_Util::trigger_error('Missing or invalid "t" parameter in recordsource plugin call.', TM_ERROR_WARNING);
        }
        foreach ($tables as $table) {
            list($placeholder, $tableName) = explode(':',$table);
            $sql = str_replace($placeholder, $tableName, $sql);
        }

        if (!empty($groupby)) {
            $sql.= " GROUP BY $groupby";
        }

        if (!empty($orderby)) {
            $sql.= " ORDER BY $orderby";
        }

        // add in limits
        if ($rowcount) {
            if (is_int($rowcount)) {
                $sql .= " limit ";
                if ($offset) {
                    if (is_int($offset)) {
                        $sql .= " $offset, ";
                    }
                }
                $sql .= "$rowcount";
            }
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
        if (!empty($sql)) {
            if ($multiple === TRUE) {
                $db = TM_Db::get();
                $dataset = $db->get_rows($sql, $params);
            } else {
                $row = TM_Db::get_row($sql, $params);
                if ($row) {
                    $dataset[] = $row;
                }
            }
        }

        // Assign the query results
        if (!$dataset || count($dataset) == 0) {
            // Return with no result. (If this is a named item, be sure to unset that tpl_var in
            // order to achieve the same effect.
            if ($name) {
                unset($smarty->_tpl_vars[$name]);
            }
            return;
        } else {

            // In every case, pump the first-row values into template vars:
            foreach ($dataset[0] as $key => $value) {
                $smarty->assign($key, $value);
            }
        }

        if ($name) {
            $smarty->assign($name, $dataset);
        } else {
            $smarty->assign("recordsource", $dataset);
        }

}
