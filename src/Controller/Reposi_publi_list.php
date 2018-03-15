<?php
/**
 * @file
 * Contains \Drupal\hello_world\Controller\HelloController.
 */

namespace Drupal\reposi\Controller;
use Drupal\Core\Database;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\reposi\Controller\Reposi_info_publication;

class Reposi_publi_list {

function reposi_list_publication_free(){
    global $base_url;
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



  function reposi_publi_list_complete(){
    global $base_url;
    $search_art = db_select('reposi_article_book', 'ab');
    $search_art->fields('ab')
            ->condition('ab.ab_type', 'Article', '=')
            ->orderBy('ab.ab_title', 'ASC');
    $art = $search_art->execute();
    $art -> allowRowCount = TRUE;
    $somethig_art = $art->rowCount();
    $articles = array();
    if ($somethig_art == 0) {
      $articles[] = 'No records';
    } else {
      foreach ($art as $article) {
        $search_date = db_select('reposi_date', 'd');
        $search_date->fields('d', array('d_year'))
                   ->condition('d.d_abid', $article->abid, '=');
        $art_year = $search_date->execute()->fetchField();
        $search_state = db_select('reposi_publication', 'p');
        $search_state->fields('p', array('p_check'))
                      ->condition('p.p_abid', $article->abid, '=');
        $val_unval = $search_state->execute()->fetchField();
        if ($val_unval == 1) {
          $val_unval = '(Validate)';
        } else {
          $val_unval = '(Unvalidate)';
        }
        $search_p_a = db_select('reposi_publication_author', 'pa');
        $search_p_a->fields('pa', array('ap_author_id', 'ap_abid'))
                   ->condition('pa.ap_abid', $article->abid, '=');
        $p_a = $search_p_a->execute();
        $list_aut_art='';
        foreach ($p_a as $art_aut) {
          $search_aut = db_select('reposi_author', 'a');
          $search_aut->fields('a')
                     ->condition('a.aid', $art_aut->ap_author_id, '=');
          $each_aut = $search_aut->execute()->fetchAssoc();
          $f_name = Reposi_info_publication::reposi_string($each_aut['a_first_name']);
          if (!empty($each_aut['a_second_name'])) {
            $s_name = Reposi_info_publication::reposi_string($each_aut['a_second_name']);
            $list_aut_art = $list_aut_art . \Drupal::l($each_aut['a_first_lastname'] . ' ' .
                          $each_aut['a_second_lastname'] . ' ' . $f_name[0] . '. ' . $s_name[0] . '.',
                          Url::fromRoute('reposi.author_aid',['node'=>$art_aut->ap_author_id])) . '.';
          } else {
            $list_aut_art = $list_aut_art . \Drupal::l($each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] .
                          ' ' . $f_name[0] . '.', Url::fromRoute('reposi.author_aid',['node'=>$art_aut->ap_author_id])) . '.';
          }
        }
        $articles[] = '<i>' . $list_aut_art . '</i>' . '(' . $art_year . '). ' . '<b>' .
                      \Drupal::l($article->ab_title, Url::fromRoute('reposi.Reposi_articleinformation',['node'=>$article->abid])) . '</b>' .
                      '. ' . '<i>' . '<small>' . $val_unval . '</small>' . '</i>';
      }
    }
    $search_book = db_select('reposi_article_book', 'ab');
    $search_book->fields('ab')
            ->condition('ab.ab_type', 'Book', '=')
            ->orderBy('ab.ab_title', 'ASC');
    $info_book = $search_book->execute();
      $info_book -> allowRowCount = TRUE;
    $somethig_book = $info_book->rowCount();
    $books = array();
    if ($somethig_book == 0) {
      $books[] = 'No records';
    } else {
      foreach ($info_book as $book) {
        $search_date = db_select('reposi_date', 'd');
        $search_date->fields('d', array('d_year'))
                   ->condition('d.d_abid', $book->abid, '=');
        $book_year = $search_date->execute()->fetchField();
        $search_state = db_select('reposi_publication', 'p');
        $search_state->fields('p', array('p_check'))
                      ->condition('p.p_abid', $book->abid, '=');
        $val_unval = $search_state->execute()->fetchField();
        if ($val_unval == 1) {
          $val_unval = '(Validate)';
        } else {
          $val_unval = '(Unvalidate)';
        }
        $search_p_a = db_select('reposi_publication_author', 'pa');
        $search_p_a->fields('pa', array('ap_author_id', 'ap_abid'))
                   ->condition('pa.ap_abid', $book->abid, '=');
        $p_a = $search_p_a->execute();
        $list_aut_book='';
        foreach ($p_a as $book_aut) {
          $search_aut = db_select('reposi_author', 'a');
          $search_aut->fields('a')
                     ->condition('a.aid', $book_aut->ap_author_id, '=');
          $each_aut = $search_aut->execute()->fetchAssoc();
          $f_name = Reposi_info_publication::reposi_string($each_aut['a_first_name']);
          if (!empty($each_aut['a_second_name'])) {
            $s_name = Reposi_info_publication::reposi_string($each_aut['a_second_name']);
            $list_aut_book = $list_aut_book . \Drupal::l($each_aut['a_first_lastname'] . ' ' .
                          $each_aut['a_second_lastname'] . ' ' . $f_name[0] . '. ' . $s_name[0] . '.',
                          Url::fromRoute('reposi.author_aid',['node'=>$book_aut->ap_author_id])) . ', ';
          } else {
            $list_aut_book = $list_aut_book . l($each_aut['a_first_lastname'] . ' ' .
                          $each_aut['a_second_lastname'] . ' ' .
                          $f_name[0] . '.', Url::fromRoute('reposi.author_aid',['node'=>$book_aut->ap_author_id])) . ', ';
          }
        }
        $books[] = '<i>' . $list_aut_book . '</i>' . '(' . $book_year . '). ' . '<b>' .
                    \Drupal::l($book->ab_title, Url::fromRoute('reposi.Reposi_bookinformation',['node'=>$book->abid])) . '</b>' .
                    '. ' . '<i>' . '<small>' . $val_unval . '</small>' . '</i>';
      }
    }
    $search_chap = db_select('reposi_article_book', 'ab');
    $search_chap->fields('ab')
            ->condition('ab.ab_type', 'Book Chapter', '=')
            ->orderBy('ab.ab_subtitle_chapter', 'ASC');
    $chap = $search_chap->execute();
    $chap -> allowRowCount = TRUE;
    $somethig_chap = $chap->rowCount();
    $chapters = array();
    if ($somethig_chap == 0) {
      $chapters[] = 'No records';
    } else {
      foreach ($chap as $chapter) {
        $search_date = db_select('reposi_date', 'd');
        $search_date->fields('d', array('d_year'))
                   ->condition('d.d_abid', $chapter->abid, '=');
        $chap_year = $search_date->execute()->fetchField();
        $search_state = db_select('reposi_publication', 'p');
        $search_state->fields('p', array('p_check'))
                      ->condition('p.p_abid', $chapter->abid, '=');
        $val_unval = $search_state->execute()->fetchField();
        if ($val_unval == 1) {
          $val_unval = '(Validate)';
        } else {
          $val_unval = '(Unvalidate)';
        }
        $search_p_a = db_select('reposi_publication_author', 'pa');
        $search_p_a->fields('pa', array('ap_author_id', 'ap_abid'))
                   ->condition('pa.ap_abid', $chapter->abid, '=');
        $p_a = $search_p_a->execute();
        $list_aut_chap='';
        foreach ($p_a as $chap_aut) {
          $search_aut = db_select('reposi_author', 'a');
          $search_aut->fields('a')
                     ->condition('a.aid', $chap_aut->ap_author_id, '=');
          $each_aut = $search_aut->execute()->fetchAssoc();
          $f_name = Reposi_info_publication::reposi_string($each_aut['a_first_name']);
          if (!empty($each_aut['a_second_name'])) {
            $s_name = Reposi_info_publication::reposi_string($each_aut['a_second_name']);
            $list_aut_chap = $list_aut_chap . \Drupal::l($each_aut['a_first_lastname'] . ' ' .
                          $each_aut['a_second_lastname'] . ' ' . $f_name[0] . '. ' . $s_name[0] . '.',
                          Url::fromRoute('reposi.author_aid',['node'=>$chap_aut->ap_author_id])) . ', ';
          } else {
            $list_aut_chap = $list_aut_chap . \Drupal::l($each_aut['a_first_lastname'] . ' ' .
                          $each_aut['a_second_lastname'] . ' ' . $f_name[0] . '.',
                          Url::fromRoute('reposi.author_aid',['node'=>$chap_aut->ap_author_id])) . '.';
          }
        }
        $chapters[] = '<i>' . $list_aut_chap . '</i>' . '(' . $chap_year . '). ' . '<b>' .
                      \Drupal::l($chapter->ab_subtitle_chapter, Url::fromRoute('reposi.Reposi_chapinformation',['node'=>$chapter->abid])) .
                      '</b>' .'. ' . '<i>' . '<small>' . $val_unval . '</small>' . '</i>';
      }
    }
    $search_sw = db_select('reposi_thesis_sw', 'sw');
    $search_sw->fields('sw')
            ->condition('sw.ts_type', 'Software', '=')
            ->orderBy('sw.ts_title', 'ASC');
    $soft_id = $search_sw->execute()->fetchField();
    $softw = $search_sw->execute();
    $softw -> allowRowCount = TRUE;
    $somethig_sw = $softw->rowCount();
    $softwares = array();
    if ($somethig_sw == 0) {
      $softwares[] = 'No records';
    } else {
      foreach ($softw as $software) {
        $search_date = db_select('reposi_date', 'd');
        $search_date->fields('d', array('d_year'))
                   ->condition('d.d_tsid', $software->tsid, '=');
        $soft_year = $search_date->execute()->fetchField();
        $search_state = db_select('reposi_publication', 'p');
        $search_state->fields('p', array('p_check'))
                      ->condition('p.p_tsid', $software->tsid, '=');
        $val_unval = $search_state->execute()->fetchField();
        if ($val_unval == 1) {
          $val_unval = '(Validate)';
        } else {
          $val_unval = '(Unvalidate)';
        }
        $search_p_a = db_select('reposi_publication_author', 'pa');
        $search_p_a->fields('pa', array('ap_author_id', 'ap_tsid'))
                   ->condition('pa.ap_tsid', $software->tsid, '=');
        $p_a = $search_p_a->execute();
        $list_aut_sw='';
        foreach ($p_a as $sw_aut) {
          $search_aut = db_select('reposi_author', 'a');
          $search_aut->fields('a')
                     ->condition('a.aid', $sw_aut->ap_author_id, '=');
          $each_aut = $search_aut->execute()->fetchAssoc();
          $f_name = Reposi_info_publication::reposi_string($each_aut['a_first_name']);
          if (!empty($each_aut['a_second_name'])) {
            $s_name = Reposi_info_publication::reposi_string($each_aut['a_second_name']);
            $list_aut_sw = $list_aut_sw . \Drupal::l($each_aut['a_first_lastname'] . ' ' .
                          $each_aut['a_second_lastname'] . ' ' . $f_name[0] . '. ' . $s_name[0] . '.',
                          Url::fromRoute('reposi.author_aid',['node'=>$sw_aut->ap_author_id])) . ', ';
          } else {
            $list_aut_sw = $list_aut_sw . \Drupal::l($each_aut['a_first_lastname'] . ' ' .
                          $each_aut['a_second_lastname'] . ' ' . $f_name[0] . '.',
                          Url::fromRoute('reposi.author_aid',['node'=>$sw_aut->ap_author_id])) . ', ';
          }
        }
        $softwares[] = '<i>' . $list_aut_sw . '</i>' . '(' . $soft_year . '). ' . '<b>' .
                      \Drupal::l($software->ts_title, Url::fromRoute('reposi.Reposi_sofinformation',['node'=>$software->tsid])) . '</b>' .
                      '. ' . '<i>' . '<small>' . $val_unval . '</small>' . '</i>';
      }
    }
    $search_pat = db_select('reposi_confer_patent', 'cp');
    $search_pat->fields('cp')
            ->condition('cp.cp_type', 'Patent', '=')
            ->orderBy('cp.cp_title', 'ASC');
    $pat = $search_pat->execute();
    $pat-> allowRowCount = TRUE;
    $somethig_pat = $pat->rowCount();
    $patents = array();
    if ($somethig_pat == 0) {
      $patents[] = 'No records';
    } else {
      foreach ($pat as $patent) {
        $search_date = db_select('reposi_date', 'd');
        $search_date->fields('d', array('d_year'))
                   ->condition('d.d_cpid', $patent->cpid, '=');
        $pat_year = $search_date->execute()->fetchField();
        $search_state = db_select('reposi_publication', 'p');
        $search_state->fields('p', array('p_check'))
                      ->condition('p.p_cpid', $patent->cpid, '=');
        $val_unval = $search_state->execute()->fetchField();
        if ($val_unval == 1) {
          $val_unval = '(Validate)';
        } else {
          $val_unval = '(Unvalidate)';
        }
        $search_p_a = db_select('reposi_publication_author', 'pa');
        $search_p_a->fields('pa', array('ap_author_id', 'ap_cpid'))
                   ->condition('pa.ap_cpid', $patent->cpid, '=');
        $p_a = $search_p_a->execute();
        $list_aut_pat='';
        foreach ($p_a as $pat_aut) {
          $search_aut = db_select('reposi_author', 'a');
          $search_aut->fields('a')
                     ->condition('a.aid', $pat_aut->ap_author_id, '=');
          $each_aut = $search_aut->execute()->fetchAssoc();
          $f_name = Reposi_info_publication::reposi_string($each_aut['a_first_name']);
          if (!empty($each_aut['a_second_name'])) {
            $s_name = Reposi_info_publication::reposi_string($each_aut['a_second_name']);
            $list_aut_pat = $list_aut_pat . \Drupal::l($each_aut['a_first_lastname'] . ' ' .
                          $each_aut['a_second_lastname'] . ' ' . $f_name[0] . '. ' . $s_name[0] . '.',
                          Url::fromRoute('reposi.author_aid',['node'=>$pat_aut->ap_author_id])) . ', ';
          } else {
            $list_aut_pat = $list_aut_pat . \Drupal::l($each_aut['a_first_lastname'] . ' ' .
                          $each_aut['a_second_lastname'] . ' ' . $f_name[0] . '.',
                          Url::fromRoute('reposi.author_aid',['node'=>$pat_aut->ap_author_id])) . ', ';
          }
        }
        $patents[] = '<i>' . $list_aut_pat . '</i>' . '(' . $pat_year . '). ' . '<b>' .
                     \Drupal::l($patent->cp_title, Url::fromRoute('reposi.Reposi_patinformation',['node'=> $patent->cpid])) . '</b>' .
                     '. ' . '<i>' . '<small>' . $val_unval . '</small>' . '</i>';
      }
    }
    $search_the = db_select('reposi_thesis_sw', 'th');
    $search_the->fields('th')
            ->condition('th.ts_type', 'Thesis', '=')
            ->orderBy('th.ts_title', 'ASC');
    $the = $search_the->execute();
    $the -> allowRowCount = TRUE;
    $somethig_the = $the->rowCount();
    $all_thesis = array();
    if ($somethig_the == 0) {
      $all_thesis[] = 'No records';
    } else {
      foreach ($the as $thesis) {
        $search_date = db_select('reposi_date', 'd');
        $search_date->fields('d', array('d_year'))
                   ->condition('d.d_tsid', $thesis->tsid, '=');
        $the_year = $search_date->execute()->fetchField();
        $search_state = db_select('reposi_publication', 'p');
        $search_state->fields('p', array('p_check'))
                      ->condition('p.p_tsid', $thesis->tsid, '=');
        $val_unval = $search_state->execute()->fetchField();
        if ($val_unval == 1) {
          $val_unval = '(Validate)';
        } else {
          $val_unval = '(Unvalidate)';
        }
        $search_p_a = db_select('reposi_publication_author', 'pa');
        $search_p_a->fields('pa', array('ap_author_id', 'ap_tsid'))
                   ->condition('pa.ap_tsid', $thesis->tsid, '=');
        $p_a = $search_p_a->execute();
        $list_aut_the='';
        foreach ($p_a as $the_aut) {
          $search_aut = db_select('reposi_author', 'a');
          $search_aut->fields('a')
                     ->condition('a.aid', $the_aut->ap_author_id, '=');
          $each_aut = $search_aut->execute()->fetchAssoc();
          $f_name = Reposi_info_publication::reposi_string($each_aut['a_first_name']);
          if (!empty($each_aut['a_second_name'])) {
            $s_name = Reposi_info_publication::reposi_string($each_aut['a_second_name']);
            $list_aut_the = $list_aut_the . \Drupal::l($each_aut['a_first_lastname'] . ' ' .
                          $each_aut['a_second_lastname'] . ' ' . $f_name[0] . '. ' . $s_name[0] . '.',
                          Url::fromRoute('reposi.author_aid',['node'=>$the_aut->ap_author_id])) . ', ';
          } else {
            $list_aut_the = $list_aut_the . \Drupal::l($each_aut['a_first_lastname'] . ' ' .
                          $each_aut['a_second_lastname'] . ' ' . $f_name[0] . '.',
                          Url::fromRoute('reposi.author_aid',['node'=>$the_aut->ap_author_id])) . ', ';
          }
        }
        $all_thesis[] = '<i>' . $list_aut_the . '</i>' . '(' . $the_year . '). ' .
                        '<b>' . \Drupal::l($thesis->ts_title, Url::fromRoute('reposi.Reposi_thesinformation',['node'=>$thesis->tsid])) .
                        '</b>' . '. ' . '<i>' . '<small>' . $val_unval . '</small>' . '</i>';
      }
    }
    $search_con = db_select('reposi_confer_patent', 'cp');
    $search_con->fields('cp')
            ->condition('cp.cp_type', 'Conference', '=')
            ->orderBy('cp.cp_publication', 'ASC');
    $con = $search_con->execute();
    $con -> allowRowCount = TRUE;
    $somethig_con = $con->rowCount();
    $conferences = array();
    if ($somethig_con == 0) {
      $conferences[] = 'No records';
    } else {
      foreach ($con as $conference) {
        $search_date = db_select('reposi_date', 'd');
        $search_date->fields('d', array('d_year'))
                   ->condition('d.d_cpid', $conference->cpid, '=');
        $con_year = $search_date->execute()->fetchField();
        $search_state = db_select('reposi_publication', 'p');
        $search_state->fields('p', array('p_check'))
                      ->condition('p.p_cpid', $conference->cpid, '=');
        $val_unval = $search_state->execute()->fetchField();
        if ($val_unval == 1) {
          $val_unval = '(Validate)';
        } else {
          $val_unval = '(Unvalidate)';
        }
        $search_p_a = db_select('reposi_publication_author', 'pa');
        $search_p_a->fields('pa', array('ap_author_id', 'ap_cpid'))
                   ->condition('pa.ap_cpid', $conference->cpid, '=');
        $p_a = $search_p_a->execute();
        $list_aut_con='';
        foreach ($p_a as $con_aut) {
          $search_aut = db_select('reposi_author', 'a');
          $search_aut->fields('a')
                     ->condition('a.aid', $con_aut->ap_author_id, '=');
          $each_aut = $search_aut->execute()->fetchAssoc();
          $f_name = Reposi_info_publication::reposi_string($each_aut['a_first_name']);
          if (!empty($each_aut['a_second_name'])) {
            $s_name = Reposi_info_publication::reposi_string($each_aut['a_second_name']);
            $list_aut_con = $list_aut_con . \Drupal::l($each_aut['a_first_lastname'] . ' ' .
                          $each_aut['a_second_lastname'] . ' ' . $f_name[0] . '. ' . $s_name[0] . '.',
                          Url::fromRoute('reposi.author_aid',['node'=> $con_aut->ap_author_id])) . ', ';
          } else {
            $list_aut_con = $list_aut_con . \Drupal::l($each_aut['a_first_lastname'] . ' ' .
                          $each_aut['a_second_lastname'] . ' ' . $f_name[0] . '.',
                          Url::fromRoute('reposi.author_aid',['node'=> $con_aut->ap_author_id])) . ', ';
          }
        }
        $conferences[] = '<i>' . $list_aut_con . '</i>' . '('. $con_year  . '). ' .'<b>' .
                      \Drupal::l($conference->cp_publication, Url::fromRoute('reposi.Reposi_coninformation',['node'=>  $conference->cpid])) .
                      '</b>' .'. ' . '<i>' . '<small>' . $val_unval . '</small>' . '</i>';
      }
    }
    $display_art = '';
    foreach ($articles as $new_art) {
      $display_art = $display_art . '<p>' . $new_art . '</p>';
    }
    $display_book = '';
    foreach ($books as $new_book) {
      $display_book = $display_book . '<p>' . $new_book . '</p>';
    }
    $display_chap = '';
    foreach ($chapters as $new_chap) {
      $display_chap = $display_chap . '<p>' . $new_chap . '</p>';
    }
    $display_con = '';
    foreach ($conferences as $new_con) {
      $display_con = $display_con . '<p>' . $new_con . '</p>';
    }
    $display_the = '';
    foreach ($all_thesis as $new_the) {
      $display_the = $display_the . '<p>' . $new_the . '</p>';
    }
    $display_pat = '';
    foreach ($patents as $new_pat) {
      $display_pat = $display_pat . '<p>' . $new_pat . '</p>';
    }
    $display_sw = '';
    foreach ($softwares as $new_software) {
      $display_sw = $display_sw . '<p>' . $new_software . '</p>';
    }
    $markup = '<p>'.'<b>'.'<big>'. ' Article ' .'</big>'.'</b>'.'</p>'. $display_art .
              '<p>'.'<b>'.'<big>'. ' Book ' .'</big>'.'</b>'.'</p>'. $display_book .
              '<p>'.'<b>'.'<big>'. ' Book Chapter ' .'</big>'.'</b>'.'</p>'. $display_chap .
              '<p>'.'<b>'.'<big>'. ' Conference Paper' .'</big>'.'</b>'.'</p>'. $display_con .
              '<p>'.'<b>'.'<big>'. ' Thesis ' .'</big>'.'</b>'.'</p>'. $display_the .
              '<p>'.'<b>'.'<big>'. ' Patent ' .'</big>'.'</b>'.'</p>'. $display_pat .
              '<p>'.'<b>'.'<big>'. ' Software ' .'</big>'.'</b>'.'</p>'. $display_sw;
    $form['body'] = array('#markup' => $markup);
    return $form;
  }


public function reposi_list_publication(){
  global $base_url;

  $search_publi = db_select('reposi_publication', 'p');
  $search_publi->fields('p')
             ->orderBy('p.p_check', 'DESC')
             ->orderBy('p.p_year', 'DESC')
             ->orderBy('p.p_title', 'ASC');
  $pager=$search_publi->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(20);
  $list_pub = $pager->execute();
  $publications = '';
  $publications_un = '';
  $form['body'] = array();
  foreach ($list_pub as $list_p) {
    $pub_type = $list_p->p_type;
    $pub_title = $list_p->p_title;
    $pub_year = $list_p->p_year;
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
                          \Drupal::l($pub_title, Url::fromRoute('reposi.Reposi_articleinformation',['node'=>$abid])) . '</b>' . '.' . '</p>';

        } elseif ($list_p->p_type == 'Book'){
          $publications = $publications .'<p>'. $list_aut_abc.'(' . $pub_year . ') ' .
                          '<b>'. \Drupal::l($pub_title, Url::fromRoute('reposi.Reposi_bookinformation',['node'=>$abid])) . '</b>' . '.' . '</p>';
        } else {
          $publications = $publications . '<p>'. $list_aut_abc.'(' . $pub_year . ') ' .'<b>'.
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
                          \Drupal::l($pub_title, Url::fromRoute('reposi.Reposi_thesinformation',['node'=>$tsid])) . '</b>' . '.' . '</p>';
        } else {
          $publications = $publications .'<p>'. $list_aut_ts. '(' . $pub_year . ') ' .'<b>'.
                          \Drupal::l($pub_title, Url::fromRoute('reposi.Reposi_sofinformation',['node'=>$tsid])) . '</b>' . '.' . '</p>';
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
                          \Drupal::l($pub_title, Url::fromRoute('reposi.Reposi_coninformation',['node'=>$cpid])) . '</b>' . '.' . '</p>';
        } else {
          $publications = $publications . '<p>'.$list_aut_cp . '(' . $pub_year . ') ' .'<b>'.
                    \Drupal::l($pub_title, Url::fromRoute('reposi.Reposi_patinformation',['node'=>$cpid])) . '</b>' . '.' . '</p>';
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
}//End classes
