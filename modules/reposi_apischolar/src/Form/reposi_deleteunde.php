<?php

namespace Drupal\reposi_apischolar\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\reposi_apischolar\Controller\reposidoc_scholar;

/**
* Implements an example form.
*/

class reposi_deleteunde extends FormBase {

  public function getFormId() {
    return 'reposi_del_unde';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $uid=\Drupal::routeMatch()->getParameter('node');
    $serch_p = db_select('reposi_publication', 'p');
    $serch_p->fields('p')
    ->condition('p.p_unde', $uid, '=');
    $search_pub = $serch_p->execute()->fetchAssoc();
    $p_title=$search_pub['p_title'];
    $markup='Do you delete '.$p_title.' Now?'.'<p>';
    $form['body'] = array('#markup' => $markup);
    $form['delete'] = array(
      '#type' => 'submit',
      '#value' => t('Delete'),
    );
    $form['cancel'] = array(
      '#type' => 'submit',
      '#value' => t('Cancel'),
      '#submit' => array([$this, 'Cancel']),
    );
    return $form;
  }

  function Cancel($form, &$form_state){
    $uid=\Drupal::routeMatch()->getParameter('node');
    $form_state->setRedirect('reposi.define_typePublicationGS', ['node' => $uid]);
  }

  public function validateForm(array &$form, FormStateInterface $form_state){

  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $uid=\Drupal::routeMatch()->getParameter('node');
    reposidoc_scholar::delete_unde($uid);
    $form_state->setRedirect('reposi.gspub');
  }

}
