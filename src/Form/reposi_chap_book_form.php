<?php

namespace Drupal\reposi\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\reposi\Controller\Reposi_info_publication;
use Drupal\Component\Utility\UrlHelper;
/**
 * Implements an example form.
 */
class reposi_chap_book_form extends FormBase {

public function getFormId() {
    return 'chap_book_form';
  }


public function buildForm(array $form, FormStateInterface $form_state) {
  global $_reposi_start_form;
    $markup = '<p>' . '<i>' . t('You must complete the required fields before the
              add authors.') . '</i>' . '</p>';
    $form['body'] = array('#markup' => $markup);
    $form['title'] = array(
      '#title' => t('Title of book'),
      '#type' => 'textfield',
      '#required' => TRUE,
      '#maxlength' => 511,
    );
    $form['chapter'] = array(
      '#title' => t('Chapter name'),
      '#type' => 'textfield',
      '#required' => TRUE,
    );
    $form['chapter_num'] = array(
      '#title' => t('Chapter number'),
      '#type' => 'textfield',
    );

////Fecha

      $form['year'] = array(
        '#title' => t('Publication Year'),
        '#type' => 'textfield',
        '#size' => 5,
        '#required' => TRUE,
        '#description' => t('Four numbers'),
      );

////////////////AUTORES


    $form['author'] = array(
        '#type' => 'details',
        '#open' => TRUE,
        '#title' => t('Authors'),
        '#prefix' => '<div id="modules-wrapper">',
        '#suffix' => '</div>',
      );

    $max = $form_state->get('fields_count');
      if(is_null($max)) {
        $max = 0;
        $form_state->set('fields_count', $max);
      }

    $header = array (
      'first_name' => t('First name'),
      'second_name'=> t('Second name'),
      'f_lastname' => t('First last name'),
      's_lastname' => t('Second last name'),
    );

    $form['author']['table'] = array(
      '#type' => 'table',
      '#title' => 'Author Table',
      '#header' => $header,
      '#empty' => t('No lines found'),
    );

    for ($i=0; $i<=$max; $i++) {

    $table = $form_state->getValue('table');
    $fn=$table[$i]['first_name'];
    $sn=$table[$i]['second_name'];
    $fln=$table[$i]['f_lastname'];
    $sln=$table[$i]['s_lastname'];

      $form['author']['table'][$i]['first_name'] = array(
        '#type' => 'textfield',
        '#value' => isset($fn)?$fn:'',
        '#size' => 16,
      );
      $form['author']['table'][$i]['second_name'] = array(
        '#type' => 'textfield',
        '#value' => isset($sn)?$sn:'',
        '#size' => 16,
      );
      $form['author']['table'][$i]['f_lastname'] = array(
        '#type' => 'textfield',
        '#value' => isset($fln)?$fln:'',
        '#size' => 16,
      );
      $form['author']['table'][$i]['s_lastname'] = array(
        '#type' => 'textfield',
        '#value' => isset($sln)?$sln:'',
        '#size' => 16,
      );
    }

    $form['author']['add'] = array(
        '#type' => 'submit',
        '#name' => 'addfield',
        '#value' => t('Add more field'),
        '#submit' => array(array($this, 'addfieldsubmit')),
        '#ajax' => array(
          'callback' => array($this, 'addfieldCallback'),
          'wrapper' => 'modules-wrapper',
          'effect' => 'fade',
        ),
      );




/////////chapter book
$form['vol'] = array(
    '#title' => t('Volume/Series'),
    '#type' => 'textfield',
  );
  $form['issue'] = array(
    '#title' => t('Number (Issue)'),
    '#type' => 'textfield',
  );
  $form['book_editor_name'] = array(
    '#title' => t('Publisher name'),
    '#type' => 'textfield',
    '#description' => t('Editor person'),
  );
  $form['edito'] = array(
    '#title' => t('Publisher'),
    '#type' => 'textfield',
  );
  $form['start_page'] = array(
    '#title' => t('Start page'),
    '#type' => 'textfield',
    '#maxlength' => 10,
  );
  $form['final_page'] = array(
    '#title' => t('Final page'),
    '#type' => 'textfield',
    '#maxlength' => 10,
  );
  $form['pub'] = array(
    '#title' => t('Place of publication'),
    '#type' => 'textfield',
  );
  $form['issn'] = array(
    '#title' => t('ISSN'),
    '#type' => 'textfield',
  );
  $form['isbn'] = array(
    '#title' => t('ISBN'),
    '#type' => 'textfield',
  );
  $form['url'] = array(
    '#title' => t('URL'),
    '#description' => t('Example: https://www.example.com'),
    '#type' => 'textfield',
    '#maxlength' => 511,
  );
  $form['doi'] = array(
    '#title' => t('DOI'),
    '#type' => 'textfield',
  );
  $form['save'] = array(
    '#type' => 'submit',
    '#value' => t('Save'),
  );

//////////////////final
  return $form;
}
////Add autor
public function addfieldsubmit(array &$form, FormStateInterface &$form_state) {
  $max = $form_state->get('fields_count') + 1;
  $form_state->set('fields_count',$max);
  $form_state->setRebuild(TRUE);
}

/**
  * Ajax callback to add new field.
  */
public function addfieldCallback(array &$form, FormStateInterface &$form_state) {
  return $form['author'];
}

public function validateForm(array &$form, FormStateInterface $form_state) {

  //function reposi_chap_title_validate($form, &$form_state){
    $title_validate = $form_state->getValue('chapter');
    $title_book = $form_state->getValue('title');
    $search_chap = db_select('reposi_article_book', 'ab');
    $search_chap->fields('ab', array('ab_subtitle_chapter', 'ab_title'))
            ->condition('ab.ab_type', 'Book Chapter', '=')
            ->condition('ab.ab_title', $title_book, '=')
            ->condition('ab.ab_subtitle_chapter', $title_validate, '=');
    $info_chap = $search_chap->execute();
    $new_title=Reposi_info_publication::reposi_string($title_validate);
    foreach ($info_chap as $titles) {
      drupal_set_message($new_title,'error');
      $new_titles=Reposi_info_publication::reposi_string($titles->ab_subtitle_chapter);
      if (strcasecmp($new_title, $new_titles) == 0) {
        $form_state->setErrorByName('chapter', t('This Book Chapter publication exists on Data Base.'));
      }
    }



///////////////////////////77




//function reposi_chap_title_validate($form, &$form_state){
  $title_validate = $form_state->getValue('chapter');
  $title_book = $form_state->getValue('title');
  $search_chap = db_select('reposi_article_book', 'ab');
  $search_chap->fields('ab', array('ab_subtitle_chapter', 'ab_title'))
          ->condition('ab.ab_type', 'Book Chapter', '=')
          ->condition('ab.ab_title', $title_book, '=')
          ->condition('ab.ab_subtitle_chapter', $title_validate, '=');
  $info_chap = $search_chap->execute();
  $new_title=Reposi_info_publication::reposi_string($title_validate);
  foreach ($info_chap as $titles) {
    drupal_set_message($new_title,'error');
    $new_titles=Reposi_info_publication::reposi_string($titles->ab_subtitle_chapter);
    if (strcasecmp($new_title, $new_titles) == 0) {
      $form_state->setErrorByName('chapter', t('This Book Chapter publication exists on Data Base.'));
    }
  }
  //function reposi_publiform_num_validate($form, &$form_state){
    $num_validate = $form_state->getValue('chapter_num');
    if(!empty($num_validate) && (!is_numeric($num_validate) || $num_validate < '0')){
      $form_state->setErrorByName('chapter_num', t('Chapter number is a numerical field.'));
    }
    //function reposi_publiform_year2_validate($form, &$form_state){
      $year_validate = $form_state->getValue('year');
      if(!is_numeric($year_validate) || $year_validate > '9999' || $year_validate < '1000') {
        $form_state->setErrorByName('publi_year', t('It is not an allowable value for year.'));
      }
      //function reposi_publiform_authorn_validate($form, &$form_state){
      $table = $form_state->getValue('table');
      $first_name_validate=$table[0]['first_name'];
      $first_lastname_validate=$table[0]['f_lastname'];
      if (empty($first_name_validate) || empty($first_lastname_validate)){
        $form_state->setErrorByName('first_name', t('One author is required as minimum
        (first name and last name).'));
      }

    //function reposi_chap_start_page_validate($form, &$form_state){
      $startp_validate = $form_state->getValue('start_page');
      if(!empty($startp_validate) && (!is_numeric($startp_validate) || $startp_validate < '0')){
        $form_state->setErrorByName('start_page', t('Start page is a numerical field.'));
      }
    //  function reposi_chap_final_page_validate($form, &$form_state){
        $finalp_validate = $form_state->getValue('final_page');
        if(!empty($finalp_validate) && (!is_numeric($finalp_validate) || $finalp_validate < '0')){
          $form_state->setErrorByName('final_page', t('Final page is a numerical field.'));
        }


        ///validate Url
          $url=$form_state->getValue('url');
          if(!empty($url) && !UrlHelper::isValid($url, TRUE))
          {
           $form_state->setErrorByName('uri', t('The URL is not valid.'));
          }

///////////////////////////////


  }
public function submitForm(array &$form, FormStateInterface $form_state) {

  $chap_title = $form_state->getValue('title');
    $chap_chap = $form_state->getValue('chapter');
    $chap_num = $form_state->getValue('chapter_num');
    $chap_year = $form_state->getValue('year');
    $chap_vol = $form_state->getValue('vol');
    $chap_issue = $form_state->getValue('issue');
    $chap_editor_name = $form_state->getValue('book_editor_name');
    $chap_editor = $form_state->getValue('edito');
    $chap_spage = $form_state->getValue('start_page');
    $chap_fpage = $form_state->getValue('final_page');
    $chap_place = $form_state->getValue('pub');
    $chap_issn = $form_state->getValue('issn');
    $chap_isbn = $form_state->getValue('isbn');
    $chap_url = $form_state->getValue('url');
    $chap_doi = $form_state->getValue('doi');
    if (!empty($chap_num)) {
      $new_chap_num = (int)$chap_num;
    } else {
      $new_chap_num = NULL;
    }
    db_insert('reposi_article_book')->fields(array(
      'ab_type'              => 'Book Chapter',
      'ab_title'             => $chap_title,
      'ab_subtitle_chapter'  => $chap_chap,
      'ab_chapter'           => $new_chap_num,
      'ab_journal_editorial' => $chap_editor,
      'ab_publisher'         => $chap_editor_name,
      'ab_place'             => $chap_place,
  ))->execute();
  $search_chap = db_select('reposi_article_book', 'ab');
  $search_chap->fields('ab')
          ->condition('ab.ab_type', 'Book Chapter', '=')
          ->condition('ab.ab_title', $chap_title, '=')
          ->condition('ab.ab_subtitle_chapter', $chap_chap, '=');
  $chap_id = $search_chap->execute()->fetchField();
  $new_chap_year = (int)$chap_year;
  db_insert('reposi_date')->fields(array(
      'd_year' => $new_chap_year,
      'd_abid' => $chap_id,
  ))->execute();
  db_insert('reposi_publication')->fields(array(
      'p_type'  => 'Book Chapter',
      'p_source'=> 'Manual',
      'p_title' => $chap_chap,
      'p_year'  => $new_chap_year,
      'p_check' => 1,
      'p_abid'  => $chap_id,
  ))->execute();
  if (!empty($chap_spage)) {
    $chap_start_page = (int)$chap_spage;
  } else {
    $chap_start_page = NULL;
  }
  if (!empty($chap_fpage)) {
    $chap_final_page = (int)$chap_fpage;
  } else {
    $chap_final_page = NULL;
  }
  if (!empty($chap_vol) || !empty($chap_issue) || !empty($chap_spage) ||
      !empty($chap_fpage) || !empty($chap_issn) || !empty($chap_isbn) ||
      !empty($chap_url) || !empty($chap_doi)) {
    db_insert('reposi_article_book_detail')->fields(array(
      'abd_volume'     => $chap_vol,
      'abd_issue'      => $chap_issue,
      'abd_start_page' => $chap_start_page,
      'abd_final_page' => $chap_final_page,
      'abd_issn'       => $chap_issn,
      'abd_isbn'       => $chap_isbn,
      'abd_url'        => $chap_url,
      'abd_doi'        => $chap_doi,
      'abd_abid'       => $chap_id,
    ))->execute();
  }

        $max = $form_state->get('fields_count');
        if(is_null($max)) {
        $max = 0;
        $form_state->set('fields_count', $max);
        }
        $table = $form_state->getValue('table');
        for ($a = 0; $a <= $max ; $a++) {

          $first_name_validate=$table[$a]['first_name'];
        $first_lastname_validate=$table[$a]['f_lastname'];
        $aut_fn=$table[$a]['first_name'];
        $aut_sn=$table[$a]['second_name'];
        $aut_fl=$table[$a]['f_lastname'];
        $aut_sl=$table[$a]['s_lastname'];

         !empty($aut_fn)?$aut_fn:'';
         !empty($aut_sn)?$aut_sn:'';
         !empty($aut_fl)?$aut_fl:'';
         !empty($aut_sl)?$aut_sl:'';

          $info_author = array('a_first_name'      => ucfirst($aut_fn),
                               'a_second_name'     => ucfirst($aut_sn),
                               'a_first_lastname'  => ucfirst($aut_fl),
                               'a_second_lastname' => ucfirst($aut_sl),
                              );

          if(!empty($table[$a]['first_name']) && !empty($table[$a]['f_lastname'])){
            $serch_a = db_select('reposi_author', 'a');
            $serch_a->fields('a')
                    ->condition('a.a_first_name', $aut_fn, '=')
                    ->condition('a.a_second_name', $aut_sn, '=')
                    ->condition('a.a_first_lastname', $aut_fl, '=')
                    ->condition('a.a_second_lastname', $aut_sl, '=');
            $serch_aut[$a] = $serch_a->execute()->fetchField();
            if (empty($serch_aut[$a])) {
              db_insert('reposi_author')->fields($info_author)->execute();
              $serch2_a = db_select('reposi_author', 'a');
              $serch2_a ->fields('a')
                        ->condition('a.a_first_name', $aut_fn, '=')
                        ->condition('a.a_second_name', $aut_sn, '=')
                        ->condition('a.a_first_lastname', $aut_fl, '=')
                        ->condition('a.a_second_lastname', $aut_sl, '=');
              $serch2_aut[$a] = $serch2_a->execute()->fetchField();
              $aut_publi_id = (int)$serch2_aut[$a];
              db_insert('reposi_publication_author')->fields(array(
                'ap_author_id' => $aut_publi_id,
                'ap_abid'      => $chap_id,
              ))->execute();
            } else {
              $aut_publi_id2 = (int)$serch_aut[$a];
              db_insert('reposi_publication_author')->fields(array(
                  'ap_author_id' => $aut_publi_id2,
                  'ap_abid'      => $chap_id,
              ))->execute();
            }
          } else {
            if(isset($table[$a]['first_name']) || isset($table[$a]['f_lastname'])){
              drupal_set_message(t('The authors without first name or first
              last name will not be save.'), 'warning');
            }
          }
      }

        drupal_set_message(t('Book Chapter: ') . $chap_chap . t(' was save.'));
        \Drupal::state()->delete('aut');


}
}
