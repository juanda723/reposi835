<?php

namespace Drupal\reposi_apischolar\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;

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

  	$form['google_scholar_api_url'] = array(
  	    '#type' => 'textfield',
  	    '#title' => t('Url of the API'),
  	    '#default_value' => $config->get('google_scholar_api_url', ""),
  	    '#size' => 60,
  	    '#maxlength' => 500,
  	    '#required' => TRUE,
  	    '#description' => t('Configure the URL for the API.'),
  	);
  	$form['reposi_apischolar_size'] = array(
  	    '#title' => t('Size to query'),
  	    '#type' => 'fieldset',
  	    '#description' => t('This is the number of titles that query by author.'),
        );
  	$form['reposi_apischolar_size']['query_scholar_size'] = array(
	    '#title' => t('Size to query'),
      	    '#type' => 'select',
      	    '#options' => array(10, 20, 100, 200, 300, 400 , 500),
      	    '#default_value' => $config->get('query_scholar_size', 0),
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
   
  public function validateForm(array &$form, FormStateInterface $form_state){

	$url=$form_state->getValue('google_scholar_api_url');
	if(!empty($url) && !UrlHelper::isValid($url, TRUE)){
		$form_state->setErrorByName('uri', t('The URL is not valid.'));
	}
  }

  /**
   * {@inheritdoc}
   */
public function submitForm(array &$form, FormStateInterface $form_state) {
      $this->config('system.maintenance')
      ->set('query_scholar_size', $form_state->getValue('query_scholar_size'))
      ->set('google_scholar_api_url', $form_state->getValue('google_scholar_api_url'))
      ->set('reposi_apischolar_cron', $form_state->getValue('reposi_apischolar_cron'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
