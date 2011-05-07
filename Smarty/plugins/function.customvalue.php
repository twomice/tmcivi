<?php

/**
 * $Id: function.customvalue.php 580 2010-10-25 04:40:09Z as $
 */


/*
* Smarty plugin
* -------------------------------------------------------------
* Type:     function
* Name:     customvalue
* Purpose:  get value of CiviCRM custom field for a given entity
* -------------------------------------------------------------
*

* Parameters
*    base (Required) - base name of the custom field (e.g., for field stored in column foo_21, the base name is "foo")
*    eid (Required) - entity_id
*    assign - name of template variable to which return value should be assigned
*/

function smarty_function_customvalue($params, &$smarty) {

    extract( $params );

    if (empty($base) || empty($eid) ) {
        // pref not provided!
        TM_Util::trigger_error("plugin customvalue: Missing one of required parameters 'base' and 'eid'.", TM_ERROR_ERROR);
        return;
    }

    $custom = TM_Custom::getCustomFields('base', $base);
    if ( $custom ) {
        $fieldId = 'custom_'. $custom[$base]['id'];
        $params = array(
            'entityID' => $eid,
            $fieldId => 1,
        );
        $values = CRM_Core_BAO_CustomValueTable::getValues($params);
        if ( array_key_exists( $fieldId, $values ) ) {
            $value = $values[$fieldId];
        }
    } else {
        TM_Util::trigger_error('Custom field not found with given base.', TM_ERROR_ERROR, TRUE, "Requested base: {$base}");
    }

    if (!empty($assign)) {
        $smarty->assign($assign, $value);
    } else {
        return $value;
    }

}

