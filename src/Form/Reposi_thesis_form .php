<?php

namespace Drupal\reposi\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Query;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\reposi\Controller\Reposi_info_publication;

/**
 * Implements an example form.
 */
class Reposi_thesis_form extends FormBase {

  public function getFormId() {
    return 'add_thesis_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

  $markup = '<p>' . '<i>' . t('You must complete the required fields before the 
            add authors or keywords.') . '</i>' . '</p>';
  $form['body'] = array('#markup' => $markup);
  $form['title'] = array(
    '#title' => t('Title'),
    '#type' => 'textfield',
    '#required' => TRUE,
    '#maxlength' => 511,
  );
  //*****************************************************************************************
  //*************************************PUBLICATION DATE************************************
  //*****************************************************************************************

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
    '#description' => t('1-31'),
  );
  $form['date']['month'] = array(
    '#title' => t('Month'),
    '#type' => 'textfield',
    '#size' => 5,
    '#description' => t('1-12'),
  );
  $form['date']['year'] = array(
    '#title' => t('Year'),
    '#type' => 'textfield',
    '#size' => 5,
    '#required' => TRUE,
    '#description' => t('Four numbers'),
  );



  //*****************************************************************************************
  //*********************************AUTORES AUTORES AUTORES*********************************
  //*****************************************************************************************

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

  //*****************************************************************************************//
  //*********************************KEYWORD KEYWORD KEYWORD*********************************//
  //*****************************************************************************************//

  $form['keyword'] = array(
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => t('Keywords'),
      '#prefix' => '<div id="keyword-wrapper">',
      '#suffix' => '</div>',
    );

  $maxkeyword = $form_state->get('fields_keyword_count');
    if(is_null($max)) {
      $max = 0;
      $form_state->set('fields_keyword_count', $max);
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

  for ($i=0; $i<=$maxkeyword; $i++) {

  $keyword = $form_state->getValue('keywordtable');
  $key=$keyword[$i]['key'];

    $form['keyword']['keywordtable'][$i]['key'] = array(
      '#type' => 'textfield',
      '#value' => isset($key)?$key:'',
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


  //*****************************************************************************************//
  //********************************JOURNAL/BOOK JOURNAL/BOOK *******************************//
  //*****************************************************************************************//

  $form['institu'] = array(
    '#title' => t('Academic institution'),
    '#type' => 'textfield',
  );
  $form['degree'] = array(
    '#title' => t('Type degree'),
    '#type' => 'select',
    '#options' => array(t('University Degree'), 
                        t('Specialization’s Degree'), 
                        t('Master’s Degree'), 
                        t('PhD thesis')),
    '#required' => TRUE,
  );
  $form['discipline'] = array(
    '#title' => t('Discipline'),
    '#type' => 'textfield',
  );
  $form['url'] = array(
    '#title' => t('URL'),
    '#type' => 'textfield',
    '#maxlength' => 511,
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

  public function addfieldkeywordsubmit(array &$form, FormStateInterface &$form_state) {

    $max = $form_state->get('fields_keyword_count') + 1;
    $form_state->set('fields_keyword_count',$max);
    $form_state->setRebuild(TRUE);
  }

  public function addfieldCallback(array &$form, FormStateInterface &$form_state) {
    return $form['author'];
  }

  public function addfieldkeywordCallback(array &$form, FormStateInterface &$form_state) {
    return $form['keyword'];
  }


  public function validateForm(array &$form, FormStateInterface $form_state) {

  //------------------------------------------------------------------------------------------------

 /* $name_validate = $form_state->getValue('title');
  $search_sw = db_select('reposi_thesis_sw', 'sw');
  $search_sw->fields('sw')
          ->condition('sw.ts_type', 'Thesis', '=')
          ->condition('sw.ts_title', $name_validate, '=');
  $info_sw = $search_sw->execute();
  $new_name=Reposi_info_publication::reposi_string($name_validate);
  foreach ($info_sw as $titles) {
    $new_titles=Reposi_info_publication::reposi_string($titles->ts_title);
    if (strcasecmp($new_name, $new_titles) == 0) {
      $form_state->setErrorByName('publi_title', t('This Thesis exists on Data Base.'));
    }
  }

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

  $table = $form_state->getValue('table');
  $first_name_validate=$table[0]['first_name'];
  $first_lastname_validate=$table[0]['f_lastname'];

  $key = $form_state->getValue('keywordtable');
  $keyword=$key[0]['key'];
  if (empty($first_name_validate) || empty($first_lastname_validate)){
    $form_state->setErrorByName('first_name', t('One author is required as minimum 
    (first name and last name).'));
  }

  if (empty($keyword)){
        drupal_set_message(t('One keyword is required as minimum.'), 'warning');
  }*/
  }
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
/*
  $the_title = $form_state->getValue('title');
  $the_day = $form_state->getValue('day');
  $the_mon = $form_state->getValue('month');
  $the_year = $form_state->getValue('year');
  $the_institu = $form_state->getValue('institu');
  $the_degree = $form_state->getValue('degree');
  $the_disc = $form_state->getValue('discipline');
  $the_url = $form_state->getValue('url');
  if ($the_degree == 0) {
    $degree = 'University Degree';
  } elseif ($the_degree == 1) {
    $degree = 'Specialization’s Degree';
  } elseif ($the_degree == 2) {
    $degree = 'Master’s Degree';
  } elseif ($the_degree == 3) {
    $degree = 'Doctorate';
  }
  db_insert('reposi_thesis_sw')->fields(array(
      'ts_type'        => 'Thesis',
      'ts_title'       => $the_title,
      'ts_institu_ver' => $the_institu,
      'ts_degree'      => $degree,
      'ts_discip_place'=> $the_disc,
      'ts_url'         => $the_url,
  ))->execute();
  $search_the = db_select('reposi_thesis_sw', 'th');
  $search_the->fields('th')
          ->condition('th.ts_type', 'Thesis', '=')
          ->condition('th.ts_title', $the_title, '=');
  $the_id = $search_the->execute()->fetchField();
  $thesis_id = (int)$the_id;
  if (!empty($the_day)) {
    $thesis_day = (int)$the_day;
  } else {
    $thesis_day = NULL;
  }
  if (!empty($the_mon)) {
    $thesis_mon = (int)$the_mon;
  } else {
    $thesis_mon = NULL;
  }
  $thesis_year = (int)$the_year;
  db_insert('reposi_date')->fields(array(
      'd_day'   => $thesis_day,
      'd_month' => $thesis_mon,
      'd_year'  => $thesis_year,
      'd_tsid'  => $thesis_id,
  ))->execute();
  db_insert('reposi_publication')->fields(array(
      'p_type'  => 'Thesis',
      'p_title' => $the_title,
      'p_year'  => $thesis_year,
      'p_check' => 1,
      'p_tsid'  => $thesis_id,
  ))->execute();


//-------------------------------------------------------------------------------------------------------------------------
  $max = $form_state->get('fields_count');
  if(is_null($max)) {
    $max = 0;
    $form_state->set('fields_count', $max);
  }
  $table = $form_state->getValue('table');
  for ($a=0; $a<=$max; $a++) {
  $first_name_validate=$table[$a]['first_name'];
  $first_lastname_validate=$table[$a]['f_lastname'];
  $aut_fn=$table[$a]['first_name'];
  $aut_sn=$table[$a]['second_name'];
  $aut_fl=$table[$a]['f_lastname']; 
  $aut_sl=$table[$a]['s_lastname'];
 
   !empty($aut_fn)?strtolower($aut_fn):'';
   !empty($aut_sn)?strtolower($aut_sn):'';
   !empty($aut_fl)?strtolower($aut_fl):'';
   !empty($aut_sl)?strtolower($aut_sl):'';

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
          'ap_tsid'      => $thesis_id,
        ))->execute();
      } else {
        $aut_publi_id2 = (int)$serch_aut[$a];
        db_insert('reposi_publication_author')->fields(array(
            'ap_author_id' => $aut_publi_id2,
            'ap_tsid'      => $thesis_id,
        ))->execute();
      }
    } else {
      if(isset($table[$a]['first_name']) || isset($table[$a]['f_lastname'])){
        drupal_set_message(t('The authors without first name or first 
        last name will not be save.'), 'warning');
      }
    }
}
  $maxkeyword = $form_state->get('fields_keyword_count');
    if(is_null($max)) {
      $max = 0;
      $form_state->set('fields_keyword_count', $max);
    }

 
  for ($q = 0; $q <= $maxkeyword ; $q++) { 
  $keyword = $form_state->getValue('keywordtable');
    if (!empty($keyword[$q]['key'])) {
      $keywords[] = $keyword[$q]['key'];
    } else {
      $keywords[] = NULL;
    }
  }
  $cont_keywords=0;
  foreach ($keywords as $new_keyw){
    if (isset($new_keyw)) {
      $serch_k = db_select('reposi_keyword', 'k');
      $serch_k->fields('k')
              ->condition('k.k_word', $new_keyw, '=');
      $serch_keyw[$cont_keywords] = $serch_k->execute()->fetchField();
      if (empty($serch_keyw[$cont_keywords])) {
        db_insert('reposi_keyword')->fields(array(
          'k_word' => $new_keyw,
        ))->execute();
        $serch2_k = db_select('reposi_keyword', 'k');
        $serch2_k->fields('k')
                ->condition('k.k_word', $new_keyw, '=');
        $serch2_keyw[$cont_keywords] = $serch2_k->execute()->fetchField();
        $serch2_keyw_id = (int)$serch2_keyw[$cont_keywords];
        db_insert('reposi_publication_keyword')->fields(array(
        'pk_keyword_id' => $serch2_keyw_id,
        'pk_tsid'       => $thesis_id,
      ))->execute();
      } else {
        $serch_keyw_id = (int)$serch_keyw[$cont_keywords];
        db_insert('reposi_publication_keyword')->fields(array(
          'pk_keyword_id' => $serch_keyw_id,
          'pk_tsid'       => $thesis_id,
        ))->execute();
      }
      $cont_keywords++;
    } 
  }   

  drupal_set_message(t('Thesis: ') . $the_title . t(' was update.')); 
*/
//-------------------------------------------------------------------------------------------------------------------------
  }
// Llave que cierra la clase:--->
}
?>
