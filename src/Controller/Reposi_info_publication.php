<?php
/**
 * @file
 * Contains \Drupal\hello_world\Controller\HelloController.
 */

namespace Drupal\reposi\Controller;
use Drupal\Core\Database;
use Drupal\Core\Url;

class Reposi_info_publication {


public static function reposi_string($string) {
    $string = trim($string);
    $string = str_replace(
      array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
      array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
      $string
    );
    $string = str_replace(
      array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
      array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
      $string
    );
    $string = str_replace(
      array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
      array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
      $string
    );
    $string = str_replace(
      array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
      array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
      $string
    );
    $string = str_replace(
      array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
      array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
      $string
    );
    $string = str_replace(
      array('ñ', 'Ñ', 'ç', 'Ç'),
      array('n', 'N', 'c', 'C',),
      $string
    );
    return $string;
  }



public static function reposi_formt_date($day, $month, $year){
  if (empty($month)) {
    $month_letter = '';
  } else {
    if ($month == 1) {
      $month_letter = 'January';
    } elseif ($month == 2) {
      $month_letter = 'February';
    } elseif ($month == 3) {
      $month_letter = 'March';
    } elseif ($month == 4) {
      $month_letter = 'April';
    } elseif ($month == 5) {
      $month_letter = 'May';
    } elseif ($month == 6) {
      $month_letter = 'June';
    } elseif ($month == 7) {
      $month_letter = 'July';
    } elseif ($month == 8) {
      $month_letter = 'August';
    } elseif ($month == 9) {
      $month_letter = 'September';
    } elseif ($month == 10) {
      $month_letter = 'October';
    } elseif ($month == 11) {
      $month_letter = 'November';
    } else {
      $month_letter = 'December';
    }
  }
  if (empty($day) && empty($month)) {
    $format_dates = $year;
  } elseif (empty($day)) {
    $format_dates = $month_letter . '/' . $year;
  } else {
    $format_dates = $day . '/' . $month_letter . '/' . $year;
  }
  return $format_dates;
}

public function reposi_info_article_free(){

  $art_id = \Drupal::routeMatch()->getParameter('node');
  global $base_url;
  $form['pid'] = array(
    '#type' => 'value',
    '#value' => $art_id,
  );
  $search_art = db_select('reposi_article_book', 'ab');
  $search_art->fields('ab')
          ->condition('ab.abid', $art_id, '=');
  $info_publi = $search_art->execute()->fetchAssoc();
  $ejemplovar = print_r($info_publi['ab_title']);

  $search_art_detail = db_select('reposi_article_book_detail', 'abd');
  $search_art_detail->fields('abd')
          ->condition('abd.abd_abid', $art_id, '=');
  $info_publi_2 = $search_art_detail->execute()->fetchAssoc();
  $search_date = db_select('reposi_date', 'd');
  $search_date->fields('d')
              ->condition('d.d_abid', $art_id, '=');
  $art_date = $search_date->execute()->fetchAssoc();

  $format_date = Reposi_info_publication::reposi_formt_date($art_date['d_day'],$art_date['d_month'],$art_date['d_year']);
  $search_p_a_art = db_select('reposi_publication_author', 'pa');
  $search_p_a_art->fields('pa')
                ->condition('pa.ap_abid', $art_id, '=');
  $couple_art = $search_p_a_art->execute();
  $authors_art = '';
  $couple_art -> allowRowCount = TRUE;
  $num_aut = $couple_art->rowCount();
  $flag_aut = 0;
  foreach ($couple_art as $aut_art) {
    $flag_aut++;
    $search_aut = db_select('reposi_author', 'a');
    $search_aut->fields('a')
               ->condition('a.aid', $aut_art->ap_author_id, '=');
    $each_aut = $search_aut->execute()->fetchAssoc();
    if ($flag_aut <> $num_aut) {
      $f_name = Reposi_info_publication::reposi_string($each_aut['a_first_name']);
      if (!empty($each_aut['a_second_name'])) {
        $s_name = Reposi_info_publication::reposi_string($each_aut['a_second_name']);
        $authors_art = $authors_art . \Drupal::l($each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] .
                      ' ' . $f_name[0] . '. ' . $s_name[0] . '.',
                      Url::fromRoute('reposi.author_aid',['node'=>$aut_art->ap_author_id])) . '.';
      } else {
        $authors_art = $authors_art . \Drupal::l($each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] .
                      ' ' . $f_name[0] . '. ',Url::fromRoute('reposi.author_aid',['node'=>$aut_art->ap_author_id])) . '.';
      }
    } else {
      $search_aut = db_select('reposi_author', 'a');
      $search_aut->fields('a')
                 ->condition('a.aid', $aut_art->ap_author_id, '=');
      $each_aut = $search_aut->execute()->fetchAssoc();
      $f_name = Reposi_info_publication::reposi_string($each_aut['a_first_name']);
      if (!empty($each_aut['a_second_name'])) {
        $s_name = Reposi_info_publication::reposi_string($each_aut['a_second_name']);
        $authors_art = $authors_art . \Drupal::l($each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] .
                      ' ' . $f_name[0] . '. ' . $s_name[0] . '.',Url::fromRoute('reposi.author_aid',['node'=>$aut_art->ap_author_id])) . '.';
/////////////////////////Duireccion CAmbiar Falta /reposi/author/{node}
      } else {
        $authors_art = $authors_art . \Drupal::l($each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] .
                      ' ' . $f_name[0] . '. ',Url::fromRoute('reposi.author_aid',['node'=>$aut_art->ap_author_id])) . '.';
      }
    }
  }
  $search_p_k = db_select('reposi_publication_keyword', 'pk');
  $search_p_k->fields('pk')
            ->condition('pk.pk_abid', $art_id, '=');
  $keyword = $search_p_k->execute();
  $flag_keyw = 0;
  $keyws_art = '';
  foreach ($keyword as $key_art) {
    $flag_keyw++;
    $search_keyw = db_select('reposi_keyword', 'k');
    $search_keyw->fields('k')
                ->condition('k.kid', $key_art->pk_keyword_id, '=');
    $keywords = $search_keyw->execute()->fetchAssoc();
    if ($flag_keyw == 1) {
      $keyws_art = \Drupal::l($keywords['k_word'],Url::fromRoute('reposi.reposi_format_ris',['node'=>$keywords['kid']]));
      ///////////////////////Falta direccion a /reposi/keyword/{node} ESta en ris

    } else {
      $keyws_art = $keyws_art . ', ' . $keyws_art = \Drupal::l($keywords['k_word'],Url::fromRoute('reposi.reposi_format_ris',['node'=>$keywords['kid']]));
    }
  }
  //$info_publi['ab_title']='1';
  $markup = '<p>' . '<b>' . '<big>' . t($info_publi['ab_title']) . '</big>' .
            '</b>' .'</p>' . '<div>'. t('Publication details: ') . '</div>' . '<ul>' .
            '<li>'.'<i>'. t('Type: ') . '</i>' . $info_publi['ab_type'] .'</li>';
  $markup .= '<li>'.'<i>'.t('Author(s): ').'</i>'. $authors_art .'</li>';
  if (!empty($info_publi['ab_abstract'])) {
    $markup .= '<li>' . '<i>' . t('Abstract: ') . '</i>' . '<p align="justify">' .
               $info_publi['ab_abstract'] . '</p>' . '</li>';
  }
  if (!empty($keyws_art)) {
    $markup .= '<li>'.'<i>'.t('Keyword(s): ').'</i>'. $keyws_art .'</li>';
  }
  $markup .= '<li>'. '<i>'. t('Date: ') . '</i>' . $format_date . '</li>';
  if (!empty($info_publi_2['abd_url'])) {
    $markup .= '<li>'. '<i>'. t('URL: ') . '</i>' .
               l($info_publi_2['abd_url'], $info_publi_2['abd_url']) . '</li>';
  }
  if (!empty($info_publi_2['abd_doi'])) {
    $markup .= '<li>'. '<i>'. t('DOI: ') . '</i>' . $info_publi_2['abd_doi'] . '</li>';
  }
  $markup .= '<br>';
  if (!empty($info_publi['ab_journal_editorial'])) {
    $markup .= '<li>'. '<i>'. t('Journal/Book: ') . '</i>' . $info_publi['ab_journal_editorial'] .
               '</li>' . '<ul type = square>';
    if (!empty($info_publi_2['abd_volume'])) {
      $markup .= '<li>'. '<i>'. t('Volume: ') . '</i>' . $info_publi_2['abd_volume'] . '</li>';
    }
    if (!empty($info_publi_2['abd_issue'])) {
      $markup .= '<li>'. '<i>'. t('Issue: ') . '</i>' . $info_publi_2['abd_issue'] . '</li>';
    }
    if (!empty($info_publi_2['abd_start_page'])) {
      $markup .= '<li>'. '<i>'. t('Start page: ') . '</i>' . $info_publi_2['abd_start_page'] . '</li>';
    }
    if (!empty($info_publi_2['abd_final_page'])) {
      $markup .= '<li>'. '<i>'. t('Final page: ') . '</i>' . $info_publi_2['abd_final_page'] . '</li>';
    }
    if (!empty($info_publi_2['abd_issn'])) {
      $markup .= '<li>'. '<i>'. t('ISSN: ') . '</i>' . $info_publi_2['abd_issn'] . '</li>';
    }
    $markup .= '</ul>';
  }
  $markup .= '</ul>';
  $form['body'] = array('#markup' => $markup);
  $search_id = db_select('reposi_publication', 'p');
  $search_id->fields('p', array('pid'))
                ->condition('p.p_abid', $art_id, '=');
  $id_publication = $search_id->execute()->fetchField();
