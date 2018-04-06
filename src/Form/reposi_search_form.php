<?php

namespace Drupal\reposi\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\reposi\Controller\Reposi_info_publication;

/**
 * Implements an example form.
 */

class reposi_search_form extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'reposi_search_form_id';
  }

  /**
   * {@inheritdoc}
   */
public function buildForm(array $form, FormStateInterface $form_state) {  

  $words = \Drupal::routeMatch()->getParameter('node');
  $form['words'] = array(
    '#type' => 'value',
    '#value' => $words,
  );
  $each_word = explode('-', $words);
  $num_words = count($each_word);
  $complete = implode(' ', $each_word);
  $search_compl = db_select('reposi_publication', 'p');
  $search_compl->fields('p')
              ->condition('p.p_check', 1, '=')
              ->condition('p.p_title', $complete, '=');
  $search_cpl = $search_compl->execute();
  $search_cpl -> allowRowCount = TRUE;
  $somethig_cpl = $search_cpl->rowCount();
  $pids = array();
  $form['body'] = array();
  if ($somethig_cpl <> 0) {
    foreach ($search_cpl as $ids_cpl) {
      $pids[] = $ids_cpl->pid;
    }
  }
  $search_by_char = db_select('reposi_publication', 'p');
  $search_by_char->fields('p')
              ->condition('p.p_check', 1, '=');
  $search_char = $search_by_char->execute();
  foreach ($search_char as $characters) {
    $title_bit = explode(' ', $characters->p_title);
    foreach ($title_bit as $search_word) {
      for ($i=1; $i <= $num_words; $i++) {
        $a = $i-1;
        $new_search_word = strtolower($search_word);
        $new_each_word = strtolower($each_word[$a]);
        $new_search_w = Reposi_info_publication::reposi_string($new_search_word);
        $new_each_w = Reposi_info_publication::reposi_string($new_each_word);
        if ($new_search_w == $new_each_w) {
          $pids[] = $characters->pid;
        }
      }
    }
  }
  $new_pids = array_unique($pids);
  $publications = '';
  foreach ($new_pids as $ids) {
    $search_publi = db_select('reposi_publication', 'p');
    $search_publi->fields('p')
                ->condition('p.pid', $ids, '=')
                ->orderBy('p.p_year', 'DESC');
    $pager=$search_publi->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(10);
    $list_pub = $pager->execute();
    foreach ($list_pub as $list_p) {
      $pub_type = $list_p->p_type;
      $pub_title = $list_p->p_title;
      $pub_year = $list_p->p_year;
      $tsid = $list_p->p_tsid;
      $abid = $list_p->p_abid;
      if (isset($abid)) {
        $search_p_a = db_select('reposi_publication_author', 'pa');
        $search_p_a->fields('pa', array('ap_author_id', 'ap_abid'))
                   ->condition('pa.ap_abid', $abid, '=');
        $p_a = $search_p_a->execute();
        $list_aut_abc='';
        foreach ($p_a as $art_aut) {
          $search_aut = db_select('reposi_author', 'a');
          $search_aut->fields('a')
                     ->condition('a.aid', $art_aut->ap_author_id, '=');
          $each_aut = $search_aut->execute()->fetchAssoc();
          $f_name = Reposi_info_publication::reposi_string($each_aut['a_first_name']);
          if (!empty($each_aut['a_second_name'])) {
            $s_name = Reposi_info_publication::reposi_string($each_aut['a_second_name']);
            $list_aut_abc = $list_aut_abc . \Drupal::l($each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] .
                          ' ' . $f_name[0] . '. ' . $s_name[0] . '.',
                          Url::fromRoute('reposi.author_aid',['node'=> $art_aut->ap_author_id])) . ', ';
          } else {
            $list_aut_abc = $list_aut_abc . \Drupal::l($each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] .
                          ' ' . $f_name[0] . '.',Url::fromRoute('reposi.author_aid',['node'=> $art_aut->ap_author_id])) . ', ';
          }
        }
        if ($pub_type == 'Article') {
          $publications = $publications .'<p>'. $list_aut_abc.'(' . $pub_year . ') ' .'<b>'. \Drupal::l($pub_title,
                          Url::fromRoute('reposi.Reposi_info_publicationAF',['node'=> $abid])) . '</b>' . '.' . '<br>' .
                          '<small>' . t('Export formats: ') .
                          \Drupal::l(t('RIS'),Url::fromRoute('reposi.author_aid',['node'=> $list_p->pid])) . '</small>' . '</p>';
        } elseif ($list_p->p_type == 'Book'){
          $publications .= '<p>'. $list_aut_abc.'(' . $pub_year . ') ' .'<b>'. \Drupal::l($pub_title,
                          Url::fromRoute('reposi.Reposi_info_publicationBF',['node'=> $abid])) . '</b>' . '.' . '<br>' .
                          '<small>' . t('Export formats: ') .
                          \Drupal::l(t('RIS'),Url::fromRoute('reposi.author_aid',['node'=> $list_p->pid])) . '</small>' . '</p>';
        } else {
          $publications .= '<p>'. $list_aut_abc.'(' . $pub_year . ') ' .'<b>'.
                          \Drupal::l($pub_title, Url::fromRoute('reposi.Reposi_info_publicationCBF',['node'=> $abid])) . '</b>' .
                          '.' . '<br>' . '<small>' . t('Export formats: ') .
                          \Drupal::l(t('RIS'),Url::fromRoute('reposi.author_aid',['node'=> $list_p->pid])) . '</small>' . '</p>';
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
          $f_name = Reposi_info_publication::reposi_string($each_aut['a_first_name']);
          if (!empty($each_aut['a_second_name'])) {
            $s_name = Reposi_info_publication::reposi_string($each_aut['a_second_name']);
            $list_aut_ts = $list_aut_ts . \Drupal::l($each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] .
                          ' ' . $f_name[0] . '. ' . $s_name[0] . '.',
                          Url::fromRoute('reposi.author_aid',['node'=> $the_aut->ap_author_id])) . ', ';
          } else {
            $list_aut_ts = $list_aut_ts . \Drupal::l($each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] .
                          ' ' . $f_name[0] . '.', Url::fromRoute('reposi.author_aid',['node'=> $the_aut->ap_author_id])) . ', ';
          }
        }
        if ($pub_type == 'Thesis') {
          $publications .= '<p>'. $list_aut_ts. '(' . $pub_year . ') ' .'<b>'.
                          \Drupal::l($pub_title, Url::fromRoute('reposi.Reposi_info_publicationTF',['node'=> $tsid])) . '</b>' . '.' . '<br>' .
                          '<small>' . t('Export formats: ') . \Drupal::l(t('RIS'),
                          Url::fromRoute('reposi.author_aid',['node'=> $list_p->pid])) . '</small>' . '</p>';
        } else {
          $publications .= '<p>'. $list_aut_ts. '(' . $pub_year . ') ' .'<b>'. \Drupal::l($pub_title,
                          Url::fromRoute('reposi.Reposi_info_publicationSF',['node'=> $tsid])) . '</b>' . '.' . '<br>' .
                          '<small>' . t('Export formats: ') .
                          \Drupal::l(t('RIS'),Url::fromRoute('reposi.author_aid',['node'=> $list_p->pid])) . '</small>' . '</p>';
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
          $f_name = Reposi_info_publication::reposi_string($each_aut['a_first_name']);
          if (!empty($each_aut['a_second_name'])) {
            $s_name = Reposi_info_publication::reposi_string($each_aut['a_second_name']);
            $list_aut_cp = $list_aut_cp . \Drupal::l($each_aut['a_first_lastname'] . ' ' .
                          $each_aut['a_second_lastname'] . ' ' . $f_name[0] . '. ' . $s_name[0] . '.',
                          Url::fromRoute('reposi.author_aid',['node'=> $con_aut->ap_author_id])) . ', ';
          } else {
            $list_aut_cp = $list_aut_cp . \Drupal::l($each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] .
                          ' ' . $f_name[0] . '.', Url::fromRoute('reposi.author_aid',['node'=> $con_aut->ap_author_id])) . ', ';
          }
        }
        if ($pub_type == 'Conference') {
          $publications .= '<p>'.$list_aut_cp . '(' . $pub_year . ') ' .'<b>'.
                          \Drupal::l($pub_title, Url::fromRoute('reposi.Reposi_info_publicationCF',['node'=> $cpid])) .
                          '</b>' . '.' . '<br>' . '<small>' . t('Export formats: ') .
                          \Drupal::l(t('RIS'),Url::fromRoute('reposi.author_aid',['node'=> $list_p->pid])) . '</small>' . '</p>';
        } else {
          $publications .= '<p>'.$list_aut_cp . '(' . $pub_year . ') ' .'<b>'.
                    \Drupal::l($pub_title, Url::fromRoute('reposi.Reposi_info_publicationPF',['node'=> $cpid])) . '</b>' . '.' . '<br>' .
                    '<small>' . t('Export formats: ') .
                    \Drupal::l(t('RIS'),Url::fromRoute('reposi.author_aid',['node'=> $list_p->pid])) . '</small>' . '</p>';
        }
      }
    }
  }
  if (empty($publications)) {
    $publications .= '<p>'. 'No matches'. '</p>';
  }
  $form['body'] = array('#markup' => $publications);
  $form['pid']= array('#type' => 'value',
  		    '#value' => $ids,);
  $form['pager']=['#type' => 'pager'];     

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) 
  {
  
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
 
  }
 
}


