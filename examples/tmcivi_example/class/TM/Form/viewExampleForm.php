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
  CRM_Core_Session::setStatus('If this were a real form, we might have saved the value: '. check_plain($form->_submitValues['title']));
}