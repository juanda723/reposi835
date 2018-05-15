<?php
public static function docs_scholar(){


  $form['body'] = array();
  $search_doc = 'http://localhost/apiGS/getallpublication.php?user=56ReWfsAAAAJ100';
  $data= file_get_contents($search_doc);
  $scholar_publication  = Json::decode($data);
  $search_pub_state = db_select('reposi_publication', 'p');
  $search_pub_state->fields('p', array('pid', 'p_pid_scholar'));
  $pub_state = $search_pub_state->execute()->fetchAll();
  $pid = array();
  $idgs = array();
  $scholar_pub_clean= array();
  $inte=count((array)$pub_state);
  for ($i=0; $i <$inte ; $i++) {
    $pid[$i] = $pub_state[$i]->pid;
    $idgs[$i]= $pub_state[$i]->p_pid_scholar;
  }
  $pid_idgs = array('pid' => $pid, 'idgs' => $idgs);
  $counta=-1;
  $inter=count($scholar_publication['publications']);//json
  for ($i=0; $i <$inter ; $i++) {
    if (!in_array($scholar_publication['publications'][$i]['idpub'],$pid_idgs['idgs'])) {
      $counta=$counta+1;
      $scholar_pub_clean['publications'][$counta]['title']=$scholar_publication['publications'][$i]['title'];
      $scholar_pub_clean['publications'][$counta]['year']=$scholar_publication['publications'][$i]['year'];
      $scholar_pub_clean['publications'][$counta]['idpub']=$scholar_publication['publications'][$i]['idpub'];
    }
  }
  $alltitle='';
  for ($i=0; $i <=$counta ; $i++) {

    $search_pmax = db_select('reposi_publication', 'r');
    $search_pmax->fields('r',array('p_unde'))
    ->orderBy('r.p_unde', 'DESC');
    $p_max1 = $search_pmax->execute()->fetchAssoc();
    $new_max=$p_max1['p_unde']+1;
    $idgs='56ReWfsAAAAJ';
    $search_art = db_select('reposi_author', 'ab');
    $search_art->fields('ab')
    ->condition('ab.a_id_scholar', $idgs, '=');
    $au_id = (int)($search_art->execute()->fetchField());
    drupal_set_message('hola '.$new_max.' y '.$au_id);

    db_insert('reposi_publication')->fields(array(
      'p_type'  => 'Undefined',
      'p_source'=> 'Google Scholar',
      'p_title' => $scholar_pub_clean['publications'][$i]['title'],
      'p_year'  => $scholar_pub_clean['publications'][$i]['year'],
      'p_pid_scholar'=> $scholar_pub_clean['publications'][$i]['idpub'],
      'p_check' => 0,
      'p_unde'  => $new_max,
    ))->execute();
    db_insert('reposi_publication_author')->fields(array(
      'ap_author_id'  => $au_id,
      'ap_unde'=> $new_max,
    ))->execute();
    $alltitle .= '<p>'.$scholar_pub_clean['publications'][$i]['title'].'</p>';
  }
  if (empty($alltitle)) {
    $alltitle .='No records';
  }
  $form['body'] = array('#markup' => $alltitle);
  drupal_set_message('hola '.$inter.' mata'.print_r($scholar_publication,true));
  drupal_set_message('hola '.$inter.' mata'.print_r($scholar_pub_clean,true));
  return $form;

}



