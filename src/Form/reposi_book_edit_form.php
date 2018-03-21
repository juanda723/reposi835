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

/**
 * Implements an example form.
 */
class reposi_book_edit_form extends FormBase {

  public function getFormId() {
    return 'reposi_book_edit_form_id';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
 
    $abid = \Drupal::routeMatch()->getParameter('node');
    $search_book = db_select('reposi_article_book', 'ab');
    $search_book->fields('ab')
            ->condition('ab.abid', $abid, '=');
    $this_book = $search_book->execute()->fetchAssoc();
    $search_book_detail = db_select('reposi_article_book_detail', 'abd');
    $search_book_detail->fields('abd')
            ->condition('abd.abd_abid', $abid, '=');
    $this_book_2 = $search_book_detail->execute()->fetchAssoc();
    $search_date = db_select('reposi_date', 'd');
    $search_date->fields('d')
                ->condition('d.d_abid', $abid, '=');
    $book_date = $search_date->execute()->fetchAssoc();
    
    $search_p_a = db_select('reposi_publication_author', 'pa');
    $search_p_a->fields('pa')
               ->condition('pa.ap_abid', $abid, '=');
    $p_a = $search_p_a->execute();
    $p_a -> allowRowCount = TRUE;
    $num_aut = $p_a->rowCount();
    foreach ($p_a as $id_author){
      $search_aut = db_select('reposi_author', 'a');
      $search_aut->fields('a')
                 ->condition('a.aid', $id_author->ap_author_id, '=');
      $info_aut = $search_aut->execute()->fetchAssoc();
      $this_aut[] = $info_aut;
    }



//---------------------------------------------------------------------------------------------------------
/*
    $search_art = db_select('reposi_article_book', 'ab');
    $search_art->fields('ab')
            ->condition('ab.abid', $abid, '=');
    $this_art = $search_art->execute()->fetchAssoc();
    $search_art_detail = db_select('reposi_article_book_detail', 'abd');
    $search_art_detail->fields('abd')
            ->condition('abd.abd_abid', $abid, '=');
    $this_art_2 = $search_art_detail->execute()->fetchAssoc();
    $search_date = db_select('reposi_date', 'd');
    $search_date->fields('d')
                ->condition('d.d_abid', $abid, '=');
    $art_date = $search_date->execute()->fetchAssoc();
    $search_publi_key = db_select('reposi_publication_keyword', 'pk');
    $search_publi_key->fields('pk')
                     ->condition('pk.pk_abid', $abid, '=');
    $id_keyword = $search_publi_key->execute();
    $id_keyword -> allowRowCount = TRUE;
    $num_keyw = $id_keyword->rowCount();
    foreach ($id_keyword as $key_id) {
      $search_keyw = db_select('reposi_keyword', 'k');
      $search_keyw->fields('k')
                  ->condition('k.kid', $key_id->pk_keyword_id, '=');
      $keywords = $search_keyw->execute()->fetchAssoc();
      $this_keyw[] = $keywords['k_word'];
    }
    $search_p_a = db_select('reposi_publication_author', 'pa');
    $search_p_a->fields('pa')
               ->condition('pa.ap_abid', $abid, '=');
    $p_a = $search_p_a->execute();
    $p_a -> allowRowCount = TRUE;
    $num_aut = $p_a->rowCount();
    foreach ($p_a as $id_author){
      $search_aut = db_select('reposi_author', 'a');
      $search_aut->fields('a')
                 ->condition('a.aid', $id_author->ap_author_id, '=');
      $info_aut = $search_aut->execute()->fetchAssoc();
      $this_aut[] = $info_aut;
    }
*/
    $form['abid'] = array(
      '#type' => 'value',
      '#value' => $abid,
    );
    $form['num_aut'] = array(
      '#type' => 'value',
      '#value' => $num_aut,
    );
    $form['one'] = array(
      '#title' => t('Publication Title: Non-editable field'),
      '#type' => 'details',
      '#open' => TRUE,
      '#size' => 10,
    );
    $form['one']['title'] = array(
      '#type' => 'item',
      '#title' => $this_book['ab_title'],
    );
    $form['subtitle'] = array(
      '#title' => t('Subtitle'),
      '#type' => 'details',
      '#open' => TRUE,
      '#size' => 10,
    );
    $form['subtitle']['sub'] = array(
      '#type' => 'textfield',
      '#size' => 82,
      '#default_value' => $this_book['ab_subtitle_chapter'],
    );
    $form['two'] = array(
      '#title' => t('Description'),
      '#type' => 'details',
      '#open' => TRUE,
      '#size' => 10,
    );
    $form['two']['description'] = array(
      '#default_value' => $this_book['ab_abstract'],
      '#type' => 'textarea',
    );
    $form['date'] = array(
      '#title' => t('Publication year'),
      '#type' => 'details',
      '#open' => TRUE,
      '#size' => 10,
    );
 /*   $form['date']['day'] = array(
      '#title' => t('Day'),
      '#type' => 'textfield',
      '#size' => 5,
      '#default_value' => $book_date['d_day'],
      '#description' => t('1-31'),
    );
    $form['date']['month'] = array(
      '#title' => t('Month'),
      '#type' => 'textfield',
      '#size' => 5,
      '#default_value' => $book_date['d_month'],
      '#description' => t('1-12'),
    );*/
    $form['date']['year'] = array(
      '#title' => t('Year'),
      '#type' => 'textfield',
      '#size' => 5,
      '#default_value' => $book_date['d_year'],
      '#required' => TRUE,
      '#description' => t('Four numbers'),
    );
  $header = array (
    'first_name' => t('First name'),
    'second_name'=> t('Second name'),
    'f_lastname' => t('First last name'),
    's_lastname' => t('Second last name'),
  );
  $form['author'] = array(
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => t('Author(s)'),
      '#prefix' => '<div id="modules-wrapper">',
      '#suffix' => '</div>',
    );
  $form['author']['table'] = array(
    '#type' => 'table',
    '#title' => 'Author Table',
    '#header' => $header,
    '#empty' => t('No lines found'),
  );
  $cont= $form_state->get('fields_count');
    if(is_null($cont)) {
      $cont = 0;
      $form_state->set('fields_count', $cont);
    }
  for ($i=0; $i<$num_aut+$cont; $i++) {

  $table = $form_state->getValue('table');
  $fn=$table[$i]['first_name'];
  $sn=$table[$i]['second_name'];
  $fln=$table[$i]['f_lastname'];
  $sln=$table[$i]['s_lastname'];

    $form['author']['table'][$i]['first_name'] = array(
      '#type' => 'textfield',
      '#value' => isset($fn)?$fn:$this_aut[$i]['a_first_name'],
      '#size' => 16,
    );
    $form['author']['table'][$i]['second_name'] = array(
      '#type' => 'textfield',
      '#value' => isset($sn)?$sn:$this_aut[$i]['a_second_name'],
      '#size' => 16,
    );
    $form['author']['table'][$i]['f_lastname'] = array(
      '#type' => 'textfield',
      '#value' => isset($fln)?$fln:$this_aut[$i]['a_first_lastname'],
      '#size' => 16,
    );
    $form['author']['table'][$i]['s_lastname'] = array(
      '#type' => 'textfield',
      '#value' => isset($sln)?$sln:$this_aut[$i]['a_second_lastname'],
      '#size' => 16,
    );
  }
  $form['author']['add'] = array(
      '#type' => 'submit',
      '#name' => 'editAuthor',
      '#value' => t('Add more field'),
      '#submit' => array(array($this, 'addfieldsubmit')),
      '#ajax' => array(
        'callback' => array($this, 'addfieldCallback'),
        'wrapper' => 'modules-wrapper',
        'effect' => 'fade',
      ),
   );

  //*****************************************************************************************
  //********************************JOURNAL/BOOK JOURNAL/BOOK *******************************
  //*****************************************************************************************
   $form['detail'] = array(
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => t('Details'),
    );
    $form['detail']['langua'] = array(
      '#title' => t('Language'),
      '#type' => 'textfield',
      '#default_value' => $this_book['ab_language'],
    );
    $form['detail']['vol'] = array(
      '#title' => t('Volume/Series'),
      '#type' => 'textfield',
      '#default_value' => $this_book_2['abd_volume'],
    );
    $form['detail']['issue'] = array(
      '#title' => t('Number (Issue)'),
      '#type' => 'textfield',
      '#default_value' => $this_book_2['abd_issue'],
    );
    $form['detail']['edito'] = array(
      '#title' => t('Editorial'),
      '#type' => 'textfield',
      '#default_value' => $this_book['ab_journal_editorial'],
    );
    $form['detail']['editor_name'] = array(
      '#title' => t('Publisher name'),
      '#type' => 'textfield',
      '#default_value' => $this_book['ab_publisher'],
    );
    $form['detail']['pub'] = array(
      '#title' => t('Place of publication'),
      '#type' => 'textfield',
      '#default_value' => $this_book['ab_place'],
    );
    $form['detail']['issn'] = array(
      '#title' => t('ISSN'),
      '#type' => 'textfield',
      '#default_value' => $this_book_2['abd_issn'],
    );
    $form['detail']['isbn'] = array(
      '#title' => t('ISBN'),
      '#type' => 'textfield',
      '#default_value' => $this_book_2['abd_isbn'],
    );
    $form['detail']['url'] = array(
      '#title' => t('URL'),
      '#type' => 'textfield',
      '#description' => t('Example: https://www.example.com'),
      '#maxlength' => 511,
      '#default_value' => $this_book_2['abd_url'],
    );
    $form['detail']['doi'] = array(
      '#title' => t('DOI'),
      '#type' => 'textfield',
      '#default_value' => $this_book_2['abd_doi'],
    );

  $form['update'] = array(
    '#type' => 'submit',
    '#value' => t('Update'),
  );
  /******************************************************************/
  /******************************************************************/
  /******************************************************************/

//--------------------------------------------------------------------------------------------------------

//--------------------------------------------------------------------------------------------------------
   return $form;

  }

