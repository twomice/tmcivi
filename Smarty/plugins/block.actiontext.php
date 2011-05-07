<?php

/**
 * $Id: block.actiontext.php 198 2010-08-14 01:38:16Z as $
 */


/*
* Smarty plugin
* -------------------------------------------------------------
* File:     block.actiontext.php
* Type:     block
* Name:     actiontext
* Purpose:  Displays block of text according to the permission of one or more actions. For multiple
*               actions, the text is displayed if any of the actions are allowed.
*
* Params:
*    default: string to return if perms fail
*    ref:     actionname value for given action
*    refs:    multiple refs, either as an array, or separated by $refsdelim
*    refsdelim: delimiter on which to explode $refs value (defaults to the space character)
*    args:  arguments that would be used in the action link; required (or $noargs) for sound ACL checking
*    noargs:  TRUE if args (and thus ACL checking) are not applicable to this actiontext block
*
* -------------------------------------------------------------
*/
function smarty_block_actiontext($params, $content, &$smarty) {
    if ($content) {
        extract($params);

        if (empty($ref)) {
            if (empty($refs) || (empty($args) && !$noargs)) {
                // ref or args does not exists!
                TM_Util::trigger_error("Actiontext: Required argument (ref and/or args or noargs) is missing.", TM_ERROR_ERROR, $repeat=false, var_export($params, true));
                return;
            } else {
                if (is_array($refs)) {
                    $refsArr = $refs;
                } else {
                    if (empty($refsdelim)) {
                        $refsdelim = ' ';
                    }
                    $refsArr = explode($refsdelim, $refs);
                }
            }
        } else {
            $refsArr = array($ref);
        }

        $registry = TM_Core_ActionRegistry::get();

        /* Check access based on drupal perms and acls.
         */
        $flagHasAccess = TRUE;

        // Do permission and ACL checking on all given refs
        foreach ($refsArr as $ref) {

            $properties = $registry->get_action_properties($ref);

            if (!$properties['access']) {
                $flagHasAccess = FALSE;
                break;
            } elseif (!$noargs && $properties['acl_code']) {
                // if we're not bypassing acl checking, and there's an acl requirement to check
                // extract request vars for easy (and standardized) reference
                if (!$get) {
                    // Only pars args on the first loop.
                    parse_str($args, $get);
                    extract ($get, EXTR_PREFIX_ALL, 'post');
                }

                if (! eval("return {$properties['acl_code']};") ) {
                    // if the acl requirement doesn't pass muster
                    $flagHasAccess = FALSE;
                    break;
                }
            }
        }

        if ($flagHasAccess)  {
            echo $content;
        } else {
            echo $default;
        }

    }
}