//variable_set('publication_id',$id_publication);

\Drupal::state()->set('publication_id', $id_publication);
if(empty($id_publication)){
$id_publication=t("Error");
}

$url=Url::fromRoute('reposi.reposi_format_ris',['node'=>$id_publication]);
$risSend=\Drupal::l(t('RIS'),$url);
  $form['export'] = array(
    '#type' => 'item',
    '#title' => t('Export formats: '.$ejemplovar),
    '#markup' => $risSend,
  );
  return $form;
}

////////////////////////////////////////////////

function reposi_info_book_free(){
  $book_id = \Drupal::routeMatch()->getParameter('node');
  global $base_url;
  $form['pid'] = array(
    '#type' => 'value',
    '#value' => $book_id,
  );
  $search_book = db_select('reposi_article_book', 'ab');
  $search_book->fields('ab')
          ->condition('ab.abid', $book_id, '=');
  $info_publi = $search_book->execute()->fetchAssoc();
  $search_book_detail = db_select('reposi_article_book_detail', 'abd');
  $search_book_detail->fields('abd')
          ->condition('abd.abd_abid', $book_id, '=');
  $info_publi_2 = $search_book_detail->execute()->fetchAssoc();
  $search_date = db_select('reposi_date', 'd');
  $search_date->fields('d', array('d_year'))
              ->condition('d.d_abid', $book_id, '=');
  $book_year = $search_date->execute()->fetchField();

  $search_p_a_book = db_select('reposi_publication_author', 'pa');
  $search_p_a_book->fields('pa')
                ->condition('pa.ap_abid', $book_id, '=');
  $couple_book = $search_p_a_book->execute();
  $authors_book = '';
  $couple_book -> allowRowCount = TRUE;
  $num_aut = $couple_book->rowCount();
  $flag_aut = 0;
  foreach ($couple_book as $aut_book) {
    $flag_aut++;
    if ($flag_aut <> $num_aut) {
      $search_aut = db_select('reposi_author', 'a');
      $search_aut->fields('a')
                 ->condition('a.aid', $aut_book->ap_author_id, '=');
      $each_aut = $search_aut->execute()->fetchAssoc();
      $f_name = Reposi_info_publication::reposi_string($each_aut['a_first_name']);
      if (!empty($each_aut['a_second_name'])) {
        $s_name = Reposi_info_publication::reposi_string($each_aut['a_second_name']);
        $authors_book = $authors_book . \Drupal::l($each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] .
                      ' ' . $f_name[0] . '. ' . $s_name[0] . '.',Url::fromRoute('reposi.author_aid',['node'=>$aut_art->ap_author_id])) . ', ';
      } else {
        $authors_book = $authors_book . \Drupal::l($each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] .
                      ' ' . $f_name[0] . '.', Url::fromRoute('reposi.author_aid',['node'=>$aut_art->ap_author_id] )) . ', ';
      }
    } else {
      $search_aut = db_select('reposi_author', 'a');
      $search_aut->fields('a')
                 ->condition('a.aid', $aut_book->ap_author_id, '=');
      $each_aut = $search_aut->execute()->fetchAssoc();
      $f_name = reposi_string($each_aut['a_first_name']);
      if (!empty($each_aut['a_second_name'])) {
        $s_name = reposi_string($each_aut['a_second_name']);
        $authors_book = $authors_book . \Drupal::l($each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] .
                      ' ' . $f_name[0] . '. ' . $s_name[0] . '.',
                      Url::fromRoute('reposi.reposi_format_ris',['node'=>$aut_art->ap_author_id])) . '.';
      } else {
        $authors_book = $authors_book . \Drupal::l($each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] .
                      ' ' . $f_name[0] . '.', Url::fromRoute('reposi.reposi_format_ris',['node'=>$aut_art->ap_author_id])) . '.';
      }
    }
  }
  $markup = '<p>' . '<b>' . '<big>' . t($info_publi['ab_title']) . '</big>' .
            '</b>' . '</p>' . '<div>'. t('Publication details: ') . '</div>' . '<ul>' .
            '<li>'.'<i>'. t('Type: ') . '</i>' . $info_publi['ab_type'] .'</li>';
  if (!empty($info_publi['ab_subtitle_chapter'])) {
    $markup .= '<li>'.'<i>'.t('Subtitle: ').'</i>'. $info_publi['ab_subtitle_chapter']. '</li>';
  }
  $markup .= '<li>'.'<i>'.t('Author(s): ').'</i>'. $authors_book .'</li>';
  if (!empty($info_publi['ab_abstract'])) {
    $markup .= '<li>' . '<i>' . t('Description: ') . '</i>' . '<p align="justify">' .
               $info_publi['ab_abstract'] . '</p>' . '</li>';
  }
  $markup .= '<li>'. '<i>'. t('Publication’s year: ') . '</i>' . $book_year . '</li>';
  if (!empty($info_publi['ab_language'])) {
    $markup .= '<li>'. '<i>'. t('Language: ') . '</i>' . $info_publi['ab_language'] . '</li>';
  }
  if (!empty($info_publi_2['abd_volume'])) {
    $markup .= '<li>'. '<i>'. t('Volume: ') . '</i>' . $info_publi_2['abd_volume'] . '</li>';
  }
  if (!empty($info_publi_2['abd_issue'])) {
    $markup .= '<li>'. '<i>'. t('Issue: ') . '</i>' . $info_publi_2['abd_issue'] . '</li>';
  }
  if (!empty($info_publi['ab_journal_editorial'])) {
    $markup .= '<li>'. '<i>'. t('Publisher: ') . '</i>' . $info_publi['ab_journal_editorial'] . '</li>';
  }
  if (!empty($info_publi['ab_publisher'])) {
    $markup .= '<li>'. '<i>'. t('Publisher name: ') . '</i>' . $info_publi['ab_publisher'] . '</li>';
  }
  if (!empty($info_publi['ab_place'])) {
    $markup .= '<li>'. '<i>'. t('Publication’s place: ') . '</i>' . $info_publi['ab_place'] . '</li>';
  }
  if (!empty($info_publi_2['abd_issn'])) {
    $markup .= '<li>'. '<i>'. t('ISSN: ') . '</i>' . $info_publi_2['abd_issn'] . '</li>';
  }
  if (!empty($info_publi_2['abd_isbn'])) {
    $markup .= '<li>'. '<i>'. t('ISBN: ') . '</i>' . $info_publi_2['abd_isbn'] . '</li>';
  }
  if (!empty($info_publi_2['abd_url'])) {
    $markup .= '<li>'. '<i>'. t('URL: ') . '</i>' .
               l($info_publi_2['abd_url'], $info_publi_2['abd_url']) . '</li>';
  }
  if (!empty($info_publi_2['abd_doi'])) {
    $markup .= '<li>'. '<i>'. t('DOI: ') . '</i>' . $info_publi_2['abd_doi'] . '</li>';
  }
  $markup .= '</ul>';
  $form['body'] = array('#markup' => $markup);
  $search_id = db_select('reposi_publication', 'p');
  $search_id->fields('p', array('pid'))
                ->condition('p.p_abid', $book_id, '=');
  $id_publication = $search_id->execute()->fetchField();
  \Drupal::state()->set('publication_id',$id_publication);
  if(empty($id_publication)){
  $id_publication=t("Error");
  }

  $url=Url::fromRoute('reposi.reposi_format_ris',['node'=>$id_publication]);
  $risSend=\Drupal::l(t('RIS'),$url);
  $form['export'] = array(
    '#type' => 'item',
    '#title' => t('Export formats: '),
    '#markup' => $risSend,
  );
  return $form;
}

