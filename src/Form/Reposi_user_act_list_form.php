<?php

namespace Drupal\reposi\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Implements an example form.
 */
class Reposi_user_act_list_form extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'reposi_user_act_list_form';
  }

  /**
   * {@inheritdoc}
   */

  public function buildForm(array $form, FormStateInterface $form_state) {

    $header = array('ID', t('Name'), t('Last name'), t('Email'));
    $search_act_state = db_select('reposi_state', 's');
    $search_act_state->fields('s', array('s_uid'))
                     ->condition('s.s_type', 'Active', '=');
    $id_act_state = $search_act_state->execute();
    foreach ($id_act_state as $list_act) {
      $query = db_select('reposi_user', 'p');
      $query->fields('p', array('uid', 'u_first_name', 'u_first_lastname',
                     'u_second_lastname', 'u_email'))
            ->condition('p.uid', $list_act->s_uid, '=')
            ->orderBy('u_first_name', 'ASC');
      $results[] = $query->execute()->fetchAssoc();
    }
    $rows = array();
    foreach ($results as $row) {
      if (!empty($row)) {
	$url = Url::fromRoute('reposi.admuser_info', ['node' => $row['uid']]);
	$link= \Drupal::l(t($row['uid']), $url);
        $rows[$row['uid']] = array($link,
                        $row['u_first_name'],
                        $row['u_first_lastname'] . ' ' . $row['u_second_lastname'],
                        $row['u_email'],
        );
      }
    }

   $form['table'] = array ('#type'     => 'tableselect',
			    '#title' => $this->t('Users'),
                            '#header'   => $header,
                            '#options'  => $rows,
                            '#multiple' => TRUE,
                            '#empty'    => t('No records.')
                            );

    $form['pager'] = ['#type' => 'pager'];

    $form['deactivate'] = array(
      '#type' => 'submit',
      '#value' => t('Deactivate select items'),
    );
    return $form;


  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state)
  {

/*  $check_del = $form_state->getValue('table');
    foreach ($check_del as $user_check_del){
    $results=$form_state->getValue(['complete form','table', '#options', $user_check_del, 3]);
    }
drupal_set_message(print_r($results,1));*/

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

        $results = array_filter($form_state->getValue('table'));
	foreach ($results as $result) 
        {
	db_update('reposi_state')->fields(array(
        's_type'   => 'Inactive',
        ))->condition('s_uid',$result)
        ->execute();
        }
  }

}
