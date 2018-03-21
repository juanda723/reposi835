<?php

namespace Drupal\reposi_apiscopus\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements an example form.
 */

class reposi_author_scopus_assoc extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'scopus_assoc';
  }

  /**
   * {@inheritdoc}
   */
public function buildForm(array $form, FormStateInterface $form_state) {
      $uid=\Drupal::routeMatch()->getParameter('node');
      $au_id=\Drupal::routeMatch()->getParameter('nod');
      $serch_u = db_select('reposi_user', 'u');
      $serch_u->fields('u')
              ->condition('u.uid', $uid, '=');
      $serch_user = $serch_u->execute()->fetchField();
      $info_user = $serch_u->execute()->fetchAssoc();
      $search_stat = db_select('reposi_state', 's');
    	$search_stat->fields('s', array('s_type'))
                    	 ->condition('s.s_uid', $serch_user, '=');
    	$state = $search_stat->execute()->fetchField();
    	$search_aca_rol = db_select('reposi_academic', 'a');
    	$search_aca_rol->fields('a', array('academic_type'))
                       ->condition('a.academic_uid', $serch_user, '=');
    	$aca_rol = $search_aca_rol->execute()->fetchField();
      $form['uid'] = array(
    		'#type' => 'value',
    		'#value' => $uid,
    	);
    	$form['au_id'] = array(
    		'#type' => 'value',
    		'#value' => $au_id,
    	);
      $markup = '<p>' . '<b>' . '<big>' . t('This user doesn’t have Scopus ID Author
      		  associated.') . '</big>' . '</b>' . '</p>' . '<p>' . '<big>' .
      		  t('Are you sure about this change?') . '</big>' . '</p>' . '<ul>' .
      		  '<li>' . '<i>' . t('ID: ') . '</i>'. $info_user['uid'] . '</li>' .
                '<li>' . '<i>' . t('Name: ') . '</i>' . $info_user['u_first_name']. ' ' .
                $info_user['u_second_name'] .'</li>' .
                '<li>'.'<i>'.t('Last name: ').'</i>'.$info_user['u_first_lastname']. ' ' .
                $info_user['u_second_lastname'].'</li>' .
                '<li>'. '<i>'. t('Email 1: ') . '</i>' . $info_user['u_email'] .'</li>' .
                '<li>' . '<b>' . '<i>'. t('Scopus ID Author: ') . '</i>' . $au_id . '</b>' .'</li>' . '</ul>';
      $form['body'] = array('#markup' => $markup);
      $form['accept'] = array(
        '#type' => 'submit',
        '#value' => t('Accept'),
      );
      $form['cancel'] = array(
        '#type' => 'submit',
        '#value' => t('Cancel'),
        '#submit' => array([$this, 'Cancel']),
      );
    return $form;
  }

  /**
   * {@inheritdoc}
   */

      function Cancel($form, &$form_state){
          $form_state->setRedirect('reposi');
      }
  public function validateForm(array &$form, FormStateInterface $form_state)
  {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $arg=\Drupal::routeMatch()->getParameter('node');
    db_update('reposi_user')->fields(array(
   	      'u_id_scopus' => \Drupal::routeMatch()->getParameter('nod'),
   	    ))->condition('uid', $arg)
   	    ->execute();
   		drupal_set_message('The user was update');
        $form_state->setRedirect('reposi.admuser_info', ['node' => $arg]);
       	//$form_state['redirect'] = $base_url . '/reposi/adm_user/' . $form_state['build_info']['args'][0];
  }

}
