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
class reposi_conference_edit_form extends FormBase {

  public function getFormId() {
    return 'reposi_conference_edit_form_id';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
  
    $cpid = \Drupal::routeMatch()->getParameter('node');
    $search_con = db_select('reposi_confer_patent', 'cp');
    $search_con->fields('cp')
            ->condition('cp.cpid', $cpid, '=');
    $this_con = $search_con->execute()->fetchAssoc();
    $dates = array();
    $search_date = db_select('reposi_date', 'd');
    $search_date->fields('d')
                ->condition('d.d_cpid', $cpid, '=');
    $con_date = $search_date->execute();
    foreach ($con_date as $new_date) {
      $dates[] = array($new_date->d_day, $new_date->d_month, $new_date->d_year);
    }
    $search_p_a = db_select('reposi_publication_author', 'pa');
    $search_p_a->fields('pa')
               ->condition('pa.ap_cpid', $cpid, '=');
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
    $search_publi_key = db_select('reposi_publication_keyword', 'pk');
    $search_publi_key->fields('pk')
                     ->condition('pk.pk_cpid', $cpid, '=');
    $id_keyword = $search_publi_key->execute();
    $id_keyword -> allowRowCount = TRUE;
    $num_keyw=$id_keyword->rowCount();
    foreach ($id_keyword as $key_id) {
      $search_keyw = db_select('reposi_keyword', 'k');
      $search_keyw->fields('k')
                  ->condition('k.kid', $key_id->pk_keyword_id, '=');
      $keywords = $search_keyw->execute()->fetchAssoc();
      $this_keyw[] = $keywords['k_word'];
    }    


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
    }*/

