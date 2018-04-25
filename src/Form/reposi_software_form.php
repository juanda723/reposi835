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
class reposi_software_form extends FormBase {

public function getFormId() {
    return 'software_form';
  }


public function buildForm(array $form, FormStateInterface $form_state) {
  global $_reposi_start_form;
  $markup = '<p>' . '<i>' . t('You must complete the required fields before the
            add producers.') . '</i>' . '</p>';
  $form['body'] = array('#markup' => $markup);
  $form['name'] = array(
    '#title' => t('Name'),
    '#type' => 'textfield',
    '#required' => TRUE,
    '#maxlength' => 511,
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
          '#title' => t('Producers'),
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
////////////////software
$form['version'] = array(
    '#title' => t('Version'),
    '#type' => 'textfield',
  );
  $form['place'] = array(
    '#title' => t('Place of production'),
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

  //function reposi_software_name_validate($form, &$form_state){
    $name_validate = $form_state->getValue('name');
    $search_sw = db_select('reposi_thesis_sw', 'sw');
    $search_sw->fields('sw')
            ->condition('sw.ts_type', 'Software', '=')
            ->condition('sw.ts_title', $name_validate, '=');
    $info_sw = $search_sw->execute();
    $new_name=Reposi_info_publication::reposi_string($name_validate);
    foreach ($info_sw as $titles) {
      $new_titles=Reposi_info_publication::reposi_string($titles->ts_title);
      if (strcasecmp($new_name, $new_titles) == 0) {
        $form_state->setErrorByName('name', t('This Software exists on Data Base.'));
      }
    }

////function reposi_publiform_year2_validate($form, &$form_state){
$year_validate = $form_state->getValue('year');
if(!is_numeric($year_validate) || $year_validate > '9999' ||
    $year_validate < '1000') {
  $form_state->setErrorByName('year', t('It is not an allowable value for year.'));
}
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
///end function
}
public function submitForm(array &$form, FormStateInterface $form_state) {
  $sw_name = $form_state->getValue('name');
  $sw_year = $form_state->getValue('year');
  $sw_vers = $form_state->getValue('version');
  $sw_place = $form_state->getValue('place');
  $sw_url = $form_state->getValue('url');
  db_insert('reposi_thesis_sw')->fields(array(
      'ts_type'        => 'Software',
      'ts_title'       => $sw_name,
      'ts_institu_ver' => $sw_vers,
      'ts_discip_place'=> $sw_place,
      'ts_url'         => $sw_url,
  ))->execute();
  $search_sw = db_select('reposi_thesis_sw', 'sw');
  $search_sw->fields('sw')
          ->condition('sw.ts_type', 'Software', '=')
          ->condition('sw.ts_title', $sw_name, '=');
  $softw_id = $search_sw->execute()->fetchField();
  $sw_id = (int)$softw_id;
  $softw_year = (int)$sw_year;

  db_insert('reposi_date')->fields(array(
      'd_year'        => $softw_year,
      'd_tsid'       => $sw_id,
  ))->execute();
  db_insert('reposi_publication')->fields(array(
      'p_type'  => 'Software',
      'p_source'=> 'Manual',
      'p_title' => $sw_name,
      'p_year'  => $softw_year,
      'p_check' => 1,
      'p_tsid'  => $sw_id,
  ))->execute();

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
                  'ap_tsid'      => $sw_id,
                ))->execute();
              } else {
                $aut_publi_id2 = (int)$serch_aut[$a];
                db_insert('reposi_publication_author')->fields(array(
                    'ap_author_id' => $aut_publi_id2,
                    'ap_tsid'      => $sw_id,
                ))->execute();
              }
            } else {
              if(isset($table[$a]['first_name']) || isset($table[$a]['f_lastname'])){
                drupal_set_message(t('The authors without first name or first
                last name will not be save.'), 'warning');
              }
            }
        }

          drupal_set_message(t('Software: ') . $sw_name . ' ' . t('was update.'));
          \Drupal::state()->delete('aut');


}
///Close class
}
