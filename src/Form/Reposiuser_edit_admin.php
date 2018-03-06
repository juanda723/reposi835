<?php

namespace Drupal\reposi\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;

//TENGO QUE EDITAR UN FORMULARIO PARA LO CUAL SE MANTIENE EL ID DEL USUARIO
class Reposiuser_edit_admin extends FormBase {

  public function getFormId() {
    return 'reposi_user_admin_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

  $id = \Drupal::routeMatch()->getParameter('node');
  $search_this_user = db_select('reposi_user', 'u');
  $search_this_user->condition('u.uid', $id, '=')
                   ->fields('u');
  $this_user = $search_this_user->execute()->fetchAssoc();
  $search_aca_rol = db_select('reposi_academic', 'a');
  $search_aca_rol->fields('a', array('academic_type'))
                     ->condition('a.academic_uid', $id, '=');
  $aca_rol = $search_aca_rol->execute()->fetchField();
  if ($aca_rol == 'Teacher') {
      $id_academic = 0;
    } elseif($aca_rol == 'Student'){
      $id_academic = 1;
    } else {
      $id_academic = 2;
    }

    $form['uid'] = array(
      '#type' => 'value',
      '#value' => $id,
    );
    $form['adm_user'] = array(
      '#title' => t('User'),
      '#type' => 'fieldset',
      '#required' => TRUE,
    );
    $form['adm_user']['adm_user_fname'] = array(
      '#title' => t('First Name'),
      '#type' => 'textfield',
      '#maxlength' => 254,
      '#default_value' => $this_user['u_first_name'],
      '#required' => TRUE,
    );
    $form['adm_user']['adm_user_sname'] = array(
      '#title' => t('Second Name'),
      '#type' => 'textfield',
      '#maxlength' => 254,
      '#default_value' => $this_user['u_second_name'],
      '#required' => FALSE,
    );
    $form['adm_user']['adm_user_flastname'] = array(
      '#title' => t('First last name'),
      '#type' => 'textfield',
      '#maxlength' => 254,
      '#default_value' => $this_user['u_first_lastname'],
      '#required' => TRUE,
    );
    $form['adm_user']['adm_user_slastname'] = array(
      '#title' => t('Second last name'),
      '#type' => 'textfield',
      '#maxlength' => 254,
      '#default_value' => $this_user['u_second_lastname'],
      '#required' => TRUE,
    );
    $form['adm_user']['adm_user_email1'] = array(
      '#title' => t('Email 1'),
      '#type' => 'textfield',
      '#maxlength' => 254,
      '#default_value' => $this_user['u_email'],
      '#description' => t('Institutional email'),
      '#required' => TRUE,
    );
    $form['adm_user']['adm_user_email2'] = array(
      '#title' => t('Email 2'),
      '#type' => 'textfield',
      '#maxlength' => 254,
      '#default_value' => $this_user['u_optional_email_1'],
      '#description' => t('Optional Email'),
      '#required' => FALSE,
    );
    $form['adm_user']['adm_user_email3'] = array(
      '#title' => t('Email 3'),
      '#type' => 'textfield',
      '#maxlength' => 254,
      '#default_value' => $this_user['u_optional_email_2'],
      '#description' => t('Email'),
      '#required' => FALSE,
    );
    $form['adm_user']['adm_user_homonymous'] = array(
      '#title' => t('ORCID'),
      '#type' => 'textfield',
      '#maxlength' => 100,
      '#default_value' => $this_user['u_id_homonymous'],
      '#required' => FALSE,
    );
    $form['adm_user']['adm_user_idscopus'] = array(
      '#title' => t('Scupos ID Author'),
      '#type' => 'textfield',
      '#maxlength' => 100,
      '#default_value' => $this_user['u_id_scopus'],
      '#required' => FALSE,
    );

  $result = db_query('SELECT academic_type FROM {reposi_academic} LIMIT 3');
  foreach ($result as $res) {
    $academic_r[]=$res->academic_type;
  }
  $form['adm_acad_options'] = array(
    '#type' => 'value',
    '#value' => $academic_r,
  );
  $form['adm_user']['adm_user_acarol'] = array(
    '#title' => t('Academic rol'),
    '#type' => 'select',
    '#default_value' => $id_academic,
    '#options' => $form['adm_acad_options']['#value'],
  );
  $form['save'] = array(
    '#type' => 'submit',
    '#value' => t('Save'),
  );
  return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state)
  {
  $adm_fname = $form_state->getValue('adm_user_fname');
  $adm_sname = $form_state->getValue('adm_user_sname');
  $adm_flastname = $form_state->getValue('adm_user_flastname');
  $adm_slastname = $form_state->getValue('adm_user_slastname');
  //$adm_affilia = $form_state->getValue('adm_user_affiliation');
  $adm_email1 = $form_state->getValue('adm_user_email1');
  $adm_email2 = $form_state->getValue('adm_user_email2');
  $adm_email3 = $form_state->getValue('adm_user_email3');
  $adm_homo = $form_state->getValue('adm_user_orcid');
  $adm_scopus = $form_state->getValue('adm_user_idscopus');
  $adm_academic = $form_state->getValue('adm_user_acarol');
  $adm_aca_type = $form_state->getValue(['adm_acad_options', $adm_academic]);
  $uid = $form_state->getValue('uid');
  //$adm_aca_type = $form_state['values']['adm_acad_options'][$adm_academic];------------->CAMBIO:ASÍ ERA EN DRUPAL 7
  $query = db_select('reposi_user', 'n');
  $query->condition('n.u_first_name', $adm_fname, '=')
        ->condition('n.u_first_lastname', $adm_flastname, '=')
        ->condition('n.u_second_lastname', $adm_slastname, '=')
        ->fields('n', array('u_first_name', 'u_first_lastname', 'u_second_lastname'));
  $repet_user = $query->execute()->fetchField();
  $repet_u = strtolower($repet_user);
  $adm_fn = strtolower($adm_fname);

	if (!valid_email_address($adm_email1)) 
        {
        drupal_set_message(t('Email 1 is not a valid e-mail address.'),'error');
        }
        else
	{
     		if (!empty($adm_email2) && !valid_email_address($adm_email2))
		{
   
    			drupal_set_message(t('Email 2 is not a valid e-mail address.'),'error');
    		}
			else
			{
                        	if (!empty($adm_email3) && !valid_email_address($adm_email3))
				{
  	  				drupal_set_message(t('Email 3 is not a valid e-mail address.'),'error');
       				}     

				else{
    $search_mail = "SELECT u_email FROM {reposi_user} WHERE u_email = :u_email";
    $test_email = db_query($search_mail, array(':u_email' => $adm_email1))->fetchField();

      }
    }
  }
  }
  