/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

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
			drupal_set_message('****id scholar user'.$scholar_user_id[$i]);
			$data[$i]= file_get_contents($search_doc[$i]);
			$decoded[$i] = array('scholar_user_id' => $scholar_user_id[$i], 'data'=>Json::decode($data[$i]));
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

		
				$search_pub = db_select('reposi_publication', 'p');
				$search_pub->fields('p');
				$find_pub = $search_pub->execute();
				$pub_id = $find_pub->fetchField();
              			$find_pub -> allowRowCount = TRUE;
				$find_something = $find_pub->rowCount();
				if ($find_something == '0'){
			
                        		$form['doc'][$p] = array('#markup' => '<br><strong> ['.$i.']'.$scholar_doc_title[$i].'</strong>, '.
							       $scholar_doc_authors[$i].', <strong>'.$scholar_doc_year[$i].'</strong></br>');

			/*		db_insert('reposi_undefined_publication')->fields(array(
      						'up_title'	=> $scholar_doc_title[$i],
      						'up_year'		=> $scholar_doc_year[$i],
  					))->execute();
  					$search_id = db_select('reposi_undefined_publication', 'up');
  					$search_id->fields('up')
          					  ->condition('up.up_year', $scholar_doc_year[$i], '=')
          					  ->condition('up.up_title', $scholar_doc_title[$i], '=');
					$unde_pub_id = $search_id->execute()->fetchField();

					db_insert('reposi_publication')->fields(array(
						'p_type'       => 'Undefined',
						'p_title'      => $scholar_doc_title[$i],
						'p_year'       => $scholar_doc_year[$i],
						'p_pid_scholar'=> $scholar_doc_id[$i],
						'p_check'      => 0,
						'p_source'     => t('Google Scholar'),
						'p_unde'       => $unde_pub_id,
						'p_uid'        => $reposi_user_id[$i],
					))->execute();

					$search_author = db_select('reposi_author', 'a');
  					$search_author->fields('a')
          					      ->condition('a.a_id_scholar', $scholar_user_id[$i], '=');
  					$unde_pub_author_id = $search_author->execute()->fetchField();

					db_insert('reposi_publication_author')->fields(array(
						'ap_author_id' => $unde_pub_author_id,
						'ap_unde'      => $unde_pub_id,
					))->execute();*/
					
				}else{
		//			drupal_set_message('*********************HAY COINCIDENCIAS');
					$search_pub_state = db_select('reposi_publication', 'p');
					$search_pub_state->fields('p', array('pid', 'p_pid_scholar'));
					$pub_state = $search_pub_state->execute()->fetchAll();

					for ($a=0; $a <count($pub_state) ; $a++) {
						$reposi_pub_pid_db[$a] = $pub_state[$a]->pid;
						$scholar_pub_id_db[$a]= $pub_state[$a]->p_pid_scholar;
					}

  					if (!in_array($scholar_doc_id[$i], $scholar_pub_id_db)) {

                        		$form['doc'][$i][$p] = array('#markup' => '<br><strong>['.$i.'] '.$scholar_doc_title[$i].'</strong>, '.
							       $scholar_doc_authors[$i].', <strong>'.$scholar_doc_year[$i].'</strong></br>');
/*
  					db_insert('reposi_undefined_publication')->fields(array(
      						'up_title'	=> $scholar_doc_title[$i],
      						'up_year'		=> $scholar_doc_year[$i],
  					))->execute();
  					$search_id = db_select('reposi_undefined_publication', 'up');
  					$search_id->fields('up')
          					  ->condition('up.up_year', $scholar_doc_year[$i], '=')
          					  ->condition('up.up_title', $scholar_doc_title[$i], '=');
  					$unde_pub_id = $search_id->execute()->fetchField();

			db_insert('reposi_publication')->fields(array(
					'p_type'       => 'Undefined',
					'p_title'      => $scholar_doc_title[$i],
					'p_year'       => $scholar_doc_year[$i],
					'p_pid_scholar'=> $scholar_doc_id[$i],
					'p_check'      => 0,
					'p_source'     => t('Google Scholar'),
					'p_unde'       => $unde_pub_id,
					'p_uid'        => $reposi_user_id[$i],
			))->execute();

  $search_author = db_select('reposi_author', 'a');
  $search_author->fields('a')
          ->condition('a.a_id_scholar', $scholar_user_id[$i], '=');
  $unde_pub_author_id = $search_author->execute()->fetchField();



			db_insert('reposi_publication_author')->fields(array(
					'ap_author_id' => $unde_pub_author_id,
					'ap_unde'      => $unde_pub_id,
			))->execute();

*/
 					}else{
						$markup = 'No se encontraron nuevas publicaciones';
					}

				}
			}
