<?php
/*
 * $Id$
 */

/*
 * Notice how this preprocessor file works for both AJAX output and raw HTML output
 */

/* If $_GET['tmformat'] == 'raw', a Smarty template is compiled. Pass values to the template
   with Smarty's assign() method. */
$this->assign('INC_templatePath', module_invoke('tmcivi', 'tmcivi_template_dir'));


// Otherwise, $this->output is translated to JSON and printed.
$this->output['explanation'] = 'This is JSON output; Call this page with "?tmformat=raw" to see raw HTML output.';
