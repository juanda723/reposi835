<?php
/**
* @file
* Contains \Drupal\hello_world\Controller\HelloController.
*/
namespace Drupal\reposi\Controller;

use Drupal\Core\Database;
use Drupal\Core\Url;
use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Html;


class ReposiController {
  public function PubliListReposiSearch() {

    global $base_url;
    $words = \Drupal::routeMatch()->getParameter('node');

    $inventory=' ';

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
          $new_search_w = reposi_string($new_search_word);
          $new_each_w = reposi_string($new_each_word);
          if ($new_search_w == $new_each_w) {
            $pids[] = $characters->pid;
          }
        }
      }
    }
    $new_pids = array_unique($pids);
    $publications = '';
    foreach ($new_pids as $ids) {
      $search_publi = db_select('reposi_publication', 'p')->extend('PagerDefault');
      $search_publi->fields('p')
      ->condition('p.pid', $ids, '=')
      ->orderBy('p.p_year', 'DESC')
      ->limit(10);
      $list_pub = $search_publi->execute();
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
            $f_name = reposi_string($each_aut['a_first_name']);
            if (!empty($each_aut['a_second_name'])) {
              $s_name = reposi_string($each_aut['a_second_name']);
              $list_aut_abc = $list_aut_abc . l($each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] .
              ' ' . $f_name[0] . '. ' . $s_name[0] . '.',
              $base_url . '/reposi/author/' . $art_aut->ap_author_id) . ', ';
            } else {
              $list_aut_abc = $list_aut_abc . l($each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] .
              ' ' . $f_name[0] . '.', $base_url . '/reposi/author/' . $art_aut->ap_author_id) . ', ';
            }
          }
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
            l($pub_title, $base_url . '/reposi/chap_book/' . $abid . '/free') . '</b>' .
            '.' . '<br>' . '<small>' . t('Export formats: ') .
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
              $list_aut_cp = $list_aut_cp . l($each_aut['a_first_lastname'] . ' ' .
              $each_aut['a_second_lastname'] . ' ' . $f_name[0] . '. ' . $s_name[0] . '.',
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
            l($pub_title, $base_url . '/reposi/patent/' . $cpid . '/free') . '</b>' . '.' . '<br>' .
            '<small>' . t('Export formats: ') .
            l(t('RIS'), $base_url . '/reposi/ris/' . $list_p->pid) . '</small>' . '</p>';
          }
        }
      }
    }
    if (empty($publications)) {
      $publications .= '<p>'. 'No matches'. '</p>';
    }



    return array(
      '#type' => 'markup',
      '#markup' => t($publications)
    );

    /* $pids = array();
    if ($somethig_cpl <> 0) {
    foreach ($search_cpl as $ids_cpl) {
    $pids[] = $ids_cpl->pid;
  }
}
*/

}

/*public function ListAuthor() {
return array(
'#type' => 'markup',
'#markup' => t('Hello, World!'),

$query = db_select('reposi_user', 'p');
$query->fields('p', array('uid', 'u_first_name', 'u_first_lastname',
'u_second_lastname', 'u_email'))
->orderBy('u_first_name', 'ASC');//->limit(60)
$pager = $query->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(20);
$results =$pager->execute()->fetchAll();


);
}*/
//La siguiente muestra la lista de autores almacenados en el repositorio y los redirecciona para mostrar su información.
function ListAuthor() {
  global $base_url;
  $search_aut = db_select('reposi_author', 'a');
  $search_aut->fields('a')
  ->orderBy('a_first_lastname', 'ASC');
  $pager = $search_aut->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(30);
  $authors = $pager->execute();
  $flag_aut=0;
  foreach ($authors as $keyw) {
    $flag_aut++;
    if ($flag_aut == 1) {
      $url = Url::fromRoute('reposi.author_aid', ['node' => $keyw->aid]);
      $internal_link = \Drupal::l(t($keyw->a_first_lastname . ' ' . $keyw->a_second_lastname .
      ' ' . $keyw->a_first_name . ' ' . $keyw->a_second_name.' '), $url);
      $author = '<li>'.$internal_link.'</li>';
      /*$author = '<li>'. l($keyw->a_first_lastname . ' ' . $keyw->a_second_lastname .
      ' ' . $keyw->a_first_name . ' ' . $keyw->a_second_name . ' ',
      $base_url . '/reposi/author/' . $keyw->aid) . '</li>';*/
    } else {
      $url = Url::fromRoute('reposi.author_aid', ['node' => $keyw->aid]);
      $internal_link = \Drupal::l(t($keyw->a_first_lastname . ' ' . $keyw->a_second_lastname .
      ' ' . $keyw->a_first_name . ' ' . $keyw->a_second_name.' '), $url);
      $author = $author.'<li>'.$internal_link.'</li>';
      /*$author = $author . '<li>'.
      l($keyw->a_first_lastname . ' ' . $keyw->a_second_lastname . ' ' .
      $keyw->a_first_name . ' ' . $keyw->a_second_name . ' ',
      $base_url . '/reposi/author/' . $keyw->aid) . '</li>';*/
    }
  }
  if (empty($author)) {
    $display_aut =  'Without authors';
  } else {
    $display_aut = $author;
  }
  $markup = '';
  $myAuthor=\Drupal::l('Author ▼',Url::fromRoute('reposi.authordesc'));
  $markup .= '<p>'.$myAuthor.'</p>';
  $markup .= '<div>' . '</div>' . '<ul>' . $display_aut . '</ul>';
  $form['body'] = array('#markup' => $markup);
  $form['pager'] = ['#type' => 'pager'];
  return $form;
}

