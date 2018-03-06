<?php

namespace Drupal\reposi\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements an example form.
 */
class ReposiForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'reposi_form';
  }

  /**
   * {@inheritdoc}
   */

public function buildForm(array $form, FormStateInterface $form_state) {        
  $form['fields']['modules'] = array(
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => t('sample field'),
      '#prefix' => '<div id="modules-wrapper">',
      '#suffix' => '</div>',
    );

    $max = $form_state->get('fields_count');
    if(is_null($max)) {
      $max = 0;
      $form_state->set('fields_count', $max);
    }

    // Add elements that don't already exist
    for($delta=0; $delta<=$max; $delta++) {
      if (!isset($form['fields']['modules'][$delta])) {
        $element = array(
            '#type' => 'textfield',
            '#title' => t('field Name'),
            '#autocomplete_route_name' => '',
        );
        $form['fields']['modules'][$delta]['iqqq'] = $element;
        $element = array('#type' => 'textfield','#title' => t('aaa'),'#required' => FALSE);
        $form['fields']['modules'][$delta]['iaaa'] = $element;
        $element = array('#type' => 'textfield','#title' => t('sss'),'#required' => FALSE, '#suffix' => '<hr />');
        $form['fields']['modules'][$delta]['isss'] = $element;
      }
    }

    $form['fields']['modules']['add'] = array(
      '#type' => 'submit',
      '#name' => 'addfield',
      '#value' => t('Add more field'),
      '#submit' => array(array($this, 'addfieldsubmit')),
      '#ajax' => array(
        'callback' => array($this, 'addfieldCallback'),
        'wrapper' => 'modules-wrapper',
        'effect' => 'fade',
      ),
    );

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Submit sample'),
    );


    return $form;
  }

  /**
    * Ajax submit to add new field.
    */
  public function addfieldsubmit(array &$form, FormStateInterface &$form_state) {
    $max = $form_state->get('fields_count') + 1;
    $form_state->set('fields_count',$max);
    $form_state->setRebuild(TRUE);
  }

  /**
    * Ajax callback to add new field.
    */
  public function addfieldCallback(array &$form, FormStateInterface &$form_state) {
    return $form['fields']['modules'];
  }


  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
 
  }
 
}


