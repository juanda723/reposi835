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
class reposi_patent_edit_form extends FormBase {

  public function getFormId() {
    return 'reposi_patent_edit_form_id';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
  
    $cpid = \Drupal::routeMatch()->getParameter('node');
    $search_pat = db_select('reposi_confer_patent', 'cp');
    $search_pat->fields('cp')
            ->condition('cp.cpid', $cpid, '=');
    $this_pat = $search_pat->execute()->fetchAssoc();
    $search_date = db_select('reposi_date', 'd');
    $search_date->fields('d')
                ->condition('d.d_cpid', $cpid, '=');
    $pat_date = $search_date->execute()->fetchAssoc();
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






////////////////////////////////////////////////////////////////////////////////////////////////////
/*    $search_the = db_select('reposi_thesis_sw', 'ts');
    $search_the->fields('ts')
            ->condition('ts.tsid', $tsid, '=');
    $this_the = $search_the->execute()->fetchAssoc();
    $search_date = db_select('reposi_date', 'd');
    $search_date->fields('d')
                ->condition('d.d_tsid', $tsid, '=');
    $the_date = $search_date->execute()->fetchAssoc();
    $search_publi_key = db_select('reposi_publication_keyword', 'pk');
    $search_publi_key->fields('pk')
                     ->condition('pk.pk_tsid', $tsid, '=');
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
    $search_p_a = db_select('reposi_publication_author', 'pa');
    $search_p_a->fields('pa')
               ->condition('pa.ap_tsid', $tsid, '=');
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
/////////////////////////////////////////////////////////////////////////////////////////////////////////
    $markup = '<p>' . '<i>' . t('You must complete the required fields before the 
              add authors or keywords.') . '</i>' . '</p>';
    $form['body'] = array('#markup' => $markup);
    $form['cpid'] = array(
      '#type' => 'value',
      '#value' => $cpid,
    );
    $form['num_aut'] = array(
      '#type' => 'value',
      '#value' => $num_aut,
    );
    $form['non'] = array(
      '#title' => t('Non-editable field'),
      '#type' => 'details',
      '#open' => TRUE,
      '#size' => 10,
    );
    $form['non']['title'] = array(
      '#type' => 'item',
      '#title' => t('Title: ') . $this_pat['cp_title'],
    );
    $form['descrip'] = array(
      '#title' => t('Abstract'),
      '#type' => 'details',
      '#open' => TRUE,
      '#size' => 10,
    );
    $form['descrip']['abstract'] = array(
      '#type' => 'textarea',
      '#default_value' => $this_pat['cp_abstract'],
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
      '#default_value' => $pat_date['d_day'],
      '#description' => t('1-31'),
    );
    $form['date']['month'] = array(
      '#title' => t('Month'),
      '#type' => 'textfield',
      '#size' => 5,
      '#default_value' => $pat_date['d_month'],
      '#description' => t('1-12'),
    );
    $form['date']['year'] = array(
      '#title' => t('Year'),
      '#type' => 'textfield',
      '#size' => 5,
      '#default_value' => $pat_date['d_year'],
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
       '#title' => t('Originator(s)'),
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



  //*****************************************************************************************
  //********************************JOURNAL/BOOK JOURNAL/BOOK *******************************
  //*****************************************************************************************/

    $form['deta'] = array(
       '#title' => t('Details'),
       '#type' => 'details',
       '#open' => TRUE,
    );
    $form['deta']['owner'] = array(
      '#title' => t('Owner'),
      '#type' => 'textfield',
      '#default_value' => $this_pat['cp_spon_owner'],
    );
    $form['deta']['type'] = array(
      '#title' => t('Type patent'),
      '#type' => 'textfield',
      '#default_value' => $this_pat['cp_place_type'],
    );
    $form['deta']['num'] = array(
      '#title' => t('Number'),
      '#type' => 'textfield',
      '#default_value' => $this_pat['cp_number'],
    );
    $form['deta']['url'] = array(
      '#title' => t('URL'),
      '#description' => t('Example: https://www.example.com'),
      '#type' => 'textfield',
      '#default_value' => $this_pat['cp_url'],
      '#maxlength' => 511,
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


    $pat_cpid = $form_state->getValue('cpid');
    $pat_day = $form_state->getValue('day');
    $pat_month = $form_state->getValue('month');
    $pat_year = $form_state->getValue('year');
    $pat_url = $form_state->getValue('url');
    $pat_abstract = $form_state->getValue('abstract');
    $pat_owner = $form_state->getValue('owner');
    $pat_type = $form_state->getValue('type');
    $pat_num = $form_state->getValue('num');
    $pat_author = $form_state->getValue('table');


     $params['send'] = [
      'cpid'                 => $pat_cpid,
      'day'                  => $pat_day,
      'month'                => $pat_month,
      'year'                 => $pat_year,
      'owner'                => $pat_owner,  
      'info_author'          => $pat_author,
      'url'                  => $pat_url,
      'abstract'	     => $pat_abstract,
      'type'	             => $pat_type,
      'num'	             => $pat_num,
       ];
    foreach ($params as $param) {
          $form_state->setRedirect('reposi.confirm_patent', $param);
         // drupal_set_message(t('se envia esto:  ').print_r($param,true));
    }

//-------------------------------------------------------------------------------------------------------------------------
  }
// Llave que cierra la clase:--->
}
?>
