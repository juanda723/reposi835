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
use Drupal\Component\Utility\Unicode;

class reposidoc_scholar extends reposi_apischolar_admin{

  public static function pubscolar_art($uid,$p_uid,$user_gs,$p_pid_scholar){
    $form = 0;
    $search_pub_state = db_select('reposi_publication', 'p');
    $search_pub_state->fields('p')
    ->condition('p.p_unde', $uid, '=');
    $pub_state = $search_pub_state->execute()->fetchAssoc();
    $search_doc = 'http://localhost/apiGS/getpublication.php?puser='.$user_gs.$p_pid_scholar;
    $data= file_get_contents($search_doc);
    $scholar_publication  = Json::decode($data);
    if (empty($data)){
      $form = 1;
      drupal_set_message('Error getting data. please make sure your api is working correctly.',"error");
    }else{
      $authors = explode(", ",$scholar_publication['authors']);
      $authors = implode(",",$authors);
      $authors = explode(",",$authors);
      foreach ($authors as $eids) {
        $authorss[] = explode(" ", $eids);
      }
      $authors = explode(", ",$scholar_publication['authors']);
      $search_pmax = db_select('reposi_publication', 'r');
      $search_pmax->fields('r',array('pid'))
      ->orderBy('r.pid', 'DESC');
      $p_max = $search_pmax->execute()->fetchAssoc();
      $search_pmax = db_select('reposi_publication', 'r');
      $search_pmax->fields('r',array('p_abid'))
      ->orderBy('r.p_abid', 'DESC');
      $p_max1 = $search_pmax->execute()->fetchAssoc();
      $new_abid=$p_max1['p_abid']+1;
      //echo 'ID '.$p_max['pid'].' ab_id '. $p_max1['p_abid'];
      $date=explode("/",$scholar_publication['Publication date']);

      if ($date[0]==" ") {
        $date[0]=1000;
      }
      $new_art_year = (int)$date[0];
      if (isset($date[2])) {
        $new_art_day = (int)$date[2];
      } else {
        $new_art_day = NULL;
      }
      if (isset($date[1])) {
        $new_art_month = (int)$date[1];
      } else {
        $new_art_month = NULL;
      }
      if ($scholar_publication['Pages']==' ') {
        # code...
      }else {
        $pages=explode("-",$scholar_publication['Pages']);
        $art_spage=$pages[0];
        $art_fpage=$pages[1];
      }
      if (!empty($art_spage)) {
        $art_start_page = $art_spage;
      } else {
        $art_start_page = NULL;
      }
      if (!empty($art_fpage)) {
        $art_final_page = $art_fpage;
      } else {
        $art_final_page = NULL;
      }
      if ($scholar_publication['Volume']==' ') {
        $art_vol= NULL;
      }else {
        $art_vol= $scholar_publication['Volume'];
      }
      if ($scholar_publication['Issue']==' ') {
        $art_issue= NULL;
      }else {
        $art_issue= $scholar_publication['Issue'];
      }
      if ($scholar_publication['URL']==' ') {
        $art_url= NULL;
      }else {
        $art_url= $scholar_publication['URL'];
      }
      if ($scholar_publication['abstract']==' ') {
        $art_abs= NULL;
      }else {
        $art_abs= $scholar_publication['abstract'];
      }
      if ($scholar_publication['Journal']==' ') {
        $art_jour= NULL;
      }else {
        $art_jour= $scholar_publication['Journal'];
      }
      $art_title=Reposi_info_publication::reposi_string($pub_state['p_title']);
      db_insert('reposi_article_book')->fields(array(
        'ab_type'              => 'Article',
        'ab_title'             => $art_title,
        'ab_abstract'          => $art_abs,
        'ab_journal_editorial' => $art_jour,
      ))->execute();

      $search_art = db_select('reposi_article_book', 'ab');
      $search_art->fields('ab')
      ->condition('ab.ab_type', 'Article', '=')
      ->condition('ab.ab_title', $art_title, '=');
      $art_id = $search_art->execute()->fetchField();

      db_insert('reposi_date')->fields(array(
        'd_day'  => $new_art_day,
        'd_month'=> $new_art_month,
        'd_year' => $new_art_year,
        'd_abid' => $art_id,
      ))->execute();
      db_insert('reposi_publication')->fields(array(
        'p_type'  => 'Article',
        'p_source'=> 'Google Scholar',
        'p_title' => $art_title,
        'p_year'  => $new_art_year,
        'p_check' => 0,
        'p_abid'  => $art_id,
      ))->execute();

      if (!empty($art_vol) || !empty($art_issue) || !empty($art_spage) ||
      !empty($art_fpage) || !empty($art_url)) {
        db_insert('reposi_article_book_detail')->fields(array(
          'abd_volume'     => $art_vol,
          'abd_issue'      => $art_issue,
          'abd_start_page' => $art_start_page,
          'abd_final_page' => $art_final_page,
          'abd_url'        => $art_url,
          'abd_abid'       => $art_id,
        ))->execute();
      }

      $max = count($authorss);
      if(is_null($max)) {
        $max = 0;
      }
      $table = $authorss;
      for ($a=0; $a<$max; $a++) {
        $names=count($authorss[$a]);
        if ($names==2) {
          $aut_fn=$table[$a][0];
          $aut_sn='';
          $aut_fl=$table[$a][1];
          $aut_sl='';
        }elseif ($names==3) {
          $aut_fn=$table[$a][0];
          $aut_sn='';
          $aut_fl=$table[$a][1];
          $aut_sl=$table[$a][2];;
        }else {
          $aut_fn=$table[$a][0];
          $aut_sn=$table[$a][1];
          $aut_fl=$table[$a][2];
          $aut_sl=$table[$a][3];;
        }

        $info_author = array('a_first_name'      => ucfirst($aut_fn),
        'a_second_name'     => ucfirst($aut_sn),
        'a_first_lastname'  => ucfirst($aut_fl),
        'a_second_lastname' => ucfirst($aut_sl),
      );

      if(($aut_fn!='') && ($aut_fl!='')){
        $serch_a = db_select('reposi_author', 'a');
        $serch_a->fields('a')
        ->condition('a.a_first_name', $aut_fn, '=')
        ->condition('a.a_second_name', $aut_sn, '=')
        ->condition('a.a_first_lastname', $aut_fl, '=')
        ->condition('a.a_second_lastname', $aut_sl, '=');
        $serch_aut[$a] = $serch_a->execute()->fetchField();
        if (empty($serch_aut[$a])) {
          db_insert('reposi_author')->fields($info_author)->execute();
          $serch2_a = db_select('reposi_author', 'a');
          $serch2_a ->fields('a')
          ->condition('a.a_first_name', $aut_fn, '=')
          ->condition('a.a_second_name', $aut_sn, '=')
          ->condition('a.a_first_lastname', $aut_fl, '=')
          ->condition('a.a_second_lastname', $aut_sl, '=');
          $serch2_aut[$a] = $serch2_a->execute()->fetchField();
          $aut_publi_id = (int)$serch2_aut[$a];
          db_insert('reposi_publication_author')->fields(array(
            'ap_author_id' => $aut_publi_id,
            'ap_abid'      => $art_id,
          ))->execute();
        } else {
          $aut_publi_id2 = (int)$serch_aut[$a];
          db_insert('reposi_publication_author')->fields(array(
            'ap_author_id' => $aut_publi_id2,
            'ap_abid'      => $art_id,
          ))->execute();
        }
      }
    }
    drupal_set_message('Data Import successfull');
    reposidoc_scholar::delete_unde($uid);
  }
  return $form;
}

public static function pubscolar_book($uid,$p_uid,$user_gs,$p_pid_scholar){
  $form= 0;
  $search_pub_state = db_select('reposi_publication', 'p');
  $search_pub_state->fields('p')
  ->condition('p.p_unde', $uid, '=');
  $pub_state = $search_pub_state->execute()->fetchAssoc();
  $search_doc = 'http://localhost/apiGS/getpublication.php?puser='.$user_gs.$p_pid_scholar;
  $data= file_get_contents($search_doc);
  $scholar_publication  = Json::decode($data);
  if (empty($data)){
    $form = 1;
    drupal_set_message('Error getting data. please make sure your api is working correctly.',"error");
  }else{
    $authors = explode(", ",$scholar_publication['authors']);
    $authors = implode(",",$authors);
    $authors = explode(",",$authors);
    foreach ($authors as $eids) {
      $authorss[] = explode(" ", $eids);
    }
    $authors = explode(", ",$scholar_publication['authors']);
    $search_pmax = db_select('reposi_publication', 'r');
    $search_pmax->fields('r',array('pid'))
    ->orderBy('r.pid', 'DESC');
    $p_max = $search_pmax->execute()->fetchAssoc();
    $search_pmax = db_select('reposi_publication', 'r');
    $search_pmax->fields('r',array('p_abid'))
    ->orderBy('r.p_abid', 'DESC');
    $p_max1 = $search_pmax->execute()->fetchAssoc();
    $new_abid=$p_max1['p_abid']+1;
    $date=explode("/",$scholar_publication['Publication date']);
    if ($date[0]==" ") {
      $date[0]=1000;
    }
    $new_book_year = (int)$date[0];

    if (isset($date[2])) {
      $new_book_day = (int)$date[2];
    } else {
      $new_book_day = NULL;
    }
    if (isset($date[1])) {
      $new_book_month = (int)$date[1];
    } else {
      $new_book_month = NULL;
    }
    if ($scholar_publication['Pages']==' ') {
      # code...
    }else {
      $pages=explode("-",$scholar_publication['Pages']);
      $book_spage=$pages[0];
      $book_fpage=$pages[1];
    }
    if (!empty($book_spage)) {
      $book_start_page = $book_spage;
    } else {
      $book_start_page = NULL;
    }
    if (!empty($book_fpage)) {
      $book_final_page = $book_fpage;
    } else {
      $book_final_page = NULL;
    }
    if ($scholar_publication['Volume']==' ') {
      $book_vol= NULL;
    }else {
      $book_vol= $scholar_publication['Volume'];
    }
    if ($scholar_publication['Issue']==' ') {
      $book_issue= NULL;
    }else {
      $book_issue= $scholar_publication['Issue'];
    }
    if ($scholar_publication['URL']==' ') {
      $book_url= NULL;
    }else {
      $book_url= $scholar_publication['URL'];
    }
    if ($scholar_publication['Publisher']==' ') {
      $book_pub= NULL;
    }else {
      $book_pub= $scholar_publication['Publisher'];
    }

    if ($scholar_publication['abstract']==' ') {
      $book_abs= NULL;
    }else {
      $book_abs= $scholar_publication['abstract'];
    }
    if ($scholar_publication['Journal']==' ') {
      $book_jour= NULL;
    }else {
      $book_jour= $scholar_publication['Journal'];
    }
    $book_title=Reposi_info_publication::reposi_string($pub_state['p_title']);
    db_insert('reposi_article_book')->fields(array(
      'ab_type'              => 'Book',
      'ab_title'             => $book_title,
      'ab_abstract'          => $book_abs,
      'ab_journal_editorial' => $book_jour,
      'ab_publisher'         => $book_pub,
    ))->execute();
    $search_book = db_select('reposi_article_book', 'ab');
    $search_book->fields('ab')
    ->condition('ab.ab_type', 'Book', '=')
    ->condition('ab.ab_title', $book_title, '=');
    $book_id = $search_book->execute()->fetchField();

    db_insert('reposi_date')->fields(array(
      'd_year' => $new_book_year,
      'd_abid' => $book_id,
    ))->execute();

    db_insert('reposi_publication')->fields(array(
      'p_type'  => 'Book',
      'p_source'=> 'Google Scholar',
      'p_title' => $book_title,
      'p_year'  => $new_book_year,
      'p_check' => 0,
      'p_abid'  => $book_id,
    ))->execute();

    if (!empty($book_vol) || !empty($book_issue) || !empty($book_url)) {
      db_insert('reposi_article_book_detail')->fields(array(
        'abd_volume'     => $book_vol,
        'abd_issue'      => $book_issue,
        'abd_url'        => $book_url,
        'abd_abid'       => $book_id,
      ))->execute();
    }

    $max = count($authorss);
    if(is_null($max)) {
      $max = 0;
    }
    $table = $authorss;
    for ($a=0; $a<$max; $a++) {
      $names=count($authorss[$a]);
      if ($names==2) {
        $aut_fn=$table[$a][0];
        $aut_sn='';
        $aut_fl=$table[$a][1];
        $aut_sl='';
      }elseif ($names==3) {
        $aut_fn=$table[$a][0];
        $aut_sn='';
        $aut_fl=$table[$a][1];
        $aut_sl=$table[$a][2];;
      }else {
        $aut_fn=$table[$a][0];
        $aut_sn=$table[$a][1];
        $aut_fl=$table[$a][2];
        $aut_sl=$table[$a][3];;
      }

      $info_author = array('a_first_name'      => ucfirst($aut_fn),
      'a_second_name'     => ucfirst($aut_sn),
      'a_first_lastname'  => ucfirst($aut_fl),
      'a_second_lastname' => ucfirst($aut_sl),
    );

    if(($aut_fn!='') && ($aut_fl!='')){
      $serch_a = db_select('reposi_author', 'a');
      $serch_a->fields('a')
      ->condition('a.a_first_name', $aut_fn, '=')
      ->condition('a.a_second_name', $aut_sn, '=')
      ->condition('a.a_first_lastname', $aut_fl, '=')
      ->condition('a.a_second_lastname', $aut_sl, '=');
      $serch_aut[$a] = $serch_a->execute()->fetchField();
      if (empty($serch_aut[$a])) {
        db_insert('reposi_author')->fields($info_author)->execute();
        $serch2_a = db_select('reposi_author', 'a');
        $serch2_a ->fields('a')
        ->condition('a.a_first_name', $aut_fn, '=')
        ->condition('a.a_second_name', $aut_sn, '=')
        ->condition('a.a_first_lastname', $aut_fl, '=')
        ->condition('a.a_second_lastname', $aut_sl, '=');
        $serch2_aut[$a] = $serch2_a->execute()->fetchField();
        $aut_publi_id = (int)$serch2_aut[$a];
        db_insert('reposi_publication_author')->fields(array(
          'ap_author_id' => $aut_publi_id,
          'ap_abid'      => $book_id,
        ))->execute();
      } else {
        $aut_publi_id2 = (int)$serch_aut[$a];
        db_insert('reposi_publication_author')->fields(array(
          'ap_author_id' => $aut_publi_id2,
          'ap_abid'      => $book_id,
        ))->execute();
      }
    }
  }
  reposidoc_scholar::delete_unde($uid);
  drupal_set_message('Data Import successfull');
}
return $form;
}

public static function pubscolar_chap($uid,$p_uid,$user_gs,$p_pid_scholar){
  $form = 0;
  $search_pub_state = db_select('reposi_publication', 'p');
  $search_pub_state->fields('p')
  ->condition('p.p_unde', $uid, '=');
  $pub_state = $search_pub_state->execute()->fetchAssoc();

  $search_doc = 'http://localhost/apiGS/getpublication.php?puser='.$user_gs.$p_pid_scholar;
  $data= file_get_contents($search_doc);
  $scholar_publication  = Json::decode($data);
  if (empty($data)){
    $form = 1;
    drupal_set_message('Error getting data. please make sure your api is working correctly.',"error");
  }else{
    $authors = explode(", ",$scholar_publication['authors']);
    $authors = implode(",",$authors);
    $authors = explode(",",$authors);
    foreach ($authors as $eids) {
      $authorss[] = explode(" ", $eids);
    }
    $authors = explode(", ",$scholar_publication['authors']);
    $search_pmax = db_select('reposi_publication', 'r');
    $search_pmax->fields('r',array('pid'))
    ->orderBy('r.pid', 'DESC');
    $p_max = $search_pmax->execute()->fetchAssoc();
    $search_pmax = db_select('reposi_publication', 'r');
    $search_pmax->fields('r',array('p_abid'))
    ->orderBy('r.p_abid', 'DESC');
    $p_max1 = $search_pmax->execute()->fetchAssoc();
    $new_abid=$p_max1['p_abid']+1;
    //echo 'ID '.$p_max['pid'].' ab_id '. $p_max1['p_abid'];
    $date=explode("/",$scholar_publication['Publication date']);

    if ($date[0]==" ") {
      $date[0]=1000;
    }

    $new_chap_year = (int)$date[0];

    if (isset($date[2])) {
      $new_chap_day = (int)$date[2];
    } else {
      $new_chap_day = NULL;
    }
    if (isset($date[1])) {
      $new_chap_month = (int)$date[1];
    } else {
      $new_chap_month = NULL;
    }
    if ($scholar_publication['Pages']==' ') {
      # code...
    }else {
      $pages=explode("-",$scholar_publication['Pages']);
      $chap_spage=$pages[0];
      $chap_fpage=$pages[1];
    }
    if (!empty($chap_spage)) {
      $chap_start_page = $chap_spage;
    } else {
      $chap_start_page = NULL;
    }
    if (!empty($chap_fpage)) {
      $chap_final_page = $chap_fpage;
    } else {
      $chap_final_page = NULL;
    }
    if ($scholar_publication['Volume']==' ') {
      $chap_vol= NULL;
    }else {
      $chap_vol= $scholar_publication['Volume'];
    }
    if ($scholar_publication['Issue']==' ') {
      $chap_issue= 0;
    }else {
      $chap_issue= $scholar_publication['Issue'];
    }
    if ($scholar_publication['URL']==' ') {
      $chap_url= NULL;
    }else {
      $chap_url= $scholar_publication['URL'];
    }
    if ($scholar_publication['Publisher']==' ') {
      $chap_pub= NULL;
    }else {
      $chap_pub= $scholar_publication['Publisher'];
    }

    if ($scholar_publication['Book']==' ') {
      $chap_chap= 'without a book';
    }else {
      $chap_chap= $scholar_publication['Publisher'];
    }
    if ($scholar_publication['Journal']==' ') {
      $chap_jour= NULL;
    }else {
      $chap_jour= $scholar_publication['Journal'];
    }
    /////////////////////////////////////////////////////////////////
    $chap_title=Reposi_info_publication::reposi_string($pub_state['p_title']);

    $serch_rp = db_select('reposi_article_book', 'rp');
    $serch_rp->fields('rp')
    ->condition('rp.ab_type', 'Book Chapter', '=')
    ->condition('rp.ab_title', $chap_chap, '=')
    ->condition('rp.ab_subtitle_chapter', $chap_title, '=');
    $search_pubc = $serch_rp->execute()->fetchAssoc();
    if (!empty($search_pubc)) {
      drupal_set_message('Error, the Chapter book already exists. To import you must delete the existing Chapter book or Change the book','error');
    }
    else {
      db_insert('reposi_article_book')->fields(array(
        'ab_type'              => 'Book Chapter',
        'ab_title'             => $chap_chap,
        'ab_subtitle_chapter'  => $chap_title,
        'ab_chapter'           => $chap_issue,
        'ab_journal_editorial' => $chap_jour,
        'ab_publisher'         => $chap_pub,
      ))->execute();

      $search_chap = db_select('reposi_article_book', 'ab');
      $search_chap->fields('ab')
      ->condition('ab.ab_type', 'Book Chapter', '=')
      ->condition('ab.ab_title', $chap_chap, '=')
      ->condition('ab.ab_subtitle_chapter', $chap_title, '=');
      $chap_id = $search_chap->execute()->fetchField();

      db_insert('reposi_date')->fields(array(
        'd_year' => $new_chap_year,
        'd_abid' => $chap_id,
      ))->execute();

      db_insert('reposi_publication')->fields(array(
        'p_type'  => 'Book Chapter',
        'p_source'=> 'Google Scholar',
        'p_title' => $chap_title,
        'p_year'  => $new_chap_year,
        'p_check' => 0,
        'p_abid'  => $chap_id,
      ))->execute();

      if (!empty($chap_vol) || !empty($chap_issue) || !empty($chap_start_page) ||
      !empty($chap_url) || !empty($chap_final_page)) {
        db_insert('reposi_article_book_detail')->fields(array(
          'abd_volume'     => $chap_vol,
          'abd_issue'      => $chap_issue,
          'abd_start_page' => $chap_start_page,
          'abd_final_page' => $chap_final_page,
          'abd_url'        => $chap_url,
          'abd_abid'       => $chap_id,
        ))->execute();
      }

      $max = count($authorss);
      if(is_null($max)) {
        $max = 0;
      }
      $table = $authorss;
      for ($a=0; $a<$max; $a++) {
        $names=count($authorss[$a]);
        if ($names==2) {
          $aut_fn=$table[$a][0];
          $aut_sn='';
          $aut_fl=$table[$a][1];
          $aut_sl='';
        }elseif ($names==3) {
          $aut_fn=$table[$a][0];
          $aut_sn='';
          $aut_fl=$table[$a][1];
          $aut_sl=$table[$a][2];;
        }else {
          $aut_fn=$table[$a][0];
          $aut_sn=$table[$a][1];
          $aut_fl=$table[$a][2];
          $aut_sl=$table[$a][3];;
        }

        $info_author = array('a_first_name'      => ucfirst($aut_fn),
        'a_second_name'     => ucfirst($aut_sn),
        'a_first_lastname'  => ucfirst($aut_fl),
        'a_second_lastname' => ucfirst($aut_sl),
      );

      if(($aut_fn!='') && ($aut_fl!='')){
        $serch_a = db_select('reposi_author', 'a');
        $serch_a->fields('a')
        ->condition('a.a_first_name', $aut_fn, '=')
        ->condition('a.a_second_name', $aut_sn, '=')
        ->condition('a.a_first_lastname', $aut_fl, '=')
        ->condition('a.a_second_lastname', $aut_sl, '=');
        $serch_aut[$a] = $serch_a->execute()->fetchField();
        if (empty($serch_aut[$a])) {
          db_insert('reposi_author')->fields($info_author)->execute();
          $serch2_a = db_select('reposi_author', 'a');
          $serch2_a ->fields('a')
          ->condition('a.a_first_name', $aut_fn, '=')
          ->condition('a.a_second_name', $aut_sn, '=')
          ->condition('a.a_first_lastname', $aut_fl, '=')
          ->condition('a.a_second_lastname', $aut_sl, '=');
          $serch2_aut[$a] = $serch2_a->execute()->fetchField();
          $aut_publi_id = (int)$serch2_aut[$a];
          db_insert('reposi_publication_author')->fields(array(
            'ap_author_id' => $aut_publi_id,
            'ap_abid'      => $chap_id,
          ))->execute();
        } else {
          $aut_publi_id2 = (int)$serch_aut[$a];
          db_insert('reposi_publication_author')->fields(array(
            'ap_author_id' => $aut_publi_id2,
            'ap_abid'      => $chap_id,
          ))->execute();
        }
      }
    }
    reposidoc_scholar::delete_unde($uid);
    drupal_set_message('Data Import successfull');
  }
}
return $form;
}


public static function pubscolar_con($uid,$p_uid,$user_gs,$p_pid_scholar){
  $form = 0;
  $search_pub_state = db_select('reposi_publication', 'p');
  $search_pub_state->fields('p')
  ->condition('p.p_unde', $uid, '=');
  $pub_state = $search_pub_state->execute()->fetchAssoc();
  $search_doc = 'http://localhost/apiGS/getpublication.php?puser='.$user_gs.$p_pid_scholar;
  $data= file_get_contents($search_doc);
  $scholar_publication  = Json::decode($data);
  if (empty($data)){
    $form = 1;
    drupal_set_message('Error getting data. please make sure your api is working correctly.',"error");
  }else{
    $authors = explode(", ",$scholar_publication['authors']);
    $authors = implode(",",$authors);
    $authors = explode(",",$authors);
    foreach ($authors as $eids) {
      $authorss[] = explode(" ", $eids);
    }
    $authors = explode(", ",$scholar_publication['authors']);
    $search_pmax = db_select('reposi_publication', 'r');
    $search_pmax->fields('r',array('pid'))
    ->orderBy('r.pid', 'DESC');
    $p_max = $search_pmax->execute()->fetchAssoc();
    $search_pmax = db_select('reposi_publication', 'r');
    $search_pmax->fields('r',array('p_abid'))
    ->orderBy('r.p_abid', 'DESC');
    $p_max1 = $search_pmax->execute()->fetchAssoc();
    $new_abid=$p_max1['p_abid']+1;
    //echo 'ID '.$p_max['pid'].' ab_id '. $p_max1['p_abid'];
    $date=explode("/",$scholar_publication['Publication date']);

    if ($date[0]==" ") {
      $date[0]=1000;
    }

    $new_con_year = (int)$date[0];

    if (isset($date[2])) {
      $new_con_day = (int)$date[2];
    } else {
      $new_con_day = NULL;
    }
    if (isset($date[1])) {
      $new_con_month = (int)$date[1];
    } else {
      $new_con_month = NULL;
    }
    if ($scholar_publication['Pages']==' ') {
      # code...
    }else {
      $pages=explode("-",$scholar_publication['Pages']);
      $con_spage=$pages[0];
      $con_fpage=$pages[1];
    }
    if (!empty($con_spage)) {
      $con_start_page = $con_spage;
    } else {
      $con_start_page = NULL;
    }
    if (!empty($con_fpage)) {
      $con_final_page = $con_fpage;
    } else {
      $con_final_page = NULL;
    }
    if ($scholar_publication['Volume']==' ') {
      $con_vol= NULL;
    }else {
      $con_vol= $scholar_publication['Volume'];
    }
    if ($scholar_publication['Issue']==' ') {
      $con_issue= NULL;
    }else {
      $con_issue= $scholar_publication['Issue'];
    }
    if ($scholar_publication['URL']==' ') {
      $con_url= NULL;
    }else {
      $con_url= $scholar_publication['URL'];
    }
    if ($scholar_publication['Publisher']==' ') {
      $con_pub= NULL;
    }else {
      $con_pub= $scholar_publication['Publisher'];
    }

    if ($scholar_publication['abstract']==' ') {
      $con_abs= NULL;
    }else {
      $con_abs= $scholar_publication['abstract'];
    }
    if ($scholar_publication['Journal']==' ') {
      $con_jour= NULL;
    }else {
      $con_jour= $scholar_publication['Journal'];
    }
    if ($scholar_publication['Conference']==' ') {
      $con_con= 'without a conference';
    }else {
      $con_con= $scholar_publication['Conference'];
    }


    /////////////////////////////////////////////////////////////////
    $con_title=Reposi_info_publication::reposi_string($pub_state['p_title']);

    $serch_rp = db_select('reposi_confer_patent', 'rp');
    $serch_rp->fields('rp')
    ->condition('rp.cp_type', 'Conference', '=')
    ->condition('rp.cp_title', $con_con, '=')
    ->condition('rp.cp_publication', $con_title, '=');
    $search_pubc = $serch_rp->execute()->fetchAssoc();
    if (!empty($search_pubc)) {
      drupal_set_message('Error, the Conference already exists. To import you must delete the existing Conferen','error');
    }
    else {
      db_insert('reposi_confer_patent')->fields(array(
        'cp_type'       => 'Conference',
        'cp_title'      => $con_con,
        'cp_abstract'   => $con_abs,
        'cp_number'     => $con_issue,
        'cp_publication'=> $con_title,
        'cp_start_page' => $con_start_page,
        'cp_final_page' => $con_final_page,
        'cp_url'        => $con_url,
      ))->execute();
      $search_con = db_select('reposi_confer_patent', 'cp');
      $search_con->fields('cp')
      ->condition('cp.cp_type', 'Conference', '=')
      ->condition('cp.cp_publication', $con_title, '=');
      $con_id = $search_con->execute()->fetchField();
      $conference_id = (int)$con_id;
      db_insert('reposi_date')->fields(array(
        'd_day'   => $new_con_day,
        'd_month' => $new_con_month,
        'd_year'  => $new_con_year,
        'd_cpid'  => $conference_id,
      ))->execute();
      db_insert('reposi_date')->fields(array(
        'd_year'  => $new_con_year,
        'd_cpid'  => $conference_id,
      ))->execute();
      db_insert('reposi_date')->fields(array(
        'd_year'  => $new_con_year,
        'd_cpid'  => $conference_id,
      ))->execute();

      db_insert('reposi_publication')->fields(array(
        'p_type'  => 'Conference',
        'p_source'=> 'Google Scholar',
        'p_title' => $con_title,
        'p_year'  => $new_con_year,
        'p_check' => 0,
        'p_cpid'  => $conference_id,
      ))->execute();
      $max = count($authorss);
      if(is_null($max)) {
        $max = 0;
      }
      $table = $authorss;
      for ($a=0; $a<$max; $a++) {
        $names=count($authorss[$a]);
        if ($names==2) {
          $aut_fn=$table[$a][0];
          $aut_sn='';
          $aut_fl=$table[$a][1];
          $aut_sl='';
        }elseif ($names==3) {
          $aut_fn=$table[$a][0];
          $aut_sn='';
          $aut_fl=$table[$a][1];
          $aut_sl=$table[$a][2];;
        }else {
          $aut_fn=$table[$a][0];
          $aut_sn=$table[$a][1];
          $aut_fl=$table[$a][2];
          $aut_sl=$table[$a][3];;
        }

        $info_author = array('a_first_name'      => ucfirst($aut_fn),
        'a_second_name'     => ucfirst($aut_sn),
        'a_first_lastname'  => ucfirst($aut_fl),
        'a_second_lastname' => ucfirst($aut_sl),
      );

      if(($aut_fn!='') && ($aut_fl!='')){
        $serch_a = db_select('reposi_author', 'a');
        $serch_a->fields('a')
        ->condition('a.a_first_name', $aut_fn, '=')
        ->condition('a.a_second_name', $aut_sn, '=')
        ->condition('a.a_first_lastname', $aut_fl, '=')
        ->condition('a.a_second_lastname', $aut_sl, '=');
        $serch_aut[$a] = $serch_a->execute()->fetchField();
        if (empty($serch_aut[$a])) {
          db_insert('reposi_author')->fields($info_author)->execute();
          $serch2_a = db_select('reposi_author', 'a');
          $serch2_a ->fields('a')
          ->condition('a.a_first_name', $aut_fn, '=')
          ->condition('a.a_second_name', $aut_sn, '=')
          ->condition('a.a_first_lastname', $aut_fl, '=')
          ->condition('a.a_second_lastname', $aut_sl, '=');
          $serch2_aut[$a] = $serch2_a->execute()->fetchField();
          $aut_publi_id = (int)$serch2_aut[$a];
          db_insert('reposi_publication_author')->fields(array(
            'ap_author_id' => $aut_publi_id,
            'ap_cpid'      => $conference_id,
          ))->execute();
        } else {
          $aut_publi_id2 = (int)$serch_aut[$a];
          db_insert('reposi_publication_author')->fields(array(
            'ap_author_id' => $aut_publi_id2,
            'ap_cpid'      => $conference_id,
          ))->execute();
        }
      }
    }
    reposidoc_scholar::delete_unde($uid);
    drupal_set_message('Data Import successfull');
  }
}
return $form;
}

public static function pubscolar_pat($uid,$p_uid,$user_gs,$p_pid_scholar){
  $form = 0;
  $search_pub_state = db_select('reposi_publication', 'p');
  $search_pub_state->fields('p')
  ->condition('p.p_unde', $uid, '=');
  $pub_state = $search_pub_state->execute()->fetchAssoc();
  $search_doc = 'http://localhost/apiGS/getpublication.php?puser='.$user_gs.$p_pid_scholar;
  $data= file_get_contents($search_doc);
  $scholar_publication  = Json::decode($data);
  if (empty($data)){
    $form = 1;
    drupal_set_message('Error getting data. please make sure your api is working correctly.',"error");
  }else{
    $authors = explode(", ",$scholar_publication['authors']);
    $authors = implode(",",$authors);
    $authors = explode(",",$authors);
    foreach ($authors as $eids) {
      $authorss[] = explode(" ", $eids);
    }
    $authors = explode(", ",$scholar_publication['authors']);
    $search_pmax = db_select('reposi_publication', 'r');
    $search_pmax->fields('r',array('pid'))
    ->orderBy('r.pid', 'DESC');
    $p_max = $search_pmax->execute()->fetchAssoc();
    $search_pmax = db_select('reposi_publication', 'r');
    $search_pmax->fields('r',array('p_abid'))
    ->orderBy('r.p_abid', 'DESC');
    $p_max1 = $search_pmax->execute()->fetchAssoc();
    $new_abid=$p_max1['p_abid']+1;
    //echo 'ID '.$p_max['pid'].' ab_id '. $p_max1['p_abid'];
    $date=explode("/",$scholar_publication['Publication date']);

    if ($date[0]==" ") {
      $date[0]=1000;
    }

    $new_pat_year = (int)$date[0];

    if (isset($date[2])) {
      $new_pat_day = (int)$date[2];
    } else {
      $new_pat_day = NULL;
    }
    if (isset($date[1])) {
      $new_pat_month = (int)$date[1];
    } else {
      $new_pat_month = NULL;
    }
    if ($scholar_publication['Pages']==' ') {
      # code...
    }else {
      $pages=explode("-",$scholar_publication['Pages']);
      $pat_spage=$pages[0];
      $pat_fpage=$pages[1];
    }
    if (!empty($pat_spage)) {
      $pat_start_page = $pat_spage;
    } else {
      $pat_start_page = NULL;
    }
    if (!empty($pat_fpage)) {
      $pat_final_page = $pat_fpage;
    } else {
      $pat_final_page = NULL;
    }
    if ($scholar_publication['Volume']==' ') {
      $pat_vol= NULL;
    }else {
      $pat_vol= $scholar_publication['Volume'];
    }
    if ($scholar_publication['Number']==' ') {
      $pat_number= NULL;
    }else {
      $pat_number= $scholar_publication['Number'];
    }
    if ($scholar_publication['URL']==' ') {
      $pat_url= NULL;
    }else {
      $pat_url= $scholar_publication['URL'];
    }
    if ($scholar_publication['Publisher']==' ') {
      $pat_pub= NULL;
    }else {
      $pat_pub= $scholar_publication['Publisher'];
    }

    if ($scholar_publication['abstract']==' ') {
      $pat_abs= NULL;
    }else {
      $pat_abs= $scholar_publication['abstract'];
    }
    if ($scholar_publication['Journal']==' ') {
      $pat_jour= NULL;
    }else {
      $pat_jour= $scholar_publication['Journal'];
    }
    $pat_title=Reposi_info_publication::reposi_string($pub_state['p_title']);
    db_insert('reposi_confer_patent')->fields(array(
      'cp_type'       => 'Patent',
      'cp_title'      => $pat_title,
      'cp_abstract'   => $pat_abs,
      'cp_number'     => $pat_number,
      'cp_url'        => $pat_url,
    ))->execute();


    $search_pat = db_select('reposi_confer_patent', 'cp');
    $search_pat->fields('cp')
    ->condition('cp.cp_type', 'Patent', '=')
    ->condition('cp.cp_title', $pat_title, '=');
    $pat_id = $search_pat->execute()->fetchField();
    $patent_id = (int)$pat_id;
    db_insert('reposi_date')->fields(array(
      'd_day'   => $new_pat_day,
      'd_month' => $new_pat_month,
      'd_year'  => $new_pat_year,
      'd_cpid'  => $patent_id,
    ))->execute();


    db_insert('reposi_publication')->fields(array(
      'p_type'  => 'Patent',
      'p_source'=> 'Google Scholar',
      'p_title' => $pat_title,
      'p_year'  => $new_pat_year,
      'p_check' => 0,
      'p_cpid'  => $patent_id,
    ))->execute();


    $max = count($authorss);
    if(is_null($max)) {
      $max = 0;
    }
    $table = $authorss;
    for ($a=0; $a<$max; $a++) {
      $names=count($authorss[$a]);
      if ($names==2) {
        $aut_fn=$table[$a][0];
        $aut_sn='';
        $aut_fl=$table[$a][1];
        $aut_sl='';
      }elseif ($names==3) {
        $aut_fn=$table[$a][0];
        $aut_sn='';
        $aut_fl=$table[$a][1];
        $aut_sl=$table[$a][2];;
      }else {
        $aut_fn=$table[$a][0];
        $aut_sn=$table[$a][1];
        $aut_fl=$table[$a][2];
        $aut_sl=$table[$a][3];
      }

      $info_author = array('a_first_name'      => ucfirst($aut_fn),
      'a_second_name'     => ucfirst($aut_sn),
      'a_first_lastname'  => ucfirst($aut_fl),
      'a_second_lastname' => ucfirst($aut_sl),
    );

    if(($aut_fn!='') && ($aut_fl!='')){
      $serch_a = db_select('reposi_author', 'a');
      $serch_a->fields('a')
      ->condition('a.a_first_name', $aut_fn, '=')
      ->condition('a.a_second_name', $aut_sn, '=')
      ->condition('a.a_first_lastname', $aut_fl, '=')
      ->condition('a.a_second_lastname', $aut_sl, '=');
      $serch_aut[$a] = $serch_a->execute()->fetchField();
      if (empty($serch_aut[$a])) {
        db_insert('reposi_author')->fields($info_author)->execute();
        $serch2_a = db_select('reposi_author', 'a');
        $serch2_a ->fields('a')
        ->condition('a.a_first_name', $aut_fn, '=')
        ->condition('a.a_second_name', $aut_sn, '=')
        ->condition('a.a_first_lastname', $aut_fl, '=')
        ->condition('a.a_second_lastname', $aut_sl, '=');
        $serch2_aut[$a] = $serch2_a->execute()->fetchField();
        $aut_publi_id = (int)$serch2_aut[$a];
        db_insert('reposi_publication_author')->fields(array(
          'ap_author_id' => $aut_publi_id,
          'ap_cpid'      => $patent_id,
        ))->execute();
      } else {
        $aut_publi_id2 = (int)$serch_aut[$a];
        db_insert('reposi_publication_author')->fields(array(
          'ap_author_id' => $aut_publi_id2,
          'ap_cpid'      => $patent_id,
        ))->execute();
      }
    }
  }
  reposidoc_scholar::delete_unde($uid);
  drupal_set_message('Data Import successfull');
}
return $form;
}



public static function pubscolar_the($uid,$p_uid,$user_gs,$p_pid_scholar){
  $form = 0;
  $search_pub_state = db_select('reposi_publication', 'p');
  $search_pub_state->fields('p')
  ->condition('p.p_unde', $uid, '=');
  $pub_state = $search_pub_state->execute()->fetchAssoc();
  $search_doc = 'http://localhost/apiGS/getpublication.php?puser='.$user_gs.$p_pid_scholar;
  $data= file_get_contents($search_doc);
  $scholar_publication  = Json::decode($data);
  if (empty($data)){
    $form = 1;
    drupal_set_message('Error getting data. please make sure your api is working correctly.',"error");
  }else{
    $authors = explode(", ",$scholar_publication['authors']);
    $authors = implode(",",$authors);
    $authors = explode(",",$authors);
    foreach ($authors as $eids) {
      $authorss[] = explode(" ", $eids);
    }
    $authors = explode(", ",$scholar_publication['authors']);
    $search_pmax = db_select('reposi_publication', 'r');
    $search_pmax->fields('r',array('pid'))
    ->orderBy('r.pid', 'DESC');
    $p_max = $search_pmax->execute()->fetchAssoc();
    $search_pmax = db_select('reposi_publication', 'r');
    $search_pmax->fields('r',array('p_abid'))
    ->orderBy('r.p_abid', 'DESC');
    $p_max1 = $search_pmax->execute()->fetchAssoc();
    $new_abid=$p_max1['p_abid']+1;
    //echo 'ID '.$p_max['pid'].' ab_id '. $p_max1['p_abid'];
    $date=explode("/",$scholar_publication['Publication date']);

    if ($date[0]==" ") {
      $date[0]=1000;
    }

    $new_the_year = (int)$date[0];

    if (isset($date[2])) {
      $new_the_day = (int)$date[2];
    } else {
      $new_the_day = NULL;
    }
    if (isset($date[1])) {
      $new_the_month = (int)$date[1];
    } else {
      $new_the_month = NULL;
    }
    if ($scholar_publication['Pages']==' ') {
      # code...
    }else {
      $pages=explode("-",$scholar_publication['Pages']);
      $the_spage=$pages[0];
      $the_fpage=$pages[1];
    }
    if (!empty($the_spage)) {
      $the_start_page = $the_spage;
    } else {
      $the_start_page = NULL;
    }
    if (!empty($the_fpage)) {
      $the_final_page = $the_fpage;
    } else {
      $the_final_page = NULL;
    }
    if ($scholar_publication['Volume']==' ') {
      $the_vol= NULL;
    }else {
      $the_vol= $scholar_publication['Volume'];
    }
    if ($scholar_publication['Number']==' ') {
      $the_number= NULL;
    }else {
      $the_number= $scholar_publication['Number'];
    }
    if ($scholar_publication['URL']==' ') {
      $the_url= NULL;
    }else {
      $the_url= $scholar_publication['URL'];
    }
    if ($scholar_publication['Publisher']==' ') {
      $the_pub= NULL;
    }else {
      $the_pub= $scholar_publication['Publisher'];
    }

    if ($scholar_publication['abstract']==' ') {
      $the_abs= NULL;
    }else {
      $the_abs= $scholar_publication['abstract'];
    }
    if ($scholar_publication['Institution']==' ') {
      $the_ins= NULL;
    }else {
      $the_ins= $scholar_publication['Institution'];
    }
    $degree='Unspecified';
    $the_title=Reposi_info_publication::reposi_string($pub_state['p_title']);
    db_insert('reposi_thesis_sw')->fields(array(
      'ts_type'        => 'Thesis',
      'ts_title'       => $the_title,
      'ts_institu_ver' => $the_ins,
      'ts_degree'      => $degree,
      'ts_url'         => $the_url,
    ))->execute();
    $search_the = db_select('reposi_thesis_sw', 'th');
    $search_the->fields('th')
    ->condition('th.ts_type', 'Thesis', '=')
    ->condition('th.ts_title', $the_title, '=');
    $the_id = $search_the->execute()->fetchField();
    $thesis_id = (int)$the_id;

    db_insert('reposi_date')->fields(array(
      'd_day'   => $new_the_day,
      'd_month' => $new_the_month,
      'd_year'  => $new_the_year,
      'd_tsid'  => $thesis_id,
    ))->execute();

    db_insert('reposi_publication')->fields(array(
      'p_type'  => 'Thesis',
      'p_source'=> 'Google Scholar',
      'p_title' => $the_title,
      'p_year'  => $new_the_year,
      'p_check' => 0,
      'p_tsid'  => $thesis_id,
    ))->execute();


    $max = count($authorss);
    if(is_null($max)) {
      $max = 0;
    }
    $table = $authorss;
    for ($a=0; $a<$max; $a++) {
      $names=count($authorss[$a]);
      if ($names==2) {
        $aut_fn=$table[$a][0];
        $aut_sn='';
        $aut_fl=$table[$a][1];
        $aut_sl='';
      }elseif ($names==3) {
        $aut_fn=$table[$a][0];
        $aut_sn='';
        $aut_fl=$table[$a][1];
        $aut_sl=$table[$a][2];;
      }else {
        $aut_fn=$table[$a][0];
        $aut_sn=$table[$a][1];
        $aut_fl=$table[$a][2];
        $aut_sl=$table[$a][3];;
      }

      $info_author = array('a_first_name'      => ucfirst($aut_fn),
      'a_second_name'     => ucfirst($aut_sn),
      'a_first_lastname'  => ucfirst($aut_fl),
      'a_second_lastname' => ucfirst($aut_sl),
    );

    if(($aut_fn!='') && ($aut_fl!='')){
      $serch_a = db_select('reposi_author', 'a');
      $serch_a->fields('a')
      ->condition('a.a_first_name', $aut_fn, '=')
      ->condition('a.a_second_name', $aut_sn, '=')
      ->condition('a.a_first_lastname', $aut_fl, '=')
      ->condition('a.a_second_lastname', $aut_sl, '=');
      $serch_aut[$a] = $serch_a->execute()->fetchField();
      if (empty($serch_aut[$a])) {
        db_insert('reposi_author')->fields($info_author)->execute();
        $serch2_a = db_select('reposi_author', 'a');
        $serch2_a ->fields('a')
        ->condition('a.a_first_name', $aut_fn, '=')
        ->condition('a.a_second_name', $aut_sn, '=')
        ->condition('a.a_first_lastname', $aut_fl, '=')
        ->condition('a.a_second_lastname', $aut_sl, '=');
        $serch2_aut[$a] = $serch2_a->execute()->fetchField();
        $aut_publi_id = (int)$serch2_aut[$a];
        db_insert('reposi_publication_author')->fields(array(
          'ap_author_id' => $aut_publi_id,
          'ap_tsid'      => $thesis_id,
        ))->execute();
      } else {
        $aut_publi_id2 = (int)$serch_aut[$a];
        db_insert('reposi_publication_author')->fields(array(
          'ap_author_id' => $aut_publi_id2,
          'ap_tsid'      => $thesis_id,
        ))->execute();
      }
    }
  }
  reposidoc_scholar::delete_unde($uid);
  drupal_set_message('Data Import successfull');
}
return $form;
}

///////////////////////
public static function pubscolar_sof($uid,$p_uid,$user_gs,$p_pid_scholar){
  $form = 0;
  $search_pub_state = db_select('reposi_publication', 'p');
  $search_pub_state->fields('p')
  ->condition('p.p_unde', $uid, '=');
  $pub_state = $search_pub_state->execute()->fetchAssoc();
  $search_doc = 'http://localhost/apiGS/getpublication.php?puser='.$user_gs.$p_pid_scholar;
  $data= file_get_contents($search_doc);
  $scholar_publication  = Json::decode($data);
  if (empty($data)){
    $form = 1;
    drupal_set_message('Error getting data. please make sure your api is working correctly.',"error");
  }else{
    $authors = explode(", ",$scholar_publication['authors']);
    $authors = implode(",",$authors);
    $authors = explode(",",$authors);
    foreach ($authors as $eids) {
      $authorss[] = explode(" ", $eids);
    }
    $authors = explode(", ",$scholar_publication['authors']);
    $search_pmax = db_select('reposi_publication', 'r');
    $search_pmax->fields('r',array('pid'))
    ->orderBy('r.pid', 'DESC');
    $p_max = $search_pmax->execute()->fetchAssoc();
    $search_pmax = db_select('reposi_publication', 'r');
    $search_pmax->fields('r',array('p_abid'))
    ->orderBy('r.p_abid', 'DESC');
    $p_max1 = $search_pmax->execute()->fetchAssoc();
    $new_abid=$p_max1['p_abid']+1;
    //echo 'ID '.$p_max['pid'].' ab_id '. $p_max1['p_abid'];
    $date=explode("/",$scholar_publication['Publication date']);

    if ($date[0]==" ") {
      $date[0]=1000;
    }

    $new_sw_year = (int)$date[0];

    if (isset($date[2])) {
      $new_sw_day = (int)$date[2];
    } else {
      $new_sw_day = NULL;
    }
    if (isset($date[1])) {
      $new_sw_month = (int)$date[1];
    } else {
      $new_sw_month = NULL;
    }
    if ($scholar_publication['Pages']==' ') {
      # code...
    }else {
      $pages=explode("-",$scholar_publication['Pages']);
      $sw_spage=$pages[0];
      $sw_fpage=$pages[1];
    }
    if (!empty($sw_spage)) {
      $sw_start_page = $sw_spage;
    } else {
      $sw_start_page = NULL;
    }
    if (!empty($sw_fpage)) {
      $sw_final_page = $sw_fpage;
    } else {
      $sw_final_page = NULL;
    }
    if ($scholar_publication['Volume']==' ') {
      $sw_vol= NULL;
    }else {
      $sw_vol= $scholar_publication['Volume'];
    }
    if ($scholar_publication['Number']==' ') {
      $sw_number= NULL;
    }else {
      $sw_number= $scholar_publication['Number'];
    }
    if ($scholar_publication['URL']==' ') {
      $sw_url= NULL;
    }else {
      $sw_url= $scholar_publication['URL'];
    }
    if ($scholar_publication['Publisher']==' ') {
      $sw_pub= NULL;
    }else {
      $sw_pub= $scholar_publication['Publisher'];
    }

    if ($scholar_publication['abstract']==' ') {
      $sw_abs= NULL;
    }else {
      $sw_abs= $scholar_publication['abstract'];
    }
    if ($scholar_publication['P_office']==' ') {
      $sw_pof= NULL;
    }else {
      $sw_pof= $scholar_publication['P_office'];
    }
    /////////////////////////////////////////////////////////////////
    $sw_title=Reposi_info_publication::reposi_string($pub_state['p_title']);
    db_insert('reposi_thesis_sw')->fields(array(
      'ts_type'        => 'Software',
      'ts_title'       => $sw_title,
      'ts_discip_place'=> $sw_pof,
      'ts_url'         => $sw_url,
    ))->execute();

    $search_sw = db_select('reposi_thesis_sw', 'sw');
    $search_sw->fields('sw')
    ->condition('sw.ts_type', 'Software', '=')
    ->condition('sw.ts_title', $sw_title, '=');
    $softw_id = $search_sw->execute()->fetchField();
    $sw_id = (int)$softw_id;

    db_insert('reposi_date')->fields(array(
      'd_day'   => $new_sw_day,
      'd_month' => $new_sw_month,
      'd_year'  => $new_sw_year,
      'd_tsid'  => $sw_id,
    ))->execute();

    db_insert('reposi_publication')->fields(array(
      'p_type'  => 'Software',
      'p_source'=> 'Google Scholar',
      'p_title' => $sw_title,
      'p_year'  => $new_sw_year,
      'p_check' => 0,
      'p_tsid'  => $sw_id,
    ))->execute();


    $max = count($authorss);
    if(is_null($max)) {
      $max = 0;
    }
    $table = $authorss;
    for ($a=0; $a<$max; $a++) {
      $names=count($authorss[$a]);
      if ($names==2) {
        $aut_fn=$table[$a][0];
        $aut_sn='';
        $aut_fl=$table[$a][1];
        $aut_sl='';
      }elseif ($names==3) {
        $aut_fn=$table[$a][0];
        $aut_sn='';
        $aut_fl=$table[$a][1];
        $aut_sl=$table[$a][2];;
      }else {
        $aut_fn=$table[$a][0];
        $aut_sn=$table[$a][1];
        $aut_fl=$table[$a][2];
        $aut_sl=$table[$a][3];;
      }

      $info_author = array('a_first_name'      => ucfirst($aut_fn),
      'a_second_name'     => ucfirst($aut_sn),
      'a_first_lastname'  => ucfirst($aut_fl),
      'a_second_lastname' => ucfirst($aut_sl),
    );

    if(($aut_fn!='') && ($aut_fl!='')){
      $serch_a = db_select('reposi_author', 'a');
      $serch_a->fields('a')
      ->condition('a.a_first_name', $aut_fn, '=')
      ->condition('a.a_second_name', $aut_sn, '=')
      ->condition('a.a_first_lastname', $aut_fl, '=')
      ->condition('a.a_second_lastname', $aut_sl, '=');
      $serch_aut[$a] = $serch_a->execute()->fetchField();
      if (empty($serch_aut[$a])) {
        db_insert('reposi_author')->fields($info_author)->execute();
        $serch2_a = db_select('reposi_author', 'a');
        $serch2_a ->fields('a')
        ->condition('a.a_first_name', $aut_fn, '=')
        ->condition('a.a_second_name', $aut_sn, '=')
        ->condition('a.a_first_lastname', $aut_fl, '=')
        ->condition('a.a_second_lastname', $aut_sl, '=');
        $serch2_aut[$a] = $serch2_a->execute()->fetchField();
        $aut_publi_id = (int)$serch2_aut[$a];
        db_insert('reposi_publication_author')->fields(array(
          'ap_author_id' => $aut_publi_id,
          'ap_tsid'      => $sw_id,
        ))->execute();
      } else {
        $aut_publi_id2 = (int)$serch_aut[$a];
        db_insert('reposi_publication_author')->fields(array(
          'ap_author_id' => $aut_publi_id2,
          'ap_tsid'      => $sw_id,
        ))->execute();
      }
    }
  }
  reposidoc_scholar::delete_unde($uid);
  drupal_set_message('Data Import successfull');
}
return $form;
}

public static function delete_unde($uid){
  $del_publi = db_delete('reposi_publication')
  ->condition('p_unde', $uid)
  ->execute();
  $del_publi_author = db_delete('reposi_publication_author')
  ->condition('ap_unde', $uid)
  ->execute();
}


public static function docs_scholar(){
  $form['body'] = array();
  // http://cse.bth.se/~fer/googlescholar.php?user=Z9vU8awAAAAJ
  $config = \Drupal::config('system.maintenance');
  $apikey_query_start = $config->get('query_scholar_start');
  $apikey_query_final = $config->get('query_scholar_final');
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
  $eid_doc_scholar = array();
  $form['body'] = array();
  $num_articles = 0;
  $num_books = 0;
  $num_book_chaps = 0;
  foreach ($author_info as $id_scholar) {
    $title_doc_scholar = array();
    $pre_date_scholar = array();
    $date_scholar = array();
    $pre_type_scholar = array();
    $type_doc_scholar = array();
    $num_articles = 0;
    $num_books = 0;
    $num_book_chaps = 0;
    if ($id_scholar['u_id_scholar']==!NULL) {
      $author_id = $id_scholar['u_id_scholar'];


      $search_doc = 'http://localhost/googlescholar-api/gspublication.php?user='.$author_id;
      ////////////
      $client = \Drupal::httpClient();
      $response = $client->get($search_doc, ['timeout' => 600]);
      $data = $response->getBody();
      $scholar_publication = explode('{', $data);
        //$scholar_title = explode('{', $data);
        $data_number = count($scholar_publication);
        //  echo print_r($scholar_publication,true. '  *******************LA CUENTA ES: ' .$data_number);
        //$jsonData = json_encode($_POST);
        /*$headers = ['Content-Type' => 'application/json'];
        $response = $client->request('POST', $search_doc, ['timeout' => 600, 'headers'=>$headers,'body' => 						$jsonData]);
        //  $response = $client->request('GET', '/delay/5', ['timeout' => 3.14]);
        $data = $response->getBody();
        //$decoded = Json::decode($data);
        $scholar_user = explode('{', $data);
        $scholar_data = explode('"name": "', $data);
        $data_number = count($scholar_data);
        $scholar_info = explode('",',$data);
        $header = array(t('Google Scholar ID'), t('Name'), t('Affiliation'));
        drupal_set_message('user scholar:'.$author_id);*/

        //$link_scholar = \Drupal::l($scholar_id_user, Url::fromRoute('reposi.reposi_apischolar.scholar_assoc', ['node'=>$authors_name['uid'], 'nod'=>$scholar_id_user]));
        for($i=2; $i<$data_number; $i++){
          //		drupal_set_message($scholar_publication[$i]);
          $scholar_doc = explode('",', $scholar_publication[$i]);
          $scholar_doc_title1 = explode('"title": "', $scholar_doc[0]);
          $scholar_doc_authors = explode('"authors": "', $scholar_doc[1]);
          $scholar_doc_year = explode('"year": ', $scholar_doc[3]);
          //	$scholar_doc_authors = substr($scholar_doc[1],-50,8);
          $scholar_doc_title = $scholar_doc_title1[1];
          $scholar_doc_authors = $scholar_doc_authors[1];
          $scholar_doc_year = $scholar_doc_year[1];
          $scholar_doc_year = substr($scholar_doc_year,-50,4);
          /*			$link_publication_scholar = \Drupal::l($scholar_doc_title, Url::fromRoute('reposi.reposi_apischolar.scholar_assoc', ['user'=>$author_id, 'pid'=>$scholar_id_user]));*/
          $form['doc'][$i] = array('#markup' => '<br><strong>'.$scholar_doc_title.'</strong>, '.$scholar_doc_authors.', <strong>'.		$scholar_doc_year.'</strong></br>');
          //                    if.................................
          $form['title'][$i] = array('#value' => $scholar_doc_title);
          $form['authors'][$i] = array('#value' => $scholar_doc_authors);
          $form['year'][$i] = array('#value' => $scholar_doc_year);

          $google_scholar_information = array('title'=>$scholar_doc_title,'authors'=>$scholar_doc_authors,'year'=>$scholar_doc_year);
          $form['scholar_information'][$i] = array('#value' => $google_scholar_information);
          //		drupal_set_message('$scholar_doc_title: '.$form['scholar_information'][$i]['#value']['title']);
          //		drupal_set_message('$scholar_doc_title: '.$scholar_doc_title);

          /*			$search_pub = db_select('reposi_publication', 'p');
          $search_pub->fields('p');
          $find_pub = $search_pub->execute();
          foreach ($form['title'] as $title_docs) {
          $title_doc_scholar[] = $title_docs['#value'];
          //			drupal_set_message('<br>$scholar_doc_title: '.$title_docs['#value'].'</br>');
        }
        foreach ($form['authors'] as $authors_docs) {
        $authors_doc_scholar[] = $authors_docs['#value'];
        //			drupal_set_message('<br>$scholar_doc_title: '.$authors_docs['#value'].'</br>');
      }
      foreach ($form['year'] as $year_docs) {
      $year_doc_scholar[] = $year_docs['#value'];
      //	drupal_set_message('year '.$year_docs['#value'].'</br>');
    }
    foreach ($form['scholar_information'] as $scholar_information) {
    $gs_scholar_information[] = $scholar_information['#value'];
    drupal_set_message('year '.print_r($gs_scholar_information,true).'</br>');
  }*/
  $search_pub = db_select('reposi_publication', 'p');
  $search_pub->fields('p');
  $find_pub = $search_pub->execute();
  $cuenta=count($find_pub);
  $count_pub=0;
  foreach ($find_pub as $pub) {
    $count_pub++;
  }
  drupal_set_message('cuentaaAA:'.$count_pub);
  if($count_pub>0)
  {
    foreach ($find_pub as $list_p) {
      $pub_title = $list_p->p_title;
      $pub_year = $list_p->p_year;
      $pub_id = $list_p->pid;
      $database_information  = array("title_database"=>$pub_title, "year_database"=>$pub_year);
      $form['database_information'] = array('#value' => $database_information);
      //			drupal_set_message('titulo scholar: '.print_r($form['database_information'],true).'el i');
      if(($pub_title==$form['scholar_information'][$i]['#value']['title'] && $pub_year==$form['scholar_information'][$i]['#value']['year'])){
        drupal_set_message('PUBLICAIÓN IGUAL añoIGUAL AÑO BASE DE DATOS:'.$pub_title. 'VS año gs:'.$scholar_doc_year. 'id:'.$pub_id);
        $form['doc'][$i] = array('#markup' => '<br><strong>'.$scholar_doc_title.'</strong>, '.$scholar_doc_authors.', <strong>'.		$scholar_doc_year.'</strong></br>');

      }
      else{
        drupal_set_message('PUBLICAIÓN diferentes:'.$scholar_doc_title.$scholar_doc_year);
        $form['doc'][$i] = array('#markup' => '<br><strong>'.$scholar_doc_title.'</strong>, '.$scholar_doc_authors.', <strong>'.		$scholar_doc_year.'</strong></br>');
      }
    }


  }
  else{
    drupal_set_message('no hay publicaiones en la base de datos');
    $mezcla =array_combine($form['title'][$i], $form['year'][$i]);

    /*foreach ($mezcla  as $title => $year) {
    db_insert('reposi_publication')->fields(array(
    'p_type'       => 'Undefined',
    'p_title'      => $title,
    'p_year'       => $year,
    'p_check'      => 0,
    'p_source'     => t('Google Scholar'),
    ))->execute();
    //$scholar_title = explode('||||',$scholar_information);
    echo $i . '  '. ' TITULO:'.$title. ' AÑO:'.$year. '   ';
    drupal_set_message(' TITULO:'.$title. ' AÑO:'.$year);
  }*/
  foreach ($form['title'][$i]  as $scholar_information) {
    /*	db_insert('reposi_publication')->fields(array(
    'p_type'       => 'Undefined',
    'p_title'      => $scholar_information,
    'p_year'       => 5555,
    'p_check'      => 0,
    'p_source'     => t('Google Scholar'),
    ))->execute();*/
    //$scholar_title = explode('||||',$scholar_information);


  }
}
//               drupal_set_message('DATABASE: '.print_r($database_information,true). ' scholar:'.print_r($google_scholar_information,true));
//		array_combine($google_scholar_information, $database_information);

/*	foreach (array_combine($scholar_information, $find_pub) as $scholar_information => $list_p){
}

/*	for($i=0; $i<count($title_doc_scholar); $i++){
$google_scholar_information = array("title"=>$title_doc_scholar[$i],"authors"=>$authors_doc_scholar[$i],"year"=>$year_doc_scholar[$i]);
drupal_set_message('$scholar_doc_title: '.print_r($google_scholar_information,true));
}*/
///////////////////////// C I E R R A    E L 	F O R //////////////////////////////////////////
}
///////////////////////////////////////////////////////////////////////////////////////////////
$search_pub = db_select('reposi_publication', 'p');
$search_pub->fields('p');
$find_pub = $search_pub->execute();
$cuenta=count($find_pub);
$count_pub=0;
foreach ($find_pub as $pub) {
  $count_pub++;
}
drupal_set_message('cuentaaAA:'.$count_pub);
if($count_pub>0)
{
}
else{
  foreach ($form['scholar_information'] as $scholar_information) {
    /*db_insert('reposi_publication')->fields(array(
    'p_type'       => 'Undefined',
    'p_title'      => $scholar_information['#value']['title'],
    'p_year'       => $scholar_information['#value']['year'],
    'p_check'      => 0,
    'p_source'     => t('Google Scholar'),
    ))->execute();*/
    echo print_r($scholar_information['#value'],true);
    drupal_set_message('year '.print_r($scholar_information['#value'],true).'</br>');
  }
}
}}
////////////////////
/*	$get_info_docs = file_get_contents($search_doc);
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
$url_scopus_abstract = 'https://api.elsevier.com/content/search/scopus?query=eid(' .
$docs .')&field=dc:description&apikey=' . $apikey_scopus;
$search_abstract = file_get_contents($url_scopus_abstract);
$pre_abstract = explode('dc:description":"', $search_abstract);
if (isset($pre_abstract[1])) {
$abstract = explode('"}]}}', $pre_abstract[1]);
} else {
$abstract = array('');
}
$url_scopus_author = 'https://api.elsevier.com/content/search/scopus?query=eid(' .
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
$form['doc']['body'] = array('#markup' => $info_show);*/
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
