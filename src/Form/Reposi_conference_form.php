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
class Reposi_conference_form extends FormBase {

  public function getFormId() {
    return 'add_conference_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

/*ISSET: Determina si una variable está definida y no es NULL.
$form_state['storage']: Los datos colocados en el contenedor de almacenamiento de la colección $ form_state se almacenarán automáticamente en caché y se volverán a cargar cuando se envíe el formulario, permitiendo que su código acumule datos de paso a paso y lo procese en la etapa final sin ningún código adicional
ESTÁ RETORNANDO EL VALOR DE 1 EN $form_state['storage']['author'] SI LA VARIABLE ESTÁ DEBIDAMENTE DECLARADA CON ANTERIORIDAD
      $form_state['storage']['author'] = isset($form_state['storage']['author'])?
                                         $form_state['storage']['author']:1;*/

  $_reposi_start_form=TRUE;
  $markup = '<p>' . '<i>' . t('You must complete the required fields before the add authors or keywords.') . '</i>' . '</p>';
  $form['body'] = array('#markup' => $markup);
  $form['title'] = array(
    '#title' => t('Title presentation/publication'),
    '#type' => 'textfield',
    '#required' => TRUE,
    '#maxlength' => 511,
  );
  $form['confer'] = array(
    '#title' => t('Conference'),
    '#type' => 'textfield',
    '#required' => TRUE,
    '#maxlength' => 511,
  );
  $form['abstract'] = array(
    '#title' => t('Abstract'),
    '#type' => 'textarea',
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

  //*****************************************************************************************
  //*********************************KEYWORD KEYWORD KEYWORD*********************************
  //*****************************************************************************************

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


  //*****************************************************************************************
  //********************************JOURNAL/BOOK JOURNAL/BOOK *******************************
  //*****************************************************************************************

  $form['start_date'] = array(
    '#title' => t('Start date'),
    '#type' => 'details',
    '#open' => TRUE,
  );
  $form['start_date']['start_day'] = array(
    '#title' => t('Day'),
    '#type' => 'textfield',
    '#size' => 5,
    '#description' => t('1-31'),
  );
  $form['start_date']['start_month'] = array(
    '#title' => t('Month'),
    '#type' => 'textfield',
    '#size' => 5,
    '#description' => t('1-12'),
  );
  $form['start_date']['start_year'] = array(
    '#title' => t('Year'),
    '#type' => 'textfield',
    '#size' => 5,
    '#required' => TRUE,
    '#description' => t('Four numbers'),
  );
  $form['end_date'] = array(
    '#title' => t('Ending date'),
    '#type' => 'details',
    '#open' => TRUE,
  );
  $form['end_date']['end_day'] = array(
    '#title' => t('Day'),
    '#type' => 'textfield',
    '#size' => 5,
    '#description' => t('1-31'),
  );
  $form['end_date']['end_month'] = array(
    '#title' => t('Month'),
    '#type' => 'textfield',
    '#size' => 5,
    '#description' => t('1-12'),
  );
  $form['end_date']['end_year'] = array(
    '#title' => t('Year'),
    '#type' => 'textfield',
    '#size' => 5,
    '#required' => TRUE,
    '#description' => t('Four numbers'),
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
  $form['num_event'] = array(
    '#title' => t('Event number'),
    '#type' => 'textfield',
  );
  $form['sponsor'] = array(
    '#title' => t('Sponsor(s): (Each separate by comma)'),
    '#type' => 'textfield',
  );
  $form['place'] = array(
    '#title' => t('Event place'),
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

  $title_validate = $form_state->getValue('title');
  $search_pat = db_select('reposi_confer_patent', 'cp');
  $search_pat->fields('cp')
          ->condition('cp.cp_type', 'Conference', '=')
          ->condition('cp.cp_publication', $title_validate, '=');
  $info_pat = $search_pat->execute();
  $new_title=Reposi_info_publication::reposi_string($title_validate);
  foreach ($info_pat as $titles) {
    $new_titles=Reposi_info_publication::reposi_string($titles->cp_publication);
    if (strcasecmp($new_title, $new_titles) == 0) {
      $form_state->setErrorByName('name_conference', t('This Conference Proceeding exists on Data Base.'));
    }
  }

  // DAY, month year ARTICLE VALIDATION

  $day_validate = $form_state->getValue('day');
  $start_day_validate = $form_state->getValue('start_day');
  $end_day_validate = $form_state->getValue('end_day');
  if(!empty($day_validate) && (!is_numeric($day_validate) ||
      $day_validate > '31' || $day_validate < '1')) {
    $form_state->setErrorByName('day_conference', t('It is not an allowable value for day.'));
  }
  if(!empty($start_day_validate) && (!is_numeric($start_day_validate) ||
      $start_day_validate > '31' || $start_day_validate < '1')) {
    $form_state->setErrorByName('start_day', t('It is not an allowable value for start day.'));
  }
  if(!empty($end_day_validate) && (!is_numeric($end_day_validate) ||
      $end_day_validate > '31' || $end_day_validate < '1')) {
    $form_state->setErrorByName('end_day', t('It is not an allowable value for end day.'));
  }

  $month_validate =  $form_state->getValue('month');
  $start_month_validate = $form_state->getValue('start_month');
  $end_month_validate = $form_state->getValue('end_month');
  if(!empty($month_validate) && (!is_numeric($month_validate) ||
      $month_validate > '12' || $month_validate < '1')) {
    $form_state->setErrorByName('month', t('It is not an allowable value for month.'));
  }
  if(!empty($start_month_validate) && (!is_numeric($start_month_validate) ||
      $start_month_validate > '12' || $start_month_validate < '1')) {
    $form_state->setErrorByName('start_month', t('It is not an allowable value for start month.'));
  }
  if(!empty($end_month_validate) && (!is_numeric($end_month_validate) ||
      $end_month_validate > '12' || $end_month_validate < '1')) {
    $form_state->setErrorByName('end_month', t('It is not an allowable value for end month.'));
  }

  $year_validate = $form_state->getValue('year');
  $start_year_validate = $form_state->getValue('start_year');
  $end_year_validate = $form_state->getValue('end_year');
  if(!is_numeric($year_validate) || $year_validate > '9999' ||
      $year_validate < '1000') {
    $form_state->setErrorByName('year', t('It is not an allowable value for year.'));
  }
  if(!empty($start_year_validate) && (!is_numeric($start_year_validate) || $start_year_validate > '9999' ||
      $start_year_validate < '1000')) {
    $form_state->setErrorByName('year', t('It is not an allowable value for start year.'));
  }
  if(!empty($end_year_validate) && (!is_numeric($end_year_validate) || $end_year_validate > '9999' ||
      $end_year_validate < '1000')) {
    $form_state->setErrorByName('year', t('It is not an allowable value for end year.'));
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

  $table = $form_state->getValue('table');
  $first_name_validate=$table[0]['first_name'];
  $first_lastname_validate=$table[0]['f_lastname'];
  if (empty($first_name_validate) || empty($first_lastname_validate)){
    $form_state->setErrorByName('first_name', t('One author is required as minimum
    (first name and last name).'));
  }
  //Url validate re use Drupal\Component\Utility\UrlHelper;
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

  $con_confer = $form_state->getValue('confer');
  $con_des = $form_state->getValue('description');
  $con_num_ev = $form_state->getValue('num_event');
  $con_sponsor = $form_state->getValue('sponsor');
  $con_place = $form_state->getValue('place');
  $con_sday = $form_state->getValue('start_day');
  $con_smon = $form_state->getValue('start_month');
  $con_syear = $form_state->getValue('start_year');
  $con_eday = $form_state->getValue('end_day');
  $con_emon = $form_state->getValue('end_month');
  $con_eyear = $form_state->getValue('end_year');
  $title = $form_state->getValue('title');
  $publi_day = $form_state->getValue('day');
  $publi_mon = $form_state->getValue('month');
  $publi_year = $form_state->getValue('year');
  $start_page = $form_state->getValue('start_page');
  $final_page = $form_state->getValue('final_page');
  $con_url = $form_state->getValue('url');
  $con_doi = $form_state->getValue('doi');
  if (!empty($start_page)) {
    $start_page_int = (int)$start_page;
  } else {
    $start_page_int = NULL;
  }
  if (!empty($final_page)) {
    $final_page_int = (int)$final_page;
  } else {
    $final_page_int = NULL;
  }
  db_insert('reposi_confer_patent')->fields(array(
      'cp_type'       => 'Conference',
      'cp_title'      => $con_confer,
      'cp_abstract'   => $con_des,
      'cp_number'     => $con_num_ev,
      'cp_spon_owner' => $con_sponsor,
      'cp_place_type' => $con_place,
      'cp_publication'=> $title,
      'cp_start_page' => $start_page_int,
      'cp_final_page' => $final_page_int,
      'cp_url'        => $con_url,
      'cp_doi'        => $con_doi,
  ))->execute();
  $search_con = db_select('reposi_confer_patent', 'cp');
  $search_con->fields('cp')
          ->condition('cp.cp_type', 'Conference', '=')
          ->condition('cp.cp_publication', $title, '=');
  $con_id = $search_con->execute()->fetchField();
  $conference_id = (int)$con_id;
  if (!empty($publi_day)) {
    $day = (int)$publi_day;
  } else {
    $day = NULL;
  }
  if (!empty($publi_mon)) {
    $month = (int)$publi_mon;
  } else {
    $month = NULL;
  }
  $year = (int)$publi_year;
  db_insert('reposi_date')->fields(array(
      'd_day'   => $day,
      'd_month' => $month,
      'd_year'  => $year,
      'd_cpid'  => $conference_id,
  ))->execute();
  db_insert('reposi_publication')->fields(array(
      'p_type'  => 'Conference',
      'p_source'=> 'Manual',
      'p_title' => $title,
      'p_year'  => $year,
      'p_check' => 1,
      'p_cpid'  => $conference_id,
  ))->execute();
  if (!empty($con_syear)) {
    if (!empty($con_sday)) {
      $start_day = (int)$con_sday;
    } else {
      $start_day = NULL;
    }
    if (!empty($con_smon)) {
      $start_mon = (int)$con_smon;
    } else {
      $start_mon = NULL;
    }
    if (!empty($con_syear)) {
      $start_year = (int)$con_syear;
    } else {
      $start_year = NULL;
    }
    db_insert('reposi_date')->fields(array(
      'd_day'   => $start_day,
      'd_month' => $start_mon,
      'd_year'  => $start_year,
      'd_cpid'  => $conference_id,
    ))->execute();
  }
  if (!empty($con_eyear)) {
    if (!empty($con_eday)) {
      $end_day = (int)$con_eday;
    } else {
      $end_day = NULL;
    }
    if (!empty($con_emon)) {
      $end_mon = (int)$con_emon;
    } else {
      $end_mon = NULL;
    }
    if (!empty($con_eyear)) {
      $end_year = (int)$con_eyear;
    } else {
      $end_year = NULL;
    }
    db_insert('reposi_date')->fields(array(
      'd_day'   => $end_day,
      'd_month' => $end_mon,
      'd_year'  => $end_year,
      'd_cpid'  => $conference_id,
    ))->execute();
  }






//***************************************************************************************************************//

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
          'ap_cpid'      => $conference_id,
        ))->execute();
      } else {
        $aut_publi_id2 = (int)$serch_aut[$a];
        db_insert('reposi_publication_author')->fields(array(
            'ap_author_id' => $aut_publi_id2,
            'ap_cpid'      => $conference_id,
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
        'pk_cpid'       => $conference_id,
      ))->execute();
      } else {
        $serch_keyw_id = (int)$serch_keyw[$cont_keywords];
        db_insert('reposi_publication_keyword')->fields(array(
          'pk_keyword_id' => $serch_keyw_id,
          'pk_cpid'       => $conference_id,
        ))->execute();
      }
      $cont_keywords++;
    }
  }

  drupal_set_message(t('Conference: ') . $title . t(' was save.'));



//-------------------------------------------------------------------------------------------------------------------------
  }
// Llave que cierra la clase:--->
}
?>
