<?php

/**
 * $Id$
 */


    /*
    * Smarty plugin
    * -------------------------------------------------------------
    * Type:     function
    * Name:     dsm
    * Purpose:  wrapper for Drupal6 dsm() function (devel module)
    * -------------------------------------------------------------
    *

    * Parameters
    *    var = the variable to dump
    */
    function smarty_function_dsm($params, &$smarty) {
        if (!array_key_exists('var', $params)) {
            // ref does not exists!
            $smarty->trigger_error("Plugin dsm: missing required parameter 'var'. (". var_export($params, true) .")");
            return;
        }

        if(!function_exists('dsm')) {
            $smarty->trigger_error("Plugin dsm: function dsm() is not available.  Is devel module enabled?");
            return;
        }

        dsm($params['var']);
    }