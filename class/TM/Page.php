<?php

/**
 * $Id$
 */

require_once 'CRM/Core/Page.php';

class TM_Page extends CRM_Core_Page {

    var $subpage = '';
    var $tmref = '';
    var $subpageLoopLimit = 100;

    function run( ) {

        _tmcivi_initialize();
        drupal_set_title(TM_DEFAULT_TITLE);

        // get $this->tmref either from $_GET or the default page name
        $this->tmref = CRM_Utils_array::value('tmref', $_GET, TM_DEFAULT_PAGE);

        // Load action components based on action registry
        $registry = TM_Core_ActionRegistry::get();
        $properties = $registry->get_action_properties($this->tmref);

        if ( ! $properties['access'] ) {
            CRM_Utils_System::permissionDenied( );
            exit;
        }

        extract ($_POST, EXTR_PREFIX_ALL, 'post');
        extract ($_GET, EXTR_PREFIX_ALL, 'post');

        $flagHasAccess = TRUE;
        // Now check any acl requirements
        if ( $properties['acl_code'] ) {
            // if there's an acl requirement to check:
            if (! eval("return {$properties['acl_code']};") ) {
                // if the acl requirement doesn't pass muster
                $flagHasAccess = FALSE;
            }
        }
        if (!$flagHasAccess) {
            CRM_Utils_System::permissionDenied( );
            exit();
        }

        // set the default subform
        $this->subpage = $properties['template'];

        // include the preprocessor file if exists, and loop to include any newly set subpages
        $previousSubpage = '';
        $i = 0;
        // if subpage has been changed, include that preprocessor, too.
        while ($previousSubpage <> $this->subpage) {
            // ettempt to prevent endless looping.
            if (++$i > $this->subpageLoopLimit) {
                TM_Util::trigger_error('Possible endless loop detected. Cannot continue.', TM_ERROR_FATAL);
                break;
            }
            $previousSubpage = $this->subpage;

            $modules = $registry->get_modules();
            
            $file = "{$modules[$properties['module']]['page_path']}{$this->subpage}.php";
            if (file_exists($file)) {
                include($file);
            }
        }

        $template = "TM/Page/{$this->subpage}.tpl";

        $this->assign('subpage', $template);
        
        // Assign GET and POST vars to template as $post_x
        foreach (array_merge($_POST, $_GET) as $name => $value) {
            $this->assign('post_'.$name, $value);
        }

        // Include any like-named js or css files
        _tmcivi_include_resources($this->subpage);
        
        // Add breadcrumb
        $bc = TM_Util::build_action_breadcrumb($properties);
        CRM_Utils_System::appendBreadCrumb( $bc );

        // Set the title, if there is one:
        if (isset($properties['title'])) {
            drupal_set_title($properties['title']);
        } else {
            drupal_set_title(variable_get('site_name', ''));
        }

        parent::run( );
    }

}
