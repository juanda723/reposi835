<?php
namespace Drupal\reposi\Form;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Component\Utility\Xss;
use Drupal\reposi\Controller\Reposi_info_publication;

class reposi_patent_confirm extends ConfirmFormBase{

    protected $id;

    /**
     * {@inheritdoc}.
     */
    public function getFormId()
    {
        return 'reposi_patent_confirm_form';
    }

    /**
     * {@inheritdoc}
     */
    public function getQuestion() {

       return t('Patent update confirmation'); 
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

    $pat_cpid = \Drupal::request()->query->get('cpid');
    $pat_day = \Drupal::request()->query->get('day');
    $pat_month = \Drupal::request()->query->get('month');
    $pat_year = \Drupal::request()->query->get('year');
    $pat_url = \Drupal::request()->query->get('url');
    $pat_abstract = \Drupal::request()->query->get('abstract');
    $pat_owner = \Drupal::request()->query->get('owner');
    $pat_type = \Drupal::request()->query->get('type');
    $pat_num = \Drupal::request()->query->get('num');
    $pat_author = \Drupal::request()->query->get('info_author');
 
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
   $form['abstract']['body'] = array('#markup' => $pat_abstract);

  for ($a=0; $a<count($pat_author); $a++) {
  if(!empty($pat_author[$a]['first_name']))
  {
  $form['author'][$a] = array('#markup' => '<li>'. $pat_author[$a]['first_name']. ' '. $pat_author[$a]['second_name']
                            . ' '.$pat_author[$a]['f_lastname'].' '. $pat_author[$a]['s_lastname'] . '</li>');
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

   $form['date']['body']  = array('#markup' => Reposi_info_publication::reposi_formt_date($pat_day, $pat_month, $pat_year));
   $form['deta']['owner'] = array('#markup' => '<li>' . '<i>' . t('Owner: ') . '</i>' .$pat_owner. '</li>');
   $form['deta']['type']  = array('#markup' => '<li>' . '<i>' . t('Type patent: ') . '</i>' .$pat_type. '</li>');
   $form['deta']['num']   = array('#markup' => '<li>' . '<i>' . t('Number: ') . '</i>' .$pat_num. '</li>');
   $form['deta']['url']   = array('#markup' => '<li>' . '<i>' . t('URL: ') . '</i>' .$pat_url. '</li>');
   $form['pager'] = ['#type' => 'pager'];
        $this->id = $id;
        return parent::buildForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {

    $pat_id = \Drupal::request()->query->get('cpid');
    $pat_day = \Drupal::request()->query->get('day');
    $pat_month = \Drupal::request()->query->get('month');
    $pat_year = \Drupal::request()->query->get('year');
    $pat_url = \Drupal::request()->query->get('url');
    $pat_abstract = \Drupal::request()->query->get('abstract');
    $pat_owner = \Drupal::request()->query->get('owner');
    $pat_type = \Drupal::request()->query->get('type');
    $pat_num = \Drupal::request()->query->get('num');
    $new_aut2 = \Drupal::request()->query->get('info_author');
    db_update('reposi_confer_patent')->fields(array(
      'cp_abstract'   => $pat_abstract,
      'cp_number'     => $pat_num,
      'cp_spon_owner' => $pat_owner,
      'cp_place_type' => $pat_type,
      'cp_url'        => $pat_url,
    ))->condition('cpid', $pat_id)
    ->execute();
    if (!empty($pat_day)){
      $new_pat_day = (int)$pat_day;
    } else {
      $new_pat_day = NULL;
    }
    if (!empty($pat_month)){
      $new_pat_month = (int)$pat_month;
    } else {
      $new_pat_month = NULL;
    }
    $new_pat_year = (int)$pat_year;
    db_update('reposi_date')->fields(array(
      'd_day'   => $new_pat_day,
      'd_month' => $new_pat_month,
      'd_year'  => $new_pat_year,
    ))->condition('d_cpid', $pat_id)
    ->execute();
    if (!empty($new_aut2)) {
      $new_relation = db_delete('reposi_publication_author')
        ->condition('ap_cpid', $pat_id)
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
            'ap_cpid'      => $pat_id,
          ))->execute();
        } else {
          $search_p_a = db_select('reposi_publication_author', 'pa');
          $search_p_a->fields('pa')
                     ->condition('pa.ap_author_id', $id_new_aut, '=')
                     ->condition('pa.ap_cpid', $pat_id, '=');
          $p_a = $search_p_a->execute()->fetchField();
          if (empty($p_a)) {
            db_insert('reposi_publication_author')->fields(array(
              'ap_author_id' => $id_new_aut,
              'ap_cpid'      => $pat_id,
            ))->execute();
          }
        }
      }
    }

           drupal_set_message(t('The patent was updated.'));
           $form_state->setRedirect('reposi.Reposi_patinformation', ['node' => $pat_id]);
}
}
