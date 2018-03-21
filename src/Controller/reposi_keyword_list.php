<?php
/**
 * @file
 * Contains \Drupal\hello_world\Controller\HelloController.
 */

namespace Drupal\reposi\Controller;
use Drupal\Core\Database;
use Drupal\Core\Url;
use Drupal\Core\Link;

class reposi_keyword_list{

function reposi_list_key() {

  global $base_url;
  $search_keyw = db_select('reposi_keyword', 'k');
  $search_keyw->fields('k')
              ->orderBy('k_word', 'ASC');
  $pager=$search_keyw->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(25);
  $keywords = $pager->execute();
  $flag_key=0;
  $form['body'] = array();
  foreach ($keywords as $keyw) {
  	$flag_key++;
  	if ($flag_key == 1) {
      $keyws = '<li>'. \Drupal::l($keyw->k_word,Url::fromRoute('reposi.Reposi_info_keyword',['node'=> $keyw->kid])) . '</li>';
    } else {
      $keyws = $keyws . '<li>'.
                      \Drupal::l($keyw->k_word, Url::fromRoute('reposi.Reposi_info_keyword',['node'=> $keyw->kid])) . '</li>';
    }
  }
  if (empty($keyws)) {
    $display_keyw =  'Without keywords';
  } else {
    $display_keyw = $keyws;
  }
  $markup = '<div>' . '</div>' . '<ul>' . $display_keyw . '</ul>';
  $form['body'] = array('#markup' => $markup);
  $form['pager']=['#type' => 'pager'];
  return $form;

}
}
