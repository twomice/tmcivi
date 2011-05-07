<?

function viewExampleForm_preProcess(&$form) {
    $form->_isModal = true;
}

function viewExampleForm_buildQuickForm(&$form) {

  $form->add('text', 'title', 'Title:');
  $form->buttonModel = 'save_new';

  return;
}

function viewExampleForm_postProcess(&$form) {
  dsm($form->_submitValues);
}