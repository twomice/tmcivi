<?php
// $Id$

/**
 * @file
 * Documentation for pathauto API.
 *
 * @see hook_token_info
 * @see hook_tokens
 */

function hook_tmcivi_template_dir(&$template_dir = array()) {
}

function hook_tmcivi_plugins_dir(&$plugins_dir = array()) {
}

function hook_tmcivi_resource_dir($type) {
}

function hook_tmcivi_registry() {
    $registry = array(
        /* Format: ID => array(
         *      'p'     => PERM,
         *      't'     => TITLE,
         *      'b'     => BREADCRUMB,
         *      'f'     => IS_FORM,
         *      'a'     => ACL REQUIREMENT (eval'd code)
         * );
         */
        'example' => array( 'p' => 'access tmcivi'),
        'secondExample' => array( 'p' => 'access tmcivi', 't' => 'Second Example', 'b' => 'tm:example', ),
        '' => array( 'p' => ''),
    );
    return $registry;
}

/**
 * Register paths for include files for primary tmcivi classes
 *
 * @return Array Associative array of any of these keys (values should be full system path to directories):
 *   page_path: directory containing page preprocessor files called by TM_Page
 *   form_path: directory containing form processor files called by TM_Form
 *   raw_path: directory containing preprocessor files for raw output called by TM_Raw_Page
 */
function hook_tmcivi_register() {
}

/**
 * @return String Full system path to directory containing CiviCRM override files (custom PHP directory) and special tmcivi classes
 */
function hook_tmcivi_class_dir() {
}