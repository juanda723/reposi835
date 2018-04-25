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
class reposi_patent_form extends FormBase {

public function getFormId() {
    return 'patent_form';
  }


public function buildForm(array $form, FormStateInterface $form_state) {
  global $_reposi_start_form;
  $markup = '<p>' . '<i>' . t('You must complete the required fields before the
            add originators.') . '</i>' . '</p>';
  $form['body'] = array('#markup' => $markup);
  $form['invention'] = array(
    '#title' => t('Invention'),
    '#type' => 'textfield',
    '#required' => TRUE,
    '#maxlength' => 511,
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

///////////////author


      $form['author'] = array(
          '#type' => 'details',
          '#open' => TRUE,
          '#title' => t('Originator'),
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

////////Patent
$form['abstract'] = array(
  '#title' => t('Abstract'),
  '#type' => 'textarea',
);
$form['owner'] = array(
  '#title' => t('Owner'),
  '#type' => 'textfield',
  '#maxlength' => 511,
);
$form['type'] = array(
  '#title' => t('Type patent'),
  '#type' => 'textfield',
);
$form['num'] = array(
  '#title' => t('Number'),
  '#type' => 'textfield',
);
$form['url'] = array(
  '#title' => t('URL'),
  '#description' => t('Example: https://www.example.com'),
  '#type' => 'textfield',
  '#maxlength' => 511,
);
$form['save'] = array(
  '#type' => 'submit',
  '#value' => t('Save'),
);

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
//  function reposi_patent_title_validate($form, &$form_state){
    $title_validate = $form_state->getValue('invention');
    $search_pat = db_select('reposi_confer_patent', 'cp');
    $search_pat->fields('cp')
            ->condition('cp.cp_type', 'Patent', '=')
            ->condition('cp.cp_title', $title_validate, '=');
    $info_pat = $search_pat->execute();
    $new_title=Reposi_info_publication::reposi_string($title_validate);
    foreach ($info_pat as $titles) {
      $new_titles=Reposi_info_publication::reposi_string($titles->cp_title);
      if (strcasecmp($new_title, $new_titles) == 0) {
        $form_state->setErrorByName('name', t('This Patent exists on Data Base.'));
      }
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
    /////////

    //function reposi_publiform_authorn_validate($form, &$form_state){
    $table = $form_state->getValue('table');
    $first_name_validate=$table[0]['first_name'];
    $first_lastname_validate=$table[0]['f_lastname'];
    if (empty($first_name_validate) || empty($first_lastname_validate)){
      $form_state->setErrorByName('first_name', t('One author is required as minimum
      (first name and last name).'));
    }
    ///validate Url
      $url=$form_state->getValue('url');
      if(!empty($url) && !UrlHelper::isValid($url, TRUE))
      {
       $form_state->setErrorByName('uri', t('The URL is not valid.'));
      }

}
public function submitForm(array &$form, FormStateInterface $form_state) {
  $pat_name = $form_state->getValue('invention');
    $pat_abs = $form_state->getValue('abstract');
    $pat_day = $form_state->getValue('day');
    $pat_mon = $form_state->getValue('month');
    $pat_year = $form_state->getValue('year');
    $pat_owner = $form_state->getValue('owner');
    $pat_type = $form_state->getValue('type');
    $pat_num = $form_state->getValue('num');
    $pat_url = $form_state->getValue('url');
    db_insert('reposi_confer_patent')->fields(array(
        'cp_type'       => 'Patent',
        'cp_title'      => $pat_name,
        'cp_abstract'   => $pat_abs,
        'cp_number'     => $pat_num,
        'cp_spon_owner' => $pat_owner,
        'cp_place_type' => $pat_type,
        'cp_url'        => $pat_url,
    ))->execute();
    $search_pat = db_select('reposi_confer_patent', 'cp');
    $search_pat->fields('cp')
            ->condition('cp.cp_type', 'Patent', '=')
            ->condition('cp.cp_title', $pat_name, '=');
    $pat_id = $search_pat->execute()->fetchField();
    $patent_id = (int)$pat_id;
    if (!empty($pat_day)) {
      $patent_day = (int)$pat_day;
    } else {
      $patent_day = NULL;
    }
    if (!empty($pat_mon)) {
      $patent_mon = (int)$pat_mon;
    } else {
      $patent_mon = NULL;
    }
    $patent_year = (int)$pat_year;
    db_insert('reposi_date')->fields(array(
        'd_day'   => $patent_day,
        'd_month' => $patent_mon,
        'd_year'  => $patent_year,
        'd_cpid'  => $patent_id,
    ))->execute();
    db_insert('reposi_publication')->fields(array(
        'p_type'  => 'Patent',
	'p_source'=> 'Manual',
        'p_title' => $pat_name,
        'p_year'  => $patent_year,
        'p_check' => 1,
        'p_cpid'  => $patent_id,
    ))->execute();
    //forrrrrrrrrrrrrrrrrrrrrrrrrr
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
                  'ap_cpid'      => $patent_id,
                ))->execute();
              } else {
                $aut_publi_id2 = (int)$serch_aut[$a];
                db_insert('reposi_publication_author')->fields(array(
                    'ap_author_id' => $aut_publi_id2,
                    'ap_cpid'      => $patent_id,
                ))->execute();
              }
            } else {
              if(isset($table[$a]['first_name']) || isset($table[$a]['f_lastname'])){
                drupal_set_message(t('The authors without first name or first
                last name will not be save.'), 'warning');
              }
            }
        }

          drupal_set_message(t('Patent: ') . $pat_name . ' ' . t('was save.'));
          \Drupal::state()->delete('aut');

}//end function
///end class
}
