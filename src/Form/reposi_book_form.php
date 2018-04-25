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
class reposi_book_form extends FormBase {

public function getFormId() {
    return 'book_form';
  }


public function buildForm(array $form, FormStateInterface $form_state) {
  /*ISSET: Determina si una variable está definida y no es NULL.
  $form_state['storage']: Los datos colocados en el contenedor de almacenamiento de la colección $ form_state se almacenarán automáticamente en caché y se volverán a cargar cuando se envíe el formulario, permitiendo que su código acumule datos de paso a paso y lo procese en la etapa final sin ningún código adicional
  ESTÁ RETORNANDO EL VALOR DE 1 EN $form_state['storage']['author'] SI LA VARIABLE ESTÁ DEBIDAMENTE DECLARADA CON ANTERIORIDAD
        $form_state['storage']['author'] = isset($form_state['storage']['author'])?
                                           $form_state['storage']['author']:1;*/

    $_reposi_start_form=TRUE;
    $markup = '<p>' . '<i>' . t('You must complete the required fields before the
              add authors or keywords.') . '</i>' . '</p>';
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
      '#type' => 'fieldset',
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
    //********************************JOURNAL/BOOK JOURNAL/BOOK *******************************
    //*****************************************************************************************

    $form['langua'] = array(
      '#title' => t('Language'),
      '#type' => 'textfield',
    );
    $form['vol'] = array(
      '#title' => t('Volume/Series'),
      '#type' => 'textfield',
    );
    $form['issue'] = array(
      '#title' => t('Number (Issue)'),
      '#type' => 'textfield',
    );
    $form['edito'] = array(
      '#title' => t('Publisher'),
      '#type' => 'textfield',
    );
    $form['editor_name'] = array(
      '#title' => t('Publisher name'),
      '#type' => 'textfield',
      '#description' => t('Editor person'),
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
    /******************************************************************/
    /******************************************************************/
    /******************************************************************/

     return $form;

    }

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

      {
        $title_validate = $form_state->getValue('title');
        $search_book = db_select('reposi_article_book', 'ab');
          $search_book->fields('ab')
                  ->condition('ab.ab_type', 'Book', '=')
                  ->condition('ab.ab_title', $title_validate, '=');
          $info_book = $search_book->execute();
          $new_title=Reposi_info_publication::reposi_string($title_validate);
        foreach ($info_book as $titles) {
          $new_titles=Reposi_info_publication::reposi_string($titles->ab_title);
          if (strcasecmp($new_title, $new_titles) == 0) {
            $form_state->setErrorByName('name', t('This Book exists on Data Base.'));
          }
        }
      }

//valdate reposi_publiform_authorn_validate reposi_publiform_authorl_validate
$table = $form_state->getValue('table');
$first_name_validate=$table[0]['first_name'];
$first_lastname_validate=$table[0]['f_lastname'];
if (empty($first_name_validate) || empty($first_lastname_validate)){
  $form_state->setErrorByName('first_name', t('One author is required as minimum
  (first name and last name).'));
}
    // DAY, month year


    $day_validate = $form_state->getValue('day');
    if(!empty($day_validate) && (!is_numeric($day_validate) ||
        $day_validate > '31' || $day_validate < '1')) {
     // form_set_error('day', t('It is not an allowable value for day.'));
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
    ///validate Url
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
      $book_title = $form_state->getValue('title');
      $book_sub = $form_state->getValue('sub');
      $book_des = $form_state->getValue('description');
      $book_year = $form_state->getValue('year');
      $book_langu = $form_state->getValue('langua');
      $book_vol = $form_state->getValue('vol');
      $book_issue = $form_state->getValue('issue');
      $book_edito = $form_state->getValue('edito');
      $book_edit_name = $form_state->getValue('editor_name');
      $book_pub = $form_state->getValue('pub');
      $book_issn = $form_state->getValue('issn');
      $book_isbn = $form_state->getValue('isbn');
      $book_url = $form_state->getValue('url');
      $book_doi = $form_state->getValue('doi');

      db_insert('reposi_article_book')->fields(array(
          'ab_type'              => 'Book',
          'ab_title'             => $book_title,
          'ab_subtitle_chapter'  => $book_sub,
          'ab_abstract'          => $book_des,
          'ab_language'          => $book_langu,
          'ab_journal_editorial' => $book_edito,
          'ab_publisher'         => $book_edit_name,
          'ab_place'             => $book_pub,
      ))->execute();
      $search_book = db_select('reposi_article_book', 'ab');
      $search_book->fields('ab')
              ->condition('ab.ab_type', 'Book', '=')
              ->condition('ab.ab_title', $book_title, '=');
      $book_id = $search_book->execute()->fetchField();
      $new_book_year = (int)$book_year;
      db_insert('reposi_date')->fields(array(
          'd_year' => $new_book_year,
          'd_abid' => $book_id,
      ))->execute();
      db_insert('reposi_publication')->fields(array(
          'p_type'  => 'Book',
	  'p_source'=> 'Manual',
          'p_title' => $book_title,
          'p_year'  => $new_book_year,
          'p_check' => 1,
          'p_abid'  => $book_id,
      ))->execute();
      if (!empty($book_vol) || !empty($book_issue) || !empty($book_issn) ||
          !empty($book_isbn) || !empty($book_url) || !empty($book_doi)) {
        db_insert('reposi_article_book_detail')->fields(array(
          'abd_volume'     => $book_vol,
          'abd_issue'      => $book_issue,
          'abd_issn'       => $book_issn,
          'abd_isbn'       => $book_isbn,
          'abd_url'        => $book_url,
          'abd_doi'        => $book_doi,
          'abd_abid'       => $book_id,
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
              'ap_abid'      => $book_id,
            ))->execute();
          } else {
            $aut_publi_id2 = (int)$serch_aut[$a];
            db_insert('reposi_publication_author')->fields(array(
                'ap_author_id' => $aut_publi_id2,
                'ap_abid'      => $book_id,
            ))->execute();
          }
        } else {
          if(isset($table[$a]['first_name']) || isset($table[$a]['f_lastname'])){
            drupal_set_message(t('The authors without first name or first
            last name will not be save.'), 'warning');
          }
        }
    }

      drupal_set_message(t('Book: ') . $book_title . t(' was save.'));
      \Drupal::state()->delete('aut');
    }

    function reposi_book_form_submit($form, &$form_state){

    }

  // Llave que cierra la clase:--->
  }
  ?>
