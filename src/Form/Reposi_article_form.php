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
class Reposi_article_form extends FormBase {

  public function getFormId() {
    return 'AddArticle_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

/*ISSET: Determina si una variable está definida y no es NULL.
$form_state['storage']: Los datos colocados en el contenedor de almacenamiento de la colección $ form_state se almacenarán automáticamente en caché y se volverán a cargar cuando se envíe el formulario, permitiendo que su código acumule datos de paso a paso y lo procese en la etapa final sin ningún código adicional
ESTÁ RETORNANDO EL VALOR DE 1 EN $form_state['storage']['author'] SI LA VARIABLE ESTÁ DEBIDAMENTE DECLARADA CON ANTERIORIDAD
      $form_state['storage']['author'] = isset($form_state['storage']['author'])?
                                         $form_state['storage']['author']:1;*/

  $markup = '<p>' . '<i>' . t('You must complete the required fields before the
            add producers.') . '</i>' . '</p>';
  $form['body'] = array('#markup' => $markup);
  $form['title'] = array(
    '#title' => t('Title'),
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

  $form['journal'] = array(
    '#title' => t('Journal/Book'),
      '#type' => 'details',
      '#open' => TRUE,
  );
  $form['journal']['jou_name'] = array(
    '#title' => t('Journal/Book'),
    '#type' => 'textfield',
  );
  $form['journal']['jou_volume'] = array(
    '#title' => t('Volume'),
    '#type' => 'textfield',
  );
  $form['journal']['jou_issue'] = array(
    '#title' => t('Number (Issue)'),
    '#type' => 'textfield',
  );
  $form['journal']['jou_start_page'] = array(
    '#title' => t('Start page'),
    '#type' => 'textfield',
    '#maxlength' => 10,
  );
  $form['journal']['jou_final_page'] = array(
    '#title' => t('Final page'),
    '#type' => 'textfield',
    '#maxlength' => 10,
  );
  $form['journal']['jou_issn'] = array(
    '#title' => t('ISSN'),
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
  $search_art = db_select('reposi_article_book', 'ab');
  $search_art->fields('ab')
          ->condition('ab.ab_type', 'Article', '=')
          ->condition('ab.ab_title', $title_validate, '=');
  $info_art = $search_art->execute();
  $new_title=Reposi_info_publication::reposi_string($title_validate);
  foreach ($info_art as $titles) {
    $new_titles=Reposi_info_publication::reposi_string($titles->ab_title);
    if (strcasecmp($new_title, $new_titles) == 0) {
      $form_state->setErrorByName('publi_title', t('This Article exists on Data Base.'));
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


  $adm_aca_type = $form_state->getValue('name');
  $author1_validate = $form_state->getValue('table');
  $art_title = $form_state->getValue('title');
  $art_abstract = $form_state->getValue('abstract');
  $art_day = $form_state->getValue('day');
  $art_month = $form_state->getValue('month');
  $art_year = $form_state->getValue('year');
  $art_name = $form_state->getValue('jou_name');
  $art_vol = $form_state->getValue('jou_volume');
  $art_issue = $form_state->getValue('jou_issue');
  $art_spage = $form_state->getValue('jou_start_page');
  $art_fpage = $form_state->getValue('jou_final_page');
  $art_issn = $form_state->getValue('jou_issn');
  $art_url = $form_state->getValue('url');
  $art_doi = $form_state->getValue('doi');
  db_insert('reposi_article_book')->fields(array(
      'ab_type'              => 'Article',
      'ab_title'             => $art_title,
      'ab_abstract'          => $art_abstract,
      'ab_journal_editorial' => $art_name,
  ))->execute();
  $search_art = db_select('reposi_article_book', 'ab');
  $search_art->fields('ab')
          ->condition('ab.ab_type', 'Article', '=')
          ->condition('ab.ab_title', $art_title, '=');
  $art_id = $search_art->execute()->fetchField();
  $new_art_year = (int)$art_year;
  if (!empty($art_day)) {
    $new_art_day = (int)$art_day;
  } else {
    $new_art_day = NULL;
  }
  if (!empty($art_month)) {
    $new_art_month = (int)$art_month;
  } else {
    $new_art_month = NULL;
  }
  db_insert('reposi_date')->fields(array(
      'd_day'  => $new_art_day,
      'd_month'=> $new_art_month,
      'd_year' => $new_art_year,
      'd_abid' => $art_id,
  ))->execute();
  db_insert('reposi_publication')->fields(array(
      'p_type'  => 'Article',
      'p_source'=> 'Manual',
      'p_title' => $art_title,
      'p_year'  => $new_art_year,
      'p_check' => 1,
      'p_abid'  => $art_id,
  ))->execute();
  if (!empty($art_spage)) {
    $art_start_page = (int)$art_spage;
  } else {
    $art_start_page = NULL;
  }
  if (!empty($art_fpage)) {
    $art_final_page = (int)$art_fpage;
  } else {
    $art_final_page = NULL;
  }
  if (!empty($art_vol) || !empty($art_issue) || !empty($art_spage) ||
      !empty($art_fpage) || !empty($art_issn) || !empty($art_url) || !empty($art_doi)) {
    db_insert('reposi_article_book_detail')->fields(array(
      'abd_volume'     => $art_vol,
      'abd_issue'      => $art_issue,
      'abd_start_page' => $art_start_page,
      'abd_final_page' => $art_final_page,
      'abd_issn'       => $art_issn,
      'abd_url'        => $art_url,
      'abd_doi'        => $art_doi,
      'abd_abid'       => $art_id,
    ))->execute();
  }
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
          'ap_abid'      => $art_id,
        ))->execute();
      } else {
        $aut_publi_id2 = (int)$serch_aut[$a];
        db_insert('reposi_publication_author')->fields(array(
            'ap_author_id' => $aut_publi_id2,
            'ap_abid'      => $art_id,
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
        'pk_abid'       => $art_id,
      ))->execute();
      } else {
        $serch_keyw_id = (int)$serch_keyw[$cont_keywords];
        db_insert('reposi_publication_keyword')->fields(array(
          'pk_keyword_id' => $serch_keyw_id,
          'pk_abid'       => $art_id,
        ))->execute();
      }
      $cont_keywords++;
    }
  }

  drupal_set_message(t('Article: ') . $art_title . t(' was save.'));



//-------------------------------------------------------------------------------------------------------------------------
  }
// Llave que cierra la clase:--->
}
?>
