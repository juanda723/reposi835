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

	$config = \Drupal::config('system.maintenance');
	$query_size_scholar = $config->get('query_scholar_size');
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
	$form['body'] = array();
	foreach ($author_info as $id_scholar) {
		$scholar_user_id[] = $id_scholar['u_id_scholar'];
		$reposi_user_id[] = $id_scholar['uid'];
	}
	
	for($i=1; $i<count($scholar_user_id); $i++){
		if(!empty($scholar_user_id[$i])) {
			$search_doc[$i] = 'http://localhost/apiGS/getallpublication.php?user='.$scholar_user_id[$i].$query_size_scholar;
			$data[$i]= file_get_contents($search_doc[$i]);
			$decoded[$i] = array('scholar_user_id' => $scholar_user_id[$i], 'data'=>Json::decode($data[$i]));

			drupal_set_message('****DECODE'.print_r($decoded[$i],true));
			$publications_total[$i]=count($decoded[$i]['data']['publications']);
		/*	$author_total_citations[$i] = $decoded[$i]['data']['total_citations'];
			$author_citations_per_year[$i] = $decoded[$i]['data']['citations_per_year'];
			$author_indice_h = $decoded[$i]['data']['indice h'];*/
			for($p=0; $p<$publications_total[$i]; $p++){

				$scholar_doc[$i] = $decoded[$i]['data']['publications'][$p];
				$scholar_doc_title[$i] = $scholar_doc[$i]['title'];
				$scholar_doc_authors[$i] = $scholar_doc[$i]['authors'];
				$scholar_doc_year[$i] = $scholar_doc[$i]['year'];			
				$scholar_doc_venue[$i] = $scholar_doc[$i]['venue'];
				$scholar_doc_citations[$i] = $scholar_doc[$i]['citations'];
				$scholar_doc_id[$i] = $scholar_doc[$i]['idpub'];
				$form['title_scholar'][$p] = array('#type' => 'value', '#value'=>$scholar_doc_title[$i]);
				$form['year_scholar'][$p] = array('#type' => 'value', '#value'=>$scholar_doc_year[$i]);
				$form['scholar_publication_id'][$p] = array('#type' => 'value', '#value'=>$scholar_doc_id[$i]);
				$form['reposi_user_id'][$p] = array('#type' => 'value', '#value'=>$id_scholar['uid']);
				$form['scholar_user_id'][$p] = array('#type' => 'value', '#value'=>$scholar_user_id[$i]);

			        drupal_set_message($scholar_user_id[$i].'********* data   : '.print_r($scholar_doc[$i],true));	

				$search_pub = db_select('reposi_publication', 'p');
				$search_pub->fields('p')
					   ->condition('p.p_pid_scholar', $scholar_doc_id, '=');
      				$dif_pub_info = $search_pub->execute()->fetchField();
      				$this_dif_pub[] = $dif_pub_info;
			//		drupal_set_message('***************LA BASE DE DATOS dentro for:'.print_r($this_dif_pub,true));
/*
				
					if(!empty($this_dif_pub[$p]) && $this_dif_pub[$p]>0){
					$a=$p;
						echo $scholar_user_id[$i].'  ' .$i.'//////'.$scholar_doc_id[$i];
					}
					else{
						echo $scholar_user_id[$i].'  ' .$i.'xxxx'.$scholar_doc_id[$i];
					}*/
				}
		}	
	}
