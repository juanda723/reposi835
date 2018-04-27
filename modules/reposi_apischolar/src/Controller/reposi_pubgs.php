<?php
/**
 * @file
 * Contains \Drupal\hello_world\Controller\HelloController.
 */

namespace Drupal\reposi_apischolar\Controller;
use Drupal\Core\Database;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\reposi\Controller\Reposi_info_publication;

class reposi_pubgs {
  public function reposi_listgs(){

    $search_publi = db_select('reposi_publication', 'p');
    $search_publi->fields('p')
               ->orderBy('p.p_year', 'DESC')
               ->orderBy('p.p_title', 'ASC');
    $pager=$search_publi->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(20);
    $list_pub = $pager->execute();
    $publications = ' ';
    $markup = ' ';
    $form['body'] = array();
    foreach ($list_pub as $list_p) {
      $pub_type = $list_p->p_type;
      $pub_title = $list_p->p_title;
      $pub_year = $list_p->p_year;
      $pub_unde = $list_p->p_unde;

      if ($pub_type == 'Undefined') {
        $search_p_a = db_select('reposi_publication_author', 'pa');
        $search_p_a->fields('pa', array('ap_author_id', 'ap_unde'))
                   ->condition('pa.ap_unde', $pub_unde, '=');
        $p_a = $search_p_a->execute();
        $list_aut_abc='';

        foreach ($p_a as $art_aut) {
          $search_aut = db_select('reposi_author', 'a');
          $search_aut->fields('a')
                     ->condition('a.aid', $art_aut->ap_author_id, '=');
          $each_aut = $search_aut->execute()->fetchAssoc();
          $f_name = Reposi_info_publication::reposi_string($each_aut['a_first_name']);


          if (!empty($each_aut['a_second_name'])) {
            $s_name =  Reposi_info_publication::reposi_string($each_aut['a_second_name']);
            $list_aut_abc = $list_aut_abc . $each_aut['a_first_lastname'] . ' ' .
                          $each_aut['a_second_lastname'] . ' ' . $f_name[0] . '. ' . $s_name[0] . '.'.'  ';
          } else {
            $list_aut_abc = $list_aut_abc . $each_aut['a_first_lastname'] . ' ' .
                          $each_aut['a_second_lastname'] . ' ' . $f_name[0] . '.'.'  ';

          }
        }
        if (isset($pub_unde)) {
          if ($pub_type == 'Undefined') {
            $publications = ' '.$publications .'<p>'. $list_aut_abc .'<b>' .
                            \Drupal::l($pub_title, Url::fromRoute('reposi.define_typePublicationGS',['node'=>$pub_unde])) .' '.'(' . $pub_year . ') '. '</b>' . '.' . '</p>';
          }
      }
      }
    }
$markup = '';
if (!empty($publications)) {
  $markup .= '<p>'. '<b>'.'<big>'. 'Publications' .'</big>'.'</b>'. '</p>' . $publications;
}
if (empty($publications) && empty($publications_un)) {
  $markup .= '<p>'. 'No records'. '</p>';
}
    $form['body'] = array('#markup' => $markup);
    $form['pager']=['#type' => 'pager'];
    return $form;


  }
}
