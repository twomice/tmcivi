<?php
// $Id$

/**
 * @file
 * Documentation for pathauto API.
 *
 * @see hook_token_info
 * @see hook_tokens
 */

/**
 * Define a directory containing additional Smarty templates for your module
 *
 * @return String Full system path to directory containing templates
 */
function hook_tmcivi_template_dir() {
    return '/full/path/to/template/directory';
}

/**
 * Define a directory containing additional Smarty plugins for your module
 *
 * @return String Full system path to directory containing plugins
 */
function hook_tmcivi_plugins_dir() {
    return '/full/path/to/plugins/directory';
}

/**
 * Define directories containing CSS and JS files for your module
 *
 * @param String $type The type of directory requested.
 * @return Array Drupal path (relative to Drupal base_url) to the directory
 *    requested; for the following values of $type, return the proper path:
 *    'css' - directory containing CSS files to be auto-included by file name.
 *            e.g., The files named x.css in this directory will be automatically
 *            included when tmref=x
 *    'js'  - directory containing JS files to be auto-included by file name.
 *            e.g., The files named x.js in this directory will be automatically
 *            included when tmref=x
 *    'base'- directory containing any CSS, JS, image, and other files that may be
 *            referenced in template output.
 *
 *
 */
function hook_tmcivi_resource_dir($type) {
    if ($type == 'js') {
        return drupal_get_path('module', 'tmcivi_example') . '/resource/js/auto';

    } elseif ($type == 'css') {
      return drupal_get_path('module', 'tmcivi_example') . '/resource/css/auto';

    } elseif ($type == 'base') {
      return drupal_get_path('module', 'tmcivi_example') . '/resource';

    }

}

/**
 * Define any path IDs for this module.
 *
 * @return Array
 */
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
 * The following defaults are used for any paths not defined by hook implementations:
 *  page_path: [/system/path/to/module/]class/TM/Page
 *  form_path: [/system/path/to/module/]class/TM/Form
 *  raw_path : [/system/path/to/module/]class/TM/Raw
 *
 * @return Array Associative array of any of these keys (values should be full system path to directories):
 *   page: directory containing page preprocessor files called by TM_Page
 *   form: directory containing form processor files called by TM_Form
 *   raw: directory containing preprocessor files for raw output called by TM_Raw_Page
 */
function hook_tmcivi_path() {
    $return = array(
        'page' => '',
        'form' => '',
        'raw'  => '',
    );
    return $return;
}

/**
 * @return String Full system path to directory containing CiviCRM override files (custom PHP directory) and special tmcivi classes
 */
function hook_tmcivi_class_dir() {
    return '/full/path/to/custom/PHP/directory';
}