function ListAuthordesc() {
  global $base_url;
  $search_aut = db_select('reposi_author', 'a');
  $search_aut->fields('a')
  ->orderBy('a_first_lastname', 'DESC');
  $pager = $search_aut->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(30);
  $authors = $pager->execute();
  $flag_aut=0;
  foreach ($authors as $keyw) {
    $flag_aut++;
    if ($flag_aut == 1) {
      $url = Url::fromRoute('reposi.author_aid', ['node' => $keyw->aid]);
      $internal_link = \Drupal::l(t($keyw->a_first_lastname . ' ' . $keyw->a_second_lastname .
      ' ' . $keyw->a_first_name . ' ' . $keyw->a_second_name.' '), $url);
      $author = '<li>'.$internal_link.'</li>';
      /*$author = '<li>'. l($keyw->a_first_lastname . ' ' . $keyw->a_second_lastname .
      ' ' . $keyw->a_first_name . ' ' . $keyw->a_second_name . ' ',
      $base_url . '/reposi/author/' . $keyw->aid) . '</li>';*/
    } else {
      $url = Url::fromRoute('reposi.author_aid', ['node' => $keyw->aid]);
      $internal_link = \Drupal::l(t($keyw->a_first_lastname . ' ' . $keyw->a_second_lastname .
      ' ' . $keyw->a_first_name . ' ' . $keyw->a_second_name.' '), $url);
      $author = $author.'<li>'.$internal_link.'</li>';
      /*$author = $author . '<li>'.
      l($keyw->a_first_lastname . ' ' . $keyw->a_second_lastname . ' ' .
      $keyw->a_first_name . ' ' . $keyw->a_second_name . ' ',
      $base_url . '/reposi/author/' . $keyw->aid) . '</li>';*/
    }
  }
  if (empty($author)) {
    $display_aut =  'Without authors';
  } else {
    $display_aut = $author;
  }
  $markup = '';
  $myAuthor=\Drupal::l('Author ▲',Url::fromRoute('reposi.author'));
  $markup .= '<p>'.$myAuthor.'</p>';
  $markup .= '<div>' . '</div>' . '<ul>' . $display_aut . '</ul>';
  $form['body'] = array('#markup' => $markup);
  $form['pager'] = ['#type' => 'pager'];
  return $form;
}

