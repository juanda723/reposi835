<?php
namespace Drupal\reposi\Form;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Component\Utility\Xss;
use Drupal\reposi\Controller\Reposi_info_publication;

class reposi_conference_confirm extends ConfirmFormBase{

    protected $id;

    /**
     * {@inheritdoc}.
     */
    public function getFormId()
    {
        return 'reposi_conference_confirm_form';
    }

    /**
     * {@inheritdoc}
     */
    public function getQuestion() {

       return t('Conference update'); 
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

    $con_cpid = \Drupal::request()->query->get('cpid');
    $con_day = \Drupal::request()->query->get('day');
    $con_month = \Drupal::request()->query->get('month');
    $con_year = \Drupal::request()->query->get('year');
    $con_spage = \Drupal::request()->query->get('start_page');
    $con_fpage = \Drupal::request()->query->get('final_page');
    $con_confer = \Drupal::request()->query->get('confer');
    $con_description = \Drupal::request()->query->get('description');
    $con_num_event = \Drupal::request()->query->get('num_event');
    $con_sponsor = \Drupal::request()->query->get('sponsor');
    $con_place = \Drupal::request()->query->get('place');
    $con_start_day = \Drupal::request()->query->get('start_day');
    $con_start_month = \Drupal::request()->query->get('start_month');
    $con_start_year = \Drupal::request()->query->get('start_year');
    $con_end_day = \Drupal::request()->query->get('end_day');
    $con_end_month = \Drupal::request()->query->get('end_month');
    $con_end_year = \Drupal::request()->query->get('end_year');
    $con_url = \Drupal::request()->query->get('url');
    $con_doi = \Drupal::request()->query->get('doi');
    $con_author = \Drupal::request()->query->get('info_author');
    $con_keyword = \Drupal::request()->query->get('keyword');
 
  $form['conference'] = array(
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => t('Conference'),
    );
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
   $form['abstract']['body'] = array('#markup' => $con_description);
   $form['keyword'] = array(
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => t('Keyword(s)'),
    );
  for ($a=0; $a<count($con_author); $a++) {
  if(!empty($con_author[$a]['first_name']))
  {
  $form['author'][$a] = array('#markup' => '<li>'. $con_author[$a]['first_name']. ' '. $con_author[$a]['second_name']
                            . ' '.$con_author[$a]['f_lastname'].' '. $con_author[$a]['s_lastname'] . '</li>');
  }
  }
  for ($a=0; $a<count($con_keyword); $a++) {
  if(!empty($con_keyword[$a]['key']))
  {
  $form['keyword'][$a]['body'] = array('#markup' => '<li>'. $con_keyword[$a]['key'] . '</li>');
  }
  }
   $form['date'] = array(
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => t('Date'),
    );
   $form['deta'] = array(
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => t('Details'),
    );
   $form['conference']['confer'] = array('#markup' => $con_confer);
   $form['date']['body'] = array('#markup' => '<li>' . '<i>' . t('Publication date: ') . '</i>' .
			         Reposi_info_publication::reposi_formt_date($con_day, $con_month, $con_year));
   $form['date']['start_date'] = array('#markup' => '<li>' . '<i>' . t('Start date: ') . '</i>' .
                                 Reposi_info_publication::reposi_formt_date($con_start_day, $con_start_month, $con_start_year). '</li>');
   $form['date']['end_date'] = array('#markup' => '<li>' . '<i>' . t('End date: ') . '</i>' .
                                 Reposi_info_publication::reposi_formt_date($con_end_day, $con_end_month, $con_end_year). '</li>');
   $form['deta']['start_page'] = array('#markup' => '<li>' . '<i>' . t('Start page: ') . '</i>' .$con_spage. '</li>');
   $form['deta']['final_page'] = array('#markup' => '<li>' . '<i>' . t('Final page: ') . '</i>' .$con_fpage. '</li>');
   $form['deta']['num_event']  = array('#markup' => '<li>' . '<i>' . t('Event number: ') . '</i>' .$con_num_event. '</li>');
   $form['deta']['sponsor']    = array('#markup' => '<li>' . '<i>' . t('Sponsor(s): ') . '</i>' .$con_sponsor. '</li>');
   $form['deta']['place']      = array('#markup' => '<li>' . '<i>' . t('Event place: ') . '</i>' .$con_place. '</li>');
   $form['deta']['url']        = array('#markup' => '<li>' . '<i>' . t('URL: ') . '</i>' .$con_url. '</li>');
   $form['deta']['doi']        = array('#markup' => '<li>' . '<i>' . t('DOI: ') . '</i>' .$con_doi. '</li>');
   $form['pager'] = ['#type' => 'pager'];
        $this->id = $id;
        return parent::buildForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {

    $con_id = \Drupal::request()->query->get('cpid');
    $con_day = \Drupal::request()->query->get('day');
    $con_month = \Drupal::request()->query->get('month');
    $con_year = \Drupal::request()->query->get('year');
    $con_spage = \Drupal::request()->query->get('start_page');
    $con_fpage = \Drupal::request()->query->get('final_page');
    $con_confer = \Drupal::request()->query->get('confer');
    $con_description = \Drupal::request()->query->get('description');
    $con_num_event = \Drupal::request()->query->get('num_event');
    $con_sponsor = \Drupal::request()->query->get('sponsor');
    $con_place = \Drupal::request()->query->get('place');
    $con_start_day = \Drupal::request()->query->get('start_day');
    $con_start_month = \Drupal::request()->query->get('start_month');
    $con_start_year = \Drupal::request()->query->get('start_year');
    $con_end_day = \Drupal::request()->query->get('end_day');
    $con_end_month = \Drupal::request()->query->get('end_month');
    $con_end_year = \Drupal::request()->query->get('end_year');
    $con_url = \Drupal::request()->query->get('url');
    $con_doi = \Drupal::request()->query->get('doi');
    $new_aut2 = \Drupal::request()->query->get('info_author');
    $con_keywords = \Drupal::request()->query->get('keyword');
    if (!empty($con_spage)){
      $new_con_spage = (int)$con_spage;
    } else {
      $new_con_spage = NULL;
    }
    if (!empty($con_fpage)){
      $new_con_fpage = (int)$con_fpage;
    } else {
      $new_con_fpage = NULL;
    }
    db_update('reposi_confer_patent')->fields(array(
      'cp_title'      => $con_confer,
      'cp_abstract'   => $con_description,
      'cp_number'     => $con_num_event,
      'cp_spon_owner' => $con_sponsor,
      'cp_place_type' => $con_place,
      'cp_start_page' => $new_con_spage,
      'cp_final_page' => $new_con_fpage,
      'cp_url'        => $con_url,
      'cp_doi'        => $con_doi,
    ))->condition('cpid', $con_id)
    ->execute();
    if (!empty($con_day)){
      $new_con_day = (int)$con_day;
    } else {
      $new_con_day = NULL;
    }
    if (!empty($con_month)){
      $new_con_month = (int)$con_month;
    } else {
      $new_con_month = NULL;
    }
    if (!empty($con_start_day)){
      $new_con_sday = (int)$con_start_day;
    } else {
      $new_con_sday = NULL;
    }
    if (!empty($con_start_month)){
      $new_con_smonth = (int)$con_start_month;
    } else {
      $new_con_smonth = NULL;
    }
    if (!empty($con_end_day)){
      $new_con_eday = (int)$con_end_day;
    } else {
      $new_con_eday = NULL;
    }
    if (!empty($con_end_month)){
      $new_con_emonth = (int)$con_end_month;
    } else {
      $new_con_emonth = NULL;
    }
    $new_con_year = (int)$con_year;
    $new_con_syear = (int)$con_start_year;
    $new_con_eyear = (int)$con_end_year;
    $id_date = array();
    $search_date = db_select('reposi_date', 'd');
    $search_date->fields('d')
                ->condition('d.d_cpid', $con_id, '=');
    $con_date = $search_date->execute();
    foreach ($con_date as $new_date) {
      $id_date[] = $new_date->did;
    }
    db_update('reposi_date')->fields(array(
      'd_day'   => $new_con_day,
      'd_month' => $new_con_month,
      'd_year'  => $new_con_year,
    ))->condition('did', $id_date[0])
    ->execute();
    db_update('reposi_date')->fields(array(
      'd_day'   => $new_con_sday,
      'd_month' => $new_con_smonth,
      'd_year'  => $new_con_syear,
    ))->condition('did', $id_date[1])
    ->execute();
    db_update('reposi_date')->fields(array(
      'd_day'   => $new_con_eday,
      'd_month' => $new_con_emonth,
      'd_year'  => $new_con_eyear,
    ))->condition('did', $id_date[2])
    ->execute();
    if (!empty($new_aut2)) {
      $new_relation = db_delete('reposi_publication_author')
        ->condition('ap_cpid', $con_id)
        ->execute();
      foreach ($new_aut2 as $new_aut) {
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
            'ap_cpid'       => $con_id,
          ))->execute();
        } else {
          $search_p_a = db_select('reposi_publication_author', 'pa');
          $search_p_a->fields('pa')
                     ->condition('pa.ap_author_id', $id_new_aut, '=')
                     ->condition('pa.ap_cpid', $con_id, '=');
          $p_a = $search_p_a->execute()->fetchField();
          if (empty($p_a)) {
            db_insert('reposi_publication_author')->fields(array(
              'ap_author_id' => $id_new_aut,
              'ap_cpid'       => $con_id,
            ))->execute();
          }
        }
      }
    } 
    for ($q = 0; $q <= count($con_keywords) ; $q++) {
      if (!empty($con_keywords[$q]['key'])) {
      $new_keywords2[] = $con_keywords[$q]['key'];
      } else {
      $new_keywords2[] = NULL;
      }
    }
    $cont_keywords=0;
    if (!empty($new_keywords2)) {
      $new_relation = db_delete('reposi_publication_keyword')
        ->condition('pk_cpid', $con_id)
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
              'pk_cpid'       => $con_id,
            ))->execute();
          } else {
            $serch_k_p = db_select('reposi_publication_keyword', 'pk');
            $serch_k_p->fields('pk')
                    ->condition('pk.pk_keyword_id', $serch_keyw[$cont_keywords], '=')
                    ->condition('pk.pk_cpid', $con_id, '=');
            $serch_keyword[$cont_keywords] = $serch_k_p->execute()->fetchField();
            if (empty($serch_keyword[$cont_keywords])) {
              db_insert('reposi_publication_keyword')->fields(array(
                'pk_keyword_id' => $serch_keyw[$cont_keywords],
                'pk_cpid'       => $con_id,
              ))->execute();
            } 
          }
          $cont_keywords++;
        }  
      }
    }

           drupal_set_message(t('The publication was updated.'));
           $form_state->setRedirect('reposi.Reposi_coninformation', ['node' => $con_id]);
}
}
