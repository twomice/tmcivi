<?php

/**
 * $Id: function.resource.php 417 2010-10-06 04:05:53Z as $
 */


    /*
    * Smarty plugin
    * -------------------------------------------------------------
    * Type:     function
    * Name:     resource
    * Purpose:  include a js or css file from TM_RESOURCE_ROOT_URL
    * -------------------------------------------------------------
    *

    * Parameters
    *    type: [js|css] default=js; the type of resource to include;
    *    file: directory and filename path to the resource under $root - REQUIRED
    */
    function smarty_function_resource($params, &$smarty) {

        extract($params);

        if (empty($file)) {
            TM_Util::trigger_error('One of the required parameters ($file) is missing in resource plugin call.', TM_ERROR_ERROR, $repeat= false, var_export($params, true));
            return false;
        }

        if ( isset( $type ) && $type == 'css') {
            $fileSystemPath = _tmcivi_root_path() .'/resource/css/'. $file;
            $fileResourcePath = TM_RESOURCE_ROOT_URL .'/css/'. $file;

            if (file_exists($fileSystemPath)) {
                drupal_add_css($fileResourcePath);
            }

        } else {
            $fileSystemPath = _tmcivi_root_path() .'/resource/js/'. $file;
            $fileResourcePath = TM_RESOURCE_ROOT_URL .'/js/'. $file;

            if (file_exists($fileSystemPath)) {
                drupal_add_js($fileResourcePath, 'module', 'header', FALSE, TRUE, $preprocess=FALSE);
            }
        }

        

    }