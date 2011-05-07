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

function hook_tmcivi_resource_dir($type, &$plugins_dir = array()) {
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

function hook_tmcivi_register() {
}

function hook_tmcivi_class_dir() {
}