//La siguiente función muestra los metadatos de los autores almacenados en el repositorio.
function reposi_info_author() {
  global $base_url;
  $id=\Drupal::routeMatch()->getParameter('node');
  $titles_publi = '';
  $serch_u = db_select('reposi_author', 'a');
  $serch_u->fields('a')
  ->condition('a.aid', $id, '=');
  $serch_user = $serch_u->execute()->fetchField();
  $info_user = $serch_u->execute()->fetchAssoc();
  $search_p_a = db_select('reposi_publication_author', 'pa');
  $search_p_a->fields('pa')
  ->condition('pa.ap_author_id', $id, '=');
  $p_a = $search_p_a->execute();
  $empty_p_a = $search_p_a->execute()->fetchField();
  $flag_title=0;
  if (!empty($empty_p_a)) {
    foreach ($p_a as $publi) {
      $abc_id = $publi->ap_abid;
      if (isset($abc_id)) {
        $search_publi_abc = db_select('reposi_publication', 'p');
        $search_publi_abc->fields('p')
        ->condition('p.p_check', 1, '=')
        ->condition('p.p_abid', $abc_id, '=')
        ->orderBy('p.p_year', 'DESC')
        ->orderBy('p.p_title', 'ASC');
        $title_ab = $search_publi_abc->execute()->fetchAssoc();
        if ($title_ab['p_type'] == 'Book Chapter') {
          $flag_title++;
          $url = Url::fromRoute('reposi.Reposi_info_publicationCBF',['node'=>$abc_id]);
          $link  = \Drupal::l(t($title_ab['p_title']), $url);
          $titles_publi = $titles_publi . '<li>'.'<i>'.t('Title: ').'</i>'.
          $link. '</li>';
          /*$titles_publi = $titles_publi . '<li>'.'<i>'.t('Title: ').'</i>'.
          l($title_ab['p_title'], $base_url . '/reposi/chap_book/' . $abc_id . '/free') . '</li>';*/
        } elseif ($title_ab['p_type'] == 'Book') {
          $flag_title++;
          $url = Url::fromRoute('reposi.Reposi_info_publicationBF',['node'=>$abc_id]);
          $link  = \Drupal::l(t($title_ab['p_title']), $url);
          $titles_publi = $titles_publi . '<li>'.'<i>'.t('Title: ').'</i>'.
          $link . '</li>';
          /*$titles_publi = $titles_publi . '<li>'.'<i>'.t('Title: ').'</i>'.
          l($title_ab['p_title'], $base_url . '/reposi/book/' . $abc_id . '/free') . '</li>';*/
        } elseif ($title_ab['p_type'] == 'Article') {
          $flag_title++;
          $url = Url::fromRoute('reposi.Reposi_info_publicationAF',['node'=>$abc_id]);
          $link  = \Drupal::l(t($title_ab['p_title']), $url);
          $titles_publi = $titles_publi . '<li>'.'<i>'.t('Title: ').'</i>'.
          $link . '</li>';
          /*$titles_publi = $titles_publi . '<li>'.'<i>'.t('Title: ').'</i>'.
          l($title_ab['p_title'], $base_url . '/reposi/article/' . $abc_id . '/free') . '</li>';*/
        }
      }
      $ts_id = $publi->ap_tsid;
      if (isset($ts_id)) {
        $search_publi_ts = db_select('reposi_publication', 'p');
        $search_publi_ts->fields('p')
        ->condition('p.p_check', 1, '=')
        ->condition('p.p_tsid', $ts_id, '=')
        ->orderBy('p.p_year', 'DESC')
        ->orderBy('p.p_title', 'ASC');
        $title_ts = $search_publi_ts->execute()->fetchAssoc();
        if ($title_ts['p_type'] == 'Thesis') {
          $flag_title++;
          $url = Url::fromRoute('reposi.Reposi_info_publicationTF',['node'=>$ts_id]);
          $link  = \Drupal::l(t($title_ts['p_title']), $url);
          $titles_publi = $titles_publi . '<li>'.'<i>'.t('Title: ').'</i>'.
          $link . '</li>';
          /*$titles_publi = $titles_publi . '<li>'.'<i>'.t('Title: ').'</i>'.
          l($title_ts['p_title'], $base_url . '/reposi/thesis/' . $ts_id . '/free') . '</li>';*/
        } elseif ($title_ts['p_type'] == 'Software') {
          $flag_title++;
          $url = Url::fromRoute('reposi.Reposi_info_publicationSF',['node'=>$ts_id]);
          $link  = \Drupal::l(t($title_ts['p_title']), $url);
          $titles_publi = $titles_publi . '<li>'.'<i>'.t('Title: ').'</i>'.
          $link . '</li>';
          /*$titles_publi = $titles_publi . '<li>'.'<i>'.t('Title: ').'</i>'.
          l($title_ts['p_title'], $base_url . '/reposi/software/' . $ts_id . '/free') . '</li>';*/
        }
      }
      $cp_id = $publi->ap_cpid;
      if (isset($cp_id)) {
        $search_publi_cp = db_select('reposi_publication', 'p');
        $search_publi_cp->fields('p')
        ->condition('p.p_check', 1, '=')
        ->condition('p.p_cpid', $cp_id, '=')
        ->orderBy('p.p_year', 'DESC')
        ->orderBy('p.p_title', 'ASC');
        $title_cp = $search_publi_cp->execute()->fetchAssoc();
        if ($title_cp['p_type'] == 'Conference') {
          $flag_title++;
          $url = Url::fromRoute('reposi.Reposi_info_publicationCF',['node'=>$cp_id]);
          $link  = \Drupal::l(t($title_cp['p_title']), $url);
          $titles_publi = $titles_publi . '<li>'.'<i>'.t('Title: ').'</i>'.
          $link . '</li>';
          /*$titles_publi = $titles_publi . '<li>'.'<i>'.t('Title: ').'</i>'.
          l($title_cp['p_title'], $base_url . '/reposi/conference/' . $cp_id . '/free') . '</li>';*/
        } elseif ($title_cp['p_type'] == 'Patent') {
          $flag_title++;
          $url = Url::fromRoute('reposi.Reposi_info_publicationPF',['node'=>$cp_id]);
          $link  = \Drupal::l(t($title_cp['p_title']), $url);
          $titles_publi = $titles_publi . '<li>'.'<i>'.t('Title: ').'</i>'.
          $link . '</li>';
          /*$titles_publi = $titles_publi . '<li>'.'<i>'.t('Title: ').'</i>'.
          l($title_cp['p_title'], $base_url . '/reposi/patent/' . $cp_id . '/free') . '</li>';*/
        }
      }
    }
  } else {
    $titles_publi = '<p>' . 'Without associated publications.' . '</p>';
  }

  $form['uid'] = array(
    '#type' => 'value',
    '#value' => $id,
  );
  $markup = '<p>' . '<b>' . '<big>' . $info_user['a_first_name'] . ' ' .
  $info_user['a_first_lastname'] .'</big>' . '</b>' . '</p>' . '<ul>' .
  '<li>' . '<i>' . t('ID: ').'</i>'. $info_user['aid'] .'</li>' .
  '<li>' . '<i>' . t('Name(s): ') . '</i>' . $info_user['a_first_name'] .
  ' ' . $info_user['a_second_name'] .'</li>' .
  '<li>' . '<i>' . t('Last name: ') . '</i>' . $info_user['a_first_lastname'] .
  ' ' . $info_user['a_second_lastname'] . '</li>' . '</ul>' .
  '<div>'. t('Associated publication(s): (') . $flag_title . ')' . '</div>' .
  '<ul type = square>' . $titles_publi . '</ul>';
  $form['body'] = array('#markup' => $markup);
  return $form;
}


