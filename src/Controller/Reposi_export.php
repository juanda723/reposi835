<?php
/**
 * @file
 */
namespace Drupal\reposi\Controller;

use Drupal\Core\Database;
use Drupal\Core\Url;


class Reposi_export {

function reposi_format_ris(){
  $id_publi = \Drupal::routeMatch()->getParameter('node');
  $form['pid'] = array(
    '#type' => 'value',
    '#value' => $id_publi,
  );
  $search_id = db_select('reposi_publication', 'p');
  $search_id->fields('p')
                ->condition('p.pid', $id_publi, '=');
  $info_publication = $search_id->execute()->fetchAssoc();
  if ($info_publication['p_type'] == 'Article') {

    $search_art = db_select('reposi_article_book', 'ab');
    $search_art->fields('ab')
            ->condition('ab.abid', $info_publication['p_abid'], '=');
    $info_publi = $search_art->execute()->fetchAssoc();
    $search_art_detail = db_select('reposi_article_book_detail', 'abd');
    $search_art_detail->fields('abd')//////hasta aqui///// voy
            ->condition('abd.abd_abid', $info_publication['p_abid'], '=');
    $info_publi_2 = $search_art_detail->execute()->fetchAssoc();
    $search_date = db_select('reposi_date', 'd');
    $search_date->fields('d')
                ->condition('d.d_abid', $info_publication['p_abid'], '=');
    $art_date = $search_date->execute()->fetchAssoc();
    $format_date = Reposi_export::reposi_formt_date_ris($art_date['d_day'],
                                         $art_date['d_month'],
                                         $art_date['d_year']);
    $search_p_a_art = db_select('reposi_publication_author', 'pa');
    $search_p_a_art->fields('pa')
                  ->condition('pa.ap_abid', $info_publication['p_abid'], '=');
    $couple_art = $search_p_a_art->execute();
    $authors_art = array();
    foreach ($couple_art as $aut_art) {
      $search_aut = db_select('reposi_author', 'a');
      $search_aut->fields('a')
                 ->condition('a.aid', $aut_art->ap_author_id, '=');
      $each_aut = $search_aut->execute()->fetchAssoc();
      $authors_art[] = $each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] . ',' .
                    $each_aut['a_first_name'] . ' ' . $each_aut['a_second_name'];
    }
    $content = 'TY – JOUR'. '<br>';
    foreach ($authors_art as $au) {
      $content .= 'AU – ' . $au . '<br>';
    }
    $content .= 'TI – ' . $info_publi['ab_title'] . '<br>';
    if (!empty($info_publi['ab_journal_editorial'])) {
      $content .= 'T2 – ' . $info_publi['ab_journal_editorial'] . '<br>';
    }
    $content .= 'DA – ' . $format_date . '<br>';
    if (!empty($info_publi_2['abd_volume'])) {
      $content .= 'VL – ' . $info_publi_2['abd_volume'] .'<br>';
    }
    if (!empty($info_publi_2['abd_issue'])) {
      $content .= 'IS – ' . $info_publi_2['abd_issue'] .'<br>';
    }
    if (!empty($info_publi_2['abd_start_page'])) {
      $content .= 'SP – ' . $info_publi_2['abd_start_page'] .'<br>';
    }
    if (!empty($info_publi_2['abd_final_page'])) {
      $content .= 'EP – ' . $info_publi_2['abd_final_page'] .'<br>';
    }
    if (!empty($info_publi_2['abd_issn'])) {
      $content .= 'SN – ' . $info_publi_2['abd_issn'] .'<br>';
    }
    if (!empty($info_publi['ab_abstract'])) {
      $content .= 'N2 – ' . $info_publi['ab_abstract'] .'<br>';
    }
    $search_p_k_art = db_select('reposi_publication_keyword', 'pk');
    $search_p_k_art->fields('pk')
                  ->condition('pk.pk_abid', $info_publication['p_abid'], '=');
    $keyword_art = $search_p_k_art->execute();
    $keyword_art -> allowRowCount = TRUE;
    $num_kw = $keyword_art->rowCount();
    if ($num_kw <> 0) {
      foreach ($keyword_art as $key_art) {
        $search_keyw = db_select('reposi_keyword', 'k');
        $search_keyw->fields('k')
                    ->condition('k.kid', $key_art->pk_keyword_id, '=');
        $keywords = $search_keyw->execute()->fetchAssoc();
        $keyws_art[] = $keywords['k_word'];
      }
      foreach ($keyws_art as $kw) {
        $content .= 'KW – ' . $kw . '<br>';
      }
    }
    if (!empty($info_publi_2['abd_doi'])) {
      $content .= 'DO – DOI: ' . $info_publi_2['abd_doi'] .'<br>';
    }
    if (!empty($info_publi_2['abd_url'])) {
      $content .= 'UR – ' . $info_publi_2['abd_url']. '<br>';
    }
    $content .= 'ER';
    $form['body'] = array('#markup' => $content);
    return $form;

  } elseif ($info_publication['p_type'] == 'Book'){

    $search_book = db_select('reposi_article_book', 'ab');
    $search_book->fields('ab')
            ->condition('ab.abid', $info_publication['p_abid'], '=');
    $info_publi = $search_book->execute()->fetchAssoc();
    $search_book_detail = db_select('reposi_article_book_detail', 'abd');
    $search_book_detail->fields('abd')
            ->condition('abd.abd_abid', $info_publication['p_abid'], '=');
    $info_publi_2 = $search_book_detail->execute()->fetchAssoc();
    $search_p_a_book = db_select('reposi_publication_author', 'pa');
    $search_p_a_book->fields('pa')
                  ->condition('pa.ap_abid', $info_publication['p_abid'], '=');
    $couple_book = $search_p_a_book->execute();
    $authors_book = array();
    foreach ($couple_book as $aut_book) {
      $search_aut = db_select('reposi_author', 'a');
      $search_aut->fields('a')
                 ->condition('a.aid', $aut_book->ap_author_id, '=');
      $each_aut = $search_aut->execute()->fetchAssoc();
      $authors_book[] = $each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] . ',' .
                    $each_aut['a_first_name'] . ' ' . $each_aut['a_second_name'];
    }
    $content = 'TY – BOOK'. '<br>';
    foreach ($authors_book as $au) {
      $content .= 'AU – ' . $au . '<br>';
    }
    if (!empty($info_publi['ab_publisher'])) {
      $content .= 'A2 – ' . $info_publi['ab_publisher'] . '<br>';
    }
    $content .= 'TI – '. $info_publi['ab_title'] . '<br>';
    if (!empty($info_publi['ab_subtitle_chapter'])) {
      $content .= 'T2 – ' . $info_publi['ab_subtitle_chapter'] .'<br>';
    }
    if (!empty($info_publi['ab_abstract'])) {
      $content .= 'N2 – ' . $info_publi['ab_abstract'] .'<br>';
    }
    $content .= 'PY – ' . $info_publication['p_year'].'<br>';
    if (!empty($info_publi['ab_place'])) {
      $content .= 'CY – ' . $info_publi['ab_place'] .'<br>';
    }
    if (!empty($info_publi['ab_journal_editorial'])) {
      $content .= 'PB – ' . $info_publi['ab_journal_editorial'] .'<br>';
    }
    if (!empty($info_publi_2['abd_volume'])) {
      $content .= 'VL – ' . $info_publi_2['abd_volume'] .'<br>';
    }
    if (!empty($info_publi_2['abd_issue'])) {
      $content .= 'IS – ' . $info_publi_2['abd_issue'] .'<br>';
    }
    if (!empty($info_publi_2['abd_doi'])) {
      $content .= 'DO – DOI: ' . $info_publi_2['abd_doi'] .'<br>';
    }
    if (!empty($info_publi_2['abd_issn'])) {
      $content .= 'SN – ' . $info_publi_2['abd_issn'] .'<br>';
    }
    if (!empty($info_publi_2['abd_isbn'])) {
      $content .= 'SN – ' . $info_publi_2['abd_isbn'] .'<br>';
    }
    if (!empty($info_publi_2['abd_url'])) {
      $content .= 'UR – ' . $info_publi_2['abd_url']. '<br>';
    }
    $content .= 'ER';
    $form['body'] = array('#markup' => $content);
    return $form;
////////////YA

  } elseif ($info_publication['p_type'] == 'Book Chapter'){
//////YA
    $search_chap = db_select('reposi_article_book', 'ab');
    $search_chap->fields('ab')
            ->condition('ab.abid', $info_publication['p_abid'], '=');
    $info_publi = $search_chap->execute()->fetchAssoc();
    $search_chap_detail = db_select('reposi_article_book_detail', 'abd');
    $search_chap_detail->fields('abd')
            ->condition('abd.abd_abid', $info_publication['p_abid'], '=');
    $info_publi_2 = $search_chap_detail->execute()->fetchAssoc();
    $search_p_a_chap = db_select('reposi_publication_author', 'pa');
    $search_p_a_chap->fields('pa')
                  ->condition('pa.ap_abid', $info_publication['(Lp_abid'], '=');
    $couple_chap = $search_p_a_chap->execute();
    $authors_chap = array();
    foreach ($couple_chap as $aut_chap) {
      $search_aut = db_select('reposi_author', 'a');
      $search_aut->fields('a')
                 ->condition('a.aid', $aut_chap->ap_author_id, '=');
      $each_aut = $search_aut->execute()->fetchAssoc();
      $authors_chap[] = $each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] . ',' .
                    $each_aut['a_first_name'] . ' ' . $each_aut['a_second_name'];
    }
    $content = 'TY – CHAP'. '<br>';
    foreach ($authors_chap as $au) {
      $content .= 'AU – ' . $au . '<br>';
    }
    $content .= 'TI – '. $info_publi['ab_title'] . '<br>';
    $content .= 'PY – ' . $info_publication['p_year'].'<br>';
    if (!empty($info_publi_2['abd_volume'])) {
      $content .= 'VL – ' . $info_publi_2['abd_volume'] .'<br>';
    }
    if (!empty($info_publi_2['abd_issue'])) {
      $content .= 'IS – ' . $info_publi_2['abd_issue'] .'<br>';
    }
    $content .= 'T2 – ' . $info_publi['ab_subtitle_chapter'] .'<br>';
    if (!empty($info_publi['ab_publisher'])) {
      $content .= 'A2 – ' . $info_publi['ab_publisher'] . '<br>';
    }
    if (!empty($info_publi_2['abd_start_page'])) {
      $content .= 'SP – ' . $info_publi_2['abd_start_page'] .'<br>';
    }
    if (!empty($info_publi_2['abd_final_page'])) {
      $content .= 'EP – ' . $info_publi_2['abd_final_page'] .'<br>';
    }
    if (!empty($info_publi['ab_journal_editorial'])) {
      $content .= 'PB – ' . $info_publi['ab_journal_editorial'] .'<br>';
    }
    if (!empty($info_publi['ab_place'])) {
      $content .= 'CY – ' . $info_publi['ab_place'] .'<br>';
    }
    if (!empty($info_publi_2['abd_issn'])) {
      $content .= 'SN – ' . $info_publi_2['abd_issn'] .'<br>';
    }
    if (!empty($info_publi_2['abd_isbn'])) {
      $content .= 'SN – ' . $info_publi_2['abd_isbn'] .'<br>';
    }
    if (!empty($info_publi_2['abd_url'])) {
      $content .= 'UR – ' . $info_publi_2['abd_url']. '<br>';
    }
    if (!empty($info_publi_2['abd_doi'])) {
      $content .= 'DO – DOI: ' . $info_publi_2['abd_doi'] .'<br>';
    }
    $content .= 'ER';
    $form['body'] = array('#markup' => $content);
    return $form;
  } elseif ($info_publication['p_type'] == 'Conference'){

    ////////

    $content = 'TY – CPAPER'. '<br>';
    $search_con = db_select('reposi_confer_patent', 'cp');
    $search_con->fields('cp')
            ->condition('cp.cpid', $info_publication['p_cpid'], '=');
    $info_publi = $search_con->execute()->fetchAssoc();
    $search_date = db_select('reposi_date', 'd');
    $search_date->fields('d')
                ->condition('d.d_cpid', $info_publication['p_cpid'], '=');
    $con_date = $search_date->execute();
    $format_dates = array();
    foreach ($con_date as $dates) {
      $format_dates[] = Reposi_export::reposi_formt_date_ris($dates->d_day,$dates->d_month,$dates->d_year);
    }
    $search_p_a_con = db_select('reposi_publication_author', 'pa');
    $search_p_a_con->fields('pa')
                  ->condition('pa.ap_cpid', $info_publication['p_cpid'], '=');
    $couple_con = $search_p_a_con->execute();
    $authors_con = array();
    foreach ($couple_con as $aut_con) {
      $search_aut = db_select('reposi_author', 'a');
      $search_aut->fields('a')
                 ->condition('a.aid', $aut_con->ap_author_id, '=');
      $each_aut = $search_aut->execute()->fetchAssoc();
      $authors_con[] = $each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] . ',' .
                    $each_aut['a_first_name'] . ' ' . $each_aut['a_second_name'];
    }
    foreach ($authors_con as $au) {
      $content .= 'AU – ' . $au . '<br>';
    }
    $content .= 'TI – '. $info_publi['cp_publication'] . '<br>';
    $content .= 'DA – ' . $format_dates[0] .'<br>';
    if (!empty($info_publi['cp_start_page'])) {
      $content .= 'SP – ' . $info_publi['cp_start_page'] .'<br>';
    }
    if (!empty($info_publi['cp_final_page'])) {
      $content .= 'EP – ' . $info_publi['cp_final_page'] .'<br>';
    }
    $search_p_k_con = db_select('reposi_publication_keyword', 'pk');
    $search_p_k_con->fields('pk')
              ->condition('pk.pk_cpid', $info_publication['p_cpid'], '=');
    $keyword_con = $search_p_k_con->execute();
    $keyws_con = array();
    foreach ($keyword_con as $key_con) {
      $search_keyw = db_select('reposi_keyword', 'k');
      $search_keyw->fields('k')
                  ->condition('k.kid', $key_con->pk_keyword_id, '=');
      $keywords = $search_keyw->execute()->fetchAssoc();
      $keyws_con[] = $keywords['k_word'];
    }
    foreach ($keyws_con as $kw) {
      $content .= 'KW – ' . $kw . '<br>';
    }
    $content .= 'T2 – ' . $info_publi['cp_title'] . '<br>';
    if (!empty($info_publi['cp_url'])) {
      $content .= 'UR – ' . $info_publi['cp_url']. '<br>';
    }
    if (!empty($info_publi['cp_doi'])) {
      $content .= 'DO – DOI: ' . $info_publi['cp_doi'] .'<br>';
    }
    $content .= 'ER';
    $form['body'] = array('#markup' => $content);
    return $form;



    ///VECTOR POS 0 NODEFINE
  } elseif ($info_publication['p_type'] == 'Thesis'){


    $search_the = db_select('reposi_thesis_sw', 'sw');
    $search_the->fields('sw')
            ->condition('sw.tsid', $info_publication['p_tsid'], '=');
    $info_publi = $search_the->execute()->fetchAssoc();
    $search_date = db_select('reposi_date', 'd');
    $search_date->fields('d')
                ->condition('d.d_tsid', $info_publication['p_tsid'], '=');
    $the_date = $search_date->execute()->fetchAssoc();
    $search_p_a_the = db_select('reposi_publication_author', 'pa');
    $search_p_a_the->fields('pa')
                  ->condition('pa.ap_tsid', $info_publication['p_tsid'], '=');
    $couple_the = $search_p_a_the->execute();
    $authors_the = array();
    foreach ($couple_the as $aut_the) {
      $search_aut = db_select('reposi_author', 'a');
      $search_aut->fields('a')
                 ->condition('a.aid', $aut_the->ap_author_id, '=');
      $each_aut = $search_aut->execute()->fetchAssoc();
      $authors_the[] = $each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] . ',' .
                    $each_aut['a_first_name'] . ' ' . $each_aut['a_second_name'];
    }
    $content = 'TY – THES'. '<br>';
    foreach ($authors_the as $au) {
      $content .= 'AU – ' . $au . '<br>';
    }
    $content .= 'TI – '. $info_publi['ts_title'] . '<br>';
    $format_date = Reposi_export::reposi_formt_date_ris($the_date['d_day'],
                                         $the_date['d_month'],
                                         $the_date['d_year']);
    $content .= 'DA – ' . $format_date . '<br>';
    $search_p_k_the = db_select('reposi_publication_keyword', 'pk');
    $search_p_k_the->fields('pk')
                  ->condition('pk.pk_tsid', $info_publication['p_tsid'], '=');
    $keyword_the = $search_p_k_the->execute();
    $keyws_the = array();
    foreach ($keyword_the as $key_the) {
      $search_keyw = db_select('reposi_keyword', 'k');
      $search_keyw->fields('k')
                  ->condition('k.kid', $key_the->pk_keyword_id, '=');
      $keywords = $search_keyw->execute()->fetchAssoc();
      $keyws_the[] = $keywords['k_word'];
    }
    foreach ($keyws_the as $kw) {
      $content .= 'KW – ' . $kw . '<br>';
    }
    if (!empty($info_publi['ts_institu_ver'])) {
      $content .= 'PB – ' . $info_publi['ts_institu_ver'] .'<br>';
    }
    if (!empty($info_publi['ts_url'])) {
      $content .= 'UR – ' . $info_publi['ts_url']. '<br>';
    }
    $content .= 'ER';
    $form['body'] = array('#markup' => $content);
    return $form;
    ///////YA

  } elseif ($info_publication['p_type'] == 'Patent'){

    $search_pat = db_select('reposi_confer_patent', 'cp');
    $search_pat->fields('cp')
            ->condition('cp.cpid', $info_publication['p_cpid'], '=');
    $info_publi = $search_pat->execute()->fetchAssoc();
    $search_date = db_select('reposi_date', 'd');
    $search_date->fields('d')
                ->condition('d.d_cpid', $info_publication['p_cpid'], '=');
    $pat_date = $search_date->execute()->fetchAssoc();
    $search_p_a_pat = db_select('reposi_publication_author', 'pa');
    $search_p_a_pat->fields('pa')
                  ->condition('pa.ap_cpid', $info_publication['p_cpid'], '=');
    $couple_pat = $search_p_a_pat->execute();
    $authors_pat = array();
    foreach ($couple_pat as $aut_pat) {
      $search_aut = db_select('reposi_author', 'a');
      $search_aut->fields('a')
                 ->condition('a.aid', $aut_pat->ap_author_id, '=');
      $each_aut = $search_aut->execute()->fetchAssoc();
      $authors_pat[] = $each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] . ',' .
                    $each_aut['a_first_name'] . ' ' . $each_aut['a_second_name'];
    }
    $content = 'TY – PAT'. '<br>';
    foreach ($authors_pat as $au) {
      $content .= 'AU – ' . $au . '<br>';
    }
    $content .= 'TI – '. $info_publi['cp_title'] . '<br>';
    $format_date = Reposi_export::reposi_formt_date_ris($pat_date['d_day'],
                                         $pat_date['d_month'],
                                         $pat_date['d_year']);
    $content .= 'DA – ' . $format_date . '<br>';
    if (!empty($info_publi_2['cp_number'])) {
      $content .= 'IS – ' . $info_publi_2['cp_number'] .'<br>';
    }
    if (!empty($info_publi['cp_abstract'])) {
      $content .= 'N2 – ' . $info_publi['cp_abstract'] .'<br>';
    }
    if (!empty($info_publi['cp_spon_owner'])) {
      $content .= 'A2 – ' . $info_publi['cp_spon_owner'] .'<br>';
    }
    if (!empty($info_publi['cp_url'])) {
      $content .= 'UR – ' . $info_publi['cp_url']. '<br>';
    }
    $content .= 'ER';
    $form['body'] = array('#markup' => $content);
    return $form;

  } elseif ($info_publication['p_type'] == 'Software'){

    $search_sw = db_select('reposi_thesis_sw', 'sw');
    $search_sw->fields('sw')
            ->condition('sw.tsid', $info_publication['p_tsid'], '=');
    $info_publi = $search_sw->execute()->fetchAssoc();
    $search_p_a_sw = db_select('reposi_publication_author', 'pa');
    $search_p_a_sw->fields('pa')
                  ->condition('pa.ap_tsid', $info_publication['p_tsid'], '=');
    $couple_sw = $search_p_a_sw->execute();
    $authors_sw = array();
    foreach ($couple_sw as $aut_sw) {
      $search_aut = db_select('reposi_author', 'a');
      $search_aut->fields('a')
                 ->condition('a.aid', $aut_sw->ap_author_id, '=');
      $each_aut = $search_aut->execute()->fetchAssoc();
      $authors_sw[] = $each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] . ',' .
                    $each_aut['a_first_name'] . ' ' . $each_aut['a_second_name'];
    }
    $content = 'TY – GEN'. '<br>';
    foreach ($authors_sw as $au) {
      $content .= 'AU – ' . $au . '<br>';
    }
    if (!empty($info_publi['ts_discip_place'])) {
      $content .= 'CY – ' . $info_publi['ts_discip_place'] .'<br>';
    }
    $content .= 'TI – ' . $info_publi['ts_title'].'<br>';
    $content .= 'PY – ' . $info_publication['p_year'].'<br>';
    $content .= 'M3 – SOFTWARE'.'<br>';
    if (!empty($info_publi['ts_url'])) {
      $content .= 'UR – ' . $info_publi['ts_url'] . '<br>';
    }
    $content .= 'ER';
    $form['body'] = array('#markup' => $content);
    return $form;
