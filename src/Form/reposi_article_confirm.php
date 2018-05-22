<?php
namespace Drupal\reposi\Form;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Component\Utility\Xss;
use Drupal\reposi\Controller\Reposi_info_publication;

class reposi_article_confirm extends ConfirmFormBase{

    protected $id;

    /**
     * {@inheritdoc}.
     */
    public function getFormId()
    {
        return 'reposi_article_confirm_form';
    }

    /**
     * {@inheritdoc}
     */
    public function getQuestion() {

       return t('Article update confirmation'); 
    }

    /**
     * {@inheritdoc}
     */
    public function getCancelUrl() {
        return new Url('reposi.Reposi_public_list');
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription() {
	$description =t('Do you want update this information?'); 
	return $description;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfirmText() {
        return $this->t('Accept');
    }


    /**
     * {@inheritdoc}
     */
    public function getCancelText() {
        return $this->t('Cancel');
    }

    /**
     * {@inheritdoc}
     *
     * @param int $id
     *   (optional) The ID of the item to be deleted.
     */
    public function buildForm(array $form, FormStateInterface $form_state, $id = NULL) {

  $art_abid = \Drupal::request()->query->get('abid');
  $art_abstract = \Drupal::request()->query->get('abstract');
  $art_day = \Drupal::request()->query->get('day');
  $art_month = \Drupal::request()->query->get('month');
  $art_year = \Drupal::request()->query->get('year');
  $art_name = \Drupal::request()->query->get('jou_name');
  $art_vol = \Drupal::request()->query->get('jou_volume');
  $art_issue = \Drupal::request()->query->get('jou_issue');
  $art_spage = \Drupal::request()->query->get('jou_start_page');
  $art_fpage = \Drupal::request()->query->get('jou_final_page');
  $art_issn = \Drupal::request()->query->get('jou_issn');
  $art_url = \Drupal::request()->query->get('url');
  $art_doi = \Drupal::request()->query->get('doi');
  $art_author = \Drupal::request()->query->get('info_author');
  $art_keyword = \Drupal::request()->query->get('keyword');
   $form['abstract'] = array(
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => t('Abstract'),
    );
   $form['author'] = array(
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => t('Author(s)'),
    );
   $form['abstract']['body'] = array('#markup' => $art_abstract);
   $form['keyword'] = array(
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => t('Keyword(s)'),
    );
  $arrayautores=print_r($art_author,true);
  for ($a=0; $a<count($art_author); $a++) {
  if(!empty($art_author[$a]['first_name']))
  {
  $form['author']['andrea'][$a]['cuerpo'] = array('#markup' => '<li>'. $art_author[$a]['first_name']. ' '. $art_author[$a]['second_name']
                            . ' '.$art_author[$a]['f_lastname'].' '. $art_author[$a]['s_lastname'] . '</li>');
  }
  }
  for ($a=0; $a<count($art_keyword); $a++) {
  if(!empty($art_keyword[$a]['key']))
  {
  $form['keyword'][$a]['body'] = array('#markup' => '<li>'. $art_keyword[$a]['key'] . '</li>');
  }
  }
   $form['date'] = array(
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => t('Date'),
    );
   $form['date']['body'] = array('#markup' => Reposi_info_publication::reposi_formt_date($art_day, $art_month, $art_year));
   $form['journal'] = array(
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => t('Journal/Book'),
    );
   $form['url'] = array(
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => t('URL'),
    );
   $form['doi'] = array(
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => t('DOI'),
    );
   $description = '<li>'. '<i>'. t('Volume: ') . '</i>' . $art_vol. '</li>' . 
            '<li>'. '<i>'. t('Issue: ') . '</i>' . $art_issue . '</li>' .
            '<li>'. '<i>'. t('Start page: ') . '</i>' . $art_spage . '</li>' .
            '<li>'. '<i>'. t('Final page: ') . '</i>' . $art_fpage . '</li>' .
            '<li>'. '<i>'. t('ISSN: ') . '</i>' . $art_issn . '</li>' . '</ul>';
   $form['journal']['body'] = array('#markup' => $description);
   $form['url']['body'] = array('#markup' => $art_url);
   $form['doi']['body'] = array('#markup' => $art_doi);
   $form['pager'] = ['#type' => 'pager'];
        $this->id = $id;
        return parent::buildForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {

  $art_id = \Drupal::request()->query->get('abid');
  $art_abstract = \Drupal::request()->query->get('abstract');
  $art_day = \Drupal::request()->query->get('day');
  $art_month = \Drupal::request()->query->get('month');
  $art_year = \Drupal::request()->query->get('year');
  $art_name = \Drupal::request()->query->get('jou_name');
  $art_volume = \Drupal::request()->query->get('jou_volume');
  $art_issue = \Drupal::request()->query->get('jou_issue');
  $art_start_page = \Drupal::request()->query->get('jou_start_page');
  $art_final_page = \Drupal::request()->query->get('jou_final_page');
  $art_issn = \Drupal::request()->query->get('jou_issn');
  $art_url = \Drupal::request()->query->get('url');
  $art_doi = \Drupal::request()->query->get('doi');
  $art_author = \Drupal::request()->query->get('info_author');
  $art_keyword = \Drupal::request()->query->get('keyword');
  $author = $form_state->getValue('andrea');

    if (!empty($art_start_page)){
      $new_start_page=(int)$art_start_page;
    } else {
      $new_start_page=NULL;
    }
    if (!empty($art_final_page)){
      $new_final_page=(int)$art_final_page;
    } else {
      $new_final_page=NULL;
    }
    db_update('reposi_article_book')->fields(array(
      'ab_abstract'          => $art_abstract,
      'ab_journal_editorial' => $art_name,
    ))->condition('abid', $art_id)
    ->execute();
    if (!empty($art_volume) || !empty($art_issue) || !empty($art_start_page) ||
        !empty($art_final_page) || !empty($art_issn) || !empty($art_url) || !empty($art_doi)) {
      $search_art_detail = db_select('reposi_article_book_detail', 'abd');
      $search_art_detail->fields('abd')
              ->condition('abd.abd_abid', $art_id, '=');
      $info_art = $search_art_detail->execute()->fetchField();
      if (empty($info_art)) {
        db_insert('reposi_article_book_detail')->fields(array(
          'abd_volume'     => $art_volume,
          'abd_issue'      => $art_issue,
          'abd_start_page' => $new_start_page,
          'abd_final_page' => $new_final_page,
          'abd_issn'       => $art_issn,
          'abd_url'        => $art_url,
          'abd_doi'        => $art_doi,
          'abd_abid'       => $art_id,
        ))->execute();
      } else {
        db_update('reposi_article_book_detail')->fields(array(
          'abd_volume'     => $art_volume,
          'abd_issue'      => $art_issue,
          'abd_start_page' => $new_start_page,
          'abd_final_page' => $new_final_page,
          'abd_issn'       => $art_issn,
          'abd_url'        => $art_url,
          'abd_doi'        => $art_doi,
        ))->condition('abd_abid', $art_id)
        ->execute();
      }
    }
    if (!empty($art_day)) {
      $new_day = (int)$art_day;
    } else {
      $new_day = NULL;
    }
    if (!empty($art_month)) {
      $new_month = (int)$art_month;
    } else {
      $new_month = NULL;
    }
    db_update('reposi_date')->fields(array(
      'd_day'   => $new_day,
      'd_month' => $new_month,
      'd_year'  => $art_year,
    ))->condition('d_abid', $art_id)
    ->execute(); 


  for ($q = 0; $q <= count($art_keyword) ; $q++) {
    if (!empty($art_keyword[$q]['key'])) {
      $new_keywords2[] = $art_keyword[$q]['key'];
    } else {
      $new_keywords2[] = NULL;
    }
  }
    $cont_keywords=0;
    if (!empty($new_keywords2)) {
      $new_relation = db_delete('reposi_publication_keyword')
        ->condition('pk_abid', $art_id)
        ->execute();
      foreach ($new_keywords2 as $new_keyw){
        if(isset($new_keyw)){
          $serch_k = db_select('reposi_keyword', 'k');
          $serch_k->fields('k')
                  ->condition('k.k_word', $new_keyw, '=');
          $serch_keyw[$cont_keywords] = $serch_k->execute()->fetchField();
          if (empty($serch_keyw[$cont_keywords])) {
            db_insert('reposi_keyword')->fields(array(
              'k_word' => $new_keyw,
            ))->execute();
            $serch2_k = db_select('reposi_keyword', 'k');
            $serch2_k->fields('k')
                      ->condition('k.k_word', $new_keyw, '=');
            $serch2_keyw[$cont_keywords] = $serch2_k->execute()->fetchField();
            $serch2_keyw_id = (int)$serch2_keyw[$cont_keywords];
            db_insert('reposi_publication_keyword')->fields(array(
              'pk_keyword_id' => $serch2_keyw_id,
              'pk_abid'       => $art_id,
            ))->execute();
          } else {
            $serch_k_p = db_select('reposi_publication_keyword', 'pk');
            $serch_k_p->fields('pk')
                    ->condition('pk.pk_keyword_id', $serch_keyw[$cont_keywords], '=')
                    ->condition('pk.pk_abid', $art_id, '=');
            $serch_keyword[$cont_keywords] = $serch_k_p->execute()->fetchField();
            if (empty($serch_keyword[$cont_keywords])) {
              db_insert('reposi_publication_keyword')->fields(array(
                'pk_keyword_id' => $serch_keyw[$cont_keywords],
                'pk_abid'       => $art_id,
              ))->execute();
            } 
          }
          $cont_keywords++;
        }  
      }
    }       
    if (!empty($art_author)) {
      $new_relation = db_delete('reposi_publication_author')
        ->condition('ap_abid', $art_id)
        ->execute();
      foreach ($art_author as $new_aut) {
        $search_aut = db_select('reposi_author', 'a');
        $search_aut->fields('a')
                   ->condition('a.a_first_name', $new_aut['first_name'], '=')
                   ->condition('a.a_second_name', $new_aut['second_name'], '=')
                   ->condition('a.a_first_lastname', $new_aut['f_lastname'], '=')
                   ->condition('a.a_second_lastname', $new_aut['s_lastname'], '=');
        $id_new_aut = $search_aut->execute()->fetchField();
        if (empty($id_new_aut)) {
          $new_aut_1 = strtolower($new_aut['first_name']);
          $new_aut_2 = strtolower($new_aut['second_name']);
          $new_aut_3 = strtolower($new_aut['f_lastname']);
          $new_aut_4 = strtolower($new_aut['s_lastname']);
          $new_author_1 = ucfirst($new_aut_1);
          $new_author_2 = ucfirst($new_aut_2);
          $new_author_3 = ucfirst($new_aut_3);
          $new_author_4 = ucfirst($new_aut_4);
          db_insert('reposi_author')->fields(array(
            'a_first_name'      => $new_author_1,
            'a_second_name'     => $new_author_2,
            'a_first_lastname'  => $new_author_3,
            'a_second_lastname' => $new_author_4,
          ))->execute();
          $serch_a = db_select('reposi_author', 'a');
          $serch_a->fields('a')
                ->condition('a.a_first_name', $new_aut['first_name'], '=')
                ->condition('a.a_second_name', $new_aut['second_name'], '=')
                ->condition('a.a_first_lastname', $new_aut['f_lastname'], '=')
                ->condition('a.a_second_lastname', $new_aut['s_lastname'], '=');
          $id_aut2 = $serch_a->execute()->fetchField();
          db_insert('reposi_publication_author')->fields(array(
            'ap_author_id' => $id_aut2,
            'ap_abid'       => $art_id,
          ))->execute();
        }else {
          $search_p_a = db_select('reposi_publication_author', 'pa');
          $search_p_a->fields('pa')
                     ->condition('pa.ap_author_id', $id_new_aut, '=')
                     ->condition('pa.ap_abid', $art_id, '=');
          $p_a = $search_p_a->execute()->fetchField();
          if (empty($p_a)) {
            db_insert('reposi_publication_author')->fields(array(
              'ap_author_id' => $id_new_aut,
              'ap_abid'       => $art_id,
            ))->execute();
          }
        }
      }
    }
           drupal_set_message(t('The publication was updated.'));
           $form_state->setRedirect('reposi.Reposi_articleinformation', ['node' => $art_id]);
}
}