// aquí irían los para cada despues del for



	//drupal_set_message('****$author_indice_h:'.print_r($author_indice_h[$i],true).print_r($author_total_citations[$i],true));
		}	
		else{
		$markup = 'No usuarios de Google Scholar';
		}
	}




			


	

	

	return $form;





//////////////////////////////////////////////////////////////////////////////////////////////////FUNCIONO


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
  $search_doc = 'http://localhost/apiGS/getallpublication.php?user='.$id_scholar['u_id_scholar'].'100';
  $data= file_get_contents($search_doc);
  $scholar_publication  = Json::decode($data);
  $search_pub_state = db_select('reposi_publication', 'p');
  $search_pub_state->fields('p', array('pid', 'p_pid_scholar'));
  $pub_state = $search_pub_state->execute()->fetchAll();
  $pid = array();
  $idgs = array();
  $scholar_pub_clean= array();
  $inte=count((array)$pub_state);
  for ($i=0; $i <$inte ; $i++) {
    $pid[$i] = $pub_state[$i]->pid;
    $idgs[$i]= $pub_state[$i]->p_pid_scholar;
  }
  $pid_idgs = array('pid' => $pid, 'idgs' => $idgs);
  $counta=-1;
  $inter=count($scholar_publication['publications']);//json
  for ($i=0; $i <$inter ; $i++) {
    if (!in_array($scholar_publication['publications'][$i]['idpub'],$pid_idgs['idgs'])) {
      $counta=$counta+1;
      $scholar_pub_clean['publications'][$counta]['title']=$scholar_publication['publications'][$i]['title'];
      $scholar_pub_clean['publications'][$counta]['year']=$scholar_publication['publications'][$i]['year'];
      $scholar_pub_clean['publications'][$counta]['idpub']=$scholar_publication['publications'][$i]['idpub'];
    }
  }
  $alltitle='';
  for ($i=0; $i <=$counta ; $i++) {

    $search_pmax = db_select('reposi_publication', 'r');
    $search_pmax->fields('r',array('p_unde'))
    ->orderBy('r.p_unde', 'DESC');
    $p_max1 = $search_pmax->execute()->fetchAssoc();
    $new_max=$p_max1['p_unde']+1;
    $idgs='56ReWfsAAAAJ';
    $search_art = db_select('reposi_author', 'ab');
    $search_art->fields('ab')
    ->condition('ab.a_id_scholar', $idgs, '=');
    $au_id = (int)($search_art->execute()->fetchField());
    drupal_set_message('hola '.$new_max.' y '.$au_id);

    db_insert('reposi_publication')->fields(array(
      'p_type'  => 'Undefined',
      'p_source'=> 'Google Scholar',
      'p_title' => $scholar_pub_clean['publications'][$i]['title'],
      'p_year'  => $scholar_pub_clean['publications'][$i]['year'],
      'p_pid_scholar'=> $scholar_pub_clean['publications'][$i]['idpub'],
      'p_check' => 0,
      'p_unde'  => $new_max,
    ))->execute();
    db_insert('reposi_publication_author')->fields(array(
      'ap_author_id'  => $au_id,
      'ap_unde'=> $new_max,
    ))->execute();
    $alltitle .= '<p>'.$scholar_pub_clean['publications'][$i]['title'].'</p>';
  }
  if (empty($alltitle)) {
    $alltitle .='No records';
  }
  $form['body'] = array('#markup' => $alltitle);
  drupal_set_message('hola '.$inter.' mata'.print_r($scholar_publication,true));
  drupal_set_message('hola '.$counta.' mata'.print_r($scholar_pub_clean,true));
}
  return $form;