    $markup = '<p>' . '<i>' . t('You must complete the required fields before the 
              add authors or keywords.') . '</i>' . '</p>';
    $form['body'] = array('#markup' => $markup);
    $form['cpid'] = array(
      '#type' => 'value',
      '#value' => $cpid,
    );
    $form['num_keyw'] = array(
      '#type' => 'value',
      '#value' => $num_keyw,
    );
    $form['num_aut'] = array(
      '#type' => 'value',
      '#value' => $num_aut,
    );
    $form['non'] = array(
      '#title' => t('Title presentation/publication: Non-editable field'),
      '#type' => 'details',
      '#open' => TRUE,
      '#size' => 10,
    );
    $form['non']['title'] = array(
      '#type' => 'item',
      '#title' => $this_con['cp_publication'],
    );
    $form['one'] = array(
      '#title' => t('Conference'),
      '#type' => 'details',
      '#open' => TRUE,
      '#size' => 10,
    );
    $form['one']['confer'] = array(
      '#title' => t('Conference'),
      '#type' => 'textfield',
      '#required' => TRUE,
      '#maxlength' => 511,
      '#default_value' => $this_con['cp_title'],
    );
   $form['two'] = array(
      '#title' => t('Abstract'),
      '#type' => 'details',
      '#open' => TRUE,
      '#size' => 10,
    );

   $form['two']['description'] = array(
      //'#title' => t('Abstract'),
      '#default_value' => $this_con['cp_abstract'],
      '#type' => 'textarea',
    );
    $form['date'] = array(
      '#title' => t('Publication date'),
      '#type' => 'details',
      '#open' => TRUE,
      '#size' => 10,
    );
    $form['date']['day'] = array(
      '#title' => t('Day'),
      '#type' => 'textfield',
      '#size' => 5,
      '#default_value' => $dates[0][0],
      '#description' => t('1-31'),
    );
    $form['date']['month'] = array(
      '#title' => t('Month'),
      '#type' => 'textfield',
      '#size' => 5,
      '#default_value' => $dates[0][1],
      '#description' => t('1-12'),
    );
    $form['date']['year'] = array(
      '#title' => t('Year'),
      '#type' => 'textfield',
      '#size' => 5,
      '#default_value' => $dates[0][2],
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
  //*********************************KEYWORD KEYWORD KEYWORD*********************************
  //*****************************************************************************************/

  $form['keyword'] = array(
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => t('Keyword(s)'),
      '#prefix' => '<div id="keyword-wrapper">',
      '#suffix' => '</div>',
    );

  $contkeyword = $form_state->get('fields_keyword_count');
    if(is_null($contkeyword)) {
      $contkeyword = 0;
      $form_state->set('fields_keyword_count', $contkeyword);
    }

  $headerkeyword = array (
    'keyword' => t('Keywords'),
  );

  $form['keyword']['keywordtable'] = array(
    '#type' => 'table',
    '#title' => 'Keyword Table',
    '#header' => $headerkeyword,
    '#empty' => t('No lines found'),
  );


  for ($i=0; $i<$num_keyw+$contkeyword; $i++) {
  $keyword = $form_state->getValue('keywordtable');
  $key=$keyword[$i]['key'];

    $form['keyword']['keywordtable'][$i]['key'] = array(
      '#type' => 'textfield',
      '#value' => isset($key)?$key:$this_keyw[$i],
    );

  }

  $form['keyword']['add'] = array(
      '#type' => 'submit',
      '#name' => 'addfieldkeyword',
      '#value' => t('Add more field'),
      '#submit' => array(array($this, 'addfieldkeywordsubmit')),
      '#ajax' => array(
        'callback' => array($this, 'addfieldkeywordCallback'),
        'wrapper' => 'keyword-wrapper',
        'effect' => 'fade',
      ),
    );


  //*****************************************************************************************
  //********************************JOURNAL/BOOK JOURNAL/BOOK *******************************
  //*****************************************************************************************/

    $form['start_date'] = array(
       '#type' => 'details',
       '#open' => TRUE,
       '#title' => t('Start date'),
    );
    $form['end_date'] = array(
       '#type' => 'details',
       '#open' => TRUE,
       '#title' => t('End date'),
    );
    $form['deta'] = array(
       '#title' => t('Details'),
       '#type' => 'details',
       '#open' => TRUE,
    );
    $form['deta']['start_page'] = array(
      '#title' => t('Start page'),
      '#type' => 'textfield',
      '#maxlength' => 10,
      '#default_value' => $this_con['cp_start_page'],
    );
    $form['deta']['final_page'] = array(
      '#title' => t('Final page'),
      '#type' => 'textfield',
      '#maxlength' => 10,
      '#default_value' => $this_con['cp_final_page'],
    );
    $form['deta']['num_event'] = array(
      '#title' => t('Event number'),
      '#type' => 'textfield',
      '#default_value' => $this_con['cp_number'],
    );
    $form['deta']['sponsor'] = array(
      '#title' => t('Sponsor(s): (Each separate by comma)'),
      '#type' => 'textfield',
      '#default_value' => $this_con['cp_spon_owner'],
    );
    $form['deta']['place'] = array(
      '#title' => t('Event place'),
      '#type' => 'textfield',
      '#default_value' => $this_con['cp_place_type'],
    );
    $form['start_date']['start_day'] = array(
      '#title' => t('Day'),
      '#type' => 'textfield',
      '#size' => 5,
      '#default_value' => $dates[1][0],
      '#description' => t('1-31'),
    );
    $form['start_date']['start_month'] = array(
      '#title' => t('Month'),
      '#type' => 'textfield',
      '#size' => 5,
      '#default_value' => $dates[1][1],
      '#description' => t('1-12'),
    );
    $form['start_date']['start_year'] = array(
      '#title' => t('Year'),
      '#type' => 'textfield',
      '#size' => 5,
      '#default_value' => $dates[1][2],
      '#required' => TRUE,
      '#description' => t('Four numbers'),
    );
    $form['end_date']['end_day'] = array(
      '#title' => t('Day'),
      '#type' => 'textfield',
      '#size' => 5,
      '#default_value' => $dates[2][0],
      '#description' => t('1-31'),
    );
    $form['end_date']['end_month'] = array(
      '#title' => t('Month'),
      '#type' => 'textfield',
      '#size' => 5,
      '#default_value' => $dates[2][1],
      '#description' => t('1-12'),
    );
    $form['end_date']['end_year'] = array(
      '#title' => t('Year'),
      '#type' => 'textfield',
      '#size' => 5,
      '#default_value' => $dates[2][2],
      '#required' => TRUE,
      '#description' => t('Four numbers'),
    );
    $form['deta']['url'] = array(
      '#title' => t('URL'),
      '#description' => t('Example: https://www.example.com'),
      '#type' => 'textfield',
      '#default_value' => $this_con['cp_url'],
      '#maxlength' => 511,
    );
    $form['deta']['doi'] = array(
      '#title' => t('DOI'),
      '#type' => 'textfield',
      '#default_value' => $this_con['cp_doi'],
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
  public function addfieldkeywordsubmit(array &$form, FormStateInterface &$form_state) {

    $max = $form_state->get('fields_keyword_count') + 1;
    $form_state->set('fields_keyword_count',$max);
    $form_state->setRebuild(TRUE);
  }

  public function addfieldkeywordCallback(array &$form, FormStateInterface &$form_state) {
    return $form['keyword'];
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {

  //------------------------------------------------------------------------------------------------

  // DAY, month year ARTICLE VALIDATION

  $day_validate = $form_state->getValue('day');
  if(!empty($day_validate) && (!is_numeric($day_validate) ||
      $day_validate > '31' || $day_validate < '1')) {
    $form_state->setErrorByName('day', t('It is not an allowable value for day.'));
  }
  $start_day_validate = $form_state->getValue('start_day');
  $end_day_validate = $form_state->getValue('end_day');
  if(!empty($start_day_validate) && (!is_numeric($start_day_validate) || 
      $start_day_validate > '31' || $start_day_validate < '1')) {
    $form_state->setErrorByName('start_day', t('It is not an allowable value for start day.'));
  } 
  if(!empty($end_day_validate) && (!is_numeric($end_day_validate) || 
      $end_day_validate > '31' || $end_day_validate < '1')) {
    $form_state->setErrorByName('end_day', t('It is not an allowable value for end day.'));
  }
  $month_validate =  $form_state->getValue('month');
  if(!empty($month_validate) && (!is_numeric($month_validate) ||
      $month_validate > '12' || $month_validate < '1')) {
    $form_state->setErrorByName('month', t('It is not an allowable value for month.'));
  }
  $start_month_validate = $form_state->getValue('start_month');
  $end_month_validate = $form_state->getValue('end_month');
  if(!empty($start_month_validate) && (!is_numeric($start_month_validate) || 
      $start_month_validate > '12' || $start_month_validate < '1')) {
    $form_state->setErrorByName('start_month', t('It is not an allowable value for start month.'));
  } 
  if(!empty($end_month_validate) && (!is_numeric($end_month_validate) || 
      $end_month_validate > '12' || $end_month_validate < '1')) {
    $form_state->setErrorByName('end_month', t('It is not an allowable value for end month.'));
  } 

  $year_validate = $form_state->getValue('year');
  if(!is_numeric($year_validate) || $year_validate > '9999' ||
      $year_validate < '1000') {
    $form_state->setErrorByName('year', t('It is not an allowable value for year.'));
  }

  $start_year_validate = $form_state->getValue('start_year');
  if(!is_numeric($start_year_validate) || $start_year_validate > '9999' ||
      $start_year_validate < '1000') {
    $form_state->setErrorByName('start_year', t('It is not an allowable value for start year.'));
  }

  $end_year_validate = $form_state->getValue('end_year');
  if(!is_numeric($end_year_validate) || $end_year_validate > '9999' ||
      $end_year_validate < '1000') {
    $form_state->setErrorByName('end_year', t('It is not an allowable value for end year.'));
  }

  $startp_validate = $form_state->getValue('start_page');
  if(!empty($startp_validate) && (!is_numeric($startp_validate) ||
      $startp_validate < '0')){
    $form_state->setErrorByName('con_start_page', t('Start page is a numerical field.'));
  }

  $finalp_validate = $form_state->getValue('final_page');
  if(!empty($finalp_validate) && (!is_numeric($finalp_validate) ||
      $finalp_validate < '0')){
    $form_state->setErrorByName('con_final_page', t('Final page is a numerical field.'));
  }
  global $contname;
  $table = $form_state->getValue('table');
  $art_num_aut = $form_state->getValue('num_aut');
  $art_num_keyw = $form_state->getValue('num_keyw');
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

  $key = $form_state->getValue('keywordtable');

  if (empty($key)){
        drupal_set_message(t('You must include at least one keyword.'), 'warning');
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

    $con_cpid = $form_state->getValue('cpid');
    $con_day = $form_state->getValue('day');
    $con_month = $form_state->getValue('month');
    $con_year = $form_state->getValue('year');
    $con_spage = $form_state->getValue('start_page');
    $con_fpage = $form_state->getValue('final_page');
    $con_confer = $form_state->getValue('confer');
    $con_description = $form_state->getValue('description');
    $con_num_event = $form_state->getValue('num_event');
    $con_sponsor = $form_state->getValue('sponsor');
    $con_place = $form_state->getValue('place');
    $con_start_day = $form_state->getValue('start_day');
    $con_start_month = $form_state->getValue('start_month');
    $con_start_year = $form_state->getValue('start_year');
    $con_end_day = $form_state->getValue('end_day');
    $con_end_month = $form_state->getValue('end_month');
    $con_end_year = $form_state->getValue('end_year');
    $con_url = $form_state->getValue('url');
    $con_doi = $form_state->getValue('doi');
    $con_author = $form_state->getValue('table');
    $con_keyword = $form_state->getValue('keywordtable');

     $params['send'] = [
      'cpid'                 => $con_cpid,
      'description'          => $con_description,
      'day'                  => $con_day,
      'month'                => $con_month,
      'year'                 => $con_year,
      'start_page'           => $con_spage,
      'final_page'           => $con_fpage,
      'confer'               => $con_confer,
      'num_event'            => $con_num_event,
      'sponsor'              => $con_sponsor,
      'place'                => $con_place,
      'start_day'            => $con_start_day,
      'start_month'          => $con_start_month,
      'start_year'           => $con_start_year,
      'end_day'              => $con_end_day,
      'end_month'            => $con_end_month,
      'end_year'             => $con_end_year,
      'url'                  => $con_url,
      'doi'                  => $con_doi,
      'info_author'          => $con_author,
      'keyword'	             => $con_keyword,
       ];

    foreach ($params as $param) {
        $form_state->setRedirect('reposi.confirm_conference', $param);
           // drupal_set_message(t('se envia esto:  ').print_r($param,true));
    }

//-------------------------------------------------------------------------------------------------------------------------
  }
// Llave que cierra la clase:--->
}
?>
