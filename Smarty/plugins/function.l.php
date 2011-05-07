<?php

/**
 * $Id$
 */


    /*
    * Smarty plugin
    * -------------------------------------------------------------
    * Type:     function
    * Name:     l
    * Purpose:  wrapper for Drupal6 l() function
    * -------------------------------------------------------------
    *

    * Parameters
    *    ref (Required, or url) system action-registry actionid
    *    url (Required, or ref) url of destination
    *    caption - text to display as body of link
    *    id
    *    class
    *    style
    *    title
    *    accesskey - value to use for html attribute accesskey
    *    default - text to return if perms fail
    *    js_* - special variable indicating value of any javascript event for this action.  Any parameter
    *        with a name beginning "js_" will have the js_ stripped from the front and then be added
    *        to the array $js, each element of which is then added as a parameter to the action
    *        itself.  Only valid javascript event handlers (with the js_ prefix added) should
    *        be used in this way.  (Example parameter: js_onClick="alert('hello world');" We process this
    *        so that $js .= "onClick = \"alert('hello world');\"" and then the event is added to the html
    *        code for this action.)
    *    force_display: (default = false) If true, will display the link regardless of permissions; if false, will hide link unpermissioned link.
    *    button: (default = false) If true, will attempt to display the button in the civicrm standard button link format (uses jQuery styling)
    *
    */
    function smarty_function_l($params, &$smarty) {
        if (!isset($params['ref']) && !isset($params['url'])) {
            // ref does not exists!
            $smarty->trigger_error("Plugin l: missing required parameter 'ref' or 'url'. (". var_export($params, true) .")");
            return;
        }

        extract($params);

        if ($ref) {
            // before we bother with anything else, make sure the perms are right.
            $registry = TM_Core_ActionRegistry::get();
            $properties = $registry->get_action_properties($ref);

            /* Check access based on drupal perms and acls.
             */
            $flagHasAccess = TRUE;
            // If this action is not permitted, just return $default
            if ( ! ( isset( $params['force_display'] ) && $params['force_display'] ) ) {
                if (!$properties['access']) {
                    $flagHasAccess = FALSE;
                } elseif ( isset( $properties['acl'] ) ) {
                    // if there's an acl requirement to check:
                    // extract request vars for easy (and standardized) reference
                    parse_str($args, $get);
                    extract ($get, EXTR_PREFIX_ALL, 'post');

                    if (! eval("return {$properties['acl_code']};") ) {
                        // if the acl requirement doesn't pass muster
                        $flagHasAccess = FALSE;
                    }                    
                }
            }
            if (!$flagHasAccess)  {
                return $default;
            }

            $type = ( $properties['is_form'] ? 'form' : 'page' );

            $path = TM_ROOT_URL ."/{$type}";
            $query = "tmref={$ref}". ( isset( $args ) ? "&{$args}" : NULL );

            if (!isset($caption)) {
                $caption = $properties['title'];
            }
        } else {
            $path = $url;
            $query = $args;
        }

        if (!isset($caption)) {
            TM_Util::trigger_error('Missing "caption" parameter in action plugin call.', TM_ERROR_ERROR, $repeat= false, var_export($params, true));
            return false;
        }

        // Get js_* elements from $params array:
        foreach($params as $pname=>$pval) {
            if (substr($pname, 0, 3) == 'js_') {
                unset($params[$pname]);
                $attributes[strtolower(substr($pname, 3))] = $pval;
            }
        }

        if( isset( $class ) ) {
            $attributes['class'] = $class;
        }
        if( isset( $button ) && $button ) {
            $attributes['class'] .=  ' edit button ';
        }
        if( isset( $id ) ) {
            $attributes['id'] = $id;
        }
        if( isset( $style ) ) {
            $attributes['style'] = $style;
        }
        if( isset( $title ) ) {
            $attributes['title'] = $title;
        }
        if( isset( $accesskey ) ) {
            $attributes['accesskey'] = $accesskey;
        }

        $options = array(
            'attributes' => $attributes,
            'query' => $query,
            'html' => true,
        );

        $caption = ts($caption);

        return l("<span>$caption</span>", $path, $options);
    }
