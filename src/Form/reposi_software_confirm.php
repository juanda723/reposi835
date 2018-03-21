<?php
namespace Drupal\reposi\Form;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Component\Utility\Xss;
use Drupal\reposi\Controller\Reposi_info_publication;

class reposi_software_confirm extends ConfirmFormBase{

    protected $id;

    /**
     * {@inheritdoc}.
     */
    public function getFormId()
    {
        return 'reposi_software_confirm_form';
    }

    /**
     * {@inheritdoc}
     */
    public function getQuestion() {

       return t('Software update confirmation'); 
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

    $sw_tsid = \Drupal::request()->query->get('tsid');
    $sw_year = \Drupal::request()->query->get('year');
    $sw_url = \Drupal::request()->query->get('url');
    $sw_version = \Drupal::request()->query->get('version');
    $sw_place = \Drupal::request()->query->get('place');
    $sw_author = \Drupal::request()->query->get('info_author');
 
   $form['author'] = array(
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => t('Author(s)'),
    );
  for ($a=0; $a<count($sw_author); $a++) {
  if(!empty($sw_author[$a]['first_name']))
  {
  $form['author'][$a] = array('#markup' => '<li>'. $sw_author[$a]['first_name']. ' '. $sw_author[$a]['second_name']
                            . ' '.$sw_author[$a]['f_lastname'].' '. $sw_author[$a]['s_lastname'] . '</li>');
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

   $form['date']['body']  = array('#markup' => t('Year: ') .$sw_year);
   $form['deta']['version'] = array('#markup' => '<li>' . '<i>' . t('Version: ') . '</i>' .$sw_version. '</li>');
   $form['deta']['place']  = array('#markup' => '<li>' . '<i>' . t('Place of production: ') . '</i>' .$sw_place. '</li>');
   $form['deta']['url']   = array('#markup' => '<li>' . '<i>' . t('URL: ') . '</i>' .$sw_url. '</li>');
   $form['pager'] = ['#type' => 'pager'];
        $this->id = $id;
        return parent::buildForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {

    $sw_id = \Drupal::request()->query->get('tsid');
    $sw_year = \Drupal::request()->query->get('year');
    $sw_url = \Drupal::request()->query->get('url');
    $sw_version = \Drupal::request()->query->get('version');
    $sw_place = \Drupal::request()->query->get('place');
    $new_aut2 = \Drupal::request()->query->get('info_author');
    db_update('reposi_thesis_sw')->fields(array(
      'ts_institu_ver'  => $sw_version,
      'ts_discip_place' => $sw_place,
      'ts_url'          => $sw_url,
    ))->condition('tsid', $sw_id)
    ->execute();
    $new_year = (int)$sw_year;
    db_update('reposi_date')->fields(array(
      'd_year'  => $new_year,
    ))->condition('d_tsid', $sw_id)
    ->execute(); 
    if (!empty($new_aut2)) {
      $new_relation = db_delete('reposi_publication_author')
        ->condition('ap_tsid', $sw_id)
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
            'ap_tsid'       => $sw_id,
          ))->execute();
        } else {
          $search_p_a = db_select('reposi_publication_author', 'pa');
          $search_p_a->fields('pa')
                     ->condition('pa.ap_author_id', $id_new_aut, '=')
                     ->condition('pa.ap_tsid', $sw_id, '=');
          $p_a = $search_p_a->execute()->fetchField();
          if (empty($p_a)) {
            db_insert('reposi_publication_author')->fields(array(
              'ap_author_id' => $id_new_aut,
              'ap_tsid'       => $sw_id,
            ))->execute();
          }
        }
      }
    } 

           drupal_set_message(t('The software was updated.'));
           $form_state->setRedirect('reposi.Reposi_sofinformation', ['node' => $sw_id]);
}
}
