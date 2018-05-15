<?php
namespace Drupal\reposi\Form;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Component\Utility\Xss;

class Reposi_useredit_form extends ConfirmFormBase{

    protected $id;

    /**
     * {@inheritdoc}.
     */
    public function getFormId()
    {
        return 'user_edit_form';
    }

    /**
     * {@inheritdoc}
     */
    public function getQuestion() {

       return t('Edit user confirmation');
    }

    /**
     * {@inheritdoc}
     */
    public function getCancelUrl() {
        //this needs to be a valid route otherwise the cancel link won't appear
        return new Url('reposi.listus');
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription() {
        //a brief desccription
	$uid = \Drupal::request()->query->get('u_uid');
	$u_first_name = \Drupal::request()->query->get('u_first_name');
	$u_second_name = \Drupal::request()->query->get('u_second_name');
	$u_first_lastname = \Drupal::request()->query->get('u_first_lastname');
	$u_second_lastname = \Drupal::request()->query->get('u_second_lastname');
	$u_email = \Drupal::request()->query->get('u_email');
	$u_optional_email_1 =\Drupal::request()->query->get('u_optional_email_1');
	$u_optional_email_2 = \Drupal::request()->query->get('u_optional_email_2');
	$u_id_homonymous = \Drupal::request()->query->get('u_id_homonymous');
	$u_id_scopus = \Drupal::request()->query->get('u_id_scopus');
  $u_id_scholar = \Drupal::request()->query->get('u_id_scholar');
        $u_adm_aca_type = \Drupal::request()->query->get('u_adm_aca_type');

       return $description =t('Do you want update this information?') .
                      '<div>'. t('New user information: ') . '</div>' . '<ul>' .
                      '<li>'. t('Name(s):   ') . $u_first_name . ' ' . $u_second_name .'</li>' .
                      '<li>'. t('Last name(s): ') . $u_first_lastname . ' ' . $u_second_lastname .'</li>' .
                      '<li>'. t('Email 1:   ') . $u_email .'</li>' .
                      '<li>'. t('Email 2:   ') . $u_optional_email_1 .'</li>' .
                      '<li>'. t('Email 3:   ') . $u_optional_email_2 .'</li>' .
                      '<li>'. t('ORCID: ') . $u_id_homonymous .'</li>' .
                      '<li>'. t('Scopus ID Author: ') . $u_id_scopus .'</li>' .
                      '<li>'. t('Google Sholar ID Author') . $u_id_scholar .'</li>' .
                      '<li>'. t('Academic rol: ') . $u_adm_aca_type .'</li>' . '</ul>';
       // $u_first_name = \Drupal::request()->query->get('u_first_name');
        $id=$this->id;
        $serch_u = db_select('reposi_user', 'u');
        $serch_u->fields('u')
            ->condition('u.uid', $id, '=');
        $serch_user = $serch_u->execute()->fetchField();
        $info_user = $serch_u->execute()->fetchAssoc();
        $fullname = $info_user['u_first_name'] . ' ' . $info_user['u_second_name'] . ' ' . $info_user['u_first_lastname'] . ' '. $info_user['u_second_lastname'];
        $this->fullname=$fullname;
        return t('Only do this if you are sure to delete the user!'.$u_first_name);
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
        $this->id = $id;
        return parent::buildForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {

	$uid = \Drupal::request()->query->get('u_uid');
	$adm_fname = \Drupal::request()->query->get('u_first_name');
	$adm_sname = \Drupal::request()->query->get('u_second_name');
	$adm_flastname = \Drupal::request()->query->get('u_first_lastname');
	$adm_slastname = \Drupal::request()->query->get('u_second_lastname');
	$adm_email1 = \Drupal::request()->query->get('u_email');
	$new_email2 =\Drupal::request()->query->get('u_optional_email_1');
	$new_email3 = \Drupal::request()->query->get('u_optional_email_2');
	$adm_homo = \Drupal::request()->query->get('u_id_homonymous');
	$adm_scopus = \Drupal::request()->query->get('u_id_scopus');
  $adm_scholar = \Drupal::request()->query->get('u_id_scholar');
        $adm_aca_type = \Drupal::request()->query->get('u_adm_aca_type');

    db_update('reposi_user')->fields(array(
      'u_first_name'      => $adm_fname,
      'u_second_name'     => $adm_sname,
      'u_first_lastname'  => $adm_flastname,
      'u_second_lastname' => $adm_slastname,
      'u_email'           => $adm_email1,
      'u_optional_email_1'=> $new_email2,
      'u_optional_email_2'=> $new_email3,
      'u_id_homonymous'   => $adm_homo,
      'u_id_scopus'       => $adm_scopus,
      'u_id_scholar'       => $adm_scholar,
    ))->condition('uid', $uid)
    ->execute();
    db_update('reposi_academic')->fields(array(
      'academic_type' => $adm_aca_type,
    ))->condition('academic_uid', $uid)
    ->execute();
    drupal_set_message('The user was update.');
    }
}
