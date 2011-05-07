<?php

/**
 * $Id: function.sessionvar.php 371 2010-09-25 20:17:12Z as $
 */


/*
* Smarty plugin
* -------------------------------------------------------------
* Type:     function
* Name:     hasperm
* Purpose:  get or set a session variable
* -------------------------------------------------------------
*

* Parameters
*    var (Required) - name of the var we're dealing with
*    assign - if provided, $var's value will be stored in this template variable; otherwise value will be echoed.
*    default - if provided, and if named session var has not been set, this value will be returned instead.
*/

function smarty_function_sessionvar($params, &$smarty) {

    extract( $params );

    if (empty($var)) {
        // var does not exists!
        TM_Util::trigger_error("plugin sessionvar: Missing required parameter 'var'.", TM_ERROR_ERROR);
        return;
    }


    $ret = TM_Util::get_session_var($var, $default);

    if (!empty($assign)) {
        $smarty->assign($assign, $ret);
    } else {
        return $ret;
    }

}

