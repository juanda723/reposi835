<?php
namespace Drupal\reposi\Form;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Component\Utility\Xss;
use Drupal\reposi\Controller\Reposi_info_publication;

class reposi_chap_book_confirm extends ConfirmFormBase{

    protected $id;

    /**
     * {@inheritdoc}.
     */
    public function getFormId()
    {
        return 'reposi_chap_book_confirm_form';
    }

    /**
     * {@inheritdoc}
     */
    public function getQuestion() {

       return t('Chap book update confirmation'); 
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

    $chap_abid = \Drupal::request()->query->get('abid');
    $chap_chapter_num = \Drupal::request()->query->get('chapter_num');
    $chap_year = \Drupal::request()->query->get('year');
    $chap_volume = \Drupal::request()->query->get('vol');
    $chap_issue = \Drupal::request()->query->get('issue');
    $chap_edito = \Drupal::request()->query->get('edito');
    $chap_ed_name = \Drupal::request()->query->get('editor_name');
    $chap_spage = \Drupal::request()->query->get('start_page');
    $chap_fpage = \Drupal::request()->query->get('final_page');
    $chap_place = \Drupal::request()->query->get('pub');
    $chap_issn = \Drupal::request()->query->get('issn');
    $chap_isbn = \Drupal::request()->query->get('isbn');
    $chap_url = \Drupal::request()->query->get('url');
    $chap_doi = \Drupal::request()->query->get('doi');
    $chap_author = \Drupal::request()->query->get('info_author');

    $form['chapter_num'] = array(
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => t('Chapter Number'),
    );
    $form['author'] = array(
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => t('Author(s)'),
    );
    $form['date'] = array(
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => t('Year Publication'),
    );
    $form['detail'] = array(
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => t('Details'),
    );
    $form['chapter_num']['num'] = array('#markup' => $chap_chapter_num);
    $form['date']['year'] = array('#markup' => $chap_year);
    $form['detail']['vol'] = array('#markup' => '<li>' . '<i>' . t('Volume: ') . '</i>' . $chap_volume . '</li>');
    $form['detail']['issue'] = array('#markup' => '<li>' . '<i>' . t('Issue: ') . '</i>' . $chap_issue . '</li>');
    $form['detail']['edito'] = array('#markup' => '<li>' . '<i>' . t('Editorial: ').'</i>'.$chap_edito . '</li>');
    $form['detail']['editor_name'] = array('#markup' => '<li>' . '<i>' . t('Publisher name: ').'</i>'.$chap_ed_name . '</li>');
    $form['detail']['start_page'] = array('#markup' => '<li>' . '<i>' . t('Start page: ').'</i>'.$chap_spage . '</li>');
    $form['detail']['final_page'] = array('#markup' => '<li>' . '<i>' . t('Final page: ').'</i>'.$chap_fpage . '</li>');
    $form['detail']['pub'] = array('#markup' => '<li>' . '<i>' . t('Publication’s place: ').'</i>'.$chap_place . '</li>');
    $form['detail']['issn'] = array('#markup' => '<li>' . '<i>' . t('ISSN: ') . '</i>' . $chap_issn . '</li>');
    $form['detail']['isbn'] = array('#markup' => '<li>' . '<i>' . t('ISBN: ') . '</i>' . $chap_isbn . '</li>');
    $form['detail']['url'] = array('#markup' => '<li>' . '<i>' . t('URL: ') . '</i>' . $chap_url . '</li>');
    $form['detail']['doi'] = array('#markup' => '<li>' . '<i>' . t('DOI: ') . '</i>' . $chap_doi . '</li>');
    for ($a=0; $a<count($chap_author); $a++) {
    if(!empty($chap_author[$a]['first_name']))
    	{
    	$form['author'][$a]['full_name'] = array('#markup' => '<li>'. $chap_author[$a]['first_name']. ' '. $chap_author[$a]['second_name']
                           		   . ' '.$chap_author[$a]['f_lastname'].' '. $chap_author[$a]['s_lastname'] . '</li>');
    	}
    }
   $form['pager'] = ['#type' => 'pager'];
        $this->id = $id;
        return parent::buildForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {

    $chap_id = \Drupal::request()->query->get('abid');
    $chap_chapter_num = \Drupal::request()->query->get('chapter_num');
    $chap_year = \Drupal::request()->query->get('year');
    $chap_volume = \Drupal::request()->query->get('vol');
    $chap_issue = \Drupal::request()->query->get('issue');
    $chap_edito = \Drupal::request()->query->get('edito');
    $chap_ed_name = \Drupal::request()->query->get('editor_name');
    $chap_spage = \Drupal::request()->query->get('start_page');
    $chap_fpage = \Drupal::request()->query->get('final_page');
    $chap_place = \Drupal::request()->query->get('pub');
    $chap_issn = \Drupal::request()->query->get('issn');
    $chap_isbn = \Drupal::request()->query->get('isbn');
    $chap_url = \Drupal::request()->query->get('url');
    $chap_doi = \Drupal::request()->query->get('doi');
    $new_aut2 = \Drupal::request()->query->get('info_author');
    if (!empty($chap_chapter_num)){
      $new_num = (int)$chap_chapter_num;
    } else {
      $new_num = NULL;
    }
    db_update('reposi_article_book')->fields(array(
      'ab_chapter'           => $new_num,
      'ab_journal_editorial' => $chap_edito,
      'ab_publisher'         => $chap_ed_name,
      'ab_place'             => $chap_place,
    ))->condition('abid', $chap_id)
    ->execute();
    if (!empty($chap_volume) || !empty($chap_issue) || !empty($chap_issn) ||
        !empty($chap_isbn) || !empty($chap_url) || !empty($chap_doi) || 
        !empty($chap_spage) || !empty($chap_fpage)) {
      if (!empty($chap_spage)){
        $new_spage = (int)$chap_spage;
      } else {
        $new_spage = NULL;
      }
      if (!empty($chap_fpage)){
        $new_fpage = (int)$chap_fpage;
      } else {
        $new_fpage = NULL;
      }
      $search_art_detail = db_select('reposi_article_book_detail', 'abd');
      $search_art_detail->fields('abd')
              ->condition('abd.abd_abid', $chap_id, '=');
      $info_art = $search_art_detail->execute()->fetchField();
      if (empty($info_art)) {
        db_insert('reposi_article_book_detail')->fields(array(
          'abd_volume' => $chap_volume,
          'abd_issue'  => $chap_issue,
          'abd_start_page' => $new_spage,
          'abd_final_page' => $new_fpage,
          'abd_issn'   => $chap_issn,
          'abd_isbn'   => $chap_isbn,
          'abd_url'    => $chap_url,
          'abd_doi'    => $chap_doi,
          'abd_abid'   => $chap_id,
        ))->execute();
      } else {
        db_update('reposi_article_book_detail')->fields(array(
          'abd_volume' => $chap_volume,
          'abd_issue'  => $chap_issue,
          'abd_start_page' => $new_spage,
          'abd_final_page' => $new_fpage,
          'abd_issn'   => $chap_issn,
          'abd_isbn'   => $chap_isbn,
          'abd_url'    => $chap_url,
          'abd_doi'    => $chap_doi,
        ))->condition('abd_abid', $chap_id)
        ->execute();
      }
    }
    if (!empty($chap_year)){
      $new_year = (int)$chap_year;
    } else {
      $new_year = NULL;
    }
    db_update('reposi_date')->fields(array(
      'd_year'  => $new_year,
    ))->condition('d_abid', $chap_id)
    ->execute(); 
    if (!empty($new_aut2)) {
      $new_relation = db_delete('reposi_publication_author')
        ->condition('ap_abid', $chap_id)
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
            'ap_abid'       => $chap_id,
          ))->execute();
        } else {
          $search_p_a = db_select('reposi_publication_author', 'pa');
          $search_p_a->fields('pa')
                     ->condition('pa.ap_author_id', $id_new_aut, '=')
                     ->condition('pa.ap_abid', $chap_id, '=');
          $p_a = $search_p_a->execute()->fetchField();
          if (empty($p_a)) {
            db_insert('reposi_publication_author')->fields(array(
              'ap_author_id' => $id_new_aut,
              'ap_abid'       => $chap_id,
            ))->execute();
          }
        }
      }
    } 
           drupal_set_message(t('The publication was updated.'));
           $form_state->setRedirect('reposi.Reposi_chapinformation', ['node' => $chap_id]);
}
}
