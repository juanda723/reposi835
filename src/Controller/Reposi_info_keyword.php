<?php
/**
 * @file
 * Contains \Drupal\hello_world\Controller\HelloController.
 */

namespace Drupal\reposi\Controller;
use Drupal\Core\Database;
use Drupal\Core\Url;
use Drupal\Core\Link;

class Reposi_info_keyword{

function reposi_info_key() {
  $id_keyw = \Drupal::routeMatch()->getParameter('node');
  global $base_url;
  $form['uid'] = array(
    '#type' => 'value',
    '#value' => $id_keyw,
  );
  $search_keyw = db_select('reposi_publication_keyword', 'pk');
  $search_keyw->fields('pk')
              ->condition('pk.pk_keyword_id', $id_keyw, '=');
  $p_k = $search_keyw->execute();
  $empty_p_k = $search_keyw->execute()->fetchField();
  $titles_publi = '';
  $flag_title=0;
  if (!empty($empty_p_k)) {
    foreach ($p_k as $publi) {
      $abc_id = $publi->pk_abid;
      $ts_id = $publi->pk_tsid;
      $cp_id = $publi->pk_cpid;
      $search_publi_abc = db_select('reposi_publication', 'p');
      $search_publi_abc->fields('p')
                  ->condition('p.p_check', 1, '=')
                  ->condition('p.p_abid', $abc_id, '=')
                  ->orderBy('p.p_year', 'DESC')
                  ->orderBy('p.p_title', 'ASC');
      $list_pub_abc = $search_publi_abc->execute();
      foreach ($list_pub_abc as $abc) {
        $flag_title++;
        if ($abc->p_type == 'Article') {
          $titles_publi .= '<li>'.'<i>'.t('Title: ').'</i>'.
                        \Drupal::l($abc->p_title,Url::fromRoute('reposi.Reposi_info_publicationAF',['node'=>$abc_id])) . '</li>';
        }
      }
      $search_publi_cp = db_select('reposi_publication', 'p');
      $search_publi_cp->fields('p')
                  ->condition('p.p_check', 1, '=')
                  ->condition('p.p_cpid', $cp_id, '=')
                  ->orderBy('p.p_year', 'DESC')
                  ->orderBy('p.p_title', 'ASC');
      $list_pub_cp = $search_publi_cp->execute();
      foreach ($list_pub_cp as $cp) {
        $flag_title++;
        if ($cp->p_type == 'Conference') {
          $titles_publi .= '<li>'.'<i>'.t('Title: ').'</i>'.
                        \Drupal::l($cp->p_title, Url::fromRoute('reposi.Reposi_info_publicationCF',['node'=>$cp_id])) . '</li>';
        }
      }
      $search_publi_ts = db_select('reposi_publication', 'p');
      $search_publi_ts->fields('p')
                  ->condition('p.p_check', 1, '=')
                  ->condition('p.p_tsid', $ts_id, '=')
                  ->orderBy('p.p_year', 'DESC')
                  ->orderBy('p.p_title', 'ASC');
      $list_pub_ts = $search_publi_ts->execute();
      foreach ($list_pub_ts as $ts) {
        $flag_title++;
        if ($ts->p_type == 'Thesis') {
          $titles_publi .= '<li>'.'<i>'.t('Title: ').'</i>'.
                        \Drupal::l($ts->p_title, Url::fromRoute('reposi.Reposi_info_publicationAF',['node'=>$ts_id])) . '</li>';
        }
      }
    }
  } else {
    $titles_publi = '<p>' . 'Without associated publications.' . '</p>';
  }
  $keyw_name = db_select('reposi_keyword', 'k');
  $keyw_name->fields('k')
            ->condition('k.kid', $id_keyw, '=');
  $title_keyw = $keyw_name->execute()->fetchAssoc();
  $markup = '<p>' . '<b>' . '<big>' . t($title_keyw['k_word']) . '</big>' . '</b>' .'</p>' .
            '<div>'. t('Associated publication(s): (').$flag_title.')' . '</div>' . '<ul>' .
            $titles_publi . '</ul>';
  $form['body'] = array('#markup' => $markup);
  return $form;
}
}
