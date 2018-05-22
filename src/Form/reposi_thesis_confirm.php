<?php
namespace Drupal\reposi\Form;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Component\Utility\Xss;
use Drupal\reposi\Controller\Reposi_info_publication;

class reposi_thesis_confirm extends ConfirmFormBase{

    protected $id;

    /**
     * {@inheritdoc}.
     */
    public function getFormId()
    {
        return 'reposi_thesis_confirm_form';
    }

    /**
     * {@inheritdoc}
     */
    public function getQuestion() {

       return t('Thesis update confirmation'); 
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

    $the_id = \Drupal::request()->query->get('tsid');
    $the_day = \Drupal::request()->query->get('day');
    $the_month = \Drupal::request()->query->get('month');
    $the_year = \Drupal::request()->query->get('year');
    $the_url = \Drupal::request()->query->get('url');
    $the_institute = \Drupal::request()->query->get('institute');
    $the_discipline = \Drupal::request()->query->get('discipline');
    $the_author = \Drupal::request()->query->get('info_author');
    $the_keyword = \Drupal::request()->query->get('keyword');

/////////////////////////////////////////////////////////////////////////////////////////


   $form['author'] = array(
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => t('Author(s)'),
    );
   $form['keyword'] = array(
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => t('Keyword(s)'),
    );
   $form['date'] = array(
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => t('Publication date'),
    );
   $form['detail'] = array(
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => t('Details'),
    );
  for ($a=0; $a<count($the_author); $a++) {
  if(!empty($the_author[$a]['first_name']))
  {
  $form['author'][$a] = array('#markup' => '<li>'. $the_author[$a]['first_name']. ' '. $the_author[$a]['second_name']
                            . ' '.$the_author[$a]['f_lastname'].' '. $the_author[$a]['s_lastname'] . '</li>');
  }
  }
  for ($a=0; $a<count($the_keyword); $a++) {
  if(!empty($the_keyword[$a]['key']))
  {
  $form['keyword'][$a] = array('#markup' => '<li>'. $the_keyword[$a]['key'] . '</li>');
  }
  }
   $form['date']['body'] = array('#markup' => Reposi_info_publication::reposi_formt_date($the_day, $the_month, $the_year));
   $form['detail']['institute'] = array('#markup' => '<li>'. '<i>'. t('Academic institution: ') . '</i>' . $the_institute. '</li>' );
   $form['detail']['discipline'] = array('#markup' => '<li>'. '<i>'. t('Discipline: ') . '</i>' . $the_discipline. '</li>' );
   $form['detail']['url'] = array('#markup' => '<li>'. '<i>'. t('URL: ') . '</i>' . $the_url. '</li>' );
   $form['pager'] = ['#type' => 'pager'];
        $this->id = $id;
        return parent::buildForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {

    $the_id = \Drupal::request()->query->get('tsid');
    $the_day = \Drupal::request()->query->get('day');
    $the_month = \Drupal::request()->query->get('month');
    $the_year = \Drupal::request()->query->get('year');
    $the_url = \Drupal::request()->query->get('url');
    $the_institute = \Drupal::request()->query->get('institute');
    $the_discipline = \Drupal::request()->query->get('discipline');
    $new_aut2 = \Drupal::request()->query->get('info_author');
    $the_keywords = \Drupal::request()->query->get('keyword');
    db_update('reposi_thesis_sw')->fields(array(
      'ts_institu_ver'  => $the_institute,
      'ts_discip_place' => $the_discipline,
      'ts_url'          => $the_url,
    ))->condition('tsid', $the_id)
    ->execute();
    if (!empty($the_day)) {
      $new_day = (int)$the_day;
    } else {
      $new_day = NULL;
    }
    if (!empty($the_month)) {
      $new_month = (int)$the_month;
    } else {
      $new_month = NULL;
    }
    $new_year = (int)$the_year;
    db_update('reposi_date')->fields(array(
      'd_day'   => $new_day,
      'd_month' => $new_month,
      'd_year'  => $new_year,
    ))->condition('d_tsid', $the_id)
    ->execute(); 
  for ($q = 0; $q <= count($the_keywords) ; $q++) {
    if (!empty($the_keywords[$q]['key'])) {
      $new_keywords2[] = $the_keywords[$q]['key'];
    } else {
      $new_keywords2[] = NULL;
    }
  }
    $cont_keywords=0;
    if (!empty($new_keywords2)) {
      $new_relation = db_delete('reposi_publication_keyword')
        ->condition('pk_tsid', $the_id)
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
              'pk_tsid'       => $the_id,
            ))->execute();
          } else {
            $serch_k_p = db_select('reposi_publication_keyword', 'pk');
            $serch_k_p->fields('pk')
                    ->condition('pk.pk_keyword_id', $serch_keyw[$cont_keywords], '=')
                    ->condition('pk.pk_tsid', $the_id, '=');
            $serch_keyword[$cont_keywords] = $serch_k_p->execute()->fetchField();
            if (empty($serch_keyword[$cont_keywords])) {
              db_insert('reposi_publication_keyword')->fields(array(
                'pk_keyword_id' => $serch_keyw[$cont_keywords],
                'pk_tsid'       => $the_id,
              ))->execute();
            } 
          }
          $cont_keywords++;
        }  
      }
    }
    if (!empty($new_aut2)) {
      $new_relation = db_delete('reposi_publication_author')
        ->condition('ap_tsid', $the_id)
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
            'ap_tsid'       => $the_id,
          ))->execute();
        } else {
          $search_p_a = db_select('reposi_publication_author', 'pa');
          $search_p_a->fields('pa')
                     ->condition('pa.ap_author_id', $id_new_aut, '=')
                     ->condition('pa.ap_tsid', $the_id, '=');
          $p_a = $search_p_a->execute()->fetchField();
          if (empty($p_a)) {
            db_insert('reposi_publication_author')->fields(array(
              'ap_author_id' => $id_new_aut,
              'ap_tsid'       => $the_id,
            ))->execute();
          }
        }
      }
    } 
           drupal_set_message(t('The publication was updated.'));
           $form_state->setRedirect('reposi.Reposi_thesinformation', ['node' => $the_id]);
}
}
