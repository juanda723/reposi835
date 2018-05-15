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
    $or_year='DESC';
    $or_title='ASC';
    $search_publi = db_select('reposi_publication', 'p');
    $search_publi->fields('p')
    ->orderBy('p.p_year', $or_year)
    ->orderBy('p.p_title', $or_title);
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
    $mytitle=\Drupal::l('Title ▲',Url::fromRoute('reposi.gspubta'));
    $myyear=\Drupal::l('Year ▼',Url::fromRoute('reposi.gspubya'));
    $markup = '';
    if (!empty($publications)) {
      $markup .= '<p>'. '<b>'.'<big>'. 'Publications' .'</big>'.'</b>'.'<p>'.$myyear .'    '.$mytitle.'</p>' . $publications;
    }
    if (empty($publications) || $publications==' ') {
      $markup .= '<p>'. 'No records'. '</p>';
    }
    $form['body'] = array('#markup' => $markup);
    $form['pager']=['#type' => 'pager'];
    return $form;
  }
  public function reposi_listgs_yearasc(){
    $or_year='ASC';
    $or_title='ASC';
    $search_publi = db_select('reposi_publication', 'p');
    $search_publi->fields('p')
    ->orderBy('p.p_year', $or_year)
    ->orderBy('p.p_title', $or_title);
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
    $mytitle=\Drupal::l('Title ▲',Url::fromRoute('reposi.gspubtd'));
    $myyear=\Drupal::l('Year ▲',Url::fromRoute('reposi.gspub'));
    $markup = '';
    if (!empty($publications)) {
      $markup .= '<p>'. '<b>'.'<big>'. 'Publications' .'</big>'.'</b>'.'<p>'.$myyear .'    '.$mytitle.'</p>' . $publications;
    }
    if (empty($publications) || $publications==' ') {
      $markup .= '<p>'. 'No records'. '</p>';
    }
    $form['body'] = array('#markup' => $markup);
    $form['pager']=['#type' => 'pager'];
    return $form;
  }
  public function reposi_listgs_titleasc(){
    $or_year='ASC';
    $or_title='ASC';
    $search_publi = db_select('reposi_publication', 'p');
    $search_publi->fields('p')
    ->orderBy('p.p_title', $or_title)
    ->orderBy('p.p_year', $or_year);
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
    $mytitle=\Drupal::l('Title ▲',Url::fromRoute('reposi.gspubtd'));
    $myyear=\Drupal::l('Year ▲',Url::fromRoute('reposi.gspub'));
    $markup = '';
    if (!empty($publications)) {
      $markup .= '<p>'. '<b>'.'<big>'. 'Publications' .'</big>'.'</b>'.'<p>'.$mytitle .'    '.$myyear.'</p>' . $publications;
    }
    if (empty($publications) || $publications==' ') {
      $markup .= '<p>'. 'No records'. '</p>';
    }
    $form['body'] = array('#markup' => $markup);
    $form['pager']=['#type' => 'pager'];
    return $form;
  }
  public function reposi_listgs_titledesc(){
    $or_year='ASC';
    $or_title='DESC';
    $search_publi = db_select('reposi_publication', 'p');
    $search_publi->fields('p')
    ->orderBy('p.p_title', $or_title)
    ->orderBy('p.p_year', $or_year);
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
    $mytitle=\Drupal::l('Title ▼',Url::fromRoute('reposi.gspubta'));
    $myyear=\Drupal::l('Year ▲',Url::fromRoute('reposi.gspub'));
    $markup = '';
    if (!empty($publications)) {
      $markup .= '<p>'. '<b>'.'<big>'. 'Publications' .'</big>'.'</b>'.'<p>'.$mytitle .'    '.$myyear.'</p>' . $publications;
    }
    if (empty($publications) || $publications==' ') {
      $markup .= '<p>'. 'No records'. '</p>';
    }
    $form['body'] = array('#markup' => $markup);
    $form['pager']=['#type' => 'pager'];
    return $form;
  }

  public function reposi_list_sourceGs(){
    $or_year='DESC';
    $or_title='ASC';
    $search_publi = db_select('reposi_publication', 'p');
    $search_publi->fields('p')
    ->condition('p.p_source', 'Google Scholar', '=')
    ->orderBy('p.p_check', 'DESC')
    ->orderBy('p.p_title', $or_title)
    ->orderBy('p.p_year', $or_year);
    $pager=$search_publi->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(20);
    $list_pub = $pager->execute();
    $publications = '';
    $publications_un = '';
    $form['body'] = array();
    foreach ($list_pub as $list_p) {
      $pub_type = $list_p->p_type;
      $pub_title = $list_p->p_title;
      $pub_year = $list_p->p_year;
      $pub_source=$list_p->p_source;
      $souce='';
      $tsid = $list_p->p_tsid;
      $abid = $list_p->p_abid;
      if ($list_p->p_check == 1) {
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
            $s_name =  Reposi_info_publication::reposi_string($each_aut['a_second_name']);
            $list_aut_abc = $list_aut_abc . \Drupal::l($each_aut['a_first_lastname'] . ' ' .
            $each_aut['a_second_lastname'] . ' ' . $f_name[0] . '. ' . $s_name[0] . '.',
            Url::fromRoute('reposi.author_aid',['node'=>$art_aut->ap_author_id])) . '.';
          } else {
            $list_aut_abc = $list_aut_abc . \Drupal::l($each_aut['a_first_lastname'] . ' ' .
            $each_aut['a_second_lastname'] . ' ' . $f_name[0] . '.',
            Url::fromRoute('reposi.author_aid',['node'=>$art_aut->ap_author_id])) . '.';

          }
        }
        if (isset($abid)) {
          if ($pub_type == 'Article') {
            $publications = $publications .'<p>'. $list_aut_abc.'(' . $pub_year . ') ' .'<b>'.
            \Drupal::l($pub_title, Url::fromRoute('reposi.Reposi_articleinformation',['node'=>$abid])) . '</b>' .$souce. '.' . '</p>';

          } elseif ($list_p->p_type == 'Book'){
            $publications = $publications .'<p>'. $list_aut_abc.'(' . $pub_year . ') ' .
            '<b>'. \Drupal::l($pub_title, Url::fromRoute('reposi.Reposi_bookinformation',['node'=>$abid])) . '</b>' .$souce. '.' . '</p>';
          } else {
            $publications = $publications . '<p>'. $list_aut_abc.'(' . $pub_year . ') ' .'<b>'.
            \Drupal::l($pub_title, Url::fromRoute('reposi.Reposi_chapinformation',['node'=>$abid])) . '</b>' .$souce. '.' . '</p>';
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
            $f_name =  Reposi_info_publication::reposi_string($each_aut['a_first_name']);
            if (!empty($each_aut['a_second_name'])) {
              $s_name =  Reposi_info_publication::reposi_string($each_aut['a_second_name']);
              $list_aut_ts = $list_aut_ts . \Drupal::l($each_aut['a_first_lastname'] . ' ' .
              $each_aut['a_second_lastname'] . ' ' . $f_name[0] . '. ' . $s_name[0] . '.',
              Url::fromRoute('reposi.author_aid',['node'=>$the_aut->ap_author_id])) . '.';
            } else {
              $list_aut_ts = $list_aut_ts . \Drupal::l($each_aut['a_first_lastname'] . ' ' .
              $each_aut['a_second_lastname'] . ' ' . $f_name[0] . '.',
              Url::fromRoute('reposi.author_aid',['node'=>$the_aut->ap_author_id])) . '.';
            }
          }
          if ($pub_type == 'Thesis') {
            $publications = $publications .'<p>'. $list_aut_ts. '(' . $pub_year . ') ' .'<b>'.
            \Drupal::l($pub_title, Url::fromRoute('reposi.Reposi_thesinformation',['node'=>$tsid])) . '</b>' .$souce. '.' . '</p>';
          } else {
            $publications = $publications .'<p>'. $list_aut_ts. '(' . $pub_year . ') ' .'<b>'.
            \Drupal::l($pub_title, Url::fromRoute('reposi.Reposi_sofinformation',['node'=>$tsid])) . '</b>' .$souce. '.' . '</p>';
          }
        } elseif (isset($cpid)) {
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
            $f_name =  Reposi_info_publication::reposi_string($each_aut['a_first_name']);
            if (!empty($each_aut['a_second_name'])) {
              $s_name =  Reposi_info_publication::reposi_string($each_aut['a_second_name']);
              $list_aut_cp = $list_aut_cp . \Drupal::l($each_aut['a_first_lastname'] . ' ' .
              $each_aut['a_second_lastname'] . ' ' . $f_name[0] . '. ' . $s_name[0] . '.',
              Url::fromRoute('reposi.author_aid',['node'=>$con_aut->ap_author_id])) . '.';
            } else {
              $list_aut_cp = $list_aut_cp . \Drupal::l($each_aut['a_first_lastname'] . ' ' .
              $each_aut['a_second_lastname'] . ' ' . $f_name[0] . '.',
              Url::fromRoute('reposi.author_aid',['node'=>$con_aut->ap_author_id])) . '.';

            }
          }
          if ($pub_type == 'Conference') {
            $publications = $publications . '<p>'.$list_aut_cp . '(' . $pub_year . ') ' .'<b>'.
            \Drupal::l($pub_title, Url::fromRoute('reposi.Reposi_coninformation',['node'=>$cpid])) . '</b>' .$souce. '.' . '</p>';
          } else {
            $publications = $publications . '<p>'.$list_aut_cp . '(' . $pub_year . ') ' .'<b>'.
            \Drupal::l($pub_title, Url::fromRoute('reposi.Reposi_patinformation',['node'=>$cpid])) . '</b>' .$souce. '.' . '</p>';
          }
        }
      } else {
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
          $f_name =  Reposi_info_publication::reposi_string($each_aut['a_first_name']);
          if (!empty($each_aut['a_second_name'])) {
            $s_name =  Reposi_info_publication::reposi_string($each_aut['a_second_name']);
            $list_aut_abc = $list_aut_abc . \Drupal::l($each_aut['a_first_lastname'] . ' ' .
            $each_aut['a_second_lastname'] . ' ' . $f_name[0] . '. ' . $s_name[0] . '.',
            Url::fromRoute('reposi.author_aid',['node'=> $art_aut->ap_author_id])) . '.';
          } else {
            $list_aut_abc = $list_aut_abc . \Drupal::l($each_aut['a_first_lastname'] . ' ' .
            $each_aut['a_second_lastname'] . ' ' . $f_name[0] . '.',
            Url::fromRoute('reposi.author_aid',['node'=> $art_aut->ap_author_id])) . '.';
          }
        }
        if (isset($abid)) {
          if ($pub_type == 'Article') {
            $publications_un = $publications_un .'<p>'. $list_aut_abc.'(' . $pub_year . ') ' .
            '<b>'. \Drupal::l($pub_title, Url::fromRoute('reposi.Reposi_articleinformation',['node'=>$abid])) . '</b>' . '.' . '</p>';
          } elseif ($list_p->p_type == 'Book'){
            $publications_un = $publications_un .'<p>'. $list_aut_abc.'(' . $pub_year . ') ' .
            '<b>'. \Drupal::l($pub_title, Url::fromRoute('reposi.Reposi_bookinformation',['node'=>$abid])) . '</b>' . '.' . '</p>';
          } else {
            $publications_un = $publications_un . '<p>'. $list_aut_abc.'(' . $pub_year . ') ' .'<b>'.
            \Drupal::l($pub_title, Url::fromRoute('reposi.Reposi_chapinformation',['node'=>$abid])) . '</b>' . '.' . '</p>';
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
            $f_name =  Reposi_info_publication::reposi_string($each_aut['a_first_name']);
            if (!empty($each_aut['a_second_name'])) {
              $s_name = Reposi_info_publication::reposi_string($each_aut['a_second_name']);
              $list_aut_ts = $list_aut_ts . \Drupal::l($each_aut['a_first_lastname'] . ' ' .
              $each_aut['a_second_lastname'] . ' ' . $f_name[0] . '. ' . $s_name[0] . '.',
              Url::fromRoute('reposi.author_aid',['node'=>$the_aut->ap_author_id])) . '.';
            } else {
              $list_aut_ts = $list_aut_ts . \Drupal::l($each_aut['a_first_lastname'] . ' ' .
              $each_aut['a_second_lastname'] . ' ' . $f_name[0] . '.',
              Url::fromRoute('reposi.author_aid',['node'=>$the_aut->ap_author_id])) . '.';
            }
          }
          if ($pub_type == 'Thesis') {
            $publications_un = $publications_un .'<p>'. $list_aut_ts. '(' . $pub_year . ') ' .'<b>'.
            \Drupal::l($pub_title, Url::fromRoute('reposi.Reposi_thesinformation',['node'=>$tsid])) . '</b>' . '.' . '</p>';
          } else {
            $publications_un = $publications_un .'<p>'. $list_aut_ts. '(' . $pub_year . ') ' .'<b>'.
            \Drupal::l($pub_title, Url::fromRoute('reposi.Reposi_sofinformation',['node'=>$tsid])) . '</b>' . '.' . '</p>';
          }
        } elseif (isset($cpid)){
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
              Url::fromRoute('reposi.author_aid',['node'=>$con_aut->ap_author_id])) . '.';
            } else {
              $list_aut_cp = $list_aut_cp . \Drupal::l($each_aut['a_first_lastname'] . ' ' .
              $each_aut['a_second_lastname'] . ' ' . $f_name[0] . '.',
              Url::fromRoute('reposi.author_aid',['node'=>$con_aut->ap_author_id])) . '.';
            }
          }
          if ($pub_type == 'Conference') {
            $publications_un = $publications_un . '<p>'.$list_aut_cp . '(' . $pub_year . ') ' .'<b>'.
            \Drupal::l($pub_title, Url::fromRoute('reposi.Reposi_coninformation',['node'=>$cpid])) . '</b>' . '.' . '</p>';
          } else {
            $publications_un = $publications_un . '<p>'.$list_aut_cp . '(' . $pub_year . ') ' .'<b>'.
            \Drupal::l($pub_title, Url::fromRoute('reposi.Reposi_patinformation',['node'=>$cpid])) . '</b>' . '.' . '</p>';
          }
        }
      }
    }
    $markup = '';
    $mytitle=\Drupal::l('Title ▼',Url::fromRoute('reposi.googlelistdesc'));
    $markup .= '<p>'.$mytitle.'   '.'</p>';
    if (!empty($publications)) {
      $markup .= '<p>'. '<b>'.'<big>'. 'Verified Publications' .'</big>'.'</b>'. '</p>' . $publications;
    }
    if (empty($publications) && empty($publications_un)) {
      $markup .= '<p>'. 'No records'. '</p>';
    }
    if (!empty($publications_un)) {
      $markup .= '<p>'. '<b>'.'<big>'. 'Unverified Publications' .'</big>'.'</b>'. '</p>' . $publications_un;
    }
    $form['body'] = array('#markup' => $markup);
    $form['pager']=['#type' => 'pager'];
    return $form;
  }

  public function reposi_list_sourceGsdes(){
    $or_year='DESC';
    $or_title='DESC';
    $search_publi = db_select('reposi_publication', 'p');
    $search_publi->fields('p')
    ->condition('p.p_source', 'Google Scholar', '=')
    ->orderBy('p.p_check', 'DESC')
    ->orderBy('p.p_title', $or_title)
    ->orderBy('p.p_year', $or_year);
    $pager=$search_publi->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(20);
    $list_pub = $pager->execute();
    $publications = '';
    $publications_un = '';
    $form['body'] = array();
    foreach ($list_pub as $list_p) {
      $pub_type = $list_p->p_type;
      $pub_title = $list_p->p_title;
      $pub_year = $list_p->p_year;
      $pub_source=$list_p->p_source;
      $souce='';
      $tsid = $list_p->p_tsid;
      $abid = $list_p->p_abid;
      if ($list_p->p_check == 1) {
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
            $s_name =  Reposi_info_publication::reposi_string($each_aut['a_second_name']);
            $list_aut_abc = $list_aut_abc . \Drupal::l($each_aut['a_first_lastname'] . ' ' .
            $each_aut['a_second_lastname'] . ' ' . $f_name[0] . '. ' . $s_name[0] . '.',
            Url::fromRoute('reposi.author_aid',['node'=>$art_aut->ap_author_id])) . '.';
          } else {
            $list_aut_abc = $list_aut_abc . \Drupal::l($each_aut['a_first_lastname'] . ' ' .
            $each_aut['a_second_lastname'] . ' ' . $f_name[0] . '.',
            Url::fromRoute('reposi.author_aid',['node'=>$art_aut->ap_author_id])) . '.';

          }
        }
        if (isset($abid)) {
          if ($pub_type == 'Article') {
            $publications = $publications .'<p>'. $list_aut_abc.'(' . $pub_year . ') ' .'<b>'.
            \Drupal::l($pub_title, Url::fromRoute('reposi.Reposi_articleinformation',['node'=>$abid])) . '</b>' .$souce. '.' . '</p>';

          } elseif ($list_p->p_type == 'Book'){
            $publications = $publications .'<p>'. $list_aut_abc.'(' . $pub_year . ') ' .
            '<b>'. \Drupal::l($pub_title, Url::fromRoute('reposi.Reposi_bookinformation',['node'=>$abid])) . '</b>' .$souce. '.' . '</p>';
          } else {
            $publications = $publications . '<p>'. $list_aut_abc.'(' . $pub_year . ') ' .'<b>'.
            \Drupal::l($pub_title, Url::fromRoute('reposi.Reposi_chapinformation',['node'=>$abid])) . '</b>' .$souce. '.' . '</p>';
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
            $f_name =  Reposi_info_publication::reposi_string($each_aut['a_first_name']);
            if (!empty($each_aut['a_second_name'])) {
              $s_name =  Reposi_info_publication::reposi_string($each_aut['a_second_name']);
              $list_aut_ts = $list_aut_ts . \Drupal::l($each_aut['a_first_lastname'] . ' ' .
              $each_aut['a_second_lastname'] . ' ' . $f_name[0] . '. ' . $s_name[0] . '.',
              Url::fromRoute('reposi.author_aid',['node'=>$the_aut->ap_author_id])) . '.';
            } else {
              $list_aut_ts = $list_aut_ts . \Drupal::l($each_aut['a_first_lastname'] . ' ' .
              $each_aut['a_second_lastname'] . ' ' . $f_name[0] . '.',
              Url::fromRoute('reposi.author_aid',['node'=>$the_aut->ap_author_id])) . '.';
            }
          }
          if ($pub_type == 'Thesis') {
            $publications = $publications .'<p>'. $list_aut_ts. '(' . $pub_year . ') ' .'<b>'.
            \Drupal::l($pub_title, Url::fromRoute('reposi.Reposi_thesinformation',['node'=>$tsid])) . '</b>' .$souce. '.' . '</p>';
          } else {
            $publications = $publications .'<p>'. $list_aut_ts. '(' . $pub_year . ') ' .'<b>'.
            \Drupal::l($pub_title, Url::fromRoute('reposi.Reposi_sofinformation',['node'=>$tsid])) . '</b>' .$souce. '.' . '</p>';
          }
        } elseif (isset($cpid)) {
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
            $f_name =  Reposi_info_publication::reposi_string($each_aut['a_first_name']);
            if (!empty($each_aut['a_second_name'])) {
              $s_name =  Reposi_info_publication::reposi_string($each_aut['a_second_name']);
              $list_aut_cp = $list_aut_cp . \Drupal::l($each_aut['a_first_lastname'] . ' ' .
              $each_aut['a_second_lastname'] . ' ' . $f_name[0] . '. ' . $s_name[0] . '.',
              Url::fromRoute('reposi.author_aid',['node'=>$con_aut->ap_author_id])) . '.';
            } else {
              $list_aut_cp = $list_aut_cp . \Drupal::l($each_aut['a_first_lastname'] . ' ' .
              $each_aut['a_second_lastname'] . ' ' . $f_name[0] . '.',
              Url::fromRoute('reposi.author_aid',['node'=>$con_aut->ap_author_id])) . '.';

            }
          }
          if ($pub_type == 'Conference') {
            $publications = $publications . '<p>'.$list_aut_cp . '(' . $pub_year . ') ' .'<b>'.
            \Drupal::l($pub_title, Url::fromRoute('reposi.Reposi_coninformation',['node'=>$cpid])) . '</b>' .$souce. '.' . '</p>';
          } else {
            $publications = $publications . '<p>'.$list_aut_cp . '(' . $pub_year . ') ' .'<b>'.
            \Drupal::l($pub_title, Url::fromRoute('reposi.Reposi_patinformation',['node'=>$cpid])) . '</b>' .$souce. '.' . '</p>';
          }
        }
      } else {
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
          $f_name =  Reposi_info_publication::reposi_string($each_aut['a_first_name']);
          if (!empty($each_aut['a_second_name'])) {
            $s_name =  Reposi_info_publication::reposi_string($each_aut['a_second_name']);
            $list_aut_abc = $list_aut_abc . \Drupal::l($each_aut['a_first_lastname'] . ' ' .
            $each_aut['a_second_lastname'] . ' ' . $f_name[0] . '. ' . $s_name[0] . '.',
            Url::fromRoute('reposi.author_aid',['node'=> $art_aut->ap_author_id])) . '.';
          } else {
            $list_aut_abc = $list_aut_abc . \Drupal::l($each_aut['a_first_lastname'] . ' ' .
            $each_aut['a_second_lastname'] . ' ' . $f_name[0] . '.',
            Url::fromRoute('reposi.author_aid',['node'=> $art_aut->ap_author_id])) . '.';
          }
        }
        if (isset($abid)) {
          if ($pub_type == 'Article') {
            $publications_un = $publications_un .'<p>'. $list_aut_abc.'(' . $pub_year . ') ' .
            '<b>'. \Drupal::l($pub_title, Url::fromRoute('reposi.Reposi_articleinformation',['node'=>$abid])) . '</b>' . '.' . '</p>';
          } elseif ($list_p->p_type == 'Book'){
            $publications_un = $publications_un .'<p>'. $list_aut_abc.'(' . $pub_year . ') ' .
            '<b>'. \Drupal::l($pub_title, Url::fromRoute('reposi.Reposi_bookinformation',['node'=>$abid])) . '</b>' . '.' . '</p>';
          } else {
            $publications_un = $publications_un . '<p>'. $list_aut_abc.'(' . $pub_year . ') ' .'<b>'.
            \Drupal::l($pub_title, Url::fromRoute('reposi.Reposi_chapinformation',['node'=>$abid])) . '</b>' . '.' . '</p>';
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
            $f_name =  Reposi_info_publication::reposi_string($each_aut['a_first_name']);
            if (!empty($each_aut['a_second_name'])) {
              $s_name = Reposi_info_publication::reposi_string($each_aut['a_second_name']);
              $list_aut_ts = $list_aut_ts . \Drupal::l($each_aut['a_first_lastname'] . ' ' .
              $each_aut['a_second_lastname'] . ' ' . $f_name[0] . '. ' . $s_name[0] . '.',
              Url::fromRoute('reposi.author_aid',['node'=>$the_aut->ap_author_id])) . '.';
            } else {
              $list_aut_ts = $list_aut_ts . \Drupal::l($each_aut['a_first_lastname'] . ' ' .
              $each_aut['a_second_lastname'] . ' ' . $f_name[0] . '.',
              Url::fromRoute('reposi.author_aid',['node'=>$the_aut->ap_author_id])) . '.';
            }
          }
          if ($pub_type == 'Thesis') {
            $publications_un = $publications_un .'<p>'. $list_aut_ts. '(' . $pub_year . ') ' .'<b>'.
            \Drupal::l($pub_title, Url::fromRoute('reposi.Reposi_thesinformation',['node'=>$tsid])) . '</b>' . '.' . '</p>';
          } else {
            $publications_un = $publications_un .'<p>'. $list_aut_ts. '(' . $pub_year . ') ' .'<b>'.
            \Drupal::l($pub_title, Url::fromRoute('reposi.Reposi_sofinformation',['node'=>$tsid])) . '</b>' . '.' . '</p>';
          }
        } elseif (isset($cpid)){
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
              Url::fromRoute('reposi.author_aid',['node'=>$con_aut->ap_author_id])) . '.';
            } else {
              $list_aut_cp = $list_aut_cp . \Drupal::l($each_aut['a_first_lastname'] . ' ' .
              $each_aut['a_second_lastname'] . ' ' . $f_name[0] . '.',
              Url::fromRoute('reposi.author_aid',['node'=>$con_aut->ap_author_id])) . '.';
            }
          }
          if ($pub_type == 'Conference') {
            $publications_un = $publications_un . '<p>'.$list_aut_cp . '(' . $pub_year . ') ' .'<b>'.
            \Drupal::l($pub_title, Url::fromRoute('reposi.Reposi_coninformation',['node'=>$cpid])) . '</b>' . '.' . '</p>';
          } else {
            $publications_un = $publications_un . '<p>'.$list_aut_cp . '(' . $pub_year . ') ' .'<b>'.
            \Drupal::l($pub_title, Url::fromRoute('reposi.Reposi_patinformation',['node'=>$cpid])) . '</b>' . '.' . '</p>';
          }
        }
      }
    }
    $markup = '';
    $mytitle=\Drupal::l('Title ▲',Url::fromRoute('reposi.googlelistasc'));
    $markup .= '<p>'.$mytitle.'   '.'</p>';
    if (!empty($publications)) {
      $markup .= '<p>'. '<b>'.'<big>'. 'Verified Publications' .'</big>'.'</b>'. '</p>' . $publications;
    }
    if (empty($publications) && empty($publications_un)) {
      $markup .= '<p>'. 'No records'. '</p>';
    }
    if (!empty($publications_un)) {
      $markup .= '<p>'. '<b>'.'<big>'. 'Unverified Publications' .'</big>'.'</b>'. '</p>' . $publications_un;
    }
    $form['body'] = array('#markup' => $markup);
    $form['pager']=['#type' => 'pager'];
    return $form;
  }

}