function reposi_info_chap_book_free(){
$chap_id = \Drupal::routeMatch()->getParameter('node');
  global $base_url;
  $form['pid'] = array(
    '#type' => 'value',
    '#value' => $chap_id,
  );
  $search_chap = db_select('reposi_article_book', 'ab');
  $search_chap->fields('ab')
          ->condition('ab.abid', $chap_id, '=');
  $info_publi = $search_chap->execute()->fetchAssoc();
  $search_chap_detail = db_select('reposi_article_book_detail', 'abd');
  $search_chap_detail->fields('abd')
          ->condition('abd.abd_abid', $chap_id, '=');
  $info_publi_2 = $search_chap_detail->execute()->fetchAssoc();
  $search_date = db_select('reposi_date', 'd');
  $search_date->fields('d', array('d_year'))
              ->condition('d.d_abid', $chap_id, '=');
  $chap_year = $search_date->execute()->fetchField();
  $search_p_a_chap = db_select('reposi_publication_author', 'pa');
  $search_p_a_chap->fields('pa')
                ->condition('pa.ap_abid', $chap_id, '=');
  $couple_chap = $search_p_a_chap->execute();
  $authors_chap = '';
  $couple_chap -> allowRowCount = TRUE;
  $num_aut = $couple_chap->rowCount();
  $flag_aut = 0;
  foreach ($couple_chap as $aut_chap) {
    $flag_aut++;
    if ($flag_aut <> $num_aut) {
      $search_aut = db_select('reposi_author', 'a');
      $search_aut->fields('a')
                 ->condition('a.aid', $aut_chap->ap_author_id, '=');
      $each_aut = $search_aut->execute()->fetchAssoc();
      $f_name = Reposi_info_publication::reposi_string($each_aut['a_first_name']);
      if (!empty($each_aut['a_second_name'])) {
        $s_name = Reposi_info_publication::reposi_string($each_aut['a_second_name']);
        $authors_chap = $authors_chap . \Drupal::l($each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] .
                      ' ' . $f_name[0] . '. ' . $s_name[0] . '.',
                      Url::fromRoute('reposi.author_aid',['node'=>$aut_art->ap_author_id])) . ', ';
      } else {
        $authors_chap = $authors_chap . \Drupal::l($each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] .
                      ' ' . $f_name[0] . '.', Url::fromRoute('reposi.author_aid',['node'=>$aut_art->ap_author_id])) . ', ';
      }
    } else {
      $search_aut = db_select('reposi_author', 'a');
      $search_aut->fields('a')
                 ->condition('a.aid', $aut_chap->ap_author_id, '=');
      $each_aut = $search_aut->execute()->fetchAssoc();
      $f_name = Reposi_info_publication::reposi_string($each_aut['a_first_name']);
      if (!empty($each_aut['a_second_name'])) {
        $s_name = Reposi_info_publication::reposi_string($each_aut['a_second_name']);
        $authors_chap = $authors_chap . \Drupal::l($each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] .
                      ' ' . $f_name[0] . '. ' . $s_name[0] . '.',
                      Url::fromRoute('reposi.author_aid',['node'=>$aut_art->ap_author_id])) . '.';
      } else {
        $authors_chap = $authors_chap . \Drupal::l($each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] .
                      ' ' . $f_name[0] . '.', Url::fromRoute('reposi.author_aid',['node'=>$aut_art->ap_author_id])) . '.';
      }
    }
  }
  $markup = '<p>' . '<b>' . '<big>' . t($info_publi['ab_subtitle_chapter']) . '</big>' .
            '</b>' .'</p>' . '<div>'. t('Publication details: ') . '</div>' . '<ul>' .
            '<li>'.'<i>'. t('Type: ') . '</i>' . $info_publi['ab_type'] .'</li>' .
            '<li>'.'<i>'. t('Book: ') . '</i>' . $info_publi['ab_title'] .'</li>' .
            '<li>'.'<i>'.t('Chapter’s name: ').'</i>'. $info_publi['ab_subtitle_chapter']. '</li>';
  if (!empty($info_publi['ab_chapter'])) {
    $markup .= '<li>'.'<i>'.t('Chapter’s number: ').'</i>'. $info_publi['ab_chapter']. '</li>';
  }
  $markup .= '<li>'.'<i>'.t('Author(s): ').'</i>'. $authors_chap .'</li>';
  $markup .= '<li>'. '<i>'. t('Publication’s year: ') . '</i>' . $chap_year . '</li>';
  if (!empty($info_publi_2['abd_volume'])) {
    $markup .= '<li>'. '<i>'. t('Volume: ') . '</i>' . $info_publi_2['abd_volume'] . '</li>';
  }
  if (!empty($info_publi_2['abd_issue'])) {
    $markup .= '<li>'. '<i>'. t('Issue: ') . '</i>' . $info_publi_2['abd_issue'] . '</li>';
  }
  if (!empty($info_publi['ab_journal_editorial'])) {
    $markup .= '<li>'. '<i>'. t('Publisher: ') . '</i>' . $info_publi['ab_journal_editorial'] . '</li>';
  }
  if (!empty($info_publi['ab_publisher'])) {
    $markup .= '<li>'. '<i>'. t('Publisher name: ') . '</i>' . $info_publi['ab_publisher'] . '</li>';
  }
  if (!empty($info_publi_2['abd_start_page'])) {
    $markup .= '<li>'. '<i>'. t('Start page: ') . '</i>' . $info_publi_2['abd_start_page'] . '</li>';
  }
  if (!empty($info_publi_2['abd_final_page'])) {
    $markup .= '<li>'. '<i>'. t('Final page: ') . '</i>' . $info_publi_2['abd_final_page'] . '</li>';
  }
  if (!empty($info_publi['ab_place'])) {
    $markup .= '<li>'. '<i>'. t('Publication’s place: ') . '</i>' . $info_publi['ab_place'] . '</li>';
  }
  if (!empty($info_publi_2['abd_issn'])) {
    $markup .= '<li>'. '<i>'. t('ISSN: ') . '</i>' . $info_publi_2['abd_issn'] . '</li>';
  }
  if (!empty($info_publi_2['abd_isbn'])) {
    $markup .= '<li>'. '<i>'. t('ISBN: ') . '</i>' . $info_publi_2['abd_isbn'] . '</li>';
  }
  if (!empty($info_publi_2['abd_url'])) {
    $markup .= '<li>'. '<i>'. t('URL: ') . '</i>' .
               l($info_publi_2['abd_url'], $info_publi_2['abd_url']) . '</li>';
  }
  if (!empty($info_publi_2['abd_doi'])) {
    $markup .= '<li>'. '<i>'. t('DOI: ') . '</i>' . $info_publi_2['abd_doi'] . '</li>';
  }
  $markup .= '</ul>';
  $form['body'] = array('#markup' => $markup);
  $search_id = db_select('reposi_publication', 'p');
  $search_id->fields('p', array('pid'))
                ->condition('p.p_abid', $chap_id, '=');
  $id_publication = $search_id->execute()->fetchField();

  \Drupal::state()->set('publication_id', $id_publication);
  if(empty($id_publication)){
  $id_publication=t("Error");
  }

  $url=Url::fromRoute('reposi.reposi_format_ris',['node'=>$id_publication]);
  $risSend=\Drupal::l(t('RIS'),$url);
    $form['export'] = array(
      '#type' => 'item',
      '#title' => t('Export formats: '.$ejemplovar),
      '#markup' => $risSend,
    );

  return $form;
}
//////////reposi_info_conference_free

