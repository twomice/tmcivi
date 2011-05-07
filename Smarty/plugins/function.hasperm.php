<?php

/**
 * $Id: function.hasperm.php 198 2010-08-14 01:38:16Z as $
 */


/*
* Smarty plugin
* -------------------------------------------------------------
* Type:     function
* Name:     hasperm
* Purpose:  checks whether current user has given permissions
* -------------------------------------------------------------
*

* Parameters
*    perm (Required) - name of the permission we're checking
*    perms - multiple perms, either as an array, or separated by $permsdelim
*    permsdelim - delimiter on which to explode $perms (defaults to the comma character)
*    assign (Required) - name of the template variable to which we'll assign the result of the test, either true or false
*    op (and|or): boolean operator to join multiple permissions; defaults to OR
*/

function smarty_function_hasperm($params, &$smarty) {

    extract( $params );

    if (empty($assign)) {
        // ref does not exists!
        TM_Util::trigger_error("plugin hasperm: Missing required parameter 'assign'.", TM_ERROR_ERROR);
        return;
    }

    if (empty($perm)) {
        if (empty($perms)) {
            // perm does not exists!
            TM_Util::trigger_error("plugin hasperm: Permission name needed.", TM_ERROR_ERROR);
            return;
        } else {
            if (is_array($perms)) {
                $permsArr = $perms;
            } else {
                if (empty($permsdelim)) {
                    $permsdelim = ',';
                }
                $permsArr = explode($permsdelim, $perms);
            }
        }
    } else {
        $permsArr = array($perm);
    }


    require_once 'CRM/Core/Permission.php';

    if (strtolower($op) == 'and') {
        $flagAllowed = TRUE;

        foreach ($permsArr as $perm) {
            // If this action is not permitted, just return $default
            if (!CRM_Core_Permission::check( $perm )) {
                $flagAllowed = FALSE;
                break;
            }
        }
    } else {
        $flagAllowed = false;

        foreach ($permsArr as $perm) {
            // If this action is not permitted, just return $default
            if (CRM_Core_Permission::check( $perm )) {
                $flagAllowed = true;
                break;
            }
        }
    }

    $smarty->assign( $assign,  $flagAllowed);

}

