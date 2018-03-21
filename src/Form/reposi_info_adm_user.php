<?php

namespace Drupal\reposi\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Query;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\reposi\Controller\Reposi_info_publication;
use Drupal\Component\Utility\UrlHelper;
use Drupal\reposi\Form\Url;
/**
 * Implements an example form.
 */
class reposi_info_adm_user extends FormBase {

  public function getFormId() {
    return 'info_adm_user';
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
      $form['validate'] = array(
        '#type' => 'submit',
        '#value' => t('Validate/Unvalidated'),
        '#submit' => array([$this, 'Validate_Unvalidated']),
      );
      $form['delete'] = array(
        '#type' => 'submit',
        '#value' => t('Delete'),
        '#submit' => array([$this, 'Delete']),
      );

      return $form;
}
//end buildForm
public function editForm(array &$form, FormStateInterface $form_state) {
    //Edicion Formulario
    $arg=\Drupal::routeMatch()->getParameter('node');
    $form_state->setRedirect('reposi.Reposi_info_publicationAF', ['node' => $arg]);
}

function Validate_Unvalidated($form, &$form_state){
    $search_publi = db_select('reposi_publication','p');
    $arg=\Drupal::routeMatch()->getParameter('node');
    db_update('reposi_state')->fields(array(
          's_type'   => 'Inactive',
    ))->condition('s_uid', $arg)
    ->execute();
    drupal_set_message('User is update to inactive');

public function validateForm(array &$form, FormStateInterface $form_state){

}

public function Delete(array &$form, FormStateInterface $form_state) {
    //Edicion Formulario
    $arg=\Drupal::routeMatch()->getParameter('node');
    $form_state->setRedirect('reposi.Reposi_del_publi', ['node' => $arg]);
}

/**
 * {@inheritdoc}
 */
public function submitForm(array &$form, FormStateInterface $form_state) {

}


}//end class