  /**
   * {@inheritdoc}
   */
   public function submitForm(array &$form, FormStateInterface $form_state) {

  $adm_fname = $form_state->getValue('adm_user_fname');
  $adm_sname = $form_state->getValue('adm_user_sname');
  $adm_flastname = $form_state->getValue('adm_user_flastname');
  $adm_slastname = $form_state->getValue('adm_user_slastname');
  $adm_email1 = $form_state->getValue('adm_user_email1');
  $adm_email2 = $form_state->getValue('adm_user_email2');
  $adm_email3 = $form_state->getValue('adm_user_email3');
  $adm_homo = $form_state->getValue('adm_user_orcid');
  $adm_scopus = $form_state->getValue('adm_user_idscopus');
  $adm_academic = $form_state->getValue('adm_user_acarol');
  $adm_aca_type = $form_state->getValue(['adm_acad_options', $adm_academic]);
  $adm_uid = $form_state->getValue('uid');

  if (!empty($adm_email2)){
        $new_email2 = $adm_email2;
      } else {
        $new_email2 = NULL;
      }
      if (!empty($adm_email3)){
        $new_email3 = $adm_email3;
      } else {
        $new_email3 = NULL;
      }

     $params['send'] = [
      'u_uid'             => $adm_uid,
      'u_first_name'      => $adm_fname,
      'u_second_name'     => $adm_sname,
      'u_first_lastname'  => $adm_flastname,
      'u_second_lastname' => $adm_slastname,
      'u_email'           => $adm_email1,
      'u_optional_email_1'=> $new_email2,
      'u_optional_email_2'=> $new_email3,
      'u_id_homonymous'   => $adm_homo,
      'u_id_scopus'       => $adm_scopus,
      'u_adm_aca_type'    => $adm_aca_type,
       ];

    foreach ($params as $param) {
    $form_state->setRedirect('reposi.useredit', $param);
    }

   }

}
