<?php

namespace Drupal\reposi_apischolar\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\reposi\Controller\Reposi_info_publication;
use Drupal\reposi_apischolar\Controller\reposidoc_scholar;
use Drupal\Core\Url;
use Drupal\Core\Link;
/**
* Implements an example form.
*/

class reposi_gstype extends FormBase {


  /**
  * {@inheritdoc}
  */
  public function getFormId() {
    return 'reposi_gstype_id';
  }

  /**
  * {@inheritdoc}
  */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $uid=\Drupal::routeMatch()->getParameter('node');
    //$uid=1;
    $serch_p = db_select('reposi_publication', 'p');
    $serch_p->fields('p')
    ->condition('p.p_unde', $uid, '=');
    $search_pub = $serch_p->execute()->fetchAssoc();
    $p_unde=$search_pub['p_unde'];
    $p_title=$search_pub['p_title'];
    $p_year=$search_pub['p_year'];

    $serch_ap = db_select('reposi_publication_author', 'a');
    $serch_ap->fields('a', array('ap_author_id', 'ap_unde'))
    ->condition('a.ap_unde', $p_unde);
    $p_a = $serch_ap->execute();

    $p_a -> allowRowCount = TRUE;
    $num_aut = $p_a->rowCount();
    $list_aut_abc='';
    $flag_aut = 0;
    $authors_art = '';
    foreach ($p_a as $aut_art) {
      $flag_aut++;
      $search_aut = db_select('reposi_author', 'a');
      $search_aut->fields('a')
      ->condition('a.aid', $aut_art->ap_author_id, '=');
      $each_aut = $search_aut->execute()->fetchAssoc();
      if ($flag_aut <> $num_aut) {
        $f_name = Reposi_info_publication::reposi_string($each_aut['a_first_name']);
        if (!empty($each_aut['a_second_name'])) {
          $s_name = Reposi_info_publication::reposi_string($each_aut['a_second_name']);
          $authors_art = $authors_art . \Drupal::l($each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] .
          ' ' . $f_name[0] . '. ' . $s_name[0] . '.',
          Url::fromRoute('reposi.author_aid',['node'=>$aut_art->ap_author_id])) . '.';
        } else {
          $authors_art = $authors_art . \Drupal::l($each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] .
          ' ' . $f_name[0] . '. ',Url::fromRoute('reposi.author_aid',['node'=>$aut_art->ap_author_id])) . '.';
        }
      } else {
        $search_aut = db_select('reposi_author', 'a');
        $search_aut->fields('a')
        ->condition('a.aid', $aut_art->ap_author_id, '=');
        $each_aut = $search_aut->execute()->fetchAssoc();
        $f_name = Reposi_info_publication::reposi_string($each_aut['a_first_name']);
        if (!empty($each_aut['a_second_name'])) {
          $s_name = Reposi_info_publication::reposi_string($each_aut['a_second_name']);
          $authors_art = $authors_art . \Drupal::l($each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] .
          ' ' . $f_name[0] . '. ' . $s_name[0] . '.',Url::fromRoute('reposi.author_aid',['node'=>$aut_art->ap_author_id])) . '.';
        } else {
          $authors_art = $authors_art . \Drupal::l($each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] .
          ' ' . $f_name[0] . '. ',Url::fromRoute('reposi.author_aid',['node'=>$aut_art->ap_author_id])) . '.';
        }
      }
    }

    $form['uid'] = array(
      '#type' => 'value',
      '#value' => $p_unde,
    );
    $form['au_id'] = array(
      '#type' => 'value',
      '#value' => $p_title,
    );
    $form['aua_id'] = array(
      '#type' => 'value',
      '#value' => $p_year,
    );
    $markup = '<p>' .'Title: '.'<b>'.$p_title.'</b>'.
    '<p>'.'Year: '.$p_year.'<p>'.'Author: '. $authors_art.'<p>' .'</b>';
    $form['type_publication'] = array(
      '#title' => t('Type Publication'),
      '#type' => 'select',
      '#options' => array(
        t('Article'),
        t('Book'),
        t('Chapter Book'),
        t('Conference'),
        t('Thesis'),
        t('Patent'),
        t('Software'),
      ),
      '#required' => TRUE,
    );
    $form['body'] = array('#markup' => $markup);
    $form['accept'] = array(
      '#type' => 'submit',
      '#value' => t('Import data GS'),
    );
    $form['save'] = array(
      '#type' => 'submit',
      '#value' => t('Save'),
      '#submit' => array([$this, 'Cancel']),
    );
    $form['cancel'] = array(
      '#type' => 'submit',
      '#value' => t('Cancel'),
      '#submit' => array([$this, 'Save']),
    );
    return $form;
  }

  /**
  * {@inheritdoc}
  */

  function Cancel($form, &$form_state){
    $form_state->setRedirect('reposi.gspub');
  }
  function Save($form, &$form_state){
    $form_state->setRedirect('reposi.gspub');
  }

  public function validateForm(array &$form, FormStateInterface $form_state)
  {

  }

  /**
  * {@inheritdoc}
  */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $uid=\Drupal::routeMatch()->getParameter('node');
    //$uid
    $serch_p = db_select('reposi_publication', 'p');
    $serch_p->fields('p')
    ->condition('p.p_unde', $uid, '=');
    $search_pub = $serch_p->execute()->fetchAssoc();
    $p_pid_scholar=$search_pub['p_pid_scholar'];
    $p_uid=$search_pub['p_uid'];
    $p_title=$search_pub['p_title'];
    $serch_u = db_select('reposi_user', 'u');
    $serch_u->fields('u')
    ->condition('u.uid', $p_uid, '=');
    $search_use = $serch_u->execute()->fetchAssoc();
    $user_gs=$search_use['u_id_scholar'];

    $serch_st = db_select('reposi_state', 'rs');
    $serch_st->fields('rs')
    ->condition('rs.s_uid', $p_uid, '=');
    $search_state = $serch_st->execute()->fetchAssoc();
    $user_act=$search_state['s_type'];
    if (empty($user_gs) || $user_act=='Inactive') {
      drupal_set_message('User Inactive or does not exist','error');
    }else{
      $selection=$form_state->getValue('type_publication');
      if ($selection=='0') {
        $serch_rp = db_select('reposi_publication', 'rp');
        $serch_rp->fields('rp')
        ->condition('rp.p_type', 'Article', '=')
        ->condition('rp.p_title', $p_title, '=');
        $search_pubc = $serch_rp->execute()->fetchAssoc();
        if (!empty($search_pubc)) {
          drupal_set_message('Error, the article already exists. To import you must delete the existing article','error');
        }
        else {
          //Article
          $functionart=reposidoc_scholar::pubscolar_art($uid,$p_uid,$user_gs,$p_pid_scholar);
          $serch_p = db_select('reposi_publication', 'p');
          $serch_p->fields('p')
          ->orderBy('p.p_abid', 'DESC')
          ->condition('p.p_title', $p_title, '=');
          $search_pub = $serch_p->execute()->fetchAssoc();
          if ($functionart==1) {
            $form_state->setRedirect('reposi.gspub');
          }else{
            $form_state->setRedirect('reposi.Reposi_articleinformation', ['node' => (int)$search_pub['p_abid']]);
          }
        }
      }elseif ($selection=='1') {
        $serch_rp = db_select('reposi_publication', 'rp');
        $serch_rp->fields('rp')
        ->condition('rp.p_type', 'Book', '=')
        ->condition('rp.p_title', $p_title, '=');
        $search_pubc = $serch_rp->execute()->fetchAssoc();
        if (!empty($search_pubc)) {
          drupal_set_message('Error, the book already exists. To import you must delete the existing book','error');
        }
        else {
          //Book
          $functionbook=reposidoc_scholar::pubscolar_book($uid,$p_uid,$user_gs,$p_pid_scholar);
          $serch_p = db_select('reposi_publication', 'p');
          $serch_p->fields('p')
          ->orderBy('p.p_abid', 'DESC')
          ->condition('p.p_title', $p_title, '=');
          $search_pub = $serch_p->execute()->fetchAssoc();
          if ($functionbook==1) {
            $form_state->setRedirect('reposi.gspub');
          }else{
            $form_state->setRedirect('reposi.Reposi_bookinformation', ['node' => (int)$search_pub['p_abid']]);
          }
        }
      }elseif ($selection=='2') {
        //Chapter
        $functionchap=reposidoc_scholar::pubscolar_chap($uid,$p_uid,$user_gs,$p_pid_scholar);
        $serch_p = db_select('reposi_publication', 'p');
        $serch_p->fields('p')
        ->orderBy('p.p_abid', 'DESC')
        ->condition('p.p_title', $p_title, '=');
        $search_pub = $serch_p->execute()->fetchAssoc();
        if ($functionchap==1) {
          $form_state->setRedirect('reposi.gspub');
        }else{
          $form_state->setRedirect('reposi.Reposi_chapinformation', ['node' => (int)$search_pub['p_abid']]);
        }
      }elseif ($selection=='3') {
        //Conference
        $functioncon=reposidoc_scholar::pubscolar_con($uid,$p_uid,$user_gs,$p_pid_scholar);
        $serch_p = db_select('reposi_publication', 'p');
        $serch_p->fields('p')
        ->orderBy('p.p_cpid', 'DESC')
        ->condition('p.p_title', $p_title, '=');
        $search_pub = $serch_p->execute()->fetchAssoc();
        if ($functioncon==1) {
          $form_state->setRedirect('reposi.gspub');
        }else{
        $form_state->setRedirect('reposi.Reposi_coninformation', ['node' => (int)$search_pub['p_cpid']]);
      }
      }elseif ($selection=='4') {
        $serch_rp = db_select('reposi_publication', 'rp');
        $serch_rp->fields('rp')
        ->condition('rp.p_type', 'Thesis', '=')
        ->condition('rp.p_title', $p_title, '=');
        $search_pubc = $serch_rp->execute()->fetchAssoc();
        if (!empty($search_pubc)) {
          drupal_set_message('Error, the Thesis already exists. To import you must delete the existing Thesis','error');
        }
        else {
          //Thesis
          $functionthe=reposidoc_scholar::pubscolar_the($uid,$p_uid,$user_gs,$p_pid_scholar);
          $serch_p = db_select('reposi_publication', 'p');
          $serch_p->fields('p')
          ->orderBy('p.p_tsid', 'DESC')
          ->condition('p.p_title', $p_title, '=');
          $search_pub = $serch_p->execute()->fetchAssoc();
          if ($functionthe==1) {
            $form_state->setRedirect('reposi.gspub');
          }else{
            $form_state->setRedirect('reposi.Reposi_thesinformation', ['node' => (int)$search_pub['p_tsid']]);
          }
        }
      }elseif ($selection=='5') {
        $serch_rp = db_select('reposi_publication', 'rp');
        $serch_rp->fields('rp')
        ->condition('rp.p_type', 'Patent', '=')
        ->condition('rp.p_title', $p_title, '=');
        $search_pubc = $serch_rp->execute()->fetchAssoc();
        if (!empty($search_pubc)) {
          drupal_set_message('Error, the Patent already exists. To import you must delete the existing Patent','errror');
        }
        else {
          //Patent
          $functionpat=reposidoc_scholar::pubscolar_pat($uid,$p_uid,$user_gs,$p_pid_scholar);
          $serch_p = db_select('reposi_publication', 'p');
          $serch_p->fields('p')
          ->orderBy('p.p_cpid', 'DESC')
          ->condition('p.p_title', $p_title, '=');
          $search_pub = $serch_p->execute()->fetchAssoc();
          if ($functionpat==1) {
            $form_state->setRedirect('reposi.gspub');
          }else{
            $form_state->setRedirect('reposi.Reposi_patinformation', ['node' => (int)$search_pub['p_cpid']]);
          }
        }
      }elseif ($selection=='6') {
        $serch_rp = db_select('reposi_publication', 'rp');
        $serch_rp->fields('rp')
        ->condition('rp.p_type', 'Software', '=')
        ->condition('rp.p_title', $p_title, '=');
        $search_pubc = $serch_rp->execute()->fetchAssoc();
        if (!empty($search_pubc)) {
          drupal_set_message('Error, the Patent already exists. To import you must delete the existing Patent','error');
        }
        else {
          //Software
          $functionsof=reposidoc_scholar::pubscolar_sof($uid,$p_uid,$user_gs,$p_pid_scholar);
          $serch_p = db_select('reposi_publication', 'p');
          $serch_p->fields('p')
          ->orderBy('p.p_tsid', 'DESC')
          ->condition('p.p_title', $p_title, '=');
          $search_pub = $serch_p->execute()->fetchAssoc();
          if ($functionsof==1) {
            $form_state->setRedirect('reposi.gspub');
          }else{
            $form_state->setRedirect('reposi.Reposi_sofinformation', ['node' => (int)$search_pub['p_tsid']]);
          }
        }
      }
    }
  }//end function
}//end class