********************************************************************************************************************************
7******************************************************************************************************************************


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
			drupal_set_message('****id scholar user'.$scholar_user_id[$i]);
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$data[1]='{
 "total_citations": "0",
 "indice h": "0",
 "citations_per_year": " ",
 "publications": [ 
  {
    "title": "DIDÁCTICA UNIVERSITARIA DE LA INVESTIGACIÓN CIENTÍFICA. VALORACIÓN Y PROPUESTA DESDE LA PERCEPCIÓN DE LOS ESTUDIANTES",
    "authors": "JM Ordoñez",
    "venue": "Revista Científica Hacedor-AIAPÆC 1 (2), 2017 ",
    "citations": " ",
    "year": "2017 ",
    "idpub": "u5HHmVD_uO8C"
  },
  {
    "title": "LOS DERECHOS DE AUTOR Y LA PROPIEDAD INTELECTUAL EN LOS TRABAJOS UNIVERSITARIOS",
    "authors": "JM Ordoñez",
    "venue": "Revista Científica Hacedor-AIAPÆC 1 (1), 2017 ",
    "citations": " ",
    "year": "2017 ",
    "idpub": "u-x6o8ySG0sC"
  },
  {
    "title": "El aprendizaje cooperativo para el logro de los aprendizajes esperados en la educación universitaria",
    "authors": "JM Ordoñez",
    "venue": "UMBRAL Nueva Etapa. Revista de Investigaciones Socioeducativas 1 (2), 275, 2017 ",
    "citations": " ",
    "year": "2017 ",
    "idpub": "9yKSN-GCB0IC"
  },
  {
    "title": "Traverso Flores (2011). La historia de la exclusión social en el Perú",
    "authors": "JM Ordoñez",
    "venue": "UMBRAL nueva etapa. Revista de Investigaciones Socioeducativas 1 (1), 198, 2012 ",
    "citations": " ",
    "year": "2012 ",
    "idpub": "d1gkVwhDpl0C"
  }
 ]
}
';
$data[2]='{
 "total_citations": "0",
 "indice h": "0",
 "citations_per_year": " ",
 "publications": [ 
  {
    "title": "Bases for the construction of a QoS model for a video-calls service in a virtualized IMS network",
    "authors": "MC Lara Paz, HA Coral Sarria, E Rojas Pineda",
    "venue": "Sistemas &amp; Telemática 15 (42), 2017 ",
    "citations": " ",
    "year": "2017 ",
    "idpub": "WF5omc3nYNoC"
  },
  {
    "title": "RB Repository: Reference bibliographies repository for Drupal 7",
    "authors": "FO Collazos, BEH Hurtado, ER Pineda",
    "venue": "Sistemas &amp; Telemática 14 (38), 47-62, 2016 ",
    "citations": " ",
    "year": "2016 ",
    "idpub": "YsMSGLbcyi4C"
  },
  {
    "title": "Soluciones organizacionales a partir de ontologías",
    "authors": "EG Pemberty, ER Pineda",
    "venue": "Avances en Sistemas e Informática 8 (1), 101-112, 2011 ",
    "citations": " ",
    "year": "2011 ",
    "idpub": "9yKSN-GCB0IC"
  },
  {
    "title": "AGENDA CAUCANA DE CIENCIA, TECNOLOGÍA E INNOVACIÓN “CAUCACYT”",
    "authors": "DJ Sanchez Preciado, LS Pemberthy Gallo, E Rojas Pineda, ...",
    "venue": "Universidad del Cauca, ISBN: 9589475779, 2005 ",
    "citations": " ",
    "year": "2005 ",
    "idpub": "zYLM7Y9cAGgC"
  },
  {
    "title": "VI Simposio de Investigaciones Facultad de Salud 27 al 29 de Octubre de 2004. El Sistema de Investigaciones de la Universidad del Cauca y su articulación con los...",
    "authors": "ER Pineda",
    "venue": "VI Simposio de Investigaciones Facultad de Salud 27 al 29 de Octubre de 2004, 2004 ",
    "citations": " ",
    "year": "2004 ",
    "idpub": "UeHWp8X0CEIC"
  },
  {
    "title": "Comunidades y Cultura del Conocimiento: La experiencia del Sistema de Investigaciones de la Universidad del Cauca, Colombia.",
    "authors": "E Rojas Pineda, AJ Castrillón Muñoz, CA León Roa",
    "venue": "Gestión Del Conocimiento: Pautas y Lineamientos Generales. ISBN: 958-33-4823 &#8230;, 2003 ",
    "citations": " ",
    "year": "2003 ",
    "idpub": "Tyk-4Ss8FVUC"
  },
  {
    "title": "Concepto, Proceso y Gestión de Líneas de Investigación",
    "authors": "E Rojas Pineda",
    "venue": "Unicauca  Ciencia, ISSN: 0122-6037 6 (1), 19-23, 2001 ",
    "citations": " ",
    "year": "2001 ",
    "idpub": "W7OEmFMy1HYC"
  },
  {
    "title": "EL MRDP COMO ESTRATEGIA BÁSICA PARA LA COMPETITIVIDAD",
    "authors": "ER Pineda, CES Castaño",
    "venue": "X Congreso Nacional y I Andino de Telecomunicaciones 1 (1), 1995 ",
    "citations": " ",
    "year": "1995 ",
    "idpub": "u-x6o8ySG0sC"
  },
  {
    "title": "Hacia un nuevo modelo de formación en ingeniería electrónica y telecomunicaciones en la Universidad del Cauca",
    "authors": "ER Pineda",
    "venue": "Uniandes, 1993 ",
    "citations": " ",
    "year": "1993 ",
    "idpub": "2osOgNQ5qMEC"
  }
 ]
}';
//////////////////////////////////////////////////////////////////ghg/////////////////////////////////////////////////////////////////////////
	//		$data[$i]= file_get_contents($search_doc[$i]);
			$decoded[$i] = array('reposi_user_id' => $reposi_user_id[$i],'scholar_user_id' => $scholar_user_id[$i], 'data'=>Json::decode($data[$i]));
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
		//		$reposi_user_id[$p][$i] =$reposi_user_id[$i];
				$scholar_identification[$i] = array('reposi_user_id' => $reposi_user_id[$i], 'idpub'=>$scholar_doc_id[$i]);