///YAAAAAAAA

  }
else{

return $form;
}
}

/**
 * This show the date in the format DD/MM/YYYY.
 *
 * @param int $day
 *   Day in numbers.
 *
 * @param int $month
 *   Month in numbers.
 *
 * @param int $year
 *   Year with four numbers.
 */
public static function reposi_formt_date_ris($day, $month, $year){
  if (empty($month)) {
    $month_2 = '';
  } else {
    if ($month == 1) {
      $month_2 = '01';
    } elseif ($month == 2) {
      $month_2 = '02';
    } elseif ($month == 3) {
      $month_2 = '03';
    } elseif ($month == 4) {
      $month_2 = '04';
    } elseif ($month == 5) {
      $month_2 = '05';
    } elseif ($month == 6) {
      $month_2 = '06';
    } elseif ($month == 7) {
      $month_2 = '07';
    } elseif ($month == 8) {
      $month_2 = '08';
    } elseif ($month == 9) {
      $month_2 = '09';
    } elseif ($month == 10) {
      $month_2 = '10';
    } elseif ($month == 11) {
      $month_2 = '11';
    } else {
      $month_2 = '12';
    }
  }
  if (empty($day) && empty($month)) {
    $format_date_ris = $year;
  } elseif (empty($day)) {
    $format_date_ris = $year . '/' . $month_2;
  } else {
    $format_date_ris = $year . '/' . $month_2 . '/' . $day;
  }
  return $format_date_ris;
}


}