///___________________________________________________________________________________________________

function reposi_user_list(){
  $header = array('ID', t('Name'), t('Last name'), t('Email'),
  t('State'));
  $query = db_select('reposi_user', 'p');
  $query->fields('p', array('uid', 'u_first_name', 'u_first_lastname',
  'u_second_lastname', 'u_email'))
  ->orderBy('u_first_name', 'ASC');//->limit(60)
  $pager = $query->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(20);
  $results =$pager->execute()->fetchAll();
  $rows = array();
  foreach ($results as $row) {
    $search_stat = db_select('reposi_state', 's');
    $search_stat->fields('s', array('s_type'))
    ->condition('s.s_uid', $row->uid, '=');
    $state = $search_stat->execute()->fetchField();
    $url = Url::fromRoute('reposi.admuser_info', ['node' => $row->uid]);
    $link  = \Drupal::l(t($row->uid), $url);
    $rows[] = array(\Drupal::l(t($row->uid), $url),
    $row->u_first_name,
    $row->u_first_lastname  . ' ' . $row->u_second_lastname,
    $row->u_email,
    $state,
  );
  /*$rows[] = array(l($row->uid, $base_url . '/reposi/adm_user/' . $row->uid),
  $row->u_first_name,
  $row->u_first_lastname  . ' ' . $row->u_second_lastname,
  $row->u_email,
  $state,
);*/
}
$build['table'] = array(
  '#type'   => 'table',
  '#header' => $header,
  '#rows'   => $rows,
  '#empty'  => t('No records.'),
);

$build['pager'] = array(
  '#type' => 'pager'
);

return $build;
}
//_____________________________________________________________________________________________________________________________________________________________________

