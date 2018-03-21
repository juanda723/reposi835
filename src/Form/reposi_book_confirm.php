<?php
namespace Drupal\reposi\Form;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Component\Utility\Xss;
use Drupal\reposi\Controller\Reposi_info_publication;

class reposi_book_confirm extends ConfirmFormBase{

    protected $id;

    /**
     * {@inheritdoc}.
     */
    public function getFormId()
    {
        return 'reposi_book_confirm_form';
    }

    /**
     * {@inheritdoc}
     */
    public function getQuestion() {

       return t('Book update confirmation'); 
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

    $book_abid = \Drupal::request()->query->get('abid');
    $book_sub = \Drupal::request()->query->get('sub');
    $book_descrip = \Drupal::request()->query->get('description');
    $book_year = \Drupal::request()->query->get('year');
    $book_langua = \Drupal::request()->query->get('langua');
    $book_volume = \Drupal::request()->query->get('vol');
    $book_issue = \Drupal::request()->query->get('issue');
    $book_edito = \Drupal::request()->query->get('edito');
    $book_ed_name = \Drupal::request()->query->get('editor_name');
    $book_place = \Drupal::request()->query->get('pub');
    $book_issn = \Drupal::request()->query->get('issn');
    $book_isbn = \Drupal::request()->query->get('isbn');
    $book_url = \Drupal::request()->query->get('url');
    $book_doi = \Drupal::request()->query->get('doi');
    $book_author = \Drupal::request()->query->get('info_author');
    $form['subtitle'] = array(
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => t('Subtitle'),
    );
    $form['descrip'] = array(
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => t('Description'),
    );
    $form['author'] = array(
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => t('Author(s)'),
    );
    $form['date'] = array(
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => t('Year'),
    );
    $form['detail'] = array(
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => t('Details'),
    );
    $form['subtitle']['sub'] = array('#markup' => $book_sub);
    $form['descrip']['description'] = array('#markup' => $book_descrip);
    $form['date']['year'] = array('#markup' => $book_year);
    $form['detail']['langua'] = array('#markup' => '<li>' . '<i>' . t('Language: ').'</i>' .$book_langua. '</li>');
    $form['detail']['vol'] = array('#markup' => '<li>' . '<i>' . t('Volume: ') . '</i>' . $book_volume . '</li>');
    $form['detail']['issue'] = array('#markup' => '<li>' . '<i>' . t('Issue: ') . '</i>' . $book_issue . '</li>');
    $form['detail']['edito'] = array('#markup' => '<li>' . '<i>' . t('Editorial: ').'</i>'.$book_edito . '</li>');
    $form['detail']['editor_name'] = array('#markup' => '<li>' . '<i>' . t('Publisher name: ').'</i>'.$book_ed_name . '</li>');
    $form['detail']['pub'] = array('#markup' => '<li>' . '<i>' . t('Publication’s place: ').'</i>'.$book_place . '</li>');
    $form['detail']['issn'] = array('#markup' => '<li>' . '<i>' . t('ISSN: ') . '</i>' . $book_issn . '</li>');
    $form['detail']['isbn'] = array('#markup' => '<li>' . '<i>' . t('ISBN: ') . '</i>' . $book_isbn . '</li>');
    $form['detail']['url'] = array('#markup' => '<li>' . '<i>' . t('URL: ') . '</i>' . $book_url . '</li>');
    $form['detail']['doi'] = array('#markup' => '<li>' . '<i>' . t('DOI: ') . '</i>' . $book_doi . '</li>');
    for ($a=0; $a<count($book_author); $a++) {
    if(!empty($book_author[$a]['first_name']))
    	{
    	$form['author'][$a]['full_name'] = array('#markup' => '<li>'. $book_author[$a]['first_name']. ' '. $book_author[$a]['second_name']
                           		   . ' '.$book_author[$a]['f_lastname'].' '. $book_author[$a]['s_lastname'] . '</li>');
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

    $book_id = \Drupal::request()->query->get('abid');
    $book_sub = \Drupal::request()->query->get('sub');
    $book_descrip = \Drupal::request()->query->get('description');
    $book_year = \Drupal::request()->query->get('year');
    $book_langua = \Drupal::request()->query->get('langua');
    $book_volume = \Drupal::request()->query->get('vol');
    $book_issue = \Drupal::request()->query->get('issue');
    $book_edito = \Drupal::request()->query->get('edito');
    $book_ed_name = \Drupal::request()->query->get('editor_name');
    $book_place = \Drupal::request()->query->get('pub');
    $book_issn = \Drupal::request()->query->get('issn');
    $book_isbn = \Drupal::request()->query->get('isbn');
    $book_url = \Drupal::request()->query->get('url');
    $book_doi = \Drupal::request()->query->get('doi');
    $new_aut2 = \Drupal::request()->query->get('info_author');
  //  $new_aut2 = variable_get('new_aut2');
    db_update('reposi_article_book')->fields(array(
      'ab_subtitle_chapter'  => $book_sub,
      'ab_abstract'          => $book_descrip,
      'ab_language'          => $book_langua,
      'ab_journal_editorial' => $book_edito,
      'ab_publisher'         => $book_ed_name,
      'ab_place'             => $book_place,
    ))->condition('abid', $book_id)
    ->execute();
    if (!empty($book_volume) || !empty($book_issue) || !empty($book_issn) ||
        !empty($book_isbn) || !empty($book_url) || !empty($book_doi)) {
      $search_art_detail = db_select('reposi_article_book_detail', 'abd');
      $search_art_detail->fields('abd')
              ->condition('abd.abd_abid', $book_id, '=');
      $info_art = $search_art_detail->execute()->fetchField();
      if (empty($info_art)) {
        db_insert('reposi_article_book_detail')->fields(array(
          'abd_volume' => $book_volume,
          'abd_issue'  => $book_issue,
          'abd_issn'   => $book_issn,
          'abd_isbn'   => $book_isbn,
          'abd_url'    => $book_url,
          'abd_doi'    => $book_doi,
          'abd_abid'   => $book_id,
        ))->execute();
      } else {
        db_update('reposi_article_book_detail')->fields(array(
          'abd_volume' => $book_volume,
          'abd_issue'  => $book_issue,
          'abd_issn'   => $book_issn,
          'abd_isbn'   => $book_isbn,
          'abd_url'    => $book_url,
          'abd_doi'    => $book_doi,
        ))->condition('abd_abid', $book_id)
        ->execute();
      }
    }
    db_update('reposi_date')->fields(array(
      'd_year'  => $book_year,
    ))->condition('d_abid', $book_id)
    ->execute(); 
    if (!empty($new_aut2)) {
      $new_relation = db_delete('reposi_publication_author')
        ->condition('ap_abid', $book_id)
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
            'ap_abid'      => $book_id,
          ))->execute();
        } else {
          $search_p_a = db_select('reposi_publication_author', 'pa');
          $search_p_a->fields('pa')
                     ->condition('pa.ap_author_id', $id_new_aut, '=')
                     ->condition('pa.ap_abid', $book_id, '=');
          $p_a = $search_p_a->execute()->fetchField();
          if (empty($p_a)) {
            db_insert('reposi_publication_author')->fields(array(
              'ap_author_id' => $id_new_aut,
              'ap_abid'      => $book_id,
            ))->execute();
          }
        }
      }
    } 
           drupal_set_message(t('The publication was updated.'));
           $form_state->setRedirect('reposi.Reposi_bookinformation', ['node' => $book_id]);
}
}
