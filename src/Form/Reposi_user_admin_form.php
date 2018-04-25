<?php

namespace Drupal\reposi\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;

class Reposi_user_admin_form extends FormBase {

  public function getFormId() {
    return 'reposi_user_admin_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

  $form['adm_user'] = array(
    '#title' => t('User'),
    '#type' => 'fieldset',
    '#required' => TRUE,
  );
  $form['adm_user']['adm_user_fname'] = array(
    '#title' => t('First Name'),
    '#type' => 'textfield',
    '#maxlength' => 255,
    '#required' => TRUE,
  );
  $form['adm_user']['adm_user_sname'] = array(
    '#title' => t('Second Name'),
    '#type' => 'textfield',
    '#maxlength' => 255,
    '#required' => FALSE,
  );
  $form['adm_user']['adm_user_flastname'] = array(
    '#title' => t('First last name'),
    '#type' => 'textfield',
    '#maxlength' => 255,
    '#required' => TRUE,
  );
  $form['adm_user']['adm_user_slastname'] = array(
    '#title' => t('Second last name'),
    '#type' => 'textfield',
    '#maxlength' => 255,
    '#required' => TRUE,
  );
  $form['adm_user']['adm_user_affiliation'] = array(
    '#title' => t('Affiliation'),
    '#type' => 'textfield',
    '#maxlength' => 254,
    '#required' => FALSE,
  );
  $form['adm_user']['adm_user_email1'] = array(
    '#title' => t('Email 1'),
    '#type' => 'textfield',
    '#maxlength' => 254,
    //'#default_value' => 'example@unicauca.edu.co',
    '#description' => t('Institutional email'),
    '#required' => TRUE,
  );
  $form['adm_user']['adm_user_email2'] = array(
    '#title' => t('Email 2'),
    '#type' => 'textfield',
    '#maxlength' => 254,
    '#description' => t('Optional Email'),
    '#required' => FALSE,
  );
  $form['adm_user']['adm_user_email3'] = array(
    '#title' => t('Email 3'),
    '#type' => 'textfield',
    '#maxlength' => 254,
    '#description' => t('Email'),
    '#required' => FALSE,
  );
  $form['adm_user']['adm_user_orcid'] = array(
    '#title' => t('ORCID'),
    '#type' => 'textfield',
    '#maxlength' => 100,
    '#required' => FALSE,
  );
  $form['adm_user']['adm_user_idscopus'] = array(
    '#title' => t('Scopus ID Author'),
    '#type' => 'textfield',
    '#maxlength' => 100,
    '#required' => FALSE,
  );
  $form['adm_user']['adm_user_idscholar'] = array(
    '#title' => t('Google Scholar User'),
    '#type' => 'textfield',
    '#maxlength' => 100,
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
  $adm_affilia = $form_state->getValue('adm_user_affiliation');
  $adm_email1 = $form_state->getValue('adm_user_email1');
  $adm_email2 = $form_state->getValue('adm_user_email2');
  $adm_email3 = $form_state->getValue('adm_user_email3');
  $adm_homo = $form_state->getValue('adm_user_orcid');
  $adm_scopus = $form_state->getValue('adm_user_idscopus');
  $adm_scholar = $form_state->getValue('adm_user_idscholar');
  $adm_academic = $form_state->getValue('adm_user_acarol');
  $adm_aca_type = $form_state->getValue(['adm_acad_options', $adm_academic]);
  //$adm_aca_type = $form_state['values']['adm_acad_options'][$adm_academic];------------->CAMBIO:ASÍ ERA EN DRUPAL 7
  $query = db_select('reposi_user', 'n');
  $query->condition('n.u_first_name', $adm_fname, '=')
        ->condition('n.u_first_lastname', $adm_flastname, '=')
        ->condition('n.u_second_lastname', $adm_slastname, '=')
        ->fields('n', array('u_first_name', 'u_first_lastname', 'u_second_lastname'));
  $repet_user = $query->execute()->fetchField();
  $repet_u = strtolower($repet_user);
  $adm_fn = strtolower($adm_fname);  
  if ($repet_u == $adm_fn) {
    drupal_set_message(t('The user to index exists in the database.'), 'error');
  } 
  else 
  {
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
  	  				drupal_set_message(t('Email 3 is not a valid NNO NO NO e-mail address.'),'error');
       				}
				else
				{
    $search_mail = "SELECT u_email FROM {reposi_user} WHERE u_email = :u_email";
    $test_email = db_query($search_mail, array(':u_email' => $adm_email1))->fetchField();
    if ($test_email == $adm_email1) {
      drupal_set_message(t('The email exists in Data Base.'), 'error');
    } else {
      if (empty($adm_email1)) {
        $new_email1 = NULL;
      } else {
        $new_email1 = $adm_email1;
      }
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
      $this->email1=$new_email1;
      $this->email2=$new_email2;
      $this->email3=$new_email3;
      $new_fname = ucfirst($adm_fname);
      $new_sname = ucfirst($adm_sname);
      $new_flastname = ucfirst($adm_flastname);
      $new_slastname = ucfirst($adm_slastname);
      db_insert('reposi_user')->fields(array(
          'u_first_name'       => $new_fname,
          'u_second_name'      => $new_sname,
          'u_first_lastname'   => $new_flastname,
          'u_second_lastname'  => $new_slastname,
          'u_affiliation'      => $adm_affilia,
          'u_email'            => $new_email1,
          'u_optional_email_1' => $new_email2,
          'u_optional_email_2' => $new_email3,
          'u_id_homonymous'    => $adm_homo,
          'u_id_scopus'        => $adm_scopus,
	  'u_id_scholar'       => $adm_scholar,
      ))->execute();
      $serch_a = db_select('reposi_author', 'a');
      $serch_a->fields('a')
              ->condition('a.a_first_name', $new_fname, '=')
              ->condition('a.a_second_name', $new_sname, '=')
              ->condition('a.a_first_lastname', $new_flastname, '=')
              ->condition('a.a_second_lastname', $new_slastname, '=');
      $serch_aut = $serch_a->execute()->fetchField();
      if (empty($serch_aut)) {
        db_insert('reposi_author')->fields(array(
          'a_id_scopus'        => $adm_scopus,
	  'a_id_scholar'       => $adm_scholar,
          'a_first_name'       => $new_fname,
          'a_second_name'      => $new_sname,
          'a_first_lastname'   => $new_flastname,
          'a_second_lastname'  => $new_slastname,
        ))->execute();
      } 
      $search_user = "SELECT * FROM {reposi_user} WHERE u_email = :u_email";
      $uid_get = db_query($search_user, array(':u_email' => $adm_email1))->fetchField();

      if (!empty($uid_get)){
        
        db_insert('reposi_state')
        ->fields(array(    
            's_type'    => 'Active',
            's_uid'     => $uid_get,
        ))
        ->execute();

        db_insert('reposi_academic')
        ->fields(array(   
            'academic_type' => $adm_aca_type,
            'academic_uid'  => $uid_get,
        ))
        ->execute();
      }

      drupal_set_message('The user ' . $adm_fname . ' ' . $adm_flastname . ' was add. ');}
      }
    }
  }
  }}
  


  function reposi_user_admin_form_alter(&$form, FormStateInterface $form_state, $form_id) {
  $form['#validate'][] = '_reposi_user_admin_form_validate';
  return $form;
  }
  function _reposi_user_admin_form_validate(array &$form, FormStateInterface $form_state) {

	$adm_email1 = $form_state->getValue('adm_user_email1');

	if (empty($adm_email1)) {
        $new_email1 = NULL;
      } else {
        drupal_set_message(t('PASÓ A LA VALIDACIÓN'));
      }

  }
  /**
   * {@inheritdoc}
   */
   public function submitForm(array &$form, FormStateInterface $form_state) {
    
  /*  $email1=$this->email1;
    $email2=$this->email2;
    $email3=$this->email3;
*/
    $adm_email1 = $form_state->getValue('adm_user_email1');
    $adm_email2 = $form_state->getValue('adm_user_email2');
    $adm_email3 = $form_state->getValue('adm_user_email3');    

  if (!valid_email_address($adm_email1)) {
     drupal_set_message(t('Email 1 is not a valid e-mail address.'),'error');
    }
  if (!empty($adm_email2)){
    if (!valid_email_address($adm_email2)) {
     drupal_set_message(t('Email 2 is not a valid e-mail address.'),'error');
    }
  }
  if (!empty($adm_email3)){
    if (!valid_email_address($adm_email3)) {
     drupal_set_message(t('Email 3 is not a valid e-mail address.'),'error');
    }
  }

 } 

}