/*	foreach ($decoded as $get_scholar_publication_data) {
		$get_publication_data_scholar[]=$get_scholar_publication_data['data'];
		$get_scholar_user_id[]=$get_scholar_publication_data['scholar_user_id'];
echo '****id schol'.print_r($get_scholar_publication_data,true);
		$prueba[]=array('scholar_user_id' => $get_scholar_publication_data['scholar_user_id'], 'data'=>$get_scholar_publication_data['data']);
	}
	for($p=0; $p<count($prueba); $p++){
	drupal_set_message('****HAY INFO DE:'.$prueba[$p]['scholar_user_id']);
	drupal_set_message('****DATA:'.print_r($prueba[$p]['data'],true));
	/*	$scholar_doc = $get_scholar_publication_data[$p]['data'];
		$scholar_doc_title = $scholar_doc['title'];
		$scholar_doc_authors = $scholar_doc['authors'];
		$scholar_doc_year = $scholar_doc['year'];			
		$scholar_doc_venue = $scholar_doc['venue'];
		$scholar_doc_citations = $scholar_doc['citations'];
		$scholar_doc_id = $scholar_doc['idpub'];
		$form['title_scholar'][$p] = array('#type' => 'value', '#value'=>$scholar_doc_title);
		$form['year_scholar'][$p] = array('#type' => 'value', '#value'=>$scholar_doc_year);
		$form['scholar_publication_id'][$p] = array('#type' => 'value', '#value'=>$scholar_doc_id);
		$form['reposi_user_id'][$p] = array('#type' => 'value', '#value'=>$id_scholar['uid']);
		$form['scholar_user_id'][$p] = array('#type' => 'value', '#value'=>$scholar_user_id[$i]);
			}*/

   //     $cuenta=count($get_publication_data_scholar);
	//drupal_set_message('****HAY INFO DE:'.$cuenta);
		//	$publications_total=count($decoded['publications']);
		/*	$author_total_citations = $decoded['total_citations'];
			$author_citations_per_year = $decoded['citations_per_year'];
			$author_indice_h = $decoded['indice h'];
			for($p=0; $p<$publications_total; $p++){

				$scholar_doc = $decoded['publications'][$p];
				$scholar_doc_title = $scholar_doc['title'];
				$scholar_doc_authors = $scholar_doc['authors'];
				$scholar_doc_year = $scholar_doc['year'];			
				$scholar_doc_venue = $scholar_doc['venue'];
				$scholar_doc_citations = $scholar_doc['citations'];
				$scholar_doc_id = $scholar_doc['idpub'];
				$form['title_scholar'][$p] = array('#type' => 'value', '#value'=>$scholar_doc_title);
				$form['year_scholar'][$p] = array('#type' => 'value', '#value'=>$scholar_doc_year);
				$form['scholar_publication_id'][$p] = array('#type' => 'value', '#value'=>$scholar_doc_id);
				$form['reposi_user_id'][$p] = array('#type' => 'value', '#value'=>$id_scholar['uid']);
				$form['scholar_user_id'][$p] = array('#type' => 'value', '#value'=>$scholar_user_id[$i]);

			}
			foreach ($form['title_scholar'] as $scholar_title) {
				$scholar_publication_title[] = $scholar_title['#value'];
			}
			foreach ($form['year_scholar'] as $year_scholar) {
				$scholar_publication_year[] = $year_scholar['#value'];
			}
			foreach ($form['scholar_publication_id'] as $scholar_pub_id) {
				$scholar_publication_id[] = $scholar_pub_id['#value'];
			}
			foreach ($form['reposi_user_id'] as $user_id) {
				$reposi_user_id[] = $user_id['#value'];
			}
			foreach ($form['scholar_user_id'] as $scholar_user_id) {
				$scholar_user_id[] = $scholar_user_id['#value'];
			}*/



	return $form;
}

function reposi_author_scholar(){

        $config = ConfigFormBase::config('system.maintenance');
	$apikey_query_start = $config->get('query_scholar_start');
	$apikey_query_final = $config->get('query_scholar_final');
        
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
                                	$search_author_scholar='http://localhost/googlescholar-api/googlescholar.php?fname='. $search_name_1 .
					'&sname=' . '' . '&flast=' . $search_lastname_1 . '&slast=' . $search_lastname_2;
				} 
				elseif ((!empty($search_name_2)) && (!empty($search_name_1))) {
					$author_name = $search_name_1 . ' ' . $search_name_2. ' '. $search_lastname_1 . ' ' . $search_lastname_2;
                                	$search_author_scholar='http://localhost/googlescholar-api/googlescholar.php?fname='. $search_name_1 .
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
    						$scholar_data = explode('"name": "', $data);
						$data_number = count($scholar_data);
						$scholar_info = explode('",',$data);
						$header = array(t('Google Scholar ID'), t('Name'), t('Affiliation'));
					//	drupal_set_message('count:'.$data_number. ' SCHOLAR DATA'.print_r($scholar_data,true));
    						$form['scholar'][$search_lastname_1] = array(
       							'#type' => 'details',
       							'#open' => TRUE,
       							'#title' => $author_name,
							'#description' => 'Associate with a Google Scholar User',
    						);					$form['scholar'][$search_lastname_1]['table']  = array(
       							'#type' => 'table',
       							'#title' => 'Scholar Author Table',
       							'#header' => $header,
       							'#empty' => t('No lines found'),
    						);
						for($i=1; $i<$data_number; $i++)
                                                {
                                                 $scholar_name = explode('",', $scholar_data[$i]);
						// drupal_set_message(print_r($scholar_name,true));
						 $scholar_info = explode('"authors": "', $scholar_name[1]);
						 $scholar_user = explode('"User": "', $scholar_name[2]);
						 $scholar_id_user = substr($scholar_user[1],-27,12);
						 $scholar_info = $scholar_info[1];
						 $scholar_name = $scholar_name[0];
  						 $rows = array($scholar_id_user,$scholar_name,$scholar_info);
					//	 drupal_set_message('NOMBRE: '.$scholar_name.' INFORMACIÓN: '.$scholar_info.' USER SCHOLAR: '.$scholar_id_user );

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
					
			}
	    }

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
