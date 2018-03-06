<?php
namespace Drupal\reposi\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
//use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\reposi\Form\Url;

class Reposi_info_user extends FormBase {
 
  public function getFormId() {
    return 'reposiinfouser_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $arg=\Drupal::routeMatch()->getParameter('node');
    $serch_u = db_select('reposi_user', 'u');
    $serch_u->fields('u')
            ->condition('u.uid', $arg, '=');
    $serch_user = $serch_u->execute()->fetchField();
    $info_user = $serch_u->execute()->fetchAssoc(); 
    \Drupal::state()->set('info_user2', $info_user); 
    //variable_set('info_user2',$info_user);
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
  		'#value' => $arg,
  	);
    $markup = '<p>' . '<b>' . '<big>' . $info_user['u_first_name'] . ' ' . 
              $info_user['u_first_lastname'] . '</big>' . '</b>' . '</p>' . '<ul>' .
    			    '<li>' . '<i>' . t('ID: ') . '</i>'. $info_user['uid'] . '</li>' .
              '<li>' . '<i>' . t('Name: ') . '</i>' . $info_user['u_first_name']. ' ' .
              $info_user['u_second_name'] .'</li>' .
              '<li>'.'<i>'.t('Last name: ').'</i>'.$info_user['u_first_lastname']. ' ' .
              $info_user['u_second_lastname'].'</li>';
    if (!empty($info_user['u_affiliation'])) {
      $markup .= '<li>'. '<i>'. t('Affiliation: ') . '</i>' . 
          $info_user['u_affiliation'] .'</li>';
    }
    $markup .= '<li>'. '<i>'. t('Email 1: ') . '</i>' . 
        $info_user['u_email'] .'</li>';
    if (!empty($info_user['u_optional_email_1'])) {
      $markup .= '<li>'. '<i>'. t('Email 2: ') . '</i>' . 
          $info_user['u_optional_email_1'] .'</li>';
    }
    if (!empty($info_user['u_optional_email_2'])) {
      $markup .= '<li>'. '<i>'. t('Email 3: ') . '</i>' . 
          $info_user['u_optional_email_2'] .'</li>';
    }
    if (!empty($info_user['u_id_homonymous'])) {
      $markup .= '<li>'. '<i>'. t('ORCID: ') . '</i>' . 
          $info_user['u_id_homonymous'] .'</li>';
    }
    if (!empty($info_user['u_id_scopus'])) {
      $markup .= '<li>'. '<i>'. t('Scopus ID Author: ') . '</i>' . 
          $info_user['u_id_scopus'] .'</li>';
    }
    $markup .= '<li>'. '<i>'. t('State: ') . '</i>' . $state .'</li>' .
              '<li>'. '<i>'. t('Academic rol: ') . '</i>' . $aca_rol .'</li>' . '</ul>';          
    $form['body'] = array('#markup' => $markup);
   
    $form['edit'] = array(
      '#type' => 'submit',
      '#submit' => array([$this, 'editForm']),
      '#value' => t('Edit'),
    );
    $form['disable'] = array(
      '#type' => 'submit',
      '#value' => t('Disable'),
      '#submit' => array([$this, 'userDisable']),
    );
    $form['delete'] = array(
      '#type' => 'submit',
      '#value' => t('Delete'),
    );
    return $form;
  }
  public function editForm(array &$form, FormStateInterface $form_state) {
      $uid = $form_state->getValue('uid');
      $redirect=$form_state->setRedirect('reposi.useredit_admin', ['node' => $uid]);
  }

  function userDisable($form, &$form_state){
	db_update('reposi_state')->fields(array(
        's_type'   => 'Inactive',
        ))->condition('s_uid', $form_state->getValue('uid'))
        ->execute();
        drupal_set_message('User is update to inactive');
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
  
        $node=$form_state->getValue('uid');
        $form_state->setRedirect('reposi.userdelete', ['node' => $node]);
  }
 
}
?>
