<?php

/**
 * $Id$
 */

require_once 'CRM/Core/Form.php';

class TM_Form extends CRM_Core_Form {

    var $tmref = NULL;
    var $subform = NULL;
    var $buttons = array();
    var $buttonModel = 'standard';
    var $post = array();
    var $data = array();    // form-level scope data container
    var $properties = array();  // action properties keyed from tmref
    var $_done = FALSE;     // Track this to prevent form from loading multiple times.
    var $_buttonName = FALSE;  // button name as submitted.
    var $_isModal = FALSE;  // if TRUE, form will redirect to itself.
                            // This helps avoid broken pages on reload/back.
                            // Must be set in preProcess or construct functions

    /*
    On displaying the form, these methods are called in order:
        registerRules
        preProcess
        buildQuickForm
        setDefaultValues
        addRules
    */

    function __construct( $state = null,
                          $action = CRM_Core_Action::NONE,
                          $method = 'post',
                          $name = null ) {
        _tmcivi_initialize();

        $this->tmref = CRM_Utils_array::value('tmref', $_GET, false);
        if (!$this->tmref) {
            drupal_goto('civicrm');
        }

        // Load action components based on action registry
        $registry = TM_Core_ActionRegistry::get();
        $this->properties = $registry->get_action_properties($this->tmref);

        // set default title
        if (empty($this->properties['title'])) {
            $this->properties['title'] = variable_get('site_name', '');
        }
        
        $flagHasAccess = TRUE;

        if ( ! $this->properties['access'] ) {
            $flagHasAccess = FALSE;
        }
        
        // Now check any acl requirements
        if ( $this->properties['acl_code'] ) {
            // if there's an acl requirement to check:
            // extract request vars for easy (and standardized) reference
            extract ($_POST, EXTR_PREFIX_ALL, 'post');
            extract ($_GET, EXTR_PREFIX_ALL, 'post');

            if (! eval("return {$this->properties['acl_code']};") ) {
                // if the acl requirement doesn't pass muster
                $flagHasAccess = FALSE;
            }
        }

        if (!$flagHasAccess) {
            CRM_Utils_System::permissionDenied( );
            exit();
        }

        // set the default subform
        $this->subform = $this->properties['template'];

        $modules = $registry->get_modules();

        $file = "{$modules[$this->properties['module']]['form_path']}{$this->tmref}.php";
        if (file_exists($file)) {
            // Populate this->post for easy reference
            $this->post = array_merge($_POST, $_GET);

            include($file);
            $funcname = $this->tmref .'_construct';
            if (is_callable($funcname)) {
                call_user_func($funcname, $this);
            }

        }

        parent::__construct(  $state,
                          $action,
                          $method,
                          $name );

    }

    function preProcess( ) {

        $this->_done = false;

        if ($this->tmref) {
            $funcname = $this->tmref .'_preProcess';
            if (is_callable($funcname)) {
                call_user_func($funcname, $this);
            }
        }
        parent::preProcess( );

        if (!$_GET['reset'] && $this->_isModal) { // Modal forms get special handling when not on first load.
            if ($_POST) {
            // If this is a form submission, redirect
                $this->set('formValues', $this->_submitValues);
                $this->set('buttonName', $this->controller->getButtonName());
                
                $query = $_GET;
                $query['reset'] = 1;
                unset($query['q']);
                $url = CRM_Utils_System::url(TM_ROOT_URL . '/form', http_build_query($query));
                $this->postProcess();
                CRM_Utils_System::redirect($url);
            } else {
              /*
                $this->_submitValues = $this->get('formValues');
                $this->_buttonName = $this->get('buttonName');
                $this->postProcess();
                */
            }
        }

        // Make sure submitValues exists as an array
        if (!is_array($this->_submitValues)) {
            $this->_submitValues = array();
        }
    }

    function setDefaultValues() {
        if (!$this->_isModal || $_GET['reset']) {
        // On reset, we need to set real default values (or if the form's not modal, do it always)
            if ($this->tmref) {
                $funcname = $this->tmref .'_setDefaultValues';
                if (is_callable($funcname)) {
                    $defaults = call_user_func($funcname, $this);
                }
            }
        } else {
        // If it's not reset, set form field values to the current form state
            $defaults = $this->_submitValues;
        }
        return $defaults;         
    }