function reposi_info_conference_free(){

  $con_id = \Drupal::routeMatch()->getParameter('node');
  global $base_url;
  $form['pid'] = array(
    '#type' => 'value',
    '#value' => $con_id,
  );
  $search_con = db_select('reposi_confer_patent', 'cp');
  $search_con->fields('cp')
          ->condition('cp.cpid', $con_id, '=');
  $info_publi = $search_con->execute()->fetchAssoc();
  $search_date = db_select('reposi_date', 'd');
  $search_date->fields('d')
              ->condition('d.d_cpid', $con_id, '=');
  $con_date = $search_date->execute();
  $format_dates = array();
  foreach ($con_date as $dates) {
    $format_dates[] = Reposi_info_publication::reposi_formt_date($dates->d_day,$dates->d_month,$dates->d_year);
  }
  $search_p_a_con = db_select('reposi_publication_author', 'pa');
  $search_p_a_con->fields('pa')
                ->condition('pa.ap_cpid', $con_id, '=');
  $couple_con = $search_p_a_con->execute();
  $authors_con = '';
  $couple_con -> allowRowCount = TRUE;
  $num_aut = $couple_con->rowCount();
  $flag_aut = 0;
  foreach ($couple_con as $aut_con) {
    $flag_aut++;
    if ($flag_aut <> $num_aut) {
      $search_aut = db_select('reposi_author', 'a');
      $search_aut->fields('a')
                 ->condition('a.aid', $aut_con->ap_author_id, '=');
      $each_aut = $search_aut->execute()->fetchAssoc();
      $f_name = Reposi_info_publication::reposi_string($each_aut['a_first_name']);
      if (!empty($each_aut['a_second_name'])) {
        $s_name = Reposi_info_publication::reposi_string($each_aut['a_second_name']);
        $authors_con = $authors_con . \Drupal::l($each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] .
                      ' ' . $f_name[0] . '. ' . $s_name[0] . '.',
                      Url::fromRoute('reposi.author_aid',['node'=>$aut_art->ap_author_id])) . ', ';
      } else {
        $authors_con = $authors_con . \Drupal::l($each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] .
                      ' ' . $f_name[0] . '.', Url::fromRoute('reposi.author_aid',['node'=>$aut_art->ap_author_id])) . ', ';
      }
    } else {
      $search_aut = db_select('reposi_author', 'a');
      $search_aut->fields('a')
                 ->condition('a.aid', $aut_con->ap_author_id, '=');
      $each_aut = $search_aut->execute()->fetchAssoc();
      $f_name = Reposi_info_publication::reposi_string($each_aut['a_first_name']);
      if (!empty($each_aut['a_second_name'])) {
        $s_name = Reposi_info_publication::reposi_string($each_aut['a_second_name']);
        $authors_con = $authors_con . \Drupal::l($each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] .
                      ' ' . $f_name[0] . '. ' . $s_name[0] . '.',
                      Url::fromRoute('reposi.author_aid',['node'=>$aut_art->ap_author_id])) . '.';
      } else {
        $authors_con = $authors_con . \Drupal::l($each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] .
                      ' ' . $f_name[0] . '.',Url::fromRoute('reposi.author_aid',['node'=>$aut_art->ap_author_id]) ) . '.';
      }
    }
  }
  $search_p_k = db_select('reposi_publication_keyword', 'pk');
  $search_p_k->fields('pk')
            ->condition('pk.pk_cpid', $con_id, '=');
  $keyword = $search_p_k->execute();
  $flag_keyw=0;
  $keyws_con='';
  foreach ($keyword as $key_con) {
    $flag_keyw++;
    $search_keyw = db_select('reposi_keyword', 'k');
    $search_keyw->fields('k')
                ->condition('k.kid', $key_con->pk_keyword_id, '=');
    $keywords = $search_keyw->execute()->fetchAssoc();
    if ($flag_keyw == 1) {
      $keyws_con = \Drupal::l($keywords['k_word'],
                Url::fromRoute('reposi.reposi_format_ris',['node'=>$keywords['kid']]));
    } else {
      $keyws_con = $keyws_con . ', ' . \Drupal::l($keywords['k_word'],
                Url::fromRoute('reposi.reposi_format_ris',['node'=>$keywords['kid']]));
    }
  }
  $markup = '<p>' . '<b>' . '<big>' . t($info_publi['cp_publication']) . '</big>' .
            '</b>' .'</p>' . '<div>'. t('Publication details: ') . '</div>' . '<ul>' .
            '<li>'. '<i>'. t('Type: ') . '</i>' . $info_publi['cp_type'] .'</li>' .
            '<li>'.'<i>'.t('Author(s): ').'</i>'. $authors_con .'</li>';
  if (!empty($info_publi['cp_abstract'])) {
    $markup .= '<li>' . '<i>' . t('Abstract: ') . '</i>' . '<p align="justify">' .
               $info_publi['cp_abstract'].'</p>'.'</li>';
  }
  if (!empty($keyws_con)) {
    $markup .= '<li>'.'<i>'.t('Keyword(s): ').'</i>'. $keyws_con .'</li>';
  }
  $markup .= '<li>'. '<i>'. t('Date: ') . '</i>' . $format_dates[0] . '</li>';
  if (!empty($info_publi['cp_start_page'])) {
    $markup .= '<li>'. '<i>'. t('Start page: ') . '</i>' . $info_publi['cp_start_page'] . '</li>';
  }
  if (!empty($info_publi['cp_final_page'])) {
    $markup .= '<li>'. '<i>'. t('Final page: ') . '</i>' . $info_publi['cp_final_page'] . '</li>';
  }
  $markup .= '<li>'. '<i>'. t('Conference: ') . '</i>' .
              $info_publi['cp_title'] . '</li>' . '<ul type = square>';
  if (!empty($info_publi['cp_place_type'])) {
    $markup .= '<li>' . '<i>'. t('Place: ') . '</i>' . $info_publi['cp_place_type'] .'</li>';
  }
  if (!empty($info_publi['cp_spon_owner'])) {
    $markup .= '<li>' . '<i>'. t('Sponsor(s): ') . '</i>' . $info_publi['cp_spon_owner'] .'</li>';
  }
  if (!empty($info_publi['cp_number'])) {
    $markup .= '<li>'. '<i>'. t('Event’s number: ') . '</i>' . $info_publi['cp_number'] . '</li>';
  }
  $markup .= '<li>'. '<i>'. t('Start date: ') . '</i>' . $format_dates[1] . '</li>' .
             '<li>'. '<i>'. t('Ending date: ') . '</i>' . $format_dates[2] . '</li>' . '</ul>';
  if (!empty($info_publi['cp_url'])) {
    $markup .= '<li>'. '<i>'. t('URL: ') . '</i>' .
               l($info_publi['cp_url'], $info_publi['cp_url']) . '</li>';
  }
  if (!empty($info_publi['cp_doi'])) {
    $markup .= '<li>'. '<i>'. t('DOI: ') . '</i>' . $info_publi['cp_doi'] . '</li>';
  }
  $markup .= '</ul>';
  $form['body'] = array('#markup' => $markup);
  $search_id = db_select('reposi_publication', 'p');
  $search_id->fields('p', array('pid'))
                ->condition('p.p_cpid', $con_id, '=');
  $id_publication = $search_id->execute()->fetchField();

  \Drupal::state()->set('publication_id', $id_publication);
  if(empty($id_publication)){
  $id_publication=t("Error");
  }

  $url=Url::fromRoute('reposi.reposi_format_ris',['node'=>$id_publication]);
  $risSend=\Drupal::l(t('RIS'),$url);

  $form['export'] = array(
    '#type' => 'item',
    '#title' => t('Export formats: '),
    '#markup' => $risSend,
  );
  return $form;
}

