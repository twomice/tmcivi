<?php

/**
 * $Id$
 */


    /*
    * Smarty plugin
    * -------------------------------------------------------------
    * File:     block.title.php
    * Type:     block
    * Name:     form
    * Purpose:  set drupal page title to given value
    * Parameters: [none]
    * -------------------------------------------------------------
    */
    function smarty_block_title($params, $content, &$smarty) {
        if (isset($content)) {
            drupal_set_title($content);
        }
    }

