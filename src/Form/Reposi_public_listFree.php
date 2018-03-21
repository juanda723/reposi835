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
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * Implements an example form.
 */
class Reposi_public_listFree extends FormBase {

  public function getFormId() {
    return 'public_listFree';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    global $base_url;
    $form['search'] = array(
      '#title' => t('Search'),
       '#type' => 'details',
      '#open' => TRUE,
    );
    $form['search']['search_field'] = array(
      '#title' => t('Search on title'),
      '#type' => 'textfield',
      '#size' => 78,
      '#maxlength' => 511,
    );
    $search_publi = db_select('reposi_publication', 'p');
    $search_publi->fields('p')
                ->condition('p.p_check', 1, '=')
                ->orderBy('p.p_year', 'DESC');
    $pager=$search_publi->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(10);
    $list_pub = $pager->execute();
    $publications = '';
    $form['body'] = array();
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
            $list_aut_abc = $list_aut_abc . \Drupal::l($each_aut['a_first_lastname'] . ' ' .
                          $each_aut['a_second_lastname'] . ' ' . $f_name[0] . '. ' . $s_name[0] . '.',
                          Url::fromRoute('reposi.author_aid',['node'=> $art_aut->ap_author_id])) . ', ';
          } else {
            $list_aut_abc = $list_aut_abc . \Drupal::l($each_aut['a_first_lastname'] . ' ' .
                          $each_aut['a_second_lastname'] . ' ' . $f_name[0] . '.',
                          Url::fromRoute('reposi.author_aid',['node'=> $art_aut->ap_author_id])) . ', ';
          }
        }
        if ($pub_type == 'Article') {
          $publications = $publications .'<p>'. $list_aut_abc.'(' . $pub_year . ') ' .'<b>'. \Drupal::l($pub_title,
                          Url::fromRoute('reposi.Reposi_info_publicationAF',['node'=>$abid])) . '</b>' . '.' . '<br>' .
                          '<small>' . t('Export formats: ') .
                          \Drupal::l(t('RIS'), Url::fromRoute('reposi.reposi_format_ris',['node'=> $list_p->pid])) . '</small>' . '</p>';
        } elseif ($list_p->p_type == 'Book'){
          $publications .= '<p>'. $list_aut_abc.'(' . $pub_year . ') ' .'<b>'. \Drupal::l($pub_title,
                          Url::fromRoute('reposi.Reposi_info_publicationBF',['node'=>$abid])) . '</b>' . '.' . '<br>' .
                          '<small>' . t('Export formats: ') .
                          \Drupal::l(t('RIS'), Url::fromRoute('reposi.reposi_format_ris',['node'=> $list_p->pid])) . '</small>' . '</p>';
        } else {
          $publications .= '<p>'. $list_aut_abc.'(' . $pub_year . ') ' .'<b>'.
                          \Drupal::l($pub_title, Url::fromRoute('reposi.Reposi_info_publicationCBF',['node'=>$abid])) . '</b>' . '.' .
                          '<br>' . '<small>' . t('Export formats: ') .
                          \Drupal::l(t('RIS'), Url::fromRoute('reposi.reposi_format_ris',['node'=> $list_p->pid])) . '</small>' . '</p>';
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
          $publications .= '<p>'. $list_aut_ts. '(' . $pub_year . ') ' .'<b>'. \Drupal::l($pub_title,
                          Url::fromRoute('reposi.Reposi_info_publicationTF',['node'=>$tsid])) . '</b>' . '.' . '<br>' .
                          '<small>' . t('Export formats: ') .
                          \Drupal::l(t('RIS'), Url::fromRoute('reposi.reposi_format_ris',['node'=> $list_p->pid])) . '</small>' . '</p>';
        } else {
          $publications .= '<p>'. $list_aut_ts. '(' . $pub_year . ') ' .'<b>'. \Drupal::l($pub_title,
                          Url::fromRoute('reposi.Reposi_info_publicationSF',['node'=>$tsid])) . '</b>' . '.' . '<br>' .
                          '<small>' . t('Export formats: ') .
                          \Drupal::l(t('RIS'), Url::fromRoute('reposi.reposi_format_ris',['node'=> $list_p->pid])) . '</small>' . '</p>';
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
            $list_aut_cp = $list_aut_cp . \Drupal::l($each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] .
                          ' ' . $f_name[0] . '. ' . $s_name[0] . '.',
                          Url::fromRoute('reposi.author_aid',['node'=> $con_aut->ap_author_id])) . ', ';
          } else {
            $list_aut_cp = $list_aut_cp . \Drupal::l($each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] .
                          ' ' . $f_name[0] . '.', Url::fromRoute('reposi.author_aid',['node'=> $con_aut->ap_author_id])) . ', ';
          }
        }
        if ($pub_type == 'Conference') {
          $publications .= '<p>'.$list_aut_cp . '(' . $pub_year . ') ' .'<b>'.
                          \Drupal::l($pub_title, Url::fromRoute('reposi.Reposi_info_publicationCF',['node'=>$cpid])) .
                          '</b>' . '.' . '<br>' . '<small>' . t('Export formats: ') .
                          \Drupal::l(t('RIS'), Url::fromRoute('reposi.reposi_format_ris',['node'=> $list_p->pid])) . '</small>' . '</p>';
        } else {
          $publications .= '<p>'.$list_aut_cp . '(' . $pub_year . ') ' .'<b>'.
                    \Drupal::l($pub_title, Url::fromRoute('reposi.Reposi_info_publicationPF',['node'=>$cpid])) . '</b>' .
                    '.' . '<br>' . '<small>' . t('Export formats: ') .
                    \Drupal::l(t('RIS'), Url::fromRoute('reposi.reposi_format_ris',['node'=> $list_p->pid])) . '</small>' . '</p>';
        }
      }
    }
    if (empty($publications)) {
      $publications .= '<p>'. 'No records'. '</p>';
    }
    $form['body'] = array('#markup' => $publications);

    $form['pager']=['#type' => 'pager'];

    $form['search']['search_but'] = array(
      '#type' => 'submit',
      '#value' => t('Search'),
    );
    return $form;

  }


  public function validateForm(array &$form, FormStateInterface $form_state) {

}
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  $field_bit = explode(' ', $form_state->getValue('search_field'));
  $searching = implode('-', $field_bit);
  if (empty($searching)){
    $form_state->setRedirect('reposi');
    drupal_set_message(t('No records'),'error');
  }
  else
  {
    $form_state->setRedirect('reposi.PubliListReposiSearch', ['node' => $searching]);
  }

  }
// Llave que cierra la clase:--->
}
?>
