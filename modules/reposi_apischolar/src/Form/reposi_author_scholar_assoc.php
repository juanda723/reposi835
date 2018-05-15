<?php

namespace Drupal\reposi_apischolar\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements an example form.
 */

class reposi_author_scholar_assoc extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'scholar_assoc_form_id';
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
      $markup = '<p>' . '<b>' . '<big>' . t('This author doesn’t have a Google Scholar User
      		  associated.') . '</big>' . '</b>' . '</p>' . '<p>' . '<big>' .
      		  t('Are you sure about this change?') . '</big>' . '</p>' . '<ul>' .
      		  '<li>' . '<i>' . t('ID: ') . '</i>'. $info_user['uid'] . '</li>' .
                '<li>' . '<i>' . t('Name: ') . '</i>' . $info_user['u_first_name']. ' ' .
                $info_user['u_second_name'] .'</li>' .
                '<li>'.'<i>'.t('Last name: ').'</i>'.$info_user['u_first_lastname']. ' ' .
                $info_user['u_second_lastname'].'</li>' .
                '<li>'. '<i>'. t('Email 1: ') . '</i>' . $info_user['u_email'] .'</li>' .
                '<li>' . '<b>' . '<i>'. t('Scholar ID Author: ') . '</i>' . $au_id . '</b>' .'</li>' . '</ul>';
      $form['body'] = array('#markup' => $markup);
      $form['accept'] = array(
        '#type' => 'submit',
        '#value' => t('Accept'),
      );
      $fn = $info_user['u_first_name'];
      $sn = $info_user['u_second_name'];
      $fln = $info_user['u_first_lastname'];
      $sln = $info_user['u_second_lastname'];

      $serch_a = db_select('reposi_author', 'a');
      $serch_a->fields('a')
              ->condition('a.a_first_name', $info_user['u_first_name'], '=')
	      ->condition('a.a_second_name', $info_user['u_second_name'], '=')
	      ->condition('a.a_first_lastname', $info_user['u_first_lastname'], '=')
	      ->condition('a.a_second_lastname', $info_user['u_second_lastname'], '=');
      $serch_author = $serch_a->execute()->fetchField();
      $info_author = $serch_a->execute()->fetchAssoc();
      $id_author = $info_author['aid'];

    	$form['author_id'] = array(
    		'#type' => 'value',
    		'#value' => $id_author,
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
    $author_id=$form_state->getValue('author_id');
    $arg=\Drupal::routeMatch()->getParameter('node');
    db_update('reposi_user')->fields(array(
   	      'u_id_scholar' => \Drupal::routeMatch()->getParameter('nod'),
   	    ))->condition('uid', $arg)
   	    ->execute();
   		drupal_set_message('The user was update');
    db_update('reposi_author')->fields(array(
   	      'a_id_scholar' => \Drupal::routeMatch()->getParameter('nod'),
   	    ))->condition('aid', $author_id)
   	    ->execute();
   		drupal_set_message('The user was update');
        $form_state->setRedirect('reposi.admuser_info', ['node' => $arg]);
  }

}