///////////reposi_info_thesis_free
function reposi_info_thesis_free(){
  $the_id = \Drupal::routeMatch()->getParameter('node');
  global $base_url;
  $form['pid'] = array(
    '#type' => 'value',
    '#value' => $the_id,
  );
  $search_the = db_select('reposi_thesis_sw', 'th');
  $search_the->fields('th')
          ->condition('th.tsid', $the_id, '=');
  $info_publi = $search_the->execute()->fetchAssoc();
  $search_date = db_select('reposi_date', 'd');
  $search_date->fields('d')
              ->condition('d.d_tsid', $the_id, '=');
  $the_date = $search_date->execute()->fetchAssoc();
  $format_date = Reposi_info_publication::reposi_formt_date($the_date['d_day'],$the_date['d_month'],$the_date['d_year']);
  $search_p_a_the = db_select('reposi_publication_author', 'pa');
  $search_p_a_the->fields('pa')
                ->condition('pa.ap_tsid', $the_id, '=');
  $couple_the = $search_p_a_the->execute();
  $authors_the = '';
  $couple_the -> allowRowCount = TRUE;
  $num_aut = $couple_the->rowCount();
  $flag_aut = 0;
  foreach ($couple_the as $aut_the) {
    $flag_aut++;
    if ($flag_aut <> $num_aut) {
      $search_aut = db_select('reposi_author', 'a');
      $search_aut->fields('a')
                 ->condition('a.aid', $aut_the->ap_author_id, '=');
      $each_aut = $search_aut->execute()->fetchAssoc();
      $f_name = Reposi_info_publication::reposi_string($each_aut['a_first_name']);
      if (!empty($each_aut['a_second_name'])) {
        $s_name = Reposi_info_publication::reposi_string($each_aut['a_second_name']);
        $authors_the = $authors_the . \Drupal::l($each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] .
                      ' ' . $f_name[0] . '. ' . $s_name[0] . '.',
                      Url::fromRoute('reposi.reposi_format_ris',['node'=>$aut_art->ap_author_id])) . ', ';
      } else {
        $authors_the = $authors_the . \Drupal::l($each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] .
                      ' ' . $f_name[0] . '.', Url::fromRoute('reposi.reposi_format_ris',['node'=>$aut_art->ap_author_id]) ) . ', ';
      }
    } else {
      $search_aut = db_select('reposi_author', 'a');
      $search_aut->fields('a')
                 ->condition('a.aid', $aut_the->ap_author_id, '=');
      $each_aut = $search_aut->execute()->fetchAssoc();
      $f_name = Reposi_info_publication::reposi_string($each_aut['a_first_name']);
      if (!empty($each_aut['a_second_name'])) {
        $s_name = Reposi_info_publication::reposi_string($each_aut['a_second_name']);
        $authors_the = $authors_the . \Drupal::l($each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] .
                      ' ' . $f_name[0] . '. ' . $s_name[0] . '.',
                      Url::fromRoute('reposi.author_aid',['node'=>$aut_art->ap_author_id])) . '.';
      } else {
        $authors_the = $authors_the . \Drupal::l($each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] .
                      ' ' . $f_name[0] . '.', Url::fromRoute('reposi.author_aid',['node'=>$aut_art->ap_author_id])) . '.';
      }
    }
  }
  $search_p_k = db_select('reposi_publication_keyword', 'pk');
  $search_p_k->fields('pk')
            ->condition('pk.pk_tsid', $the_id, '=');
  $keyword = $search_p_k->execute();
  $flag_keyw=0;
  $keyws_the='';
  foreach ($keyword as $key_the) {
    $flag_keyw++;
    $search_keyw = db_select('reposi_keyword', 'k');
    $search_keyw->fields('k')
                ->condition('k.kid', $key_the->pk_keyword_id, '=');
    $keywords = $search_keyw->execute()->fetchAssoc();
    if ($flag_keyw == 1) {
      $keyws_the = \Drupal::l($keywords['k_word'],
                Url::fromRoute('reposi.reposi_format_ris',['node'=>$keywords['kid']]));
    } else {
      $keyws_the = $keyws_the . ', ' . \Drupal::l($keywords['k_word'],
                Url::fromRoute('reposi.reposi_format_ris',['node'=>$keywords['kid']]));
    }
  }
  $markup = '<p>' . '<b>' . '<big>' . t($info_publi['ts_title']) . '</big>' .
            '</b>' .'</p>' . '<div>'. t('Publication details: ') . '</div>' . '<ul>' .
            '<li>'.'<i>'.t('Type: ').'</i>'. $info_publi['ts_type']. '</li>' .
            '<li>'.'<i>'.t('Author(s): ').'</i>'. $authors_the .'</li>';
  if (!empty($keyws_the)) {
    $markup .= '<li>'.'<i>'.t('Keyword(s): ').'</i>'. $keyws_the .'</li>';
  }
  $markup .= '<li>'. '<i>'. t('Date: ') . '</i>' . $format_date . '</li>';
  if (!empty($info_publi['ts_institu_ver'])) {
    $markup .= '<li>'. '<i>'. t('Academic institution: ') . '</i>' .
               $info_publi['ts_institu_ver'] . '</li>';
  }
  $markup .= '<li>'. '<i>'. t('Type degree: ') . '</i>' . $info_publi['ts_degree'] . '</li>';
  if (!empty($info_publi['ts_discip_place'])) {
    $markup .= '<li>'. '<i>'. t('Discipline: ') . '</i>' . $info_publi['ts_discip_place'] . '</li>';
  }
  if (!empty($info_publi['ts_url'])) {
    $markup .= '<li>'. '<i>'. t('URL: ') . '</i>' .
               l($info_publi['ts_url'], $info_publi['ts_url']) . '</li>';
  }
  $markup .= '</ul>';
  $form['body'] = array('#markup' => $markup);
  $search_id = db_select('reposi_publication', 'p');
  $search_id->fields('p', array('pid'))
                ->condition('p.p_tsid', $the_id, '=');
  $id_publication = $search_id->execute()->fetchField();

  \Drupal::state()->set('publication_id', $id_publication);
  if(empty($id_publication)){
  $id_publication=t("Error");
  }
  $url=Url::fromRoute('reposi.reposi_format_ris',['node'=>$id_publication]);
  $risSend=\Drupal::l(t('RIS'),$url);
    $form['export'] = array(
      '#type' => 'item',
      '#title' => t('Export formats: '),
      '#markup' => $risSend,
    );
  return $form;
}

