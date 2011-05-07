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


// standard acl requiremens for easy reference.
define('TM_REGISTRY_ACL_CIDVIEW', 'CRM_Contact_BAO_Contact_Permission::allow( (int)$post_cid, CRM_Core_Permission::VIEW )');
define('TM_REGISTRY_ACL_CIDEDIT', 'CRM_Contact_BAO_Contact_Permission::allow( (int)$post_cid, CRM_Core_Permission::EDIT )');
define('TM_REGISTRY_ACL_PIDEDIT', 'TM_Permission::allowParticipantAccess( (int)$post_pid )');

class TM_Core_ActionRegistry extends TM_Core {

    private $registry = array();
    private $modules = array();
    private $cache = array();

    private function __construct() {

    }

    public static function get() {
        static $actionRegistry = NULL;
        if ($actionRegistry == NULL ){
            $actionRegistry = new TM_Core_ActionRegistry;
        };
        return $actionRegistry;
    }

    public function get_modules() {
        return $this->modules;
    }

    public function get_action_properties($actionId) {
        if (!isset($this->cache[$actionId])) {

            if ($this->registry == NULL) {
                $this->_load_registry();
            }
            if (!isset($this->registry[$actionId])) {
                TM_Util::trigger_error('Unrecognized action requested. Cannot process.', TM_ERROR_FATAL, NULL, "Requested action: '$actionId'");
            }

            $this->cache[$actionId] = array(
                'perm'      => $this->registry[$actionId]['p'],
                'template'  => isset( $this->registry['tpl'] ) ? $this->registry['tpl'] : $actionId,
                'access'    => ($this->registry[$actionId]['p'] === TRUE ? TRUE : CRM_Core_Permission::check($this->registry[$actionId]['p'])),
                'breadcrumb'    => $this->registry[$actionId]['b'],
                'is_form'    => isset( $this->registry[$actionId]['f'] ) ? (bool)$this->registry[$actionId]['f'] : FALSE,
                'title'     => isset( $this->registry[$actionId]['t'] ) ? $this->registry[$actionId]['t'] : NULL,
                'acl_code'     => isset( $this->registry[$actionId]['a'] ) ? $this->registry[$actionId]['a'] : NULL,
                'module'     => isset( $this->registry[$actionId]['m'] ) ? $this->registry[$actionId]['m'] : NULL,

            );

        }
        return $this->cache[$actionId];
    }

    private function _load_registry() {

        static $registry = NULL;
        if ($registry === NULL) {


            $modules = module_implements('tmcivi_registry');
            foreach ($modules as $module) {
                $module_registry = module_invoke($module, 'tmcivi_registry');
                foreach ($module_registry as $key => $properties) {
                    $properties['m'] = $module;
                    $registry[$key] = $properties;
                }

                $module_properties = module_invoke($module, 'tmcivi_register');

                // Set default page class path
                if (!isset($module_properties['page_path'])) {
                    $module_properties['page_path'] = rtrim(_tmcivi_document_root(), '/') . base_path() . drupal_get_path('module', $module) . '/class/TM/Page/';
                }
                $module_properties['page_path'] = rtrim($module_properties['page_path'], DIRECTORY_SEPARATOR .'/\\'). DIRECTORY_SEPARATOR;

                // Set default form class path
                if (!isset($module_properties['form_path'])) {
                    $module_properties['form_path'] = rtrim(_tmcivi_document_root(), '/') . base_path() . drupal_get_path('module', $module) . '/class/TM/Form/';
                }
                $module_properties['form_path'] = rtrim($module_properties['form_path'], DIRECTORY_SEPARATOR .'/\\'). DIRECTORY_SEPARATOR;

                // Set default raw class path
                if (!isset($module_properties['raw_path'])) {
                    $module_properties['raw_path'] = rtrim(_tmcivi_document_root(), '/') . base_path() . drupal_get_path('module', $module) . '/class/TM/Raw/Raw/';
                }
                $module_properties['raw_path'] = rtrim($module_properties['raw_path'], DIRECTORY_SEPARATOR .'/\\'). DIRECTORY_SEPARATOR;
                
                $this->modules[$module] = $module_properties;

            }
        }
        $this->registry = $registry;
        
    }
}
