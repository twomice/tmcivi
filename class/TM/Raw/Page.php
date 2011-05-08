<?php

/**
 * $Id: Page.php 545 2010-10-19 15:25:58Z as $
 */

require_once 'CRM/Core/Page.php';

class TM_Raw_Page extends CRM_Core_Page {

    var $tmref = '';
    var $output;
    var $error;
    var $tpl; // Smarty instance;
    function run( ) {

        _tmcivi_initialize();

        // get $this->tmref either from $_GET or the default page name
        $this->tmref = CRM_Utils_array::value('tmref', $_GET, false);
        if (!$this->tmref) {
            drupal_goto('civicrm');
        }

        $this->tpl = CRM_Core_Smarty::singleton();

        // Load action components based on action registry
        $registry = TM_Core_ActionRegistry::get();
        $properties = $registry->get_action_properties($this->tmref);
        $modules = $registry->get_modules();

        extract ($_POST, EXTR_PREFIX_ALL, 'post');
        extract ($_GET, EXTR_PREFIX_ALL, 'post');

        if ( 
                ! $properties['access']     // if we just don't have proper drupal permissions
                ||                          // OR
                (
                    $properties['acl_code'] &&                  // if there's an acl requirement to check, and
                    ! eval("return {$properties['acl_code']};")   // it doesn't pass muster
                )
        ) {
            $this->error = 'Permission denied.';
        } else {
            
            $file = "{$modules[$properties['module']]['raw_path']}{$this->tmref}.php";
            if (file_exists($file)) {
                include($file);
            }

        }

        // return output based on requested format (default = JSON)
        switch ($post_tmformat) {
            case 'raw':
                if ($this->error) {
                    die("Error: ". $this->error);
                } else {
                    $template = "TM/Raw/{$this->tmref}.tpl";
                    if ($this->tpl->template_exists($template)) {
                        // Assign GET and POST vars to template as $post_x
                        foreach (array_merge($_POST, $_GET) as $name => $value) {
                            $this->assign('post_'.$name, $value);
                        }
                        // Assign path for css/js/img files
                        global $base_url;
                        $this->tpl->assign('TM_RAW_RESOURCE_ROOT_URL', $base_url .'/'. module_invoke($properties['module'], 'tmcivi_resource_dir', 'base'));

                        echo $this->tpl->fetch($template);
                    } else {
                        echo $this->output;
                    }
                }
            break;

            default:
                /* _sc: acronym for "Scope Container".  Calling function may include anything in $_POST['_sc']
                 * and get it back untouched in the returning AJAX scope.
                 */
                $this->output['_sc'] = $post__sc;

                if ($this->error) {
                    // In AJAX mode, all errors are returned in CRM API error format
                    $this->output = array_merge( $this->output, CRM_Core_Error::createAPIError($this->error) );
                }

                if (!is_array($this->output) || is_object($this->output)) {
                    $this->output = array();
                }
                echo json_encode($this->output);
            break;
        }

        exit;         
    }

}