drupal_set_message('********************ID USER GET: '.print_r($reposi_user_id[$i],true));
				$form['title_scholar'][$p] = array('#type' => 'value', '#value'=>$scholar_doc_title[$i]);
				$form['year_scholar'][$p] = array('#type' => 'value', '#value'=>$scholar_doc_year[$i]);
				$form['scholar_publication_id'][$p] = array('#type' => 'value', '#value'=>$scholar_doc_id[$i]);
				$form['reposi_user_id'][$p] = array('#type' => 'value', '#value'=>$id_scholar['uid']);
				$form['scholar_user_id'][$p] = array('#type' => 'value', '#value'=>$scholar_user_id[$i]);
				$form['reposi_user_id'][$p] = array('#type' => 'value', '#value'=>$reposi_user_id[$i]);
		
				$search_pub = db_select('reposi_publication', 'p');
				$search_pub->fields('p');
				$find_pub = $search_pub->execute();
				$pub_id = $find_pub->fetchField();
              			$find_pub -> allowRowCount = TRUE;
				$find_something = $find_pub->rowCount();
				if ($find_something == '0'){
			
                        		$form['doc'][$p] = array('#markup' => 'NO HAY DATOS EN LA DB'.$scholar_user_id[$i].'<br><strong> ['.$i.']'.$scholar_doc_title[$i].'</strong>, '.
							       $scholar_doc_authors[$i].', <strong>'.$scholar_doc_year[$i].'</strong></br>');

					db_insert('reposi_undefined_publication')->fields(array(
      						'up_title'	=> $scholar_doc_title[$i],
      						'up_year'		=> $scholar_doc_year[$i],
  					))->execute();
  					$search_id = db_select('reposi_undefined_publication', 'up');
  					$search_id->fields('up')
          					  ->condition('up.up_year', $scholar_doc_year[$i], '=')
          					  ->condition('up.up_title', $scholar_doc_title[$i], '=');
					$unde_pub_id = $search_id->execute()->fetchField();

					db_insert('reposi_publication')->fields(array(
						'p_type'       => 'Undefined',
						'p_title'      => $scholar_doc_title[$i],
						'p_year'       => $scholar_doc_year[$i],
						'p_pid_scholar'=> $scholar_doc_id[$i],
						'p_check'      => 0,
						'p_source'     => t('Google Scholar'),
						'p_unde'       => $unde_pub_id,
						'p_uid'        => $reposi_user_id[$i],
					))->execute();

					$search_author = db_select('reposi_author', 'a');
  					$search_author->fields('a')
          					      ->condition('a.a_id_scholar', $scholar_user_id[$i], '=');
  					$unde_pub_author_id = $search_author->execute()->fetchField();

					db_insert('reposi_publication_author')->fields(array(
						'ap_author_id' => $unde_pub_author_id,
						'ap_unde'      => $unde_pub_id,
					))->execute();
					
				}else{
		//			drupal_set_message('*********************HAY COINCIDENCIAS');
					$search_pub_state = db_select('reposi_publication', 'p');
					$search_pub_state->fields('p', array('pid', 'p_pid_scholar', 'p_uid'))
          					        ->condition('p.p_uid', $reposi_user_id[$i], '=');
					$pub_state = $search_pub_state->execute()->fetchAll();

					for ($a=0; $a <count($pub_state) ; $a++) {
						$reposi_pub_pid_db[$a] = $pub_state[$a]->pid;
						$scholar_pub_id_db[$a]= $pub_state[$a]->p_pid_scholar;
					}



//$id_scholar['uid']  	$reposi_user_id[$i]
  					if (!in_array($scholar_doc_id[$i], $scholar_pub_id_db)) {

                       		$form['doc'][$i][$p] = array('#markup' => $scholar_user_id[$i].'<br><strong>['.$i.'] '.$scholar_doc_title[$i].'</strong>, '.
							       $scholar_doc_authors[$i].', <strong>'.$scholar_doc_year[$i].'</strong></br>');

  					db_insert('reposi_undefined_publication')->fields(array(
      						'up_title'	=> $scholar_doc_title[$i],
      						'up_year'		=> $scholar_doc_year[$i],
  					))->execute();
  					$search_id = db_select('reposi_undefined_publication', 'up');
  					$search_id->fields('up')
          					  ->condition('up.up_year', $scholar_doc_year[$i], '=')
          					  ->condition('up.up_title', $scholar_doc_title[$i], '=');
  					$unde_pub_id = $search_id->execute()->fetchField();

			db_insert('reposi_publication')->fields(array(
					'p_type'       => 'Undefined',
					'p_title'      => $scholar_doc_title[$i],
					'p_year'       => $scholar_doc_year[$i],
					'p_pid_scholar'=> $scholar_doc_id[$i],
					'p_check'      => 0,
					'p_source'     => t('Google Scholar'),
					'p_unde'       => $unde_pub_id,
					'p_uid'        => $reposi_user_id[$i],
			))->execute();

  $search_author = db_select('reposi_author', 'a');
  $search_author->fields('a')
          ->condition('a.a_id_scholar', $scholar_user_id[$i], '=');
  $unde_pub_author_id = $search_author->execute()->fetchField();



			db_insert('reposi_publication_author')->fields(array(
					'ap_author_id' => $unde_pub_author_id,
					'ap_unde'      => $unde_pub_id,
			))->execute();


 					}else{
						$markup = 'No se encontraron nuevas publicaciones';
					}

				}
			}
