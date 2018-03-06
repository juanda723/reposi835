<?php

namespace Drupal\reposi\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;

class Reposi_list_publication_free extends FormBase {

  public function getFormId() {
    return 'ListPublicationFree_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['search'] = array(
    '#title' => t('Search'),
    '#type' => 'fieldset',
  );
    $form['search']['search_field'] = array(
    '#title' => t('Search on title'),
    '#type' => 'textfield',
    '#size' => 78,
    '#maxlength' => 511,
  );
 
   $build['pager'] = [
   '#type' => 'pager',
   ];


  $form['words'] = array(
    '#type' => 'value',
    '#value' => '..words',
  );

 // $form['body'] = array('#markup' => $publications);

   /* $form['pager'] = array(
    '#theme' => 'pager',
  );*/
    $form['search']['search_but'] = array(
    '#type' => 'submit',
    '#value' => t('Search'),
  );
    return $form;

  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
   
  $publications="";
  global $base_url;
  $search_publi = db_select('reposi_publication', 'p')->extend('Drupal\Core\Database\Query\PagerSelectExtender');
   $search_publi->fields('p')
               ->condition('p.p_check', 1, '=')
               ->orderBy('p.p_year', 'DESC')
               ->limit(10);
  $list_pub = $search_publi->execute();
  
  foreach ($list_pub as $list_p) 
    {
    $pub_type = $list_p->p_type;
    $pub_title = $list_p->p_title;
    $pub_year = $list_p->p_year;
    $tsid = $list_p->p_tsid;
    $abid = $list_p->p_abid;  
    if (isset($abid)) 
      {
      $search_p_a = db_select('reposi_publication_author', 'pa');
      $search_p_a->fields('pa', array('ap_author_id', 'ap_abid'))
                 ->condition('pa.ap_abid', $abid, '=');
      $p_a = $search_p_a->execute();
      $list_aut_abc='';
      foreach ($p_a as $art_aut) 
        {
        $search_aut = db_select('reposi_author', 'a');
        $search_aut->fields('a')
                   ->condition('a.aid', $art_aut->ap_author_id, '=');
        $each_aut = $search_aut->execute()->fetchAssoc();
        $f_name = reposi_string($each_aut['a_first_name']);
        if (!empty($each_aut['a_second_name'])) 
	  {
          $s_name = reposi_string($each_aut['a_second_name']);
          $url = Url::fromRoute('reposi.author', ['node' => $art_aut->ap_author_id]);
          $link= \Drupal::l(t($each_aut['a_first_lastname'] . ' ' .
                        $each_aut['a_second_lastname'] . ' ' . $f_name[0] . '. ' . $s_name[0] . '.'), 
                        $url);
          $list_aut_abc = $list_aut_abc . $link . ', ';
          /*$list_aut_abc = $list_aut_abc . l($each_aut['a_first_lastname'] . ' ' .
                        $each_aut['a_second_lastname'] . ' ' . $f_name[0] . '. ' . $s_name[0] . '.',
                        $base_url . '/reposi/author/' . $art_aut->ap_author_id) . ', ';*/
          } else 
      	  {
          $url = Url::fromRoute('reposi.author', ['node' => $art_aut->ap_author_id]);
          $link= \Drupal::l(t($each_aut['a_first_lastname'] . ' ' .
                        $each_aut['a_second_lastname'] . ' ' . $f_name[0] . '.'), 
                        $url);
          $list_aut_abc = $list_aut_abc . $link . ', ';
          /*$list_aut_abc = $list_aut_abc . l($each_aut['a_first_lastname'] . ' ' .
                        $each_aut['a_second_lastname'] . ' ' . $f_name[0] . '.',
                        $base_url . '/reposi/author/' . $art_aut->ap_author_id) . ', ';*/
          }
       }
//****

   if ($pub_type == 'Article') {
        $publications = $publications .'<p>'. $list_aut_abc.'(' . $pub_year . ') ' .'<b>'. l($pub_title, 
                        $base_url . '/reposi/article/' . $abid . '/free') . '</b>' . '.' . '<br>' .
                        '<small>' . t('Export formats: ') . 
                        l(t('RIS'), $base_url . '/reposi/ris/' . $list_p->pid) . '</small>' . '</p>';
      } elseif ($list_p->p_type == 'Book'){
        $publications .= '<p>'. $list_aut_abc.'(' . $pub_year . ') ' .'<b>'. l($pub_title, 
                        $base_url . '/reposi/book/' . $abid . '/free') . '</b>' . '.' . '<br>' .
                        '<small>' . t('Export formats: ') . 
                        l(t('RIS'), $base_url . '/reposi/ris/' . $list_p->pid) . '</small>' . '</p>';
      } else {
        $publications .= '<p>'. $list_aut_abc.'(' . $pub_year . ') ' .'<b>'. 
                        l($pub_title, $base_url . '/reposi/chap_book/' . $abid . '/free') . '</b>' . '.' . 
                        '<br>' . '<small>' . t('Export formats: ') . 
                        l(t('RIS'), $base_url . '/reposi/ris/' . $list_p->pid) . '</small>' . '</p>';
      }
    } elseif (isset($tsid)) {
      $search_p_a = db_select('reposi_publication_author', 'pa');
      $search_p_a->fields('pa', array('ap_author_id', 'ap_tsid'))
                 ->condition('pa.ap_tsid', $tsid, '=');
      $p_a = $search_p_a->execute();
      $list_aut_ts='';
      foreach ($p_a as $the_aut) {
        $search_aut = db_select('reposi_author', 'a');
        $search_aut->fields('a')
                   ->condition('a.aid', $the_aut->ap_author_id, '=');
        $each_aut = $search_aut->execute()->fetchAssoc();
        $f_name = reposi_string($each_aut['a_first_name']);
        if (!empty($each_aut['a_second_name'])) {
          $s_name = reposi_string($each_aut['a_second_name']);
          $list_aut_ts = $list_aut_ts . l($each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] .
                        ' ' . $f_name[0] . '. ' . $s_name[0] . '.',
                        $base_url . '/reposi/author/' . $the_aut->ap_author_id) . ', ';
        } else {
          $list_aut_ts = $list_aut_ts . l($each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] .
                        ' ' . $f_name[0] . '.', $base_url . '/reposi/author/' . $the_aut->ap_author_id) . ', ';
        }
      }

//****

