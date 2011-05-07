<?php
/*
 * $Id: functions.php 743 2010-11-08 19:59:55Z as $
 */

/**
 * Initialize tmcivi
 */
function _tmcivi_initialize() {

    civicrm_initialize();
    
    static $initialized;

    if (!$initialized) {

        spl_autoload_register('tmcivi_autoload');

        // also fix php include path
        $custom_php_directories = module_invoke_all('tmcivi_class_dir');
        $include_path = '';
        foreach ($custom_php_directories as $path) {
            $include_path = $path . PATH_SEPARATOR . $include_path;
        }
        $include_path = $include_path . get_include_path( );
        set_include_path( $include_path );
        
        $template =& CRM_Core_Smarty::singleton( );

        $template->template_dir = array_merge(array_reverse(module_invoke_all('tmcivi_template_dir')), (array)$template->template_dir);
        $template->plugins_dir = array_merge(array_reverse(module_invoke_all('tmcivi_plugins_dir')), (array)$template->plugins_dir);


        // include required class files
        require('TM/Db.php');
        require('TM/Exe.php');
        require('TM/Util.php');
        require('TM/Core.php');
        require('TM/Core/ActionRegistry.php');

        /* Set up civicrm db connection
         */
        global $db_url;
        $config = CRM_Core_Config::singleton();
        if ( is_array($db_url) ) {
            $db_url['civicrm'] = $config->dsn;
        } else {
            $db_url = array (
                'default' => $db_url,
                'civicrm' => $config->dsn,
            );
        }

        $initialized = TRUE;
    }

    return $initialized;
}


/**
 * Autoloader, saves having to require files all the time. Not used at all in CiviCRM, though.
 * Registered in _tmcivi_initialize.
 * Only attempts to load files when classname starts with "TM_" or "CRM_"
 */
function tmcivi_autoload($className)
{
    if ( 0 === strpos($className, 'TM_') || 0 === strpos($className, 'CRM_') ) {
        require_once(str_replace('_', '/', $className) . '.php');
    }
}


/**
 * Include any like-named js or css files for given page
 * @param <type> $pageName
 */
function _tmcivi_include_resources($pageName) {

    static $initialized;

    if (!$initialized) {
        $css_resource_dirs = module_invoke_all('tmcivi_resource_dir', $type = 'css');
        $js_resource_dirs = module_invoke_all('tmcivi_resource_dir', $type = 'js');
    }

    if (is_array($css_resource_dirs)) {
        foreach ($css_resource_dirs as $dir) {
            // ensure $dir has trailing slash and no leading slash
            $dir = trim($dir, DIRECTORY_SEPARATOR .'/\\'). DIRECTORY_SEPARATOR;
            if (file_exists(_tmcivi_document_root() . base_path() . $dir. $pageName .'.css')) {
                drupal_add_css( $dir. $pageName .'.css', 'module', 'all', $preprocess=FALSE);
            }
        }
    }
    if (is_array($js_resource_dirs)) {
        foreach ($js_resource_dirs as $dir) {
            // ensure $dir has trailing slash and no leading slash
            $dir = trim($dir, DIRECTORY_SEPARATOR .'/\\'). DIRECTORY_SEPARATOR;
            if (file_exists(_tmcivi_document_root() . base_path() . $dir. $pageName .'.js')) {
                drupal_add_js( $dir. $pageName .'.js', 'module', 'header', FALSE, TRUE, $preprocess=FALSE);
            }
        }
    }
}

/**
 * There's some concern that $_SERVER['DOCUMENT_ROOT'] is not reliable across all
 * systems, so we need a way to get the correct value. (See http://drupal.org/node/518460)
 * Unfortunately the code from that patch is not always reliable either,
 * so currently just returning $_SERVER['DOCUMENT_ROOT'];
 *
 * @return (string) $_SERVER['DOCUMENT_ROOT']
 */
function _tmcivi_document_root() {
    return $_SERVER['DOCUMENT_ROOT'];
}

/**
 * db_query wrapper for civicrm/tmcivi tables. Switches to civicrm database,
 * makes query, then switches back to drupal database.
 * @param string $query The query to run. Extra parameters are handled same way
 *      as db_query does.
 * @return mixed Whatever is returned from db_query.
 */
function tm_query($query) {
  
    $args = func_get_args();

    $exe = TM_Exe::get();
    if ($exe->debug) {
        $func = 'db_queryd';
    } else {
        $func = 'db_query';
    }

    $default = db_set_active('civicrm');
    $ret = call_user_func_array($func, $args);
    db_set_active($default);
    return $ret;
}