// aquí irían los para cada despues del for



	//drupal_set_message('****$author_indice_h:'.print_r($author_indice_h[$i],true).print_r($author_total_citations[$i],true));
		}	
		else{
		$markup = 'No usuarios de Google Scholar';
		}
	}




			


	

	

	return $form;
















******************************************************************************************************************************************************************************************************************************************************************************************
************************************************************************************************************************************************************************************
***********************************************************************************************************************************************************


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
		if(!empty($scholar_user_id[$i]) && !empty($reposi_user_id[$i])) {
			$search_doc[$i] = 'http://localhost/apiGS/getallpublication.php?user='.$scholar_user_id[$i].$query_size_scholar;
			$data[$i]= file_get_contents($search_doc[$i]);
			$decoded[$i] = array('scholar_user_id' => $scholar_user_id[$i], 'data'=>Json::decode($data[$i]));
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
			//	$scholar_info = array();

				$pub_info_scholar=array('scholar_user' => $scholar_user_id[$i], 'pub_id'=>$scholar_doc_id[$i]);

			drupal_set_message('*********************HAY COINCIDENCIAS'.print_r($pub_info_scholar,true));
				$form['title_scholar'][$p] = array('#type' => 'value', '#value'=>$scholar_doc_title[$i]);
				$form['year_scholar'][$p] = array('#type' => 'value', '#value'=>$scholar_doc_year[$i]);
				$form['scholar_publication_id'][$p] = array('#type' => 'value', '#value'=>$scholar_doc_id[$i]);
				$form['reposi_user_id'][$p] = array('#type' => 'value', '#value'=>$id_scholar['uid']);
				$form['scholar_user_id'][$p] = array('#type' => 'value', '#value'=>$scholar_user_id[$i]);


				$search_pub = db_select('reposi_publication', 'p');
				$search_pub->fields('p');
				$find_pub = $search_pub->execute();
				$pub_id = $find_pub->fetchField();
              			$find_pub -> allowRowCount = TRUE;
				$find_something = $find_pub->rowCount();
				if ($find_something == '0'){
			
                        		$form['doc'][$p] = array('#markup' => '<br><strong> ['.$i.']'.$scholar_doc_title[$i].'</strong>, '.
							       $scholar_doc_authors[$i].', <strong>'.$scholar_doc_year[$i].'</strong></br>');

					db_insert('reposi_undefined_publication')->fields(array(
      						'up_title'	=> $scholar_doc_title[$i],
      						'up_year'		=> $scholar_doc_year[$i],
  					))->execute();
  					$search_id = db_select('reposi_undefined_publication', 'up');
  					$search_id->fields('up')
      						'up_title'	=> $scholar_doc_title[$i],
      						'up_id_scholar'	=> $scholar_user_id[$i],
      						'up_pid_scholar'=> $scholar_doc_id[$i],
      						'up_year'	=> $scholar_doc_year[$i],
					$unde_pub_id = $search_id->execute()->fetchField();

					db_insert('reposi_publication')->fields(array(
						'p_type'       => 'Undefined',
						'p_title'      => $scholar_doc_title[$i],
						'p_year'       => $scholar_doc_year[$i],
						'p_pid_scholar'=> $scholar_doc_id[$i],
						'p_check'      => 0,
						'p_source'     => t('Google Scholar'),
						'p_unde'       => $unde_pub_id,
						'p_uid'        => $reposi_user_id[$i],
					))->execute();

					$search_author = db_select('reposi_author', 'a');
  					$search_author->fields('a')
          					      ->condition('a.a_id_scholar', $scholar_user_id[$i], '=');
  					$unde_pub_author_id = $search_author->execute()->fetchField();

					db_insert('reposi_publication_author')->fields(array(
						'ap_author_id' => $unde_pub_author_id,
						'ap_unde'      => $unde_pub_id,
					))->execute();
					
				}else{
					$search_pub_state = db_select('reposi_undefined_publication', 'up');
					$search_pub_state->fields('up', array('up_id_scholar', 'up_pid_scholar'));
        //  	 				        ->condition('up.up_id_scholar', $scholar_user_id[$i], '=');
					$pub_state = $search_pub_state->execute()->fetchAll();

					for ($a=0; $a <count($pub_state) ; $a++) {
				//		$reposi_pub_pid_db[$a] = $pub_state[$a]->pid;
						$scholar_pub_id_db[$a]= $pub_state[$a]->up_pid_scholar;
						$reposi_user_id_db[$a]= $pub_state[$a]->up_id_scholar;

					}
					$pub_info_db=array('scholar_user' => $reposi_user_id_db, 'pub_id'=>$scholar_pub_id_db);
					
if (!in_array($scholar_user_id[$i], $reposi_user_id_db)) {

		//	drupal_set_message('///////////MOSTRANDO:'.print_r($pub_info_scholar,true).'=!'.print_r($pub_info_db,true));
                        		$form['doc'][$i][$p] = array('#markup' => '<br><strong> eeeeooooooo ['.$i.'] '.$scholar_doc_title[$i].'</strong>, '.
							       $scholar_doc_authors[$i].', <strong>'.$scholar_doc_year[$i].'</strong></br>');
}

/*

  					if (!in_array($scholar_doc_id[$i], $scholar_pub_id_db)) {

			drupal_set_message('****************DIFERENTES:'.print_r($pub_info_scholar,true).'=!'.print_r($pub_info_db,true));
                        		$form['doc'][$i][$p] = array('#markup' => '<br><strong>['.$i.'] '.$scholar_doc_title[$i].'</strong>, '.
							       $scholar_doc_authors[$i].', <strong>'.$scholar_doc_year[$i].'</strong></br>');

/*
  					db_insert('reposi_undefined_publication')->fields(array(
      						'up_title'	=> $scholar_doc_title[$i],
      						'up_id_scholar'	=> $scholar_user_id[$i],
      						'up_pid_scholar'=> $scholar_doc_id[$i],
      						'up_year'	=> $scholar_doc_year[$i],
  					))->execute();
  					$search_id = db_select('reposi_undefined_publication', 'up');
  					$search_id->fields('up')
          					  ->condition('up.up_year', $scholar_doc_year[$i], '=')
          					  ->condition('up.up_title', $scholar_doc_title[$i], '=')
          					  ->condition('up.up_pid_scholar', $scholar_doc_id[$i], '=');
  					$unde_pub_id = $search_id->execute()->fetchField();

			db_insert('reposi_publication')->fields(array(
					'p_type'       => 'Undefined',
					'p_title'      => $scholar_doc_title[$i],
					'p_year'       => $scholar_doc_year[$i],
					'p_pid_scholar'=> $scholar_doc_id[$i],
					'p_check'      => 0,
					'p_source'     => t('Google Scholar'),
					'p_unde'       => $unde_pub_id,
					'p_uid'        => $reposi_user_id[$i],
			))->execute();

  $search_author = db_select('reposi_author', 'a');
  $search_author->fields('a')
          ->condition('a.a_id_scholar', $scholar_user_id[$i], '=');
  $unde_pub_author_id = $search_author->execute()->fetchField();



			db_insert('reposi_publication_author')->fields(array(
					'ap_author_id' => $unde_pub_author_id,
					'ap_unde'      => $unde_pub_id,
			))->execute();

*/
 		/*			}else{
						$markup = 'No se encontraron nuevas publicaciones';
					}*/

				}
			}
// aquí irían los para cada despues del for



	//drupal_set_message('****$author_indice_h:'.print_r($author_indice_h[$i],true).print_r($author_total_citations[$i],true));
		}	
		else{
		$markup = 'No usuarios de Google Scholar';
		}
	}




			


	

	

	return $form;



