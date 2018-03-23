<?php
/**
 * Search metadata publications.
 *
 */
namespace Drupal\reposi_apiscopus\Controller;
  use Drupal\Core\Database;
  use Drupal\Core\Form\ConfigFormBase;
  use Drupal\Core\Url;
  use Drupal\Core\Link;
  use Drupal\reposi\Controller\Reposi_info_publication;
  use Drupal\reposi_apiscopus\Form\reposi_apiscopus_admin;

 class reposidoc_scopus extends reposi_apiscopus_admin{

function docs_scopus(){
  $config = $this->config('system.maintenance');
	$apikey_scopus = $config->get('reposi_apiscopus_key');
	$apikey_query_start = $config->get('query_start');
	$apikey_query_final = $config->get('query_final');
	if (empty($apikey_scopus)) {
		drupal_set_message('You must configure the module Repository -
			Scopus Search API to use all its functions.', 'warning');
		$message = '<p>' . '<b>' . '<big>' . 'First enter the APIKey from the
		configuration tab.' . '</big>'.'</b>'.'</p>'.$apikey_scopus;
		$form['message'] = array('#markup' => $message);
	    return $form;
	} else {
		$search_author_state = db_select('reposi_state', 's');
		$search_author_state->fields('s', array('s_uid'))
		                    ->condition('s.s_type', 'Active', '=');
		$id_author_active = $search_author_state->execute();
		$author_full_name = array();
		foreach ($id_author_active as $author_active) {
		    $search_author_idscopus = db_select('reposi_user', 'p');
		    $search_author_idscopus->fields('p', array('uid', 'u_first_name', 'u_second_name', 'u_first_lastname',
		                   			  'u_second_lastname', 'u_id_scopus'))
						            ->condition('p.uid', $author_active->s_uid, '=')
						            ->orderBy('u_first_lastname', 'ASC');
						       $pager=$search_author_idscopus->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(10);
		    $author_info[] = $pager->execute()->fetchAssoc();
	    }
	    $eid_doc_scopus = array();
      $form['body'] = array();
	    $num_articles = 0;
		$num_books = 0;
		$num_book_chaps = 0;
	    foreach ($author_info as $id_scopus) {
		    $title_doc_scopus = array();
		    $pre_date_scopus = array();
		    $date_scopus = array();
		    $pre_type_scopus = array();
		    $type_doc_scopus = array();
		    $num_articles = 0;
		    $num_books = 0;
		    $num_book_chaps = 0;
	    	if (!empty($id_scopus['u_id_scopus'])) {
	    		$author_id = $id_scopus['u_id_scopus'];
	    		$search_doc = 'http://api.elsevier.com/content/search/scopus?query=au-id(' .
	    		$author_id . ')&start=' . $apikey_query_start . '&count=' . $apikey_query_final .
	    		'&apikey=' . $apikey_scopus;
	    		$get_info_docs = file_get_contents($search_doc);
	    		$num_docs = explode('totalResults":"', $get_info_docs);
	    		$number_docs = explode('","opensearch:startIndex', $num_docs[1]);
	    		$search_eid = explode('"eid":"', $get_info_docs);
	    		$flag_intro = -1;
	    		foreach ($search_eid as $eids) {
	    			$flag_intro++;
	    			if ($flag_intro > 0) {
	    				$eid_doc_scopus[] = explode('","dc:title":"', $eids);
	    			}
	    		}
	    		foreach ($eid_doc_scopus as $title_docs) {
	    			$title_doc_scopus[] = explode('","dc:creator":"', $title_docs[1]);
	    		}
	    		foreach ($title_doc_scopus as $this_date) {
	    			$pre_date_scopus[] = explode('prism:coverDate":"', $this_date[1]);
	    		}
	    		foreach ($pre_date_scopus as $dates) {
	    			$date_scopus[] = explode('","prism:', $dates[1]);
	    		}
	    		foreach ($pre_date_scopus as $type) {
	    			$pre_type_scopus[] = explode('subtypeDescription":"', $type[1]);
	    		}
	    		foreach ($pre_type_scopus as $type_docs) {
	    			$type_doc_scopus[] = explode('","', $type_docs[1]);
	    		}
	    		$number_eids = count($eid_doc_scopus);
	    		for ($i=0; $i < $number_eids; $i++) {
	    			if ($type_doc_scopus[$i][0] == 'Article' || $type_doc_scopus[$i][0] == 'Review'){
	    				//|| $type_doc_scopus[$i][0] == 'Article in Press') {
			    		$pre_journal = explode('prism:publicationName":"', $title_doc_scopus[$i][1]);
			    		$journal_name = explode('","', $pre_journal[1]);
			    		$pre_issn = explode('prism:issn":"', $pre_journal[1]);
			    		if (isset($pre_issn[1])) {
			    			$issn = explode('","', $pre_issn[1]);
			    		} else {
			    			$issn = array('');
			    		}
			    		$pre_isbn = explode('prism:isbn":"', $pre_journal[1]);
			    		if (isset($pre_isbn[1])) {
			    			$isbn = explode('","', $pre_isbn[1]);
			    		} else {
			    			$isbn = array('');
			    		}
			    		$pre_volume = explode('prism:volume":"', $pre_journal[1]);
			    		if (isset($pre_volume[1])) {
			    			$volume = explode('","', $pre_volume[1]);
			    		} else {
			    			$volume = array('');
			    		}
			    		$pre_issue = explode('prism:issueIdentifier":"', $pre_journal[1]);
			    		if (isset($pre_issue[1])) {
			    			$issue = explode('","', $pre_issue[1]);
			    		} else {
			    			$issue = array('');
			    		}
			    		$pre_pages = explode('prism:pageRange":"', $pre_journal[1]);
			    		if (isset($pre_pages[1])) {
			    			$pages = explode('","', $pre_pages[1]);
			    			$per_pages = explode('-', $pages[0]);
			    		} else {
			    			$per_pages = array('','');
			    		}
			    		$pre_doi = explode('prism:doi":"', $pre_journal[1]);
			    		if (isset($pre_doi[1])) {
			    			$doi = explode('","', $pre_doi[1]);
			    		} else {
			    			$doi = array('');
			    		}
			    		$start_page = (int)$per_pages[0];
			    		if (isset($per_pages[1])) {
			    			$final_page = (int)$per_pages[1];
			    		} else {
			    			$final_page = NULL;
			    		}


	    				$search_art = db_select('reposi_article_book', 'ab');
					    $search_art->fields('ab')
					          ->condition('ab.ab_type', 'Article', '=')
					          ->condition('ab.ab_title', $title_doc_scopus[$i][0], '=');
					    $find_art = $search_art->execute();
					    $art_id = $find_art->fetchField();
              $find_art -> allowRowCount = TRUE;
					    $find_something = $find_art->rowCount();
					    if ($find_something == '0') {
					    	db_insert('reposi_article_book')->fields(array(
						        'ab_type'              => 'Article',
						        'ab_title'             => $title_doc_scopus[$i][0],
						        'ab_journal_editorial' => $journal_name[0],
						    ))->execute();
						    $search_arti = db_select('reposi_article_book', 'ab');
						    $search_arti->fields('ab')
						          ->condition('ab.ab_type', 'Article', '=')
						          ->condition('ab.ab_title', $title_doc_scopus[$i][0], '=');
						    $art_id2 = $search_arti->execute()->fetchField();
						    $fields_date = explode('-', $date_scopus[$i][0]);
						    db_insert('reposi_date')->fields(array(
						        'd_day'  => $fields_date[2],
						        'd_month'=> $fields_date[1],
						        'd_year' => $fields_date[0],
						        'd_abid' => $art_id2,
						    ))->execute();
						    db_insert('reposi_publication')->fields(array(
						        'p_type'       => 'Article',
						        'p_title'      => $title_doc_scopus[$i][0],
						        'p_year'       => $fields_date[0],
						        'p_check'      => 0,
						        'p_eid_scopus' => $eid_doc_scopus[$i][0],
						        'p_abid'       => $art_id2,
						    ))->execute();
						    if (!empty($issn[0]) || !empty($isbn[0]) || !empty($volume[0]) || !empty($issue[0]) ||
						    	!empty($per_pages[0]) || !empty($doi[0])) {
						    	db_insert('reposi_article_book_detail')->fields(array(
							        'abd_volume'     => $volume[0],
							        'abd_issue'      => $issue[0],
							        'abd_start_page' => $start_page,
							        'abd_final_page' => $final_page,
							        'abd_issn'       => $issn[0],
							        'abd_isbn'       => $isbn[0],
							        'abd_doi'        => $doi[0],
							        'abd_abid'       => $art_id2,
							    ))->execute();
						    }
					    } else {
					    	$search_arti = db_select('reposi_article_book', 'ab');
						    $search_arti->fields('ab')
						          ->condition('ab.ab_type', 'Article', '=')
						          ->condition('ab.ab_title', $title_doc_scopus[$i][0], '=');
						    $art_id3 = $search_arti->execute()->fetchField();
						    if (!empty($issn[0]) || !empty($isbn[0]) || !empty($volume[0]) || !empty($issue[0]) ||
						    	!empty($per_pages[0]) || !empty($doi[0])) {
						    	$search_artic = db_select('reposi_article_book_detail', 'abd');
							    $search_artic->fields('abd')
							          ->condition('abd.abd_abid', $art_id3, '=');
							    $art_det_id = $search_artic->execute()->fetchField();
						    	if (empty($art_det_id)) {
						    		db_insert('reposi_article_book_detail')->fields(array(
								        'abd_volume'     => $volume[0],
								        'abd_issue'      => $issue[0],
								        'abd_start_page' => $start_page,
								        'abd_final_page' => $final_page,
								        'abd_issn'       => $issn[0],
								        'abd_isbn'       => $isbn[0],
								        'abd_doi'        => $doi[0],
								        'abd_abid'       => $art_id3,
								    ))->execute();
						    	} else {
						    		db_update('reposi_article_book_detail')->fields(array(
								        'abd_volume'     => $volume[0],
								        'abd_issue'      => $issue[0],
								        'abd_start_page' => $start_page,
								        'abd_final_page' => $final_page,
								        'abd_issn'       => $issn[0],
								        'abd_isbn'       => $isbn[0],
								        'abd_doi'        => $doi[0],
								    ))->condition('abd_abid', $art_id3)
							    	->execute();
						    	}
						    }
					    }
					    $num_articles++;
	    			}
	    			elseif ($type_doc_scopus[$i][0] == 'Book') {
	    			 	//dpm('Libro**********: ' . $title_doc_scopus[$i][1]);
	    			 	//dpm($date_scopus[$i][0]);
	    			 	//dpm($title_doc_scopus[$i][0]);
	    			 	//dpm($eid_doc_scopus[$i][0]);
	    			 	$pre_name = explode('prism:publicationName":"', $title_doc_scopus[$i][1]);
			    		$book_name = explode('","', $pre_name[1]);
			    		//dpm($book_name[0]);

			    		$pre_isbn_book = explode('prism:isbn":"', $pre_name[1]);
			    		if (isset($pre_isbn_book[1])) {
			    			$isbn_book = explode('","', $pre_isbn_book[1]);
			    		} else {
			    			$isbn_book = array('');
			    		}
			    		//dpm('ISBN: ' . $isbn_book[0]);
			    		$pre_volume_book = explode('prism:volume":"', $pre_name[1]);
			    		if (isset($pre_volume_book[1])) {
			    			$volume_book = explode('","', $pre_volume_book[1]);
			    		} else {
			    			$volume_book = array('');
			    		}
			    		//dpm('Vol: ' . $volume_book[0]);
			    		$pre_pages_book = explode('prism:pageRange":"', $pre_name[1]);
			    		if (isset($pre_pages_book[1])) {
			    			$pages_book = explode('","', $pre_pages_book[1]);
			    			$per_pages_book = explode('-', $pages_book[0]);
			    		} else {
			    			$per_pages_book = array('','');
			    		}
			    		//dpm('Pages: ' . $per_pages_book[0]);
			    		$start_page_book = (int)$per_pages_book[0];
			    		//dpm('SPag: ' . $start_page_book);
			    		if (isset($per_pages_book[1])) {
			    			$final_page_book = (int)$per_pages_book[1];
			    		} else {
			    			$final_page_book = NULL;
			    		}
			    		//dpm('FPag : ' . $final_page_book);





			    		$search_book = db_select('reposi_article_book', 'ab');
					    $search_book->fields('ab')
					          ->condition('ab.ab_type', 'Book', '=')
					          ->condition('ab.ab_title', $title_doc_scopus[$i][0], '=');
					    $find_book = $search_book->execute();
					    $book_id = $find_book->fetchField();
              $find_book -> allowRowCount = TRUE;
					    $find_something = $find_book->rowCount();
					    if ($find_something == '0') {
					    	db_insert('reposi_article_book')->fields(array(
						        'ab_type'              => 'Book',
						        'ab_title'             => $title_doc_scopus[$i][0],
						    ))->execute();
						    $search_book2 = db_select('reposi_article_book', 'ab');
						    $search_book2->fields('ab')
						          ->condition('ab.ab_type', 'Book', '=')
						          ->condition('ab.ab_title', $title_doc_scopus[$i][0], '=');
						    $book_id2 = $search_book2->execute()->fetchField();
						    $fields_date_book = explode('-', $date_scopus[$i][0]);
						    db_insert('reposi_date')->fields(array(
						        'd_day'  => $fields_date_book[2],
						        'd_month'=> $fields_date_book[1],
						        'd_year' => $fields_date_book[0],
						        'd_abid' => $book_id2,
						    ))->execute();
						    db_insert('reposi_publication')->fields(array(
						        'p_type'       => 'Book',
						        'p_title'      => $title_doc_scopus[$i][0],
						        'p_year'       => $fields_date_book[0],
						        'p_check'      => 0,
						        'p_eid_scopus' => $eid_doc_scopus[$i][0],
						        'p_abid'       => $book_id2,
						    ))->execute();
						    if (!empty($isbn_book[0]) || !empty($volume_book[0]) ||
						    	!empty($per_pages_book[0])) {
						    	db_insert('reposi_article_book_detail')->fields(array(
							        'abd_volume'     => $volume_book[0],
							        'abd_start_page' => $start_page_book,
							        'abd_final_page' => $final_page_book,
							        'abd_isbn'       => $isbn_book[0],
							        'abd_abid'       => $book_id2,
							    ))->execute();
						    }
					    } else {
					    	$search_book3 = db_select('reposi_article_book', 'ab');
						    $search_book3->fields('ab')
						          ->condition('ab.ab_type', 'Book', '=')
						          ->condition('ab.ab_title', $title_doc_scopus[$i][0], '=');
						    $book_id3 = $search_book3->execute()->fetchField();
						    if (!empty($isbn_book[0]) || !empty($volume_book[0]) ||
						    	!empty($per_pages_book[0])) {
						    	$search_book_det = db_select('reposi_article_book_detail', 'abd');
							    $search_book_det->fields('abd')
							          ->condition('abd.abd_abid', $book_id3, '=');
							    $book_det_id = $search_book_det->execute()->fetchField();
						    	if (empty($book_det_id)) {
						    		db_insert('reposi_article_book_detail')->fields(array(
									    'abd_volume'     => $volume_book[0],
								        'abd_start_page' => $start_page_book,
								        'abd_final_page' => $final_page_book,
								        'abd_isbn'       => $isbn_book[0],
								        'abd_abid'       => $book_id3,
								    ))->execute();
						    	} else {
						    		db_update('reposi_article_book_detail')->fields(array(
								        'abd_volume'     => $volume_book[0],
								        'abd_start_page' => $start_page_book,
								        'abd_final_page' => $final_page_book,
								        'abd_isbn'       => $isbn_book[0],
								    ))->condition('abd_abid', $book_id3)
							    	->execute();
						    	}
						    }
					    }





			    		$num_books++;
	    			}
	    			elseif ($type_doc_scopus[$i][0] == 'Chapter') {
	    			 	//dpm('Chap**********: ' . $title_doc_scopus[$i][1]);
	    			 	//dpm($date_scopus[$i][0]);
	    			 	//dpm($title_doc_scopus[$i][0]); //Nombre del capítulo
	    			 	//dpm($eid_doc_scopus[$i][0]);
	    			 	$pre_book_name = explode('prism:publicationName":"', $title_doc_scopus[$i][1]);
			    		$book_chap_name = explode('","', $pre_book_name[1]);
			    		//dpm($book_chap_name[0]); //Nombre del libro

			    		$pre_isbn_chap = explode('prism:isbn":"', $pre_book_name[1]);
			    		if (isset($pre_isbn_chap[1])) {
			    			$isbn_chap = explode('","', $pre_isbn_chap[1]);
			    		} else {
			    			$isbn_chap = array('');
			    		}
			    		//dpm('ISBN: ' . $isbn_chap[0]);
			    		$pre_volume_chap = explode('prism:volume":"', $pre_book_name[1]);
			    		if (isset($pre_volume_chap[1])) {
			    			$volume_chap = explode('","', $pre_volume_chap[1]);
			    		} else {
			    			$volume_chap = array('');
			    		}
			    		//dpm('Vol: ' . $volume_chap[0]);
			    		$pre_pages_chap = explode('prism:pageRange":"', $pre_book_name[1]);
			    		if (isset($pre_pages_chap[1])) {
			    			$pages_chap = explode('","', $pre_pages_chap[1]);
			    			$per_pages_chap = explode('-', $pages_chap[0]);
			    		} else {
			    			$per_pages_chap = array('','');
			    		}
			    		//dpm('Pages: ' . $per_pages_chap[0]);
			    		$start_page_chap = (int)$per_pages_chap[0];
			    		//dpm('SPag: ' . $start_page_chap);
			    		if (isset($per_pages_chap[1])) {
			    			$final_page_chap = (int)$per_pages_chap[1];
			    		} else {
			    			$final_page_chap = NULL;
			    		}
			    		//dpm('FPAg: ' . $final_page_chap);
			    		$pre_doi_chap = explode('prism:doi":"', $pre_book_name[1]);
			    		if (isset($pre_doi_chap[1])) {
			    			$doi_chap = explode('","', $pre_doi_chap[1]);
			    		} else {
			    			$doi_chap = array('');
			    		}
			    		//dpm($doi_chap[0]);





			    		$search_chap = db_select('reposi_article_book', 'ab');
					    $search_chap->fields('ab')
					          ->condition('ab.ab_type', 'Book Chapter', '=')
					          ->condition('ab.ab_title', $book_chap_name[0], '=')
          					  ->condition('ab.ab_subtitle_chapter', $title_doc_scopus[$i][0], '=');
					    $find_chap = $search_chap->execute();
					    $chap_id = $find_chap->fetchField();
              $find_chap -> allowRowCount = TRUE;
					    $find_something = $find_chap->rowCount();
					    if ($find_something == '0') {
					    	db_insert('reposi_article_book')->fields(array(
						        'ab_type'              => 'Book Chapter',
						        'ab_title'             => $book_chap_name[0],
          						'ab_subtitle_chapter'  => $title_doc_scopus[$i][0],
						    ))->execute();
						    $search_chap2 = db_select('reposi_article_book', 'ab');
						    $search_chap2->fields('ab')
						          ->condition('ab.ab_type', 'Book Chapter', '=')
						          ->condition('ab.ab_title', $book_chap_name[0], '=')
          					  	  ->condition('ab.ab_subtitle_chapter', $title_doc_scopus[$i][0], '=');
						    $chap_id2 = $search_chap2->execute()->fetchField();
						    $fields_date_chap = explode('-', $date_scopus[$i][0]);
						    db_insert('reposi_date')->fields(array(
						        'd_day'  => $fields_date_chap[2],
						        'd_month'=> $fields_date_chap[1],
						        'd_year' => $fields_date_chap[0],
						        'd_abid' => $chap_id2,
						    ))->execute();
						    db_insert('reposi_publication')->fields(array(
						        'p_type'       => 'Book Chapter',
						        'p_title'      => $title_doc_scopus[$i][0],
						        'p_year'       => $fields_date_chap[0],
						        'p_check'      => 0,
						        'p_eid_scopus' => $eid_doc_scopus[$i][0],
						        'p_abid'       => $chap_id2,
						    ))->execute();
						    if (!empty($isbn_chap[0]) || !empty($volume_chap[0]) ||
						    	!empty($per_pages_chap[0]) || !empty($doi_chap[0])) {
						    	db_insert('reposi_article_book_detail')->fields(array(
							        'abd_volume'     => $volume_chap[0],
							        'abd_start_page' => $start_page_chap,
							        'abd_final_page' => $final_page_chap,
							        'abd_isbn'       => $isbn_chap[0],
							        'abd_doi'        => $doi_chap[0],
							        'abd_abid'       => $chap_id2,
							    ))->execute();
						    }
					    } else {
					    	$search_chap3 = db_select('reposi_article_book', 'ab');
						    $search_chap3->fields('ab')
						          ->condition('ab.ab_type', 'Book Chapter', '=')
						          ->condition('ab.ab_title', $book_chap_name[0], '=')
          					  	  ->condition('ab.ab_subtitle_chapter', $title_doc_scopus[$i][0], '=');
						    $chap_id3 = $search_chap3->execute()->fetchField();
						    if (!empty($isbn_chap[0]) || !empty($volume_chap[0]) ||
						    	!empty($per_pages_chap[0]) || !empty($doi_chap[0])) {
						    	$search_chap_det = db_select('reposi_article_book_detail', 'abd');
							    $search_chap_det->fields('abd')
							          ->condition('abd.abd_abid', $chap_id3, '=');
							    $chap_det_id = $search_chap_det->execute()->fetchField();
						    	if (empty($chap_det_id)) {
						    		db_insert('reposi_article_book_detail')->fields(array(
									    'abd_volume'     => $volume_chap[0],
								        'abd_start_page' => $start_page_chap,
								        'abd_final_page' => $final_page_chap,
								        'abd_isbn'       => $isbn_chap[0],
								        'abd_doi'        => $doi_chap[0],
								        'abd_abid'       => $chap_id3,
								    ))->execute();
						    	} else {
						    		db_update('reposi_article_book_detail')->fields(array(
								        'abd_volume'     => $volume_chap[0],
								        'abd_start_page' => $start_page_chap,
								        'abd_final_page' => $final_page_chap,
								        'abd_isbn'       => $isbn_chap[0],
								        'abd_doi'        => $doi_chap[0],
								    ))->condition('abd_abid', $chap_id3)
							    	->execute();
						    	}
						    }
					    }






			    		$num_book_chaps++;
	    			}
	    		}
	    	}
	    }
		$eid_doc = array();
		$eids_doc = array();
		foreach ($eid_doc_scopus as $eid) {
			$search_eid_doc = db_select('reposi_publication', 'p');
		    $search_eid_doc->fields('p', array('p_eid_scopus'))
		             ->condition('p.p_eid_scopus', $eid[0], '=');
		    $eids_doc[] = $search_eid_doc->execute()->fetchField();
		}
		$eid_doc = array_filter($eids_doc);
		$simplify_docs = array_unique($eid_doc);
		foreach ($simplify_docs as $docs) {
			$url_scopus_abstract = 'http://api.elsevier.com/content/search/scopus?query=eid(' .
			$docs .')&field=dc:description&apikey=' . $apikey_scopus;
			$search_abstract = file_get_contents($url_scopus_abstract);
			$pre_abstract = explode('dc:description":"', $search_abstract);
			if (isset($pre_abstract[1])) {
				$abstract = explode('"}]}}', $pre_abstract[1]);
			} else {
				$abstract = array('');
			}
			$url_scopus_author = 'http://api.elsevier.com/content/search/scopus?query=eid(' .
			$docs . ')&field=author&start=' . $apikey_query_start . '&count=' .
			$apikey_query_final . '&apikey=' . $apikey_scopus;
			$get_other_authors = file_get_contents($url_scopus_author);
			$search_pid = db_select('reposi_publication', 'p');
			$search_pid->fields('p', array('p_abid', 'p_eid_scopus'))
		               ->condition('p.p_eid_scopus', $docs, '=');
		    $get_p_abid = $search_pid->execute()->fetchField();
			$search_abs_doc = db_select('reposi_article_book', 'ab');
			$search_abs_doc->fields('ab', array('ab_abstract', 'abid'))
						   ->condition('ab.abid', $get_p_abid, '=');
			$find_abs = $search_abs_doc->execute()->fetchField();
			if (empty($find_abs)) {
				db_update('reposi_article_book')->fields(array(
			      'ab_abstract' => $abstract[0],
			    ))->condition('abid', $get_p_abid)
			    ->execute();
			}
			$search_aut = explode('"@seq": "', $get_other_authors);
			$simplify_auts = array_unique($search_aut);
			$find_seq_aut = array();
			foreach ($simplify_auts as $seq_aut) {
				$find_seq_aut[] = explode('", "author-url', $seq_aut);
			}
			$flag_aut = -1;
			$info_auth = array();
			foreach ($find_seq_aut as $get_aut) {
				$flag_aut++;
				if (($flag_aut == $get_aut[0]) && isset($get_aut[1])) {
					$search_aut_id = explode('authid":"', $get_aut[1]);
					$get_aut_id = explode('","authname', $search_aut_id[1]);
					$surname_aut = explode('surname":"', $get_aut[1]);
					$get_surname = explode('","given-name":"', $surname_aut[1]);
					$get_name = explode('","initials', $get_surname[1]);
					$info_auth[] = $get_aut_id[0] . ', ' . $get_surname[0] . ' ' . $get_name[0];
					$serch_a = db_select('reposi_author', 'a');
			        $serch_a->fields('a')
			              ->condition('a.a_id_scopus', $get_aut_id[0], '=');
			        $serch_aut = $serch_a->execute()->fetchField();
			        if (empty($serch_aut)) {
				        db_insert('reposi_author')->fields(array(
				        	'a_id_scopus'       => $get_aut_id[0],
				        	'a_first_name'      => $get_name[0],
	                        'a_second_name'     => '',
	                        'a_first_lastname'  => $get_surname[0],
	                        'a_second_lastname' => '',
	                    ))->execute();
				        $serch2_a = db_select('reposi_author', 'a');
				        $serch2_a ->fields('a')
				                  ->condition('a.a_id_scopus', $get_aut_id[0], '=');
				        $serch2_aut = $serch2_a->execute()->fetchField();
				        $aut_publi_id = (int)$serch2_aut;
				        db_insert('reposi_publication_author')->fields(array(
				          'ap_author_id' => $aut_publi_id,
				          'ap_abid'      => $get_p_abid,
				        ))->execute();
			        } else {
				        $aut_publi_id2 = (int)$serch_aut;
				        $search_relation = db_select('reposi_publication_author', 'pa');
						$search_relation->fields('pa')
					               		->condition('pa.ap_author_id', $aut_publi_id2, '=')
					               		->condition('pa.ap_abid', $get_p_abid, '=');
					    $relation_pa = $search_relation->execute()->fetchField();
					    if (empty($relation_pa)) {
					    	db_insert('reposi_publication_author')->fields(array(
					            'ap_author_id' => $aut_publi_id2,
					            'ap_abid'      => $get_p_abid,
					        ))->execute();
					    }
			        }
				}
			}
		}
		$info_show = $num_articles . t(' Articles were found with
	    			the ID Author on Scopus.') . '<br>' .
					$num_books . t(' Books were found with
	    			the ID Author on Scopus.') . '<br>' .
					$num_book_chaps . t(' Book Chapters were found with
	    			the ID Author on Scopus.');
              $form['pager']=['#type' => 'pager'];
	    $form['doc'] = array(
	      '#title' => t('Documents'),
	      '#type' => 'details',
	      '#open' => TRUE,
	    );
	    $form['doc']['body'] = array('#markup' => $info_show);
		return $form;
	}
}

function reposi_author_scopus(){
	global $base_url;
  $config = ConfigFormBase::config('system.maintenance');
	$apikey_scopus = $config->get('reposi_apiscopus_key');
	$apikey_query_start = $config->get('query_start');
	$apikey_query_final = $config->get('query_final');
	if (empty($apikey_scopus)) {
		drupal_set_message('You must configure the module Repository -
			Scopus Search API to use all its functions.', 'warning');
		$message = '<p>' . '<b>' . '<big>' . 'First enter the APIKey from the
		configuration tab.' . '</big>'.'</b>'.'</p>';
		$form['message'] = array('#markup' => $message);
	    return $form;
	} else {
	/*****************************************
	Info dinámica de un autor por nombre
	*****************************************/
		$search_author_state = db_select('reposi_state', 's');
		$search_author_state->fields('s', array('s_uid'))
		                    ->condition('s.s_type', 'Active', '=');
		$id_author_act_state = $search_author_state->execute();
		$author_full_name = array();
		foreach ($id_author_act_state as $author_act) {
		    $search_author_full_name = db_select('reposi_user', 'p');
		    $search_author_full_name->fields('p')
						            ->condition('p.uid', $author_act->s_uid, '=')
						            ->orderBy('u_first_lastname', 'ASC');
        $pager=$search_author_full_name->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(10);
		    $author_full_name[] = $pager->execute()->fetchAssoc();
	    }
      $form['body'] = array();
	    $dates_authors = array();
	    foreach ($author_full_name as $authors_name) {
	    	if (empty($authors_name['u_id_scopus'])) {
		    	$all_authors_scopus_info = '';
			    $authors_eid_catch = array();
			    $author_lastname = array();
			    $author_affilname = array();
			    $author_name = array();
			    $author_lname = array();
			    $aut_affil_name = array();
			    $author_aff_place = array();
			    $aut_affil_country = array();
		    	$search_lastname_1 = Reposi_info_publication::reposi_string($authors_name['u_first_lastname']);
				$search_lastname_2 = Reposi_info_publication::reposi_string($authors_name['u_second_lastname']);
				$search_name_1 = Reposi_info_publication::reposi_string($authors_name['u_first_name']);
				$search_name_2 = Reposi_info_publication::reposi_string($authors_name['u_second_name']);
				if ((empty($search_name_2)) && (!empty($search_name_1))) {
					$search_author_scopus = 'http://api.elsevier.com/content/search/author?query=authlastname(' .
						$search_lastname_1 . '+' . $search_lastname_2 . ')+AND+authfirst(' . $search_name_1 .
						')&start=' . $apikey_query_start . '&count=' . $apikey_query_final . '&apikey=' .
						$apikey_scopus;
				} elseif ((!empty($search_name_2)) && (!empty($search_name_1))) {
					$search_author_scopus = 'http://api.elsevier.com/content/search/author?query=authlastname(' .
						$search_lastname_1 . '+' . $search_lastname_2 . ')+AND+authfirst(' . $search_name_1 .
						'+' . $search_name_2 . ')&start=' . $apikey_query_start . '&count=' . $apikey_query_final .
						'&apikey=' . $apikey_scopus;
				}
				if (!empty($search_author_scopus)) {
					$get_info_authors = file_get_contents($search_author_scopus);
					$num_results = explode('totalResults":"', $get_info_authors);
					$number_results = explode('","opensearch:startIndex', $num_results[1]);
					if ($number_results[0] != 0) {
						$find_scopus_authors = explode('"AUTHOR_ID:', $get_info_authors);
						$flag_search_authors = -1;
						foreach ($find_scopus_authors as $authors) {
							$flag_search_authors++;
							if ($flag_search_authors > 0) {
								$authors_eid_catch[] = explode('","eid', $authors);
								$author_lastname[] = explode('preferred-name":{"surname":"', $authors);
								$author_affilname[] = explode('"affiliation-name":"', $authors);
							}
						}
						foreach ($author_lastname as $aut_lname) {
							$author_name[] = explode('","given-name":"', $aut_lname[1]);
						}
						foreach ($author_name as $lastname) {
							$author_lname[] = explode('","initials"', $lastname[1]);
						}
						foreach ($author_affilname as $affiln) {
							if (isset($affiln[1])) {
								$aut_affil_name[] = explode('","affiliation-city":', $affiln[1]);
							} else {
								$aut_affil_name[] = '';
							}
						}
						foreach ($aut_affil_name as $affi_country) {
							if (isset($affi_country[1])) {
								$author_aff_place[] = explode('"affiliation-country":', $affi_country[1]);
							} else {
								$author_aff_place[] = '';
							}
						}
						foreach ($author_aff_place as $affil_country) {
							if (isset($affil_country[1])) {
								$aut_affil_country[] = explode('}', $affil_country[1]);
							} else {
								$aut_affil_country[] = '';
							}
						}
						$num_authors_eid = count($authors_eid_catch);
						$all_authors_scopus_info = '<p>'.'<b>'.'<big>'. $authors_name['u_first_lastname'] .
						' ' . $authors_name['u_second_lastname'] . ' ' . $authors_name['u_first_name'] .
						' ' . $authors_name['u_second_name'] . '</big>'.'</b>'.'</p>'.
						'<table>' . '<tr>'. '<td>' . '<strong>' . 'Author ID' .
						'</strong>' . '</td>' . '<td>' . '<strong>' . 'Name' . '</strong>' . '</td>' .
					  	'<td>' . '<strong>' . 'Affiliation' . '</strong>' . '</td>' . '</tr>';
						for ($i=0; $i < $num_authors_eid; $i++) {
							if (isset($aut_affil_country[$i][0])) {
								if (($aut_affil_country[$i][0] == 'null')) {
									$aut_country = '';
								} else {
									$aut_country = $aut_affil_country[$i][0];
								}
							} else {
								$aut_country = '';
							}
							if (isset($aut_affil_name[$i][0])) {
								$affiliation_name = $aut_affil_name[$i][0];
							} else {
								$affiliation_name = '';
							}

							$all_authors_scopus_info .= '<tr>'. '<td>' . \Drupal::l($authors_eid_catch[$i][0],
                Url::fromRoute('reposi.reposi_apiscopus.scopus_assoc',['node'=>$authors_name['uid'],'nod'=>$authors_eid_catch[$i][0]])) . '</td>' .
						  	'<td>' . $author_name[$i][0] . ', ' . $author_lname[$i][0] . '</td>' .
						  	'<td>' . $affiliation_name . '. ' . $aut_country .
						  	'</td>' . '</tr>';
						}
						$all_authors_scopus_info .= '</table>' . '<br>';
					} else {
						if ((!empty($search_name_1)) && (!empty($search_lastname_1))) {
							$search_author_scopus = 'http://api.elsevier.com/content/search/author?query=authlastname(' .
								$search_lastname_1 . ')+AND+authfirst(' . $search_name_1 . ')&start=' .
								$apikey_query_start . '&count=' . $apikey_query_final . '&apikey=' .
								$apikey_scopus;
						}
						$get_info_authors = file_get_contents($search_author_scopus);
						$num_results = explode('totalResults":"', $get_info_authors);
						$number_results = explode('","opensearch:startIndex', $num_results[1]);
						if ($number_results[0] != 0) {
							$find_scopus_authors = explode('"AUTHOR_ID:', $get_info_authors);
							$flag_search_authors = -1;
							foreach ($find_scopus_authors as $authors) {
								$flag_search_authors++;
								if ($flag_search_authors > 0) {
									$authors_eid_catch[] = explode('","eid', $authors);
									$author_lastname[] = explode('preferred-name":{"surname":"', $authors);
									$author_affilname[] = explode('"affiliation-name":"', $authors);
								}
							}
							foreach ($author_lastname as $aut_lname) {
								$author_name[] = explode('","given-name":"', $aut_lname[1]);
							}
							foreach ($author_name as $lastname) {
								$author_lname[] = explode('","initials"', $lastname[1]);
							}
							foreach ($author_affilname as $affiln) {
								if (isset($affiln[1])) {
									$aut_affil_name[] = explode('","affiliation-city":', $affiln[1]);
								} else {
									$aut_affil_name[] = '';
								}
							}
							foreach ($aut_affil_name as $affi_country) {
								if (isset($affi_country[1])) {
									$author_aff_place[] = explode('"affiliation-country":', $affi_country[1]);
								} else {
									$author_aff_place[] = '';
								}
							}
							foreach ($author_aff_place as $affil_country) {
								if (isset($affil_country[1])) {
									$aut_affil_country[] = explode('}', $affil_country[1]);
								} else {
									$aut_affil_country[] = '';
								}
							}
							$num_authors_eid = count($authors_eid_catch);
							$all_authors_scopus_info = '<p>'.'<b>'.'<big>'. $authors_name['u_first_lastname'] .
							' ' . $authors_name['u_second_lastname'] . ' ' . $authors_name['u_first_name'] .
							' ' . $authors_name['u_second_name'] . '</big>'.'</b>'.'</p>'.
							'<table>' . '<tr>'. '<td>' . '<strong>' . 'Author ID' .
							'</strong>' . '</td>' . '<td>' . '<strong>' . 'Name' . '</strong>' . '</td>' .
						  	'<td>' . '<strong>' . 'Affiliation' . '</strong>' . '</td>' . '</tr>';
							for ($i=0; $i < $num_authors_eid; $i++) {
								if (isset($aut_affil_country[$i][0])) {
									if (($aut_affil_country[$i][0] == 'null')) {
										$aut_country = '';
									} else {
										$aut_country = $aut_affil_country[$i][0];
									}
								} else {
									$aut_country = '';
								}
								if (isset($aut_affil_name[$i][0])) {
									$affiliation_name = $aut_affil_name[$i][0];
								} else {
									$affiliation_name = '';
								}
								$all_authors_scopus_info .= '<tr>'. '<td>' . \Drupal::l($authors_eid_catch[$i][0],
                Url::fromRoute('reposi.reposi_apiscopus.scopus_assoc',['node'=>$authors_name['uid'],'nod'=>$authors_eid_catch[$i][0]])) . '</td>' .
                '</td>' . '<td>' . $author_name[$i][0] . ', ' . $author_lname[$i][0] .
								'</td>' . '<td>' . $affiliation_name . '. ' . $aut_country .
							  	'</td>' . '</tr>';
							}
							$all_authors_scopus_info .= '</table>' . '<br>';
						} else {
							$all_authors_scopus_info = '<p>'.'<b>'.'<big>'. $authors_name['u_first_lastname'] .
							' ' . $authors_name['u_second_lastname'] . ' ' . $authors_name['u_first_name'] .
							' ' . $authors_name['u_second_name'] . '</big>' . '</b>' . '</p>' . 'No match' .
							'<br>' . '<br>';
						}
					}
					$dates_authors[] = $all_authors_scopus_info;
				}
			}
	    }
		$number_authors = count($dates_authors);

	/*****************************************
	*****************************************/
    $form['pager']=['#type' => 'pager'];








	    $form['aut_sdin_id'] = array(
		    '#title' => t('User(s) without Scopus ID Author'),
		    '#type' => 'fieldset',
	    );
	    for ($i=0; $i < $number_authors; $i++) {
	    	$form['aut_sdin_id']['body_' . $i] = array('#markup' => $dates_authors[$i]);
	    }

		return $form;
	}
}
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

//End class
}
