<?php
/*
 * $Id$
 */

$this->assign('INC_modulePath', drupal_get_path('module', 'tmcivi'));

$resources['js'] = module_invoke('tmcivi_example', 'tmcivi_resource_dir', 'js');
$resources['css'] = module_invoke('tmcivi_example', 'tmcivi_resource_dir', 'css');
$this->assign('INC_templateDir', module_invoke('tmcivi_example', 'tmcivi_template_dir'));
$this->assign('INC_resources', $resources);

$this->assign('INC_var', 'Current date and time: '. format_date(time(), 'small'));