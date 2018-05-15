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
        if (isset($pages[1])) {
          $art_fpage=$pages[1];
        }else {
          $art_fpage=$pages[0];
        }

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
      if (isset($pages[1])) {
        $chap_fpage=$pages[1];
      }else {
        $chap_fpage=$pages[0];
      }
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
      if (isset($pages[1])) {
        $con_fpage=$pages[1];
      }else {
        $con_fpage=$pages[0];
      }

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
      ->condition('cp.cp_title', $con_con, '=')
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

    $serch_rp = db_select('reposi_confer_patent', 'rp');
    $serch_rp->fields('rp')
    ->condition('rp.cp_type', 'Patent', '=')
    ->condition('rp.cp_title', $pat_title, '=');
    $search_pubc = $serch_rp->execute()->fetchAssoc();
    if (!empty($search_pubc)) {
      drupal_set_message('Error, the Patent already exists. To import you must delete the existing Conferen','error');
    }
    else {

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

    $serch_rp = db_select('reposi_thesis_sw', 'rp');
    $serch_rp->fields('rp')
    ->condition('rp.ts_type', 'Thesis', '=')
    ->condition('rp.ts_title', $the_title, '=');
    $search_pubc = $serch_rp->execute()->fetchAssoc();
    if (!empty($search_pubc)) {
      drupal_set_message('Error, the Thesis already exists. To import you must delete the existing Conferen','error');
    }
    else {


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
}}
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

    $serch_rp = db_select('reposi_thesis_sw', 'rp');
    $serch_rp->fields('rp')
    ->condition('rp.ts_type', 'Software', '=')
    ->condition('rp.ts_title', $p_title, '=');
    $search_pubc = $serch_rp->execute()->fetchAssoc();
    if (!empty($search_pubc)) {
      drupal_set_message('Error, the Software already exists. To import you must delete the existing Conferen','error');
    }
    else {

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
  $del_publi = db_delete('reposi_undefined_publication')
  ->condition('upid', $uid)
  ->execute();
}


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
        $scholar_id[$i] = $scholar_doc_id[$i].$scholar_doc_title[$i];

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
            'up_year'	=> $scholar_doc_year[$i],
            'up_pid_scholar'=> $scholar_doc_id[$i],
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

          $search_pub_state = db_select('reposi_publication', 'p');
          $search_pub_state->fields('p', array('p_pid_scholar', 'p_title'));
          $pub_state = $search_pub_state->execute()->fetchAll();

          for ($a=0; $a <count($pub_state) ; $a++) {
            $scholar_pub_id_db[$a]= $pub_state[$a]->p_pid_scholar;
            $reposi_pub_title_db[$a]= $pub_state[$a]->p_title;
            $db_id[$a] = $scholar_pub_id_db[$a].$reposi_pub_title_db[$a];
          }

          if (!in_array($scholar_id[$i], $db_id)) {

            $form['doc'][$i][$p] = array('#markup' => '<br><strong>['.$i.'] '.$scholar_doc_title[$i]
            .'</strong>, '.$scholar_doc_authors[$i].', <strong>'.$scholar_doc_year[$i].'</strong></br>');

            db_insert('reposi_undefined_publication')->fields(array(
              'up_title'	=> $scholar_doc_title[$i],
              'up_year'	=> $scholar_doc_year[$i],
              'up_pid_scholar'=> $scholar_doc_id[$i],
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
    }
    else{
      $markup = 'No usuarios de Google Scholar';
    }
  }
  return $form;


}

function reposi_author_scholar(){


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
    if (($authors_name['u_id_scholar']==NULL || $authors_name['u_id_scholar']=='') && $authorscount>1) {
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
      if (!empty($search_author_scholar)) {


        $data1= file_get_contents($search_author_scholar);
        if (empty($data1)){
          drupal_set_message('Error getting data. please make sure your api is working correctly.',"error");
        }else{
          $scholar_publication  = Json::decode($data1);
          $numaut=(int)count($scholar_publication['Autors']);
          $header = array(t('Google Scholar ID'), t('Name'), t('Affiliation'));
          $form['scholar'][$search_lastname_1] = array(
            '#type' => 'details',
            '#open' => TRUE,
            '#title' => $author_name,
            '#description' => 'Associate with a Google Scholar User',
          );
          $form['scholar'][$search_lastname_1]['table']  = array(
            '#type' => 'table',
            '#title' => 'Scholar Author Table',
            '#header' => $header,
            '#empty' => t('No lines found'),
          );
          for($i=0; $i<$numaut; $i++)
          {
            $scholar_name = $scholar_publication['Autors'][$i]['Name'];
            $scholar_info = $scholar_publication['Autors'][$i]['institution'];
            $scholar_id_user = $scholar_publication['Autors'][$i]['user'];
            $rows = array($scholar_id_user,$scholar_name,$scholar_info);
            $link_scholar = \Drupal::l($scholar_id_user, Url::fromRoute('reposi.reposi_apischolar.scholar_assoc', ['node'=>$authors_name['uid'], 'nod'=>$scholar_id_user]));
            $form['scholar'][$search_lastname_1]['table'][$i]['id_author_scholar'] = array('#markup' => $link_scholar);
            $form['scholar'][$search_lastname_1]['table'][$i]['author_name_scholar'] = array('#markup' => $scholar_name);
            $form['scholar'][$search_lastname_1]['table'][$i]['author_information_scholar'] = array('#markup'=>$scholar_info);}
          }
        }

      }
    }
    return $form;

  }

}
