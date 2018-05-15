<?php
/**
 * Search metadata publications.
 *
 */
namespace Drupal\reposi_apischolar\Controller;
  use Drupal\Core\Database;
  use Drupal\Core\Form\ConfigFormBase;
  use Drupal\Core\Url;
  use Drupal\Core\Link;
  use Drupal\reposi\Controller\Reposi_info_publication;
  use Drupal\reposi_apischolar\Form\reposi_apischolar_admin;
  use Drupal\Component\Utility\Html;
  use Drupal\Component\Serialization\Json;
  use GuzzleHttp\Exception;

 class reposidoc_scholar extends reposi_apischolar_admin{

public static function docs_scholar(){
	$form['body'] = array();
        $config = \Drupal::config('system.maintenance');
	$query_scholar_size = $config->get('query_scholar_size');
		$search_author_state = db_select('reposi_state', 's');
		$search_author_state->fields('s', array('s_uid'))
		                    ->condition('s.s_type', 'Active', '=');
		$id_author_active = $search_author_state->execute();
		$author_full_name = array();
		foreach ($id_author_active as $author_active) {
		    $search_author_idscholar = db_select('reposi_user', 'p');
		    $search_author_idscholar->fields('p', array('uid', 'u_first_name', 'u_second_name', 'u_first_lastname',
		                   			  'u_second_lastname', 'u_id_scholar'))
						            ->condition('p.uid', $author_active->s_uid, '=')
						            ->orderBy('u_first_lastname', 'ASC');
						       $pager=$search_author_idscholar->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(10);
		    $author_info[] = $pager->execute()->fetchAssoc();

	    }
}

function reposi_author_scholar(){

        $config = ConfigFormBase::config('system.maintenance');
	$query_scholar_size = $config->get('query_scholar_size');
        
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
		$authorscount=count($authors_name);
		if ($authorscount<1) {drupal_set_message('AUTHOR NAME: '.$authorscount);
		}
	    	if ($authors_name['u_id_scholar']==NULL && $authorscount>1) {
		    	$all_authors_scholar_info = '';
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
					$author_name = $search_name_1. ' '. $search_lastname_1 . ' ' . $search_lastname_2;
                                	$search_author_scholar='http://localhost/apiGS/getuser.php?fname='. $search_name_1 .
					'&sname=' . '' . '&flast=' . $search_lastname_1 . '&slast=' . $search_lastname_2;
				} 
				elseif ((!empty($search_name_2)) && (!empty($search_name_1))) {
					$author_name = $search_name_1 . ' ' . $search_name_2. ' '. $search_lastname_1 . ' ' . $search_lastname_2;
                                	$search_author_scholar='http://localhost/apiGS/getuser.php?fname='. $search_name_1 .
					'&sname=' . $search_name_2  . '&flast=' . $search_lastname_1 . '&slast=' . $search_lastname_2;
				}	
			//	$form['bodyborrar'][$search_name_1] = array('#markup' => ' '.$search_name_1);
			//	$search_author_scholar='http://localhost/googlescholar-api/googlescholar.php?fname=eduardo&sname&flast=rojas&slast=';
				if (!empty($search_author_scholar)) {
					/*$get_info_authors = file_get_contents($search_author_scopus);
					$num_results = explode('totalResults":"', $get_info_authors);
					$number_results = explode('","opensearch:startIndex', $num_results[1]);
 					///////*/
					$client = \Drupal::httpClient();
 					try {
        					$jsonData = json_encode($_POST);    
						$headers = ['Content-Type' => 'application/json'];
    						$response = $client->request('POST', $search_author_scholar, ['timeout' => 600, 'headers'=>$headers,'body' => 							$jsonData]);
					      //  $response = $client->request('GET', '/delay/5', ['timeout' => 3.14]);
    						$data = $response->getBody();
						//$decoded = Json::decode($data);
    						$scholar_user = explode('{', $data);
    						$scholar_data = explode('"Name": "', $data);
						$data_number = count($scholar_data);
						$scholar_info = explode('",',$data);
						$header = array(t('Google Scholar ID'), t('Name'), t('Affiliation'));
					//	drupal_set_message('count:'.$data_number. ' SCHOLAR DATA'.print_r($scholar_data,true));
    						$form['scholar'][$search_lastname_1] = array(
       							'#type' => 'details',
       							'#open' => TRUE,
       							'#title' => $author_name,
							'#description' => 'Associate with a Google Scholar User',
    						);/*
$ch = curl_init($search_author_scholar);	
    						    if (curl_errno($ch)) {
      $this->error = 'cURL connection error ('.curl_errno($ch).'): '.htmlspecialchars(curl_error($ch)).' <a href="http://www.google.com/search?q='.urlencode("curl error ".curl_error($ch)).'">Search</a>';
      $this->connected = false;
    } else {
      $this->connected = true;
    }*/						$form['scholar'][$search_lastname_1]['table']  = array(
       							'#type' => 'table',
       							'#title' => 'Scholar Author Table',
       							'#header' => $header,
       							'#empty' => t('No lines found'),
    						);
						for($i=1; $i<$data_number; $i++)
                                                {
                                                 $scholar_name = explode('",', $scholar_data[$i]);
						// drupal_set_message(print_r($scholar_name,true));
						 $scholar_info = explode('"institution": "', $scholar_name[1]);
						 $scholar_user = explode('"user": "', $scholar_name[2]);
						 $scholar_id_user = substr($scholar_user[1],-27,12);
						 $scholar_info = $scholar_info[1];
						 $scholar_name = $scholar_name[0];
  						 $rows = array($scholar_id_user,$scholar_name,$scholar_info);
					//	 drupal_set_message('NOMBRE: '.$scholar_name.' INFORMACIÓN: '.$scholar_info.' USER SCHOLAR: '.$scholar_id_user);

						 $link_scholar = \Drupal::l($scholar_id_user, Url::fromRoute('reposi.reposi_apischolar.scholar_assoc', ['node'=>$authors_name['uid'], 'nod'=>$scholar_id_user]));
    						 $form['scholar'][$search_lastname_1]['table'][$i]['id_author_scholar'] = array('#markup' => $link_scholar);
    						 $form['scholar'][$search_lastname_1]['table'][$i]['author_name_scholar'] = array('#markup' => $scholar_name);
    						 $form['scholar'][$search_lastname_1]['table'][$i]['author_information_scholar'] = array('#markup'=>$scholar_info);}

  						}
 				        	catch (RequestException $e) {
   							watchdog_exception('reposi', $e->getMessage());
							return array();
  						}	
				}
					/*if ($number_results[0] != 0) {
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
							$search_author_scopus = 'https://api.elsevier.com/content/search/author?query=authlastname(' .
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
				}*/
			}
	    }
		//$number_authors = count($dates_authors);

	/*****************************************
	*****************************************/
    //$form['pager']=['#type' => 'pager'];






/*

	    $form['aut_sdin_id'] = array(
		    '#title' => t('User(s) without Scopus ID Author'),
		    '#type' => 'fieldset',
	    );
	    for ($i=0; $i < $number_authors; $i++) {
	    	$form['aut_sdin_id']['body_' . $i] = array('#markup' => $dates_authors[$i]);
	    }
*/
		return $form;
	
}

///////
/*
  public static function testdocs_scopus(){
    $search_publi = db_select('reposi_user','p');
    $arg=3;
    $search_publi->fields('p',array('u_id_scopus'))
                 ->condition('uid',$arg, '=');
    $idscopus = $search_publi->execute()->fetchField();
    $pre=$idscopus;
    $numeromas =88;
    $idscopus=$idscopus.$numeromas;
      db_update('reposi_user')->fields(array(
        'u_id_scopus'  => $idscopus,
      ))->condition('uid', $arg)
      ->execute();
      $message = '<p>' . '<b>' . '<big>' . 'Hola prueba que cambio. ' .$pre.'</big>'.'</b>'.'</p>'.$idscopus;
  		$form['message'] = array('#markup' => $message);
      return $form;
}
*/
//End class
}
