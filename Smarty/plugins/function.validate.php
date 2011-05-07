<?php

    /*
    * Smarty plugin
    * -------------------------------------------------------------
    * Type:     function
    * Name:     validate
    * Purpose:  validate a variable against given requirement
    * -------------------------------------------------------------
    *

    * Parameters
    *   val: REQUIRED - the value to validate
    *   matchtype: type requirements (a valid PHP variable type name)
    *   matchregex: a regular expression to compare against the value
    *   matcharray: A one-dimensional array; value must be one of these values to validate;
    *   matcharraycode: PHP code to create a one-dimensional array; value must be one of these values to validate;
    *       must be of the format: array('a','b')
    *   matcharraystrict: whether or not to use strict in_array() matching if matcharraycode is given
    *   matchsubstring: a substring which must be contained in the given value
    *   name: name of template variable in which to store validated value
    *   action: action to take value fails to validate.  One of:
    *       - usedefault: use a given default (see parameter: default)
    *       - fatalerror: generate a fatal error with given message (see parameter: errormessage)
    *       (defaults to 'fatalerror' with error message: 'Template variable failed to validate')
    *   default: default value to use if parameter: action has value 'usedefault'
    *   errormessage: error message to use if parameter: action has value 'fatalerror';
    *   casttype: any return variable will be cast to this type
    *   require: if TRUE, values equating to boolean false will be considered invalid; defaults to FALSE
    */

    function smarty_function_validate($params, &$smarty) {
        extract($params);

        $invalidated = false;

        if (!isset($matchtype) && !isset($matcharraycode) && !isset($matchsubstring) && !isset($matchregex) && !isset($matcharray)) {
            trigger_error('Validate: required match parameter missing: one of the following must be proveded: matchtype, matcharraycode, matchsubstring, matchregex.', E_USER_WARNING);
        }

        // warn user if they forgot to send the value in $val -- $val is required.
        if (!array_key_exists('val',$params)) {
            trigger_error('Validate: no value provided in $val', E_USER_WARNING);
        }

        if (!$val && !$require) {
            return;
        }

        if (!$invalidated && $matchtype) {
            $func = "is_$matchtype";
            if (!$func($val)) {
                $invalidated = true;
            }
        }

        if (!$invalidated && $matchregex) {
            if (!preg_match("/$matchregex/", $val)) {
                $invalidated = true;
            }
        }


        if (!$invalidated && $matchsubstring) {
            if (!strpos($val, $matchsubstring)) {
                $invalidated = true;
            }
        }

        if (!$invalidated && $matcharraycode) {
            eval ("\$array = $matcharraycode;");
            if (!in_array($val, $array, $matcharraystrict)) {
                $invalidated = true;
            }
        }

        if (!$invalidated && $matcharray) {
            if (!in_array($val, $matcharray, $matcharraystrict)) {
                $invalidated = true;
            }
        }

        if ($invalidated) {
            if ($action == 'usedefault') {
                $validatedValue = $default;
            } else {
                if (!$errormessage) {
                    $errormessage = 'Template variable failed to validate.';
                }
                // if it's 'fatalerror' or not given
                trigger_error($errormessage,E_USER_ERROR);
            }
        } else {
            $validatedValue = $val;
        }

        if ($name) {
            if ($casttype) {
                //strip out any possible code before we eval()
                $casttype = preg_replace('/\W/', '', $casttype);
                eval("\$validatedValue = ($casttype)\$validatedValue;");
            }
            $smarty->_tpl_vars[$name] = $validatedValue;
        }
    }