    function buildQuickForm() {
        // Call tmref's buildQuickForm method
        if ($this->tmref) {
            $funcname = $this->tmref .'_buildQuickForm';
            if (is_callable($funcname)) {
                call_user_func($funcname, $this);
            }
        }

        // add buttons
        $this->_addButtons();

        if (empty($this->subform)) {
            $template = "TM/Form/{$this->tmref}.tpl";
        } else {
            $template = "TM/Form/{$this->subform}.tpl";
        }

        $this->assign('subform', $template);

        // Assign GET and POST vars to template as $post_x
        foreach (array_merge($this->_submitValues, $_GET) as $name => $value) {
            $this->assign('post_'.$name, $value);
        }

        // Include any like-named js or css files
        _tmcivi_include_resources($this->tmref);

        // Add breadcrumb
        $bc = TM_Util::build_action_breadcrumb($this->properties);
        CRM_Utils_System::appendBreadCrumb( $bc );

        // SEt the title, if there is one:
        drupal_set_title($this->properties['title']);

        // Adjust form 'action' attribute to include relevant information
        $formElements = array();
        foreach ($this->_elements as $element) {
            $formElements[] = $element->_attributes['name'];
        }
        foreach ($_GET as $key => $value) {
            if ($key == 'q' || $key == 'reset' || in_array($key, $formElements)) continue;
            $query[] = "$key=$value";
        }
        $this->_attributes['action'] .= '?'. implode($query, '&');

    }

    function validate() {
        $error = parent::validate( );

        if ($this->tmref) {
            $funcname = $this->tmref .'_validate';
            if (is_callable($funcname)) {
                $errors = call_user_func($funcname, $this);
            }

            if ( $errors !== true && is_array($errors) && !empty($errors) ) {
                $this->_errors += $errors;
                $error = false;
            }

        }

        return $error;

    }

    function postProcess() {

        if ($this->_done) {
            return;
        }


        $this->_done = TRUE;

        if ($this->tmref) {
            $funcname = $this->tmref .'_postProcess';
            if (is_callable($funcname)) {
                call_user_func($funcname, $this);
            }
        }
    }

     /** Adds a button to $this->buttons, which will all be added in $this->_addButtons() later
      * @param $params array Associative array of button properties. Properties are 4:
      *                'type', 'name', 'subName' and 'isDefault'
      * @param $tmref string The unique ID of the action button being added.  If NULL, button
      *                 is added without permission checks.
      */
    function addButton($params, $tmref = NULL) {
        $this->buttonModel = 'adhoc';
        if ($tmref) {
           $registry = TM_Core_ActionRegistry::get();
           $properties = $registry->get_action_properties($tmref);
           if (!$properties['access']) {
               // If we don't have access to this tmref, then don't add the button, just return.
               return;
           }
        }
        $this->buttons[] = $params;
    }

    // check each $this->buttons and if perms are okay, include it in call to parent::addButtons()
    function _addButtons() {
        switch($this->buttonModel) {
            case 'standard':
                $buttons = array (
                    array (
                        'type' => 'submit',
                        'name' => 'Save',
                        'isDefault' => TRUE,
                    ),
                    array (
                        'type' => 'cancel',
                        'name' => 'Cancel',
                    ),
                );
            break;
            case 'save':
                $buttons = array (
                    array (
                        'type' => 'next',
                        'name' => 'Save',
                        'isDefault' => TRUE,
                    ),
                    array (
                        'type' => 'cancel',
                        'name' => 'Cancel',
                    ),
                );
            break;
            case 'continue':
                $buttons = array (
                    array (
                        'type' => 'next',
                        'name' => 'Continue',
                        'isDefault' => TRUE,
                    ),
                    array (
                        'type' => 'cancel',
                        'name' => 'Cancel',
                    ),
                );
            break;
            case 'save_new':
                $buttons = array (
                    array (
                        'type' => 'next',
                        'name' => 'Save',
                        'isDefault' => TRUE,
                    ),
                    array (
                       'type'      => 'next',
                       'name'      => ts('Save and New'),
                       'subName'   => 'new'
                    ),
                    array (
                        'type' => 'cancel',
                        'name' => 'Cancel',
                    ),
                );
            break;
            case 'save_continue':
                $buttons = array (
                    array (
                        'type' => 'next',
                        'name' => 'Save',
                        'isDefault' => TRUE,
                    ),
                    array (
                       'type'      => 'next',
                       'name'      => ts('Save and Continue Editing'),
                       'subName'   => 'continue'
                    ),
                    array (
                        'type' => 'cancel',
                        'name' => 'Cancel',
                    ),
                );
            break;
            case 'delete':
                $buttons = array (
                    array (
                        'type' => 'next',
                        'name' => 'Delete',
                        'isDefault' => TRUE,
                    ),
                    array (
                        'type' => 'cancel',
                        'name' => 'Cancel',
                    ),
                );
            break;
            case 'adhoc':
                $buttons = $this->buttons;
            break;
        }
        parent::addButtons($buttons);
    }
}
