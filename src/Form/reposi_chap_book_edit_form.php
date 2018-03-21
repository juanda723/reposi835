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
class reposi_chap_book_edit_form extends FormBase {

  public function getFormId() {
    return 'reposi_chap_book_edit_form_id';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
 
    $abid = \Drupal::routeMatch()->getParameter('node');
    $search_chap = db_select('reposi_article_book', 'ab');
    $search_chap->fields('ab')
            ->condition('ab.abid', $abid, '=');
    $this_chap = $search_chap->execute()->fetchAssoc();
    $search_chap_detail = db_select('reposi_article_book_detail', 'abd');
    $search_chap_detail->fields('abd')
            ->condition('abd.abd_abid', $abid, '=');
    $this_chap_2 = $search_chap_detail->execute()->fetchAssoc();
    $search_date = db_select('reposi_date', 'd');
    $search_date->fields('d')
                ->condition('d.d_abid', $abid, '=');
    $chap_date = $search_date->execute()->fetchAssoc();
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
      '#title' => $this_chap['ab_title'],
    );
    $form['chapter'] = array(
      '#title' => t('Chapter'),
      '#type' => 'details',
      '#open' => TRUE,
      '#size' => 10,
    );
    $form['chapter']['chapter'] = array(
      '#title' => t('Chapter Title: '.$this_chap['ab_subtitle_chapter']),
      '#type' => 'item',
      '#size' => 82,
      '#default_value' => $this_chap['ab_subtitle_chapter'],
    );
    $form['chapter']['chapter_num'] = array(
      '#title' => t('Number'),
      '#type' => 'textfield',
      '#size' => 82,
      '#default_value' => $this_chap['ab_chapter'],
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
      '#default_value' => $chap_date['d_year'],
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
    $form['detail']['vol'] = array(
      '#title' => t('Volume/Series'),
      '#type' => 'textfield',
      '#default_value' => $this_chap_2['abd_volume'],
    );
    $form['detail']['issue'] = array(
      '#title' => t('Number (Issue)'),
      '#type' => 'textfield',
      '#default_value' => $this_chap_2['abd_issue'],
    );
    $form['detail']['editor_name'] = array(
      '#title' => t('Publisher name'),
      '#type' => 'textfield',
      '#default_value' => $this_chap['ab_publisher'],
    );
    $form['detail']['edito'] = array(
      '#title' => t('Editorial'),
      '#type' => 'textfield',
      '#default_value' => $this_chap['ab_journal_editorial'],
    );
    $form['detail']['start_page'] = array(
      '#title' => t('Start page'),
      '#type' => 'textfield',
      '#maxlength' => 10,
      '#default_value' => $this_chap_2['abd_start_page'],
    );
    $form['detail']['final_page'] = array(
      '#title' => t('Final page'),
      '#type' => 'textfield',
      '#maxlength' => 10,
      '#default_value' => $this_chap_2['abd_final_page'],
    );
    $form['detail']['pub'] = array(
      '#title' => t('Place of publication'),
      '#type' => 'textfield',
      '#default_value' => $this_chap['ab_place'],
    );
    $form['detail']['issn'] = array(
      '#title' => t('ISSN'),
      '#type' => 'textfield',
      '#default_value' => $this_chap_2['abd_issn'],
    );
    $form['detail']['isbn'] = array(
      '#title' => t('ISBN'),
      '#type' => 'textfield',
      '#default_value' => $this_chap_2['abd_isbn'],
    );
    $form['detail']['url'] = array(
      '#title' => t('URL'),
      '#description' => t('Example: https://www.example.com'),
      '#type' => 'textfield',
      '#maxlength' => 511,
      '#default_value' => $this_chap_2['abd_url'],
    );
    $form['detail']['doi'] = array(
      '#title' => t('DOI'),
      '#type' => 'textfield',
      '#default_value' => $this_chap_2['abd_doi'],
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

  $num_validate = $form_state->getValue('chapter_num');
  if(!empty($num_validate) && (!is_numeric($num_validate) || $num_validate < '0')){
    $form_state->setErrorByName('chapter_num', t('Chapter number is a numerical field.'));
  }

  $year_validate = $form_state->getValue('year');
  if(!is_numeric($year_validate) || $year_validate > '9999' ||
      $year_validate < '1000') {
    $form_state->setErrorByName('year', t('It is not an allowable value for year.'));
  }

  $startp_validate = $form_state->getValue('start_page');
  if(!empty($startp_validate) && (!is_numeric($startp_validate) ||
      $startp_validate < '0')){
    $form_state->setErrorByName('jou_start_page', t('Start page is a numerical field.'));
  }

  $finalp_validate = $form_state->getValue('final_page');
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

    $chap_abid = $form_state->getValue('abid');
    $chap_chapter_num = $form_state->getValue('chapter_num');
    $chap_year = $form_state->getValue('year');
    $chap_volume = $form_state->getValue('vol');
    $chap_issue = $form_state->getValue('issue');
    $chap_edito = $form_state->getValue('edito');
    $chap_ed_name = $form_state->getValue('editor_name');
    $chap_spage = $form_state->getValue('start_page');
    $chap_fpage = $form_state->getValue('final_page');
    $chap_place = $form_state->getValue('pub');
    $chap_issn = $form_state->getValue('issn');
    $chap_isbn = $form_state->getValue('isbn');
    $chap_url = $form_state->getValue('url');
    $chap_doi = $form_state->getValue('doi');
    $chap_author = $form_state->getValue('table');

     $params['send'] = [
      'abid'                 => $chap_abid,
      'chapter_num'          => $chap_chapter_num,
      'year'                 => $chap_year,
      'vol'                  => $chap_volume,
      'issue'                => $chap_issue,
      'edito'                => $chap_edito,
      'editor_name'          => $chap_ed_name,
      'start_page'           => $chap_spage,
      'final_page'           => $chap_fpage,
      'pub'                  => $chap_place,
      'issn'                 => $chap_issn,
      'isbn'                 => $chap_isbn,
      'url'                  => $chap_url,
      'doi'                  => $chap_doi,
      'info_author'          => $chap_author,
       ];

    foreach ($params as $param) {
    $form_state->setRedirect('reposi.confirm_chap_book', $param);
    //drupal_set_message('Se envia esto '.print_r($param,true));
    }

//-------------------------------------------------------------------------------------------------------------------------
  }
// Llave que cierra la clase:--->
}
?>