/////////////////////////////////
function reposi_info_patent_free(){
  $pat_id = \Drupal::routeMatch()->getParameter('node');
  global $base_url;
  $form['pid'] = array(
    '#type' => 'value',
    '#value' => $pat_id,
  );
  $search_pat = db_select('reposi_confer_patent', 'cp');
  $search_pat->fields('cp')
          ->condition('cp.cpid', $pat_id, '=');
  $info_publi = $search_pat->execute()->fetchAssoc();
  $search_date = db_select('reposi_date', 'd');
  $search_date->fields('d')
              ->condition('d.d_cpid', $pat_id, '=');
  $pat_date = $search_date->execute()->fetchAssoc();
  $search_p_a_pat = db_select('reposi_publication_author', 'pa');
  $search_p_a_pat->fields('pa')
                ->condition('pa.ap_cpid', $pat_id, '=');
  $couple_pat = $search_p_a_pat->execute();
  $authors_pat = '';
  $couple_pat -> allowRowCount = TRUE;
  $num_aut = $couple_pat->rowCount();
  $flag_aut = 0;
  foreach ($couple_pat as $aut_pat) {
    $flag_aut++;
    if ($flag_aut <> $num_aut) {
      $search_aut = db_select('reposi_author', 'a');
      $search_aut->fields('a')
                 ->condition('a.aid', $aut_pat->ap_author_id, '=');
      $each_aut = $search_aut->execute()->fetchAssoc();
      $f_name = Reposi_info_publication::reposi_string($each_aut['a_first_name']);
      if (!empty($each_aut['a_second_name'])) {
        $s_name = Reposi_info_publication::reposi_string($each_aut['a_second_name']);
        $authors_pat = $authors_pat . \Drupal::l($each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] .
                      ' ' . $f_name[0] . '. ' . $s_name[0] . '.',
                      Url::fromRoute('reposi.author_aid',['node'=>$aut_art->ap_author_id])) . ', ';
      } else {
        $authors_pat = $authors_pat . \Drupal::l($each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] .
                      ' ' . $f_name[0] . '.', Url::fromRoute('reposi.author_aid',['node'=>$aut_art->ap_author_id])) . ', ';
      }
    } else {
      $search_aut = db_select('reposi_author', 'a');
      $search_aut->fields('a')
                 ->condition('a.aid', $aut_pat->ap_author_id, '=');
      $each_aut = $search_aut->execute()->fetchAssoc();
      $f_name = Reposi_info_publication::reposi_string($each_aut['a_first_name']);
      if (!empty($each_aut['a_second_name'])) {
        $s_name = Reposi_info_publication::reposi_string($each_aut['a_second_name']);
        $authors_pat = $authors_pat . \Drupal::l($each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] .
                      ' ' . $f_name[0] . '. ' . $s_name[0] . '.',
                      Url::fromRoute('reposi.author_aid',['node'=>$aut_art->ap_author_id])) . '.';
      } else {
        $authors_pat = $authors_pat . \Drupal::l($each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] .
                      ' ' . $f_name[0] . '.', Url::fromRoute('reposi.author_aid',['node'=>$aut_art->ap_author_id])) . '.';
      }
    }
  }
  $format_date = Reposi_info_publication::reposi_formt_date($pat_date['d_day'],$pat_date['d_month'],$pat_date['d_year']);
  $markup = '<p>' . '<b>' . '<big>' . t($info_publi['cp_title']) . '</big>' . '</b>' .
            '</p>' . '<div>'. t('Publication details: ') . '</div>' . '<ul>' .
            '<li>'. '<i>'. t('Type: ') . '</i>' . $info_publi['cp_type'] .'</li>' .
            '<li>'.'<i>'.t('Originator(s): ').'</i>'. $authors_pat .'</li>';
  if (!empty($info_publi['cp_abstract'])) {
    $markup .= '<li>' . '<i>' . t('Abstract: ') . '</i>' . '<p align="justify">' .
               $info_publi['cp_abstract'] . '</p>' . '</li>';
  }
  $markup .= '<li>'. '<i>'. t('Date: ') . '</i>' . $format_date . '</li>';
  if (!empty($info_publi['cp_spon_owner'])) {
    $markup .= '<li>'. '<i>'. t('Owner: ') . '</i>' . $info_publi['cp_spon_owner'] . '</li>';
  }
  if (!empty($info_publi['cp_place_type'])) {
    $markup .= '<li>'. '<i>'. t('Type patent: ') . '</i>' . $info_publi['cp_place_type'] . '</li>';
  }
  if (!empty($info_publi['cp_number'])) {
    $markup .= '<li>'. '<i>'. t('Number: ') . '</i>' . $info_publi['cp_number'] . '</li>';
  }
  if (!empty($info_publi['cp_url'])) {
    $markup .= '<li>'. '<i>'. t('URL: ') . '</i>' .
                l($info_publi['cp_url'], $info_publi['cp_url']) .'</li>';
  }
  $markup .= '</ul>';
  $form['body'] = array('#markup' => $markup);
  $search_id = db_select('reposi_publication', 'p');
  $search_id->fields('p', array('pid'))
                ->condition('p.p_cpid', $pat_id, '=');
  $id_publication = $search_id->execute()->fetchField();

  \Drupal::state()->set('publication_id', $id_publication);
  if(empty($id_publication)){
  $id_publication=t("Error");
  }

  $url=Url::fromRoute('reposi.reposi_format_ris',['node'=>$id_publication]);
  $risSend=\Drupal::l(t('RIS'),$url);
    $form['export'] = array(
      '#type' => 'item',
      '#title' => t('Export formats: '),
      '#markup' => $risSend,
    );
  return $form;
}

