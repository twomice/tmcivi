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

/** Singleton class of a single script execution; useful for storing properties of the particular execution.
 */
class TM_Exe {

    public $tmciviPath = '';
    public $mode = ''; // AJAX or PAGE
    public $debug = false;  // useful for debugging -- some classes check for this and dump debug info.

    private function __construct() {

    }

    public static function get() {
        static $exe = NULL;
        if ($exe == NULL ){
            $exe = new TM_Exe;
        };
        return $exe;
    }

    public function __set($name, $value) {
    // prevent overloading on config object properties.
        $trace = debug_backtrace();
        trigger_error(
            'Object overloading not allowed for TM_Exe object.  Attempted to set property "'. $name .'"'.
            ' in ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line'],
            E_USER_NOTICE);
        return null;
    }

    public function __get($name) {
    // prevent overloading on config object properties.
        $trace = debug_backtrace();
        trigger_error(
            'Object overloading not allowed for TM_Exe object.  Attempted to get property "'. $name .'"'.
            ' in ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line'],
            E_USER_NOTICE);
        return null;
    }

}