  if ($pub_type == 'Thesis') {
        $publications .= '<p>'. $list_aut_ts. '(' . $pub_year . ') ' .'<b>'. l($pub_title, 
                        $base_url . '/reposi/thesis/' . $tsid . '/free') . '</b>' . '.' . '<br>' .
                        '<small>' . t('Export formats: ') . 
                        l(t('RIS'), $base_url . '/reposi/ris/' . $list_p->pid) . '</small>' . '</p>';
      } else {
        $publications .= '<p>'. $list_aut_ts. '(' . $pub_year . ') ' .'<b>'. l($pub_title, 
                        $base_url . '/reposi/software/' . $tsid . '/free') . '</b>' . '.' . '<br>' .
                        '<small>' . t('Export formats: ') . 
                        l(t('RIS'), $base_url . '/reposi/ris/' . $list_p->pid) . '</small>' . '</p>';
      }
    } else {
      $cpid = $list_p->p_cpid;
      $search_p_a = db_select('reposi_publication_author', 'pa');
      $search_p_a->fields('pa', array('ap_author_id', 'ap_cpid'))
                 ->condition('pa.ap_cpid', $cpid, '=');
      $p_a = $search_p_a->execute();
      $list_aut_cp='';
      foreach ($p_a as $con_aut) {
        $search_aut = db_select('reposi_author', 'a');
        $search_aut->fields('a')
                   ->condition('a.aid', $con_aut->ap_author_id, '=');
        $each_aut = $search_aut->execute()->fetchAssoc();
        $f_name = reposi_string($each_aut['a_first_name']);
        if (!empty($each_aut['a_second_name'])) {
          $s_name = reposi_string($each_aut['a_second_name']);
          $list_aut_cp = $list_aut_cp . l($each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] .
                        ' ' . $f_name[0] . '. ' . $s_name[0] . '.',
                        $base_url . '/reposi/author/' . $con_aut->ap_author_id) . ', ';
        } else {
          $list_aut_cp = $list_aut_cp . l($each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] .
                        ' ' . $f_name[0] . '.', $base_url . '/reposi/author/' . $con_aut->ap_author_id) . ', ';
        }
      }
      if ($pub_type == 'Conference') {
        $publications .= '<p>'.$list_aut_cp . '(' . $pub_year . ') ' .'<b>'. 
                        l($pub_title, $base_url . '/reposi/conference/' . $cpid . '/free') . 
                        '</b>' . '.' . '<br>' . '<small>' . t('Export formats: ') . 
                        l(t('RIS'), $base_url . '/reposi/ris/' . $list_p->pid) . '</small>' . '</p>';
      } else {
        $publications .= '<p>'.$list_aut_cp . '(' . $pub_year . ') ' .'<b>'. 
                  l($pub_title, $base_url . '/reposi/patent/' . $cpid . '/free') . '</b>' .
                  '.' . '<br>' . '<small>' . t('Export formats: ') . 
                  l(t('RIS'), $base_url . '/reposi/ris/' . $list_p->pid) . '</small>' . '</p>';
      }
    } 
  }
  if (empty($publications)) {
    $publications .= '<p>'. 'No records'. '</p>';
  }

  //$form_state->setValue($publications);
  //$form_state->setErrorByName($publications, $this->t('Publicaciones....'));

  //$form['body'] = array('#markup' => $publications);
////////////////  print_r(array('#markup' => $publications));

  echo $this->publications=$publications;
// LA SIGUIENTE LLAVE CIERRA LA FUNCIÓN VALIDACIÓN

  //$form_state->response($publications);

//  return $publications;

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  
   echo $this->publications;
 // drupal_set_message(t('The redirect has been saved.'.$this->publications));

  //$field_bit = explode(' ', $form_state['input']['search_field']);
  // explode divide en varios string
  //implode convierte un array en un string
  $field_bit = explode(' ', $form_state->getValue('search_field'));
  //$field_bit = explode(' ', 'asasda');
  $searching = implode('-', $field_bit);
  //$form_state['redirect'] = $base_url . '/reposi/results/' . $searching;
  //CAMBIAAAAA LO SIGUIENTE---->
  if (empty($searching)){
    $form_state->setRedirect('reposi');
    drupal_set_message(t('No records'),'error');
  }
  else
    $form_state->setRedirect('reposi.PubliListReposiSearch', ['node' => $searching]);
  }
 
}


