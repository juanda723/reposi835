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
      '#submit' => array([$this, 'Save']),
    );
    $form['cancel'] = array(
      '#type' => 'submit',
      '#value' => t('Cancel'),
      '#submit' => array([$this, 'Cancel']),
    );
    $form['delete'] = array(
      '#type' => 'submit',
      '#value' => t('Delete'),
      '#submit' => array([$this, 'delete']),
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
    $uid=\Drupal::routeMatch()->getParameter('node');
    $serch_p = db_select('reposi_publication', 'p');
    $serch_p->fields('p')
    ->condition('p.p_unde', $uid, '=');
    $search_pub = $serch_p->execute()->fetchAssoc();
    $p_pid_scholar=$search_pub['p_pid_scholar'];
    $p_uid=$search_pub['p_uid'];
    $p_title=$search_pub['p_title'];
    $p_year=$search_pub['p_year'];
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

        db_insert('reposi_article_book')->fields(array(
          'ab_type'              => 'Article',
          'ab_title'             => $p_title,
        ))->execute();

        $search_art = db_select('reposi_article_book', 'ab');
        $search_art->fields('ab')
        ->condition('ab.ab_type', 'Article', '=')
        ->condition('ab.ab_title', $p_title, '=');
        $art_id = $search_art->execute()->fetchField();

        db_insert('reposi_date')->fields(array(
          'd_year' => $p_year,
          'd_abid' => $art_id,
        ))->execute();
        db_insert('reposi_publication')->fields(array(
          'p_type'  => 'Article',
          'p_source'=> 'Google Scholar',
          'p_title' => $p_title,
          'p_year'  => $p_year,
          'p_check' => 1,
          'p_abid'  => $art_id,
        ))->execute();
        $serch_p3 = db_select('reposi_publication_author', 'pa');
        $serch_p3->fields('pa')
        ->condition('pa.ap_unde', $uid, '=');
        $search_pub_au = $serch_p3->execute()->fetchAssoc();
        $pa_author=$search_pub_au['ap_author_id'];
        db_insert('reposi_publication_author')->fields(array(
          'ap_author_id'  => $pa_author,
          'ap_abid'  => $art_id,
        ))->execute();
        drupal_set_message('Save successfull.');
        reposidoc_scholar::delete_unde($uid);
      }
    }elseif ($selection=='1') {
      $serch_rp = db_select('reposi_publication', 'rp');
      $serch_rp->fields('rp')
      ->condition('rp.p_type', 'Book', '=')
      ->condition('rp.p_title', $p_title, '=');
      $search_pubc = $serch_rp->execute()->fetchAssoc();
      if (!empty($search_pubc)) {
        drupal_set_message('Error, the book already exists. To import you must delete the existing article','error');
      }
      else {

        db_insert('reposi_article_book')->fields(array(
          'ab_type'              => 'Book',
          'ab_title'             => $p_title,
        ))->execute();

        $search_art = db_select('reposi_article_book', 'ab');
        $search_art->fields('ab')
        ->condition('ab.ab_type', 'Book', '=')
        ->condition('ab.ab_title', $p_title, '=');
        $book_id = $search_art->execute()->fetchField();

        db_insert('reposi_date')->fields(array(
          'd_year' => $p_year,
          'd_abid' => $book_id,
        ))->execute();
        db_insert('reposi_publication')->fields(array(
          'p_type'  => 'Book',
          'p_source'=> 'Google Scholar',
          'p_title' => $p_title,
          'p_year'  => $p_year,
          'p_check' => 1,
          'p_abid'  => $book_id,
        ))->execute();
        $serch_p3 = db_select('reposi_publication_author', 'pa');
        $serch_p3->fields('pa')
        ->condition('pa.ap_unde', $uid, '=');
        $search_pub_au = $serch_p3->execute()->fetchAssoc();
        $pa_author=$search_pub_au['ap_author_id'];
        db_insert('reposi_publication_author')->fields(array(
          'ap_author_id'  => $pa_author,
          'ap_abid'  => $book_id,
        ))->execute();
        db_insert('reposi_article_book_detail')->fields(array(
          'abd_abid'       => $book_id,
        ))->execute();
        drupal_set_message('Save successfull.');
        reposidoc_scholar::delete_unde($uid);
      }
    }elseif ($selection=='2') {
      $serch_rp = db_select('reposi_article_book', 'rp');
      $serch_rp->fields('rp')
      ->condition('rp.ab_type', 'Book Chapter', '=')
      ->condition('rp.ab_title', 'without a book', '=')
      ->condition('rp.ab_subtitle_chapter', $p_title, '=');
      $search_pubc = $serch_rp->execute()->fetchAssoc();
      if (!empty($search_pubc)) {
        drupal_set_message('Error, the Book Chapter already exists. To import you must delete the existing article','error');
      }
      else {

        db_insert('reposi_article_book')->fields(array(
          'ab_type'              => 'Book Chapter',
          'ab_title'             => 'without a book',
          'ab_subtitle_chapter'  => $p_title,
        ))->execute();

        $search_art = db_select('reposi_article_book', 'ab');
        $search_art->fields('ab')
        ->condition('ab.ab_type', 'Book Chapter', '=')
        ->condition('ab.ab_title', 'without a book', '=')
        ->condition('ab.ab_subtitle_chapter', $p_title, '=');
        $chap_id = $search_art->execute()->fetchField();

        db_insert('reposi_date')->fields(array(
          'd_year' => $p_year,
          'd_abid' => $chap_id,
        ))->execute();
        db_insert('reposi_publication')->fields(array(
          'p_type'  => 'Book Chapter',
          'p_source'=> 'Google Scholar',
          'p_title' => $p_title,
          'p_year'  => $p_year,
          'p_check' => 1,
          'p_abid'  => $chap_id,
        ))->execute();
        $serch_p3 = db_select('reposi_publication_author', 'pa');
        $serch_p3->fields('pa')
        ->condition('pa.ap_unde', $uid, '=');
        $search_pub_au = $serch_p3->execute()->fetchAssoc();
        $pa_author=$search_pub_au['ap_author_id'];
        db_insert('reposi_publication_author')->fields(array(
          'ap_author_id'  => $pa_author,
          'ap_abid'  => $chap_id,
        ))->execute();
        db_insert('reposi_article_book_detail')->fields(array(
          'abd_abid'       => $chap_id,
        ))->execute();
        drupal_set_message('Save successfull.');
        reposidoc_scholar::delete_unde($uid);
      }
    } elseif ($selection=='3') {
      $serch_rp = db_select('reposi_confer_patent', 'rp');
      $serch_rp->fields('rp')
      ->condition('rp.cp_type', 'Conference', '=')
      ->condition('rp.cp_title', 'without a conference', '=')
      ->condition('rp.cp_publication', $p_title, '=');
      $search_pubc = $serch_rp->execute()->fetchAssoc();

      if (!empty($search_pubc)) {
        drupal_set_message('Error, the conference already exists. To import you must delete the existing article','error');
      }
      else {
        db_insert('reposi_confer_patent')->fields(array(
          'cp_type'       => 'Conference',
          'cp_title'      => 'without a conference',
          'cp_publication'=>  $p_title,
        ))->execute();

        $search_con = db_select('reposi_confer_patent', 'cp');
        $search_con->fields('cp')
        ->condition('cp.cp_type', 'Conference', '=')
        ->condition('cp.cp_title', 'without a conference', '=')
        ->condition('cp.cp_publication', $p_title, '=');
        $con_id = $search_con->execute()->fetchField();
        $conference_id = (int)$con_id;

        db_insert('reposi_date')->fields(array(
          'd_year'  => $p_year,
          'd_cpid'  => $conference_id,
        ))->execute();
        db_insert('reposi_date')->fields(array(
          'd_year'  => $p_year,
          'd_cpid'  => $conference_id,
        ))->execute();
        db_insert('reposi_date')->fields(array(
          'd_year'  => $p_year,
          'd_cpid'  => $conference_id,
        ))->execute();

        db_insert('reposi_publication')->fields(array(
          'p_type'  => 'Conference',
          'p_source'=> 'Google Scholar',
          'p_title' => $p_title,
          'p_year'  => $p_year,
          'p_check' => 1,
          'p_cpid'  => $conference_id,
        ))->execute();
        $serch_p3 = db_select('reposi_publication_author', 'pa');
        $serch_p3->fields('pa')
        ->condition('pa.ap_unde', $uid, '=');
        $search_pub_au = $serch_p3->execute()->fetchAssoc();
        $pa_author=$search_pub_au['ap_author_id'];
        db_insert('reposi_publication_author')->fields(array(
          'ap_author_id'  => $pa_author,
          'ap_cpid'  => $conference_id,
        ))->execute();
        drupal_set_message('Save successfull.');
        reposidoc_scholar::delete_unde($uid);
      }
    }elseif ($selection=='5') {
      $serch_rp = db_select('reposi_confer_patent', 'rp');
      $serch_rp->fields('rp')
      ->condition('rp.cp_type', 'Patent', '=')
      ->condition('rp.cp_title', $p_title, '=');
      $search_pubc = $serch_rp->execute()->fetchAssoc();
      if (!empty($search_pubc)) {
        drupal_set_message('Error, the Patent already exists. To import you must delete the existing Conferen','error');
      }
      else {
        db_insert('reposi_confer_patent')->fields(array(
          'cp_type'       => 'Patent',
          'cp_title'      => $p_title,
        ))->execute();

        $search_pat = db_select('reposi_confer_patent', 'cp');
        $search_pat->fields('cp')
        ->condition('cp.cp_type', 'Patent', '=')
        ->condition('cp.cp_title', $p_title, '=');
        $pat_id = $search_pat->execute()->fetchField();
        $patent_id = (int)$pat_id;

        db_insert('reposi_date')->fields(array(
          'd_year'  => $p_year,
          'd_cpid'  => $patent_id,
        ))->execute();


        db_insert('reposi_publication')->fields(array(
          'p_type'  => 'Patent',
          'p_source'=> 'Google Scholar',
          'p_title' => $p_title,
          'p_year'  => $p_year,
          'p_check' => 1,
          'p_cpid'  => $patent_id,
        ))->execute();
        $serch_p3 = db_select('reposi_publication_author', 'pa');
        $serch_p3->fields('pa')
        ->condition('pa.ap_unde', $uid, '=');
        $search_pub_au = $serch_p3->execute()->fetchAssoc();
        $pa_author=$search_pub_au['ap_author_id'];
        db_insert('reposi_publication_author')->fields(array(
          'ap_author_id'  => $pa_author,
          'ap_cpid'  => $patent_id,
        ))->execute();
        drupal_set_message('Save successfull.');
        reposidoc_scholar::delete_unde($uid);
      }
    }elseif ($selection=='4') {
      $serch_rp = db_select('reposi_thesis_sw', 'rp');
      $serch_rp->fields('rp')
      ->condition('rp.ts_type', 'Thesis', '=')
      ->condition('rp.ts_title', $p_title, '=');
      $search_pubc = $serch_rp->execute()->fetchAssoc();
      if (!empty($search_pubc)) {
        drupal_set_message('Error, the Thesis already exists. To import you must delete the existing Conferen','error');
      }
      else {
        db_insert('reposi_thesis_sw')->fields(array(
          'ts_type'       => 'Thesis',
          'ts_title'      => $p_title,
          'ts_degree'     => 'Unspecified',
        ))->execute();

        $search_the = db_select('reposi_thesis_sw', 'th');
        $search_the->fields('th')
        ->condition('th.ts_type', 'Thesis', '=')
        ->condition('th.ts_title', $p_title, '=');
        $the_id = $search_the->execute()->fetchField();
        $thesis_id = (int)$the_id;

        db_insert('reposi_date')->fields(array(
          'd_year'  => $p_year,
          'd_tsid'  => $thesis_id,
        ))->execute();


        db_insert('reposi_publication')->fields(array(
          'p_type'  => 'Thesis',
          'p_source'=> 'Google Scholar',
          'p_title' => $p_title,
          'p_year'  => $p_year,
          'p_check' => 1,
          'p_tsid'  => $thesis_id,
        ))->execute();
        $serch_p3 = db_select('reposi_publication_author', 'pa');
        $serch_p3->fields('pa')
        ->condition('pa.ap_unde', $uid, '=');
        $search_pub_au = $serch_p3->execute()->fetchAssoc();
        $pa_author=$search_pub_au['ap_author_id'];
        db_insert('reposi_publication_author')->fields(array(
          'ap_author_id'  => $pa_author,
          'ap_tsid'  => $thesis_id,
        ))->execute();
        drupal_set_message('Save successfull.');
        reposidoc_scholar::delete_unde($uid);
      }
    }
    elseif ($selection=='6') {
      $serch_rp = db_select('reposi_thesis_sw', 'rp');
      $serch_rp->fields('rp')
      ->condition('rp.ts_type', 'Software', '=')
      ->condition('rp.ts_title', $p_title, '=');
      $search_pubc = $serch_rp->execute()->fetchAssoc();
      if (!empty($search_pubc)) {
        drupal_set_message('Error, the Thesis already exists. To import you must delete the existing Conferen','error');
      }
      else {
        db_insert('reposi_thesis_sw')->fields(array(
          'ts_type'       => 'Software',
          'ts_title'      => $p_title,
        ))->execute();

        $search_sw = db_select('reposi_thesis_sw', 'sw');
        $search_sw->fields('sw')
        ->condition('sw.ts_type', 'Software', '=')
        ->condition('sw.ts_title', $p_title, '=');
        $softw_id = $search_sw->execute()->fetchField();
        $sw_id = (int)$softw_id;

        db_insert('reposi_date')->fields(array(
          'd_year'  => $p_year,
          'd_tsid'  => $sw_id,
        ))->execute();


        db_insert('reposi_publication')->fields(array(
          'p_type'  => 'Software',
          'p_source'=> 'Google Scholar',
          'p_title' => $p_title,
          'p_year'  => $p_year,
          'p_check' => 1,
          'p_tsid'  => $sw_id,
        ))->execute();
        $serch_p3 = db_select('reposi_publication_author', 'pa');
        $serch_p3->fields('pa')
        ->condition('pa.ap_unde', $uid, '=');
        $search_pub_au = $serch_p3->execute()->fetchAssoc();
        $pa_author=$search_pub_au['ap_author_id'];
        db_insert('reposi_publication_author')->fields(array(
          'ap_author_id'  => $pa_author,
          'ap_tsid'  => $sw_id,
        ))->execute();
        drupal_set_message('Save successfull.');
        reposidoc_scholar::delete_unde($uid);
      }
    }

    $form_state->setRedirect('reposi.gspub');
  }

  function delete($form, &$form_state){
    $uid=\Drupal::routeMatch()->getParameter('node');
    $form_state->setRedirect('reposi.deletegs', ['node' => $uid]);
  }

  public function validateForm(array &$form, FormStateInterface $form_state){

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