  public function addfieldsubmit(array &$form, FormStateInterface &$form_state) {
    $max = $form_state->get('fields_count') + 1;
    $form_state->set('fields_count',$max);
    $form_state->setRebuild(TRUE);
  }

  public function addfieldCallback(array &$form, FormStateInterface &$form_state) {
    return $form['author'];
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {

  //------------------------------------------------------------------------------------------------

  // DAY, month year ARTICLE VALIDATION

  $day_validate = $form_state->getValue('day');
  if(!empty($day_validate) && (!is_numeric($day_validate) ||
      $day_validate > '31' || $day_validate < '1')) {
    $form_state->setErrorByName('day', t('It is not an allowable value for day.'));
  }

  $month_validate =  $form_state->getValue('month');
  if(!empty($month_validate) && (!is_numeric($month_validate) ||
      $month_validate > '12' || $month_validate < '1')) {
    $form_state->setErrorByName('month', t('It is not an allowable value for month.'));
  }

  $year_validate = $form_state->getValue('year');
  if(!is_numeric($year_validate) || $year_validate > '9999' ||
      $year_validate < '1000') {
    $form_state->setErrorByName('year', t('It is not an allowable value for year.'));
  }

  $startp_validate = $form_state->getValue('jou_start_page');
  if(!empty($startp_validate) && (!is_numeric($startp_validate) ||
      $startp_validate < '0')){
    $form_state->setErrorByName('jou_start_page', t('Start page is a numerical field.'));
  }

  $finalp_validate = $form_state->getValue('jou_final_page');
  if(!empty($finalp_validate) && (!is_numeric($finalp_validate) ||
      $finalp_validate < '0')){
    $form_state->setErrorByName('jou_final_page', t('Final page is a numerical field.'));
  }
  global $contname;
  $table = $form_state->getValue('table');
  $art_num_aut = $form_state->getValue('num_aut');
  $cont = $form_state->get('fields_count');
  if(is_null($cont)) {
    $cont = 0;
    $form_state->set('fields_count', $cont);
  }
  $table = $form_state->getValue('table');
  for ($a=0; $a<$art_num_aut+$cont; $a++) {
  if (!empty($table[$a]['first_name']) && empty($table[$a]['f_lastname'])){
    $form_state->setErrorByName('last_name', t('The author requires a last name.'));
  }
 
  if (empty($table[$a]['first_name']) && !empty($table[$a]['f_lastname'])){
    $form_state->setErrorByName('first_name', t('The author requires a first name.'));
  }

  if (!empty($table[$a]['first_name']) && !empty($table[$a]['f_lastname'])){      
          $contname++;
  }
  }
  if ($contname<1){
    $form_state->setErrorByName('name', t('One author is required as minimum 
    (first name and last name).'));
  }
  $url=$form_state->getValue('url'); 
  if(!empty($url) && !UrlHelper::isValid($url, TRUE))
  {
   $form_state->setErrorByName('uri', t('The URL is not valid.'));
  }

  }
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $book_abid = $form_state->getValue('abid');
    $book_sub2 = $form_state->getValue('sub');
    $book_descrip2 = $form_state->getValue('description');
    $book_year2 = $form_state->getValue('year');
    $book_langua2 = $form_state->getValue('langua');
    $book_volume2 = $form_state->getValue('vol');
    $book_issue2 = $form_state->getValue('issue');
    $book_edito2 = $form_state->getValue('edito');
    $book_ed_name2 = $form_state->getValue('editor_name');
    $book_place2 = $form_state->getValue('pub');
    $book_issn2 = $form_state->getValue('issn');
    $book_isbn2 = $form_state->getValue('isbn');
    $book_url2 = $form_state->getValue('url');
    $book_doi2 = $form_state->getValue('doi');
    $book_author = $form_state->getValue('table');
     $params['send'] = [
      'abid'                 => $book_abid,
      'sub'                  => $book_sub2,
      'description'          => $book_descrip2,
      'year'                 => $book_year2,
      'langua'               => $book_langua2,
      'vol'                  => $book_volume2,
      'issue'                => $book_issue2,
      'edito'                => $book_edito2,
      'editor_name'          => $book_ed_name2,
      'pub'                  => $book_place2,
      'issn'                 => $book_issn2,
      'isbn'                 => $book_isbn2,
      'url'                  => $book_url2,
      'doi'                  => $book_doi2,
      'info_author'          => $book_author,
       ];

    foreach ($params as $param) {
    $form_state->setRedirect('reposi.confirm_book', $param);
    //drupal_set_message('Se envia esto '.print_r($param,true));
    }

//-------------------------------------------------------------------------------------------------------------------------
  }
// Llave que cierra la clase:--->
}
?>