/////////////////////////////

function reposi_info_sw_free(){

  $sw_id = \Drupal::routeMatch()->getParameter('node');
  global $base_url;
  $form['pid'] = array(
    '#type' => 'value',
    '#value' => $sw_id,
  );
  $search_sw = db_select('reposi_thesis_sw', 'sw');
  $search_sw->fields('sw')
          ->condition('sw.tsid', $sw_id, '=');
  $info_publi = $search_sw->execute()->fetchAssoc();
  $search_date = db_select('reposi_date', 'd');
  $search_date->fields('d', array('d_year'))
              ->condition('d.d_tsid', $sw_id, '=');
  $soft_year = $search_date->execute()->fetchField();
  $search_p_a_sw = db_select('reposi_publication_author', 'pa');
  $search_p_a_sw->fields('pa')
                ->condition('pa.ap_tsid', $sw_id, '=');
  $couple_sw = $search_p_a_sw->execute();
  $authors_sw = '';
  $couple_sw -> allowRowCount = TRUE;
  $num_aut = $couple_sw->rowCount();
  $flag_aut = 0;
  foreach ($couple_sw as $aut_sw) {
    $flag_aut++;
    if ($flag_aut <> $num_aut) {
      $search_aut = db_select('reposi_author', 'a');
      $search_aut->fields('a')
                 ->condition('a.aid', $aut_sw->ap_author_id, '=');
      $each_aut = $search_aut->execute()->fetchAssoc();
      $f_name = Reposi_info_publication::reposi_string($each_aut['a_first_name']);
      if (!empty($each_aut['a_second_name'])) {
        $s_name = Reposi_info_publication::reposi_string($each_aut['a_second_name']);
        $authors_sw = $authors_sw . \Drupal::l($each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] .
                      ' ' . $f_name[0] . '. ' . $s_name[0] . '.',
                      Url::fromRoute('reposi.author_aid',['node'=>$aut_art->ap_author_id])) . ', ';
      } else {
        $authors_sw = $authors_sw . \Drupal::l($each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] .
                      ' ' . $f_name[0] . '.', Url::fromRoute('reposi.author_aid',['node'=>$aut_art->ap_author_id])) . ', ';
      }
    } else {
      $search_aut = db_select('reposi_author', 'a');
      $search_aut->fields('a')
                 ->condition('a.aid', $aut_sw->ap_author_id, '=');
      $each_aut = $search_aut->execute()->fetchAssoc();
      $f_name = Reposi_info_publication::reposi_string($each_aut['a_first_name']);
      if (!empty($each_aut['a_second_name'])) {
        $s_name = Reposi_info_publication::reposi_string($each_aut['a_second_name']);
        $authors_sw = $authors_sw . \Drupal::l($each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] .
                      ' ' . $f_name[0] . '. ' . $s_name[0] . '.',
                      Url::fromRoute('reposi.author_aid',['node'=>$aut_art->ap_author_id])) . '.';
      } else {
        $authors_sw = $authors_sw . \Drupal::l($each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] .
                      ' ' . $f_name[0] . '.', Url::fromRoute('reposi.author_aid',['node'=>$aut_art->ap_author_id])) . '.';
      }
    }
  }
  $markup = '<p>' . '<b>' . '<big>' . t($info_publi['ts_title']) . '</big>' .
            '</b>' .'</p>' . '<div>'. t('Publication details: ') . '</div>' . '<ul>' .
            '<li>'. '<i>'. t('Type: ') . '</i>' . $info_publi['ts_type'] .'</li>' .
            '<li>'.'<i>'.t('Producer(s): ').'</i>'. $authors_sw .'</li>' .
            '<li>'. '<i>'. t('Publication’s year: ') . '</i>' . $soft_year . '</li>';
  if (!empty($info_publi['ts_institu_ver'])) {
    $markup .= '<li>'. '<i>'. t('Version: ') . '</i>' . $info_publi['ts_institu_ver'];
  }
  if (!empty($info_publi['ts_discip_place'])) {
    $markup .= '<li>'. '<i>'. t('Place of production: ') . '</i>' . $info_publi['ts_discip_place'];
  }
  if (!empty($info_publi['ts_url'])) {
    $markup .= '<li>'. '<i>'. t('URL: ') . '</i>' .
               l($info_publi['ts_url'], $info_publi['ts_url']) . '</li>';
  }
  $markup .= '</ul>';
  $form['body'] = array('#markup' => $markup);
  $search_id = db_select('reposi_publication', 'p');
  $search_id->fields('p', array('pid'))
                ->condition('p.p_tsid', $sw_id, '=');
  $id_publication = $search_id->execute()->fetchField();

  \Drupal::state()->set('publication_id', $id_publication);
  if(empty($id_publication)){
  $id_publication=t("Error");
  }

  $url=Url::fromRoute('reposi.reposi_format_ris',['node'=>$id_publication]);
  $risSend=\Drupal::l(t('RIS'),$url);
    $form['export'] = array(
      '#type' => 'item',
      '#title' => t('Export formats: '),
      '#markup' => $risSend,
    );
  return $form;
}


/////////////////////////////

}
