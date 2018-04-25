<?php

namespace Drupal\reposi_apischolar\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;


/**
 * Implements an example form.
 */

class reposi_apischolar_admin extends ConfigFormBase{


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'apischolar_admin';
  }

  /**
   * {@inheritdoc}
   */

   protected function getEditableConfigNames() {
     return ['system.maintenance'];
   }

public function buildForm(array $form, FormStateInterface $form_state) {
  $config = $this->config('system.maintenance');
    $form = array();
  	$form['reposi_apischolar_size'] = array(
  	    '#title' => t('Size to query'),
  	    '#type' => 'fieldset',
  	    '#description' => t('This is the number of titles that query by author.'),
      );
      $form['reposi_apischolar_size']['query_scholar_start'] = array(
  	    '#title' => t('Start'),
  	    '#type' => 'textfield',
  	    '#default_value' => $config->get('query_scholar_start', 0),
  	    '#size' => 5,
  	    '#maxlength' => 3,
  	    '#required' => TRUE,
  	);
  	$form['reposi_apischolar_size']['query_scholar_final'] = array(
  	    '#title' => t('Final'),
  	    '#type' => 'textfield',
  	    '#default_value' => $config->get('query_scholar_final', 200),
  	    '#size' => 5,
  	    '#maxlength' => 3,
  	    '#description' => t("Max value 200"),
  	    '#required' => TRUE,
  	);



  	$form['reposi_apischolar_cron'] = array(
      '#title' => t('Automatic execution'),
      '#type' => 'select',
      '#options' => array(t('Never'),
      					t('1 month.'),
                t('3 months.'),
                t('6 months.'),),
      '#default_value' => $config->get('reposi_apischolar_cron', 0),
      '#required' => TRUE,
    );

    return parent::buildForm($form, $form_state);

  }




  /**
   * {@inheritdoc}
   */
   /*
  public function validateForm(array &$form, FormStateInterface $form_state){
    $start_validate = $form_state->getValue('query_start');
    if(!is_numeric($start_validate) || $start_validate < '0' || $start_validate > '199'){
      $form_state->setErrorByName('query_start', t('Start is a positive numerical field.'));
    }

    $final_validate = $form_state->getValue('query_final');
    if(!is_numeric($final_validate) || $final_validate < '0' || $final_validate > '200'){
      $form_state->setErrorByName('query_final', t('Final is a positive numerical field.'));
    }
  }
*/
  /**
   * {@inheritdoc}
   */
public function submitForm(array &$form, FormStateInterface $form_state) {
      $this->config('system.maintenance')
      ->set('query_scholar_start', $form_state->getValue('query_scholar_start'))
      ->set('query_scholar_final', $form_state->getValue('query_scholar_final'))
      ->set('reposi_apischolar_cron', $form_state->getValue('reposi_apischolar_cron'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