function reposi_user_act_list(){
  global $base_url;
  $header = array('ID', t('Name'), t('Last name'), t('Email'));
  $search_act_state = db_select('reposi_state', 's');
  $search_act_state->fields('s', array('s_uid'))
  ->condition('s.s_type', 'Active', '=');
  $id_act_state = $search_act_state->execute();
  foreach ($id_act_state as $list_act) {
    $query = db_select('reposi_user', 'p')->extend('Drupal\Core\Database\Query\PagerSelectExtender');
    $query->fields('p', array('uid', 'u_first_name', 'u_first_lastname',
    'u_second_lastname', 'u_email'))
    ->condition('p.uid', $list_act->s_uid, '=')
    ->orderBy('u_first_name', 'ASC')
    ->limit(10);
    $results[] = $query->execute()->fetchAssoc();
  }
  $rows = array();
  foreach ($results as $row) {
    if (!empty($row)) {
      //  $link  = \Drupal::l(t($title_cp['p_title']), $url);               $url = Url::fromRoute('reposi.author_aid', ['node' => $keyw->aid]);
      $url = Url::fromRoute('reposi.admuser_info', ['node' => $row['uid']]);
      //  $link  = \Drupal::l(t($list_act->s_uid), $url);
      $rows[] = array(\Drupal::l(t($row['uid']), $url),
      $row['u_first_name'],
      $row['u_first_lastname'] . ' ' . $row['u_second_lastname'],
      $row['u_email'],
    );
    /*$rows[] = array(\Drupal::l(t('hola: '.$row->uid), $url),
    $row->u_first_name,
    $row->u_first_lastname . ' ' . $row->u_second_lastname,
    $row->u_email,
  ); */

  /*$rows[] = array(l($row['uid'], $base_url . '/reposi/adm_user/' . $row['uid']),
  $row['u_first_name'],
  $row['u_first_lastname'] . ' ' . $row['u_second_lastname'],
  $row['u_email'],
);*/
}
}



$form['table'] = array ('#type'     => 'table',
'#header'   => $header,
'#rows'  => $rows,
'#multiple' => TRUE,
'#empty'    => t('No records.')
);
/* $form['table'] = array ('#type'     => 'tableselect',
'#header'   => $header,
'#options'  => $rows,
'#multiple' => TRUE,
'#empty'    => t('No records.')
);
/*$form['pager'] = array(
'#theme' => 'pager',
);*/
//$form = [];

$form['pager'] = ['#type' => 'pager'];
$form['render'] = drupal_render($form);
//   return $markup;

$form['deactivate'] = array(
  '#type' => 'submit',
  '#value' => t('Deactivate select items'),
  '#submit' => array('reposi_user_list_deactivate_items'),
);
return $form;
}

//--------------------------------------------------------------------------------------------------------------
function reposi_list_keyword() {
  $search_keyw = db_select('reposi_keyword', 'k');
  $search_keyw->fields('k')
  ->orderBy('k_word', 'ASC');
  $pager = $search_keyw->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(25);
  $keywords = $pager->execute();
  $flag_key=0;
  foreach ($keywords as $keyw) {
    $flag_key++;
    if ($flag_key == 1) {
      $keyws = '<li>'. t($keyw->k_word) . '</li>';
    } else {
      $keyws = $keyws . '<li>'.
      t($keyw->k_word) . '</li>';
    }
  }
  if (empty($keyws)) {
    $display_keyw =  'Without keywords';
  } else {
    $display_keyw = $keyws;
  }
  $markup = '<div>' . '</div>' . '<ul>' . $display_keyw . '</ul>';
  $form['body'] = array('#markup' => $markup);
  $form['pager'] = ['#type' => 'pager'];
  return $form;
}
//-----------------------------------------------------------------------------------------------------------------
/*protected $reposi_client;
public function __construct(
Client $reposi_client
) {
parent::__construct();
$this->reposi_client = $reposi_client;
}

public static function create(ContainerInterface $container) {
return new static(
$container->get('reposi.client')
);
}

*/
function query_google_scholar() {

  //$this->reposi_client;
  $client = \Drupal::httpClient();
  $request = $client->get('https://scholar.google.com');
  // $response = $request->getBody();
  //drupal_set_message(t('The publication was updated.'.print_r($response,true)));
  ///////
  try {
    $response = $client->get('http://cse.bth.se/~fer/googlescholar.php?user=Z9vU8awAAAAJ');
    $data = $response->getBody();
    $hola=Json::decode($data);
    $gg=Html::decodeEntities($data);
    $num_docs = explode('[', $gg);
    $llave = explode('},', $data);
    echo print_r($num_docs,true);
    $decoded = Json::decode($data);
    $form['body'] = array('#markup' => $data);
    $request = $client->post('http://cse.bth.se/~fer/googlescholar.php?user=Z9vU8awAAAAJ', [
      'json' => [
        'publications'=> 'title'
      ]
    ]);
    $response = Json::decode($request->getBody());
    drupal_set_message(t('Scholar está mostrando lo siguiente: ').$llave);
  }
  catch (RequestException $e) {
    watchdog_exception('reposi', $e->getMessage());
  }
  return $form;
}

//--------------------------------------------------------------------------------------------------------------

// CIERRA LA CLASE
}
