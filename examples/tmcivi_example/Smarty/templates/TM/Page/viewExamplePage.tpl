{title}Example page title{/title} {* overrides site default title and title in action registry *}
<p>This is an example page.</p>
<p><strong>Full system path to page template:</strong> {$INC_templateDir}/{$subpage}</p>
<p><strong>Drupal path to JS file auto-included by name:</strong> {$INC_modulePath}/{$INC_resources.js}/examplePage.js</p>
<p><strong>Drupal path to CSS file auto-included by name:</strong> {$INC_modulePath}/{$INC_resources.css}/examplePage.css</p>
<p>Example variable output from preprocessor: </p>

<div id="exampleOutput">
{$INC_var}
</div>