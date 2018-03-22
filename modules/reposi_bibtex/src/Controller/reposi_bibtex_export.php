<?php
/**
 * Search metadata publications.
 *
 */
namespace Drupal\reposi_bibtex\Controller;
 use Drupal\Core\Database;
 use Drupal\Core\Url;
 use Drupal\Core\Link;
 use Drupal\reposi\Controller\Reposi_info_publication;

 class reposi_bibtex_export{

   function reposi_bibtex_format(){
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
       $search_art_detail->fields('abd')
               ->condition('abd.abd_abid', $info_publication['p_abid'], '=');
       $info_publi_2 = $search_art_detail->execute()->fetchAssoc();
       $search_p_a_art = db_select('reposi_publication_author', 'pa');
       $search_p_a_art->fields('pa')
                     ->condition('pa.ap_abid', $info_publication['p_abid'], '=');
       $couple_art = $search_p_a_art->execute();
       $authors_art = array();
       foreach ($couple_art as $aut_art) {
         $authors_art[] = reposi_bibtex_export::reposi_author_bibtex($aut_art->ap_author_id);
       }
       $content = '@article{'. '<br>';
       $content .= 'authors = "';
       $first_author = 0;
       foreach ($authors_art as $au) {
         $first_author++;
         if ($first_author == 1) {
         	$content .= $au;
         } else {
         	$content .= ' and ' . $au;
         }
       }
       $content .= '",' . '<br>';
       $new_title = reposi_bibtex_export::reposi_bibtex($info_publi['ab_title']);
       $content .= 'title = "' . $new_title . '",'. '<br>';
       if (!empty($info_publi['ab_journal_editorial'])) {
         $new_journal = reposi_bibtex_export::reposi_bibtex($info_publi['ab_journal_editorial']);
         $content .= 'journal = "' . $new_journal . '",' . '<br>';
       }
       if (!empty($info_publi_2['abd_volume'])) {
         $new_vol = reposi_bibtex_export::reposi_bibtex($info_publi_2['abd_volume']);
         $content .= 'volume = "' . $new_vol . '",' .'<br>';
       }
       if (!empty($info_publi_2['abd_issue'])) {
         $new_num = reposi_bibtex_export::reposi_bibtex($info_publi_2['abd_issue']);
         $content .= 'number = "' . $new_num . '",' .'<br>';
       }
       if (!empty($info_publi_2['abd_start_page']) &&
       	!empty($info_publi_2['abd_final_page'])) {
         $content .= 'pages = "' . $info_publi_2['abd_start_page'] . '-' .
         			  $info_publi_2['abd_final_page'] . '",' .'<br>';
       }
       $content .= 'year = "' . $info_publication['p_year'] . '",' .'<br>';
       if (!empty($info_publi['ab_abstract'])) {
         $new_abs = reposi_bibtex_export::reposi_bibtex($info_publi['ab_abstract']);
         $content .= 'abstract = "' . $new_abs . '",' .'<br>';
       }
   	$search_p_k_art = db_select('reposi_publication_keyword', 'pk');
       $search_p_k_art->fields('pk')
   	                ->condition('pk.pk_abid', $info_publication['p_abid'], '=');
   	$keyword_art = $search_p_k_art->execute();
   	foreach ($keyword_art as $key_art) {
         $search_keyw = db_select('reposi_keyword', 'k');
   	  $search_keyw->fields('k')
   	              ->condition('k.kid', $key_art->pk_keyword_id, '=');
   	  $keywords = $search_keyw->execute()->fetchAssoc();
   	  $keyws_art[] = $keywords['k_word'];
   	}
   	if (isset($keyws_art[0])) {
   		$content .= 'keywords = "';
   		$first_kw = 0;
   	    foreach ($keyws_art as $kw) {
   	      $first_kw++;
   	      $new_kw = reposi_bibtex_export::reposi_bibtex($kw);
   	      if ($first_kw == 1) {
   	      	$content .= $new_kw;
   	      } else {
   	      	$content .= ', ' . $new_kw;
   	      }
   		}
   		$content .= '",' . '<br>';
   	}
   	if (!empty($info_publi_2['abd_issn'])) {
         $content .= 'ISSN = "' . $info_publi_2['abd_issn'] . '",' . '<br>';
       }
       if (!empty($info_publi_2['abd_url'])) {
         $content .= 'URL = "' . $info_publi_2['abd_url']. '",' . '<br>';
       }
       if (!empty($info_publi_2['abd_doi'])) {
         $content .= 'doi = "' . $info_publi_2['abd_doi'] . '",' . '<br>';
       }
   	$content .= '}';
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
         $authors_book[] = reposi_bibtex_export::reposi_author_bibtex($aut_book->ap_author_id);
       }
       $content = '@book{'. '<br>';
       $content .= 'authors = "';
       $first_author = 0;
       foreach ($authors_book as $au) {
         $first_author++;
         if ($first_author == 1) {
         	$content .= $au;
         } else {
         	$content .= ' and ' . $au;
         }
       }
       $content .= '",' . '<br>';
       $new_title = reposi_bibtex_export::reposi_bibtex($info_publi['ab_title']);
       $content .= 'title = "' . $new_title . '",' .'<br>';
       if (!empty($info_publi['ab_subtitle_chapter'])) {
         $new_sub = reposi_bibtex_export::reposi_bibtex($info_publi['ab_subtitle_chapter']);
         $content .= 'booktitle = "' . $new_sub . '",' .'<br>';
       }
       if (!empty($info_publi['ab_abstract'])) {
         $new_abs = reposi_bibtex_export::reposi_bibtex($info_publi['ab_abstract']);
         $content .= 'abstract = "' . $new_abs . '",' .'<br>';
       }
       if (!empty($info_publi['ab_publisher'])) {
         $new_publisher = reposi_bibtex_export::reposi_bibtex($info_publi['ab_publisher']);
         $content .= 'editor = "' . $new_publisher . '",' . '<br>';
       }
       if (!empty($info_publi['ab_journal_editorial'])) {
         $new_editorial = reposi_bibtex_export::reposi_bibtex($info_publi['ab_journal_editorial']);
         $content .= 'publisher = "' . $new_editorial . '",' .'<br>';
       }
       $content .= 'year = "' . $info_publication['p_year'] . '",' .'<br>';
       if (!empty($info_publi['ab_place'])) {
         $new_place = reposi_bibtex_export::reposi_bibtex($info_publi['ab_place']);
         $content .= 'address = "' . $new_place . '",' .'<br>';
       }
       if (!empty($info_publi_2['abd_volume'])) {
         $new_vol = reposi_bibtex_export::reposi_bibtex($info_publi_2['abd_volume']);
         $content .= 'volume = "' . $new_vol . '",' .'<br>';
       }
       if (!empty($info_publi_2['abd_issue'])) {
         $new_num = reposi_bibtex_export::reposi_bibtex($info_publi_2['abd_issue']);
         $content .= 'number = "' . $new_num . '",' .'<br>';
       }
       if (!empty($info_publi_2['abd_issn'])) {
         $content .= 'ISSN = "' . $info_publi_2['abd_issn'] . '",' .'<br>';
       }
       if (!empty($info_publi_2['abd_isbn'])) {
         $content .= 'ISBN = "' . $info_publi_2['abd_isbn'] . '",' .'<br>';
       }
       if (!empty($info_publi['ab_language'])) {
         $new_language = reposi_bibtex_export::reposi_bibtex($info_publi['ab_language']);
         $content .= 'language = "' . $new_language . '",' .'<br>';
       }
       if (!empty($info_publi_2['abd_url'])) {
         $content .= 'URL = "' . $info_publi_2['abd_url'] . '",' . '<br>';
       }
       if (!empty($info_publi_2['abd_doi'])) {
         $content .= 'doi = "' . $info_publi_2['abd_doi'] . '",' . '<br>';
       }
   	$content .= '}';
     } elseif ($info_publication['p_type'] == 'Book Chapter'){
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
                     ->condition('pa.ap_abid', $info_publication['p_abid'], '=');
       $couple_chap = $search_p_a_chap->execute();
       $authors_chap = array();
       foreach ($couple_chap as $aut_chap) {
         $authors_chap[] = reposi_bibtex_export::reposi_author_bibtex($aut_chap->ap_author_id);
       }
       $content = '@incollection{'. '<br>';
       $content .= 'authors = "';
       $first_author = 0;
       foreach ($authors_chap as $au) {
         $first_author++;
         if ($first_author == 1) {
         	$content .= $au;
         } else {
         	$content .= ' and ' . $au;
         }
       }
       $content .= '",' . '<br>';
       $new_title = reposi_bibtex_export::reposi_bibtex($info_publi['ab_title']);
       $content .= 'title = "' . $new_title . '",' .'<br>';
       if (!empty($info_publi['ab_subtitle_chapter'])) {
         $new_sub = reposi_bibtex_export::reposi_bibtex($info_publi['ab_subtitle_chapter']);
         $content .= 'booktitle = "' . $new_sub . '",' .'<br>';
       }
       if (!empty($info_publi['ab_chapter'])) {
         $content .= 'chapter = "' . $info_publi['ab_chapter'] . '",' .'<br>';
       }
       $content .= 'year = "' . $info_publication['p_year'] . '",' .'<br>';
       if (!empty($info_publi['ab_publisher'])) {
         $new_publisher = reposi_bibtex_export::reposi_bibtex($info_publi['ab_publisher']);
         $content .= 'editor = "' . $new_publisher . '",' . '<br>';
       }
       if (!empty($info_publi['ab_journal_editorial'])) {
         $new_editorial = reposi_bibtex_export::reposi_bibtex($info_publi['ab_journal_editorial']);
         $content .= 'publisher = "' . $new_editorial . '",' .'<br>';
       }
       if (!empty($info_publi_2['abd_volume'])) {
         $new_vol = reposi_bibtex_export::reposi_bibtex($info_publi_2['abd_volume']);
         $content .= 'volume = "' . $new_vol . '",' .'<br>';
       }
       if (!empty($info_publi_2['abd_issue'])) {
         $new_num = reposi_bibtex_export::reposi_bibtex($info_publi_2['abd_issue']);
         $content .= 'number = "' . $new_num . '",' .'<br>';
       }
       if (!empty($info_publi_2['abd_start_page']) &&
       	!empty($info_publi_2['abd_final_page'])) {
         $content .= 'pages = "' . $info_publi_2['abd_start_page'] . '-' .
     				  $info_publi_2['abd_final_page'] . '",' .'<br>';
       }
       if (!empty($info_publi['ab_place'])) {
         $new_place = reposi_bibtex_export::reposi_bibtex($info_publi['ab_place']);
         $content .= 'address = "' . $new_place . '",' .'<br>';
       }
       if (!empty($info_publi_2['abd_issn'])) {
         $content .= 'ISSN = "' . $info_publi_2['abd_issn'] . '",' .'<br>';
       }
       if (!empty($info_publi_2['abd_isbn'])) {
         $content .= 'ISBN = "' . $info_publi_2['abd_isbn'] . '",' .'<br>';
       }
       if (!empty($info_publi_2['abd_url'])) {
         $content .= 'URL = "' . $info_publi_2['abd_url'] . '",' . '<br>';
       }
       if (!empty($info_publi_2['abd_doi'])) {
         $content .= 'doi = "' . $info_publi_2['abd_doi'] . '",' . '<br>';
       }
   	$content .= '}';
     } elseif ($info_publication['p_type'] == 'Conference'){
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
         $format_dates[] = array($dates->d_month,$dates->d_year);
       }
       $search_p_a_con = db_select('reposi_publication_author', 'pa');
       $search_p_a_con->fields('pa')
                     ->condition('pa.ap_cpid', $info_publication['p_cpid'], '=');
       $couple_con = $search_p_a_con->execute();
       $authors_con = array();
       foreach ($couple_con as $aut_con) {
         $authors_con[] = reposi_bibtex_export::reposi_author_bibtex($aut_con->ap_author_id);
       }
       $content = '@inproceedings{'. '<br>';
       $content .= 'authors = "';
       $first_author = 0;
       foreach ($authors_con as $au) {
         $first_author++;
         if ($first_author == 1) {
         	$content .= $au;
         } else {
         	$content .= ' and ' . $au;
         }
       }
       $content .= '",' . '<br>';
       $new_title = reposi_bibtex_export::reposi_bibtex($info_publi['cp_publication']);
       $content .= 'title = "' . $new_title . '",' .'<br>';
       $new_conference = reposi_bibtex_export::reposi_bibtex($info_publi['cp_title']);
       $content .= 'booktitle = "' . $new_conference . '",' .'<br>';
       if (isset($format_dates[0][0])) {
         $new_month = reposi_month_letter($format_dates[0][0]);
         $content .= 'month = "' . $new_month . '",' .'<br>';
       }
       $content .= 'year = "' . $info_publication['p_year'] . '",' .'<br>';
       if (!empty($info_publi['cp_start_page']) &&
       	!empty($info_publi['cp_final_page'])) {
         $content .= 'pages = "' . $info_publi['cp_start_page'] . '-' .
     				  $info_publi['cp_final_page'] . '",' .'<br>';
       }
       if (!empty($info_publi['cp_place_type'])) {
         $new_place = reposi_bibtex_export::reposi_bibtex($info_publi['cp_place_type']);
         $content .= 'address = "' . $new_place . '",' . '<br>';
       }
       if (!empty($info_publi['cp_abstract'])) {
         $new_abs = reposi_bibtex_export::reposi_bibtex($info_publi['cp_abstract']);
         $content .= 'abstract = "' . $new_abs . '",' .'<br>';
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
   	if (isset($keyws_con[0])) {
   		$content .= 'keywords = "';
   		$first_kw = 0;
   	    foreach ($keyws_con as $kw) {
   	      $first_kw++;
   	      $new_kw = reposi_bibtex_export::reposi_bibtex($kw);
   	      if ($first_kw == 1) {
   	      	$content .= $new_kw;
   	      } else {
   	      	$content .= ', ' . $new_kw;
   	      }
   		}
   		$content .= '",' . '<br>';
   	}
       if (!empty($info_publi['cp_url'])) {
         $content .= 'URL = "' . $info_publi['cp_url'] . '",' . '<br>';
       }
       if (!empty($info_publi['cp_doi'])) {
         $content .= 'doi = "' . $info_publi['cp_doi'] . '",' . '<br>';
       }
       if (!empty($info_publi['cp_spon_owner'])) {
         $new_owmer = reposi_bibtex_export::reposi_bibtex($info_publi['cp_spon_owner']);
         $content .= 'note = "' . t('Sponsor(s): ') . $new_owmer . '",' .'<br>';
       }
   	$content .= '}';
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
         $authors_the[] = reposi_bibtex_export::reposi_author_bibtex($aut_the->ap_author_id);
       }
       if ($info_publi['ts_degree'] == 'Master’s Degree') {
       	$content = '@mastersthesis{'. '<br>';
       } elseif ($info_publi['ts_degree'] == 'PhD thesis'){
       	$content = '@phdthesis{'. '<br>';
       } else {
       	$content = '@unpublished{'. '<br>';
       }
       $content .= 'authors = "';
       $first_author = 0;
       foreach ($authors_the as $au) {
         $first_author++;
         if ($first_author == 1) {
         	$content .= $au;
         } else {
         	$content .= ' and ' . $au;
         }
       }
       $content .= '",' . '<br>';
       $new_title = reposi_bibtex_export::reposi_bibtex($info_publi['ts_title']);
       $content .= 'title = "' . $new_title . '",' .'<br>';
       if (!empty($info_publi['ts_institu_ver'])) {
         $new_ver = reposi_bibtex_export::reposi_bibtex($info_publi['ts_institu_ver']);
         $content .= 'school = "' . $new_ver . '",' .'<br>';
       }
       if (!empty($the_date['d_month'])) {
         $new_month = reposi_bibtex_export::reposi_month_letter($the_date['d_month']);
   	  $content .= 'month = "' . $new_month . '",' .'<br>';
       }
       $content .= 'year = "' . $info_publication['p_year'] . '",' .'<br>';
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
   	if (isset($keyws_the[0])) {
   		$content .= 'keywords = "';
   		$first_kw = 0;
   	    foreach ($keyws_the as $kw) {
   	      $first_kw++;
   	      $new_kw = reposi_bibtex_export::reposi_bibtex($kw);
   	      if ($first_kw == 1) {
   	      	$content .= $new_kw;
   	      } else {
   	      	$content .= ', ' . $new_kw;
   	      }
   		}
   		$content .= '",' . '<br>';
   	}
       if (!empty($info_publi['ts_url'])) {
         $content .= 'URL = "' . $info_publi['ts_url'] . '",' . '<br>';
       }
       if (!empty($info_publi['ts_discip_place'])) {
         $new_disc = reposi_bibtex_export::reposi_bibtex($info_publi['ts_discip_place']);
         $content .= 'note = "' . t('Discipline: ') . $new_disc . '",' . '<br>';
       }
   	$content .= '}';
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
         $authors_pat[] = reposi_bibtex_export::reposi_author_bibtex($aut_pat->ap_author_id);
       }
       $content = '@patent{'. '<br>';
       $content .= 'authors = "';
       $first_author = 0;
       foreach ($authors_pat as $au) {
         $first_author++;
         if ($first_author == 1) {
         	$content .= $au;
         } else {
         	$content .= ' and ' . $au;
         }
       }
       $content .= '",' . '<br>';
       $new_title = reposi_bibtex_export::reposi_bibtex($info_publi['cp_title']);
       $content .= 'title = "' . $new_title . '",' .'<br>';
       $content .= 'year = "' . $info_publication['p_year'] . '",' .'<br>';
       if (!empty($info_publi_2['cp_number'])) {
         $content .= 'number = "' . $info_publi['cp_number'] . '",' .'<br>';
       }
       if (!empty($info_publi['cp_abstract'])) {
         $new_abs = reposi_bibtex_export::reposi_bibtex($info_publi['cp_abstract']);
         $content .= 'abstract = "' . $new_abs . '",' .'<br>';
       }
       if (!empty($info_publi['cp_url'])) {
         $content .= 'URL = "' . $info_publi['cp_url'] . '",' . '<br>';
       }
       if (!empty($info_publi['cp_spon_owner'])) {
         $new_owmer = reposi_bibtex_export::reposi_bibtex($info_publi['cp_spon_owner']);
         $content .= 'note = "' . t('Owner(s): ') . $new_owmer . '",' .'<br>';
       }
   	$content .= '}';
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
         $authors_sw[] = reposi_bibtex_export::reposi_author_bibtex($aut_sw->ap_author_id);
       }
       $content = '@misc{'. '<br>';
       $content .= 'authors = "';
       $first_author = 0;
       foreach ($authors_sw as $au) {
         $first_author++;
         if ($first_author == 1) {
         	$content .= $au;
         } else {
         	$content .= ' and ' . $au;
         }
       }
       $content .= '",' . '<br>';
       $new_title = reposi_bibtex_export::reposi_bibtex($info_publi['ts_title']);
       $content .= 'title = "' . $new_title . '",' .'<br>';
       $content .= 'year = "' . $info_publication['p_year'] . '",' .'<br>';
       if (!empty($info_publi['ts_discip_place'])) {
         $new_place = reposi_bibtex_export::reposi_bibtex($info_publi['ts_discip_place']);
         $content .= 'address = "' . $new_place . '",' . '<br>';
       }
       if (!empty($info_publi['ts_url'])) {
         $content .= 'URL = "' . $info_publi['ts_url'] . '",' . '<br>';
       }
       if (!empty($info_publi['ts_institu_ver'])) {
         $new_ver = reposi_bibtex_export::reposi_bibtex($info_publi['ts_institu_ver']);
         $content .= 'note = "' . t('Version: ') . $new_ver . '",' .'<br>';
       }
   	$content .= '}';
     }
     $form['body'] = array('#markup' => $content);
     return $form;
   }

   public static function reposi_author_bibtex($aid){
   	$search_aut = db_select('reposi_author', 'a');
       $search_aut->fields('a')
                  ->condition('a.aid', $aid, '=');
       $each_aut = $search_aut->execute()->fetchAssoc();
       $f_name = reposi_bibtex_export::reposi_bibtex($each_aut['a_first_name']);
       $f_lastname = reposi_bibtex_export::reposi_bibtex($each_aut['a_first_lastname']);
       $s_lastname = reposi_bibtex_export::reposi_bibtex($each_aut['a_second_lastname']);
       $backslash = reposi_bibtex_export::reposi_bibtex('á');
       $author = '';
       if ($f_name[0] == $backslash[0]) {
         $bit_fname = substr($f_name,0,5);
         if (!empty($each_aut['a_second_name'])) {
         	$s_name = reposi_bibtex_export::reposi_bibtex($each_aut['a_second_name']);
     		if ($s_name[0] == $backslash[0]) {
     			$bit_sname = substr($s_name,0,5);
     			$author = $f_lastname . ' ' . $s_lastname . ', ' .
                        	$bit_fname . '. ' . $bit_sname . '.';
     		} else {
     			$author = $f_lastname . ' ' . $s_lastname . ', ' .
                       	$bit_fname . '. ' . $s_name[0] . '.';
     		}
         } else {
           $author = $f_lastname . ' ' . $s_lastname . ', ' .
                       $bit_fname . '.';
         }
       } else {
         if (!empty($each_aut['a_second_name'])) {
   	    $s_name = reposi_bibtex_export::reposi_bibtex($each_aut['a_second_name']);
   	    if ($s_name[0] == $backslash[0]) {
     			$bit_sname = substr($s_name,0,5);
     			$author = $f_lastname . ' ' . $s_lastname . ', ' .
                        	$f_name[0] . '. ' . $bit_sname . '.';
     		} else {
     			$author = $f_lastname . ' ' . $s_lastname . ', ' .
                       	$f_name[0] . '. ' . $s_name[0] . '.';
     		}
   	  } else {
   	    $author = $f_lastname . ' ' . $s_lastname . ', ' .
   	              $f_name[0] . '.';
   	  }
       }
       return $author;
   }

   public static function reposi_month_letter($month){
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
   	return $month_letter;
   }

   public static function reposi_bibtex($text){
	    $text = trim($text);
	    $text = str_replace(
      array('á',     'à',     'ä',     'â',     'ã'    , 'å' ,  'ă', 'ª',
      		'Á',     'À',     'Â',     'Ä',     'Ã',     'Å',   'Ă'),
      array("\'{a}", '\`{a}', '\"{a}', '\^{a}', '\~{a}', '\aa', '\ua','\textordfeminine',
      		"\'{A}", '\`{A}', '\^{A}', '\"{A}', '\~{A}', '\AA', '\uA'),
      $text
    );
    $text = str_replace(
      array('é',     'è',     'ë',     'ê',     'ě',	 'É', 	  'È', 	   'Ê',     'Ë',     'Ě'),
      array("\'{e}", '\`{e}', '\"{e}', '\^{e}', '\v{e}', "\'{E}", '\`{E}', '\^{E}', '\"{E}', '\v{E}'),
      $text
    );
    $text = str_replace(
      array('í',      'ì',      'ï',      'î',      'Í',     'Ì',     'Î',     'Ï'),
      array("\'{\i}", '\`{\i}', '\"{\i}', '\^{\i}', "\'{I}", '\`{I}', '\^{I}', '\"{I}'),
      $text
    );
    $text = str_replace(
      array('ó',     'ò',     'ö',     'ô',     'õ',     'ő',
      		'Ó',     'Ò',     'Ô',     'Ö',     'Õ',     'Ő',    'º'),
      array("\'{o}", '\`{o}', '\"{o}', '\^{o}', '\~{o}', '\H{o}',
      		"\'{O}", '\`{O}', '\^{O}', '\"{O}', '\~{O}', '\H{O}', '\textordmasculine'),
      $text
    );
    $text = str_replace(
        array('ú',     'ù',     'ü',     'û',     'ű',     'Ú',     'Ù',     'Û',     'Ü',    'Ű'),
        array("\'{u}", '\`{u}', '\"{u}', '\^{u}', '\Hu',  "\'{U}", '\`{U}', '\^{U}', '\"{U}', '\HU'),
        $text
    );
    $text = str_replace(
      array('ñ',     'Ñ',     'ç',     'Ç' ,    'ş',     'Ş',     'ø',  'Ø',  'ý',  'Ý',   'ÿ',   'Ÿ',
    		'ć',    'Ć',    '&', '%', '$', '#'),
      array('\~{n}', '\~{N}', '\c{c}', '\c{C}', '\c{s}', '\c{S}', '\o', '\O', "\'y","\'Y", '\"y', '\"Y',
      		"\'{c}","\'{C}",'\&','\%','\$','\#'),
      $text
    );
    $text = str_replace(
      array('¡',              '°',          '®',              '©',             '¢',        '£',
      		'¥',       '¿',                '§',           '€',        '™',             'ƒ',
      		'¹',               '²',               '³',                 'λ'),
      array('\textexclamdown','\textdegree','\textregistered','\textcopyright','\textcent','\textsterling',
      		'\textyen','\textquestiondown','\textsection','\texteuro','\texttrademark','\textflorin',
      		'\textonesuperior','\texttwosuperior','\textthreesuperior','{$\lambda$}'),
      $text
    );
    $text = str_replace(
      array('æ',   'Æ',   'œ',   'Œ',   'ð',   'Ð',   'þ',   'Þ',   'ß',   'đ','Đ'),
      array('\ae', '\AE', '\oe', '\OE', '\dh', '\DH', '\th', '\TH', '\ss', '\dj', '\DJ'),
      $text
    );
    return $text;
    }

}//end class
