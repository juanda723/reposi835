<?php
namespace Drupal\reposi\Form;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

class User_delete_form extends ConfirmFormBase{

    protected $id;

    /**
     * {@inheritdoc}.
     */
    public function getFormId()
    {
        return 'user_delete_form';
    }

    /**
     * {@inheritdoc}
     */
    public function getQuestion() {
        $id=\Drupal::routeMatch()->getParameter('node');
        $this->id=$id;
        return t('Do you want to delete the user?');
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
        $id=$this->id;
        $serch_u = db_select('reposi_user', 'u');
        $serch_u->fields('u')
            ->condition('u.uid', $id, '=');
        $serch_user = $serch_u->execute()->fetchField();
        $info_user = $serch_u->execute()->fetchAssoc();
        $fullname = $info_user['u_first_name'] . ' ' . $info_user['u_second_name'] . ' ' . $info_user['u_first_lastname'] . ' '. $info_user['u_second_lastname'];
        $this->fullname=$fullname;
        return t('Only do this if you are sure to delete the user: ' . $fullname . '!');
    }

    /**
     * {@inheritdoc}
     */
    public function getConfirmText() {
        return $this->t('Delete it Now!');
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
        $uid = $this->id;
        $del_user = db_delete('reposi_user')
              	->condition('uid', $uid)
              	->execute();
        $form_state->setRedirect('reposi.listus');
    	drupal_set_message('The user ' . $this->fullname . ' was delete.');
    }
}
