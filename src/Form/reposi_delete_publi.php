<?php

namespace Drupal\reposi\Form;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Form\FormState;
use Drupal\Core\Database\Query;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\reposi\Controller\Reposi_info_publication;
use Drupal\Component\Utility\UrlHelper;

class reposi_delete_publi extends ConfirmFormBase{

    protected $id;

    /**
     * {@inheritdoc}.
     */
    public function getFormId()
    {
        return 'delete_publication';
    }

    /**
     * {@inheritdoc}
     */
    public function getQuestion() {
        $id=\Drupal::routeMatch()->getParameter('node');
        $this->id=$id;
        $serch_p = db_select('reposi_publication', 'p');
        $serch_p->fields('p')
            ->condition('p.pid', $id, '=');
        $serch_publi = $serch_p->execute()->fetchField();
        $info_publi = $serch_p->execute()->fetchAssoc();
        $fulltype = $info_publi['p_type'];
        return t('Do you want delete this '.$fulltype.'?');
    }

    /**
     * {@inheritdoc}
     */
    public function getCancelUrl() {
        //this needs to be a valid route otherwise the cancel link won't appear
        $idp=\Drupal::routeMatch()->getParameter('node');
        $serch_p = db_select('reposi_publication', 'p');
        $serch_p->fields('p')
            ->condition('p.pid', $idp, '=');
        $serch_publi = $serch_p->execute()->fetchField();
        $info_publi = $serch_p->execute()->fetchAssoc();
        $pidpub = $info_publi['p_abid'];
        $pidpub2 = $info_publi['p_cpid'];
        $pidpub3 = $info_publi['p_tsid'];
        $ptype = $info_publi['p_type'];
        if (($ptype == 'Article')) {
          return new Url('reposi.Reposi_articleinformation',['node' => $pidpub]);
        } elseif ($ptype == 'Book') {
          return new Url('reposi.Reposi_bookinformation',['node' => $pidpub]);
        } elseif ($ptype =='Book Chapter') {
          return new Url('reposi.Reposi_chapinformation',['node' => $pidpub]);
        } elseif ($ptype == 'Conference') {
          return new Url('reposi.Reposi_coninformation',['node' => $pidpub2]);
        } elseif ($ptype =='Patent') {
          return new Url('reposi.Reposi_patinformation',['node' => $pidpub2]);
        } elseif ($ptype == 'Thesis') {
          return new Url('reposi.Reposi_thesinformation',['node' => $pidpub3]);
        } elseif ($ptype =='Software') {
          return new Url('reposi.Reposi_sofinformation',['node' => $pidpub3]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription() {
        //a brief desccription
        $id=$this->id;
        $serch_p = db_select('reposi_publication', 'p');
        $serch_p->fields('p')
            ->condition('p.pid', $id, '=');
        $serch_publi = $serch_p->execute()->fetchField();
        $info_publi = $serch_p->execute()->fetchAssoc();
        $fullname = $info_publi['p_title'];
        return t('Only do this if you are sure to delete the publication: ' . $fullname . '!');
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

          $prid=\Drupal::routeMatch()->getParameter('node');
          $serch_p = db_select('reposi_publication', 'p');
          $serch_p->fields('p')
            ->condition('p.pid', $prid, '=');
          $serch_publi = $serch_p->execute()->fetchField();
          $info_publica = $serch_p->execute()->fetchAssoc();
          $idpub = $info_publica['p_abid'];
          $idpub2 = $info_publica['p_cpid'];
          $idpub3 = $info_publica['p_tsid'];
          $ptype = $info_publica['p_type'];

            if ($ptype == 'Article') {
              $del_publi = db_delete('reposi_publication')
                        ->condition('p_abid', $idpub)
                        ->execute();

              $del_art = db_delete('reposi_article_book')
                        ->condition('abid', $idpub)
                        ->execute();
              $del_detail_art = db_delete('reposi_article_book_detail')
                        ->condition('abd_abid',$idpub)
                        ->execute();

              $del_date_art = db_delete('reposi_date')
                        ->condition('d_abid', $idpub)
                        ->execute();
              $del_author_art = db_delete('reposi_publication_author')
                        ->condition('ap_abid', $idpub)
                        ->execute();
              $del_keyword_art = db_delete('reposi_publication_keyword')
                        ->condition('pk_abid', $idpub)
                        ->execute();
              drupal_set_message('The article was delete.');
              $form_state->setRedirect('reposi.Reposi_public_list');
            }
            elseif ($ptype == 'Book') {
                  $del_book = db_delete('reposi_article_book')
                            ->condition('abid', $idpub)
                            ->execute();
                  $del_detail_book = db_delete('reposi_article_book_detail')
                            ->condition('abd_abid', $idpub)
                            ->execute();
                  $del_publi = db_delete('reposi_publication')
                            ->condition('p_abid', $idpub)
                            ->execute();
                  $del_date_book = db_delete('reposi_date')
                            ->condition('d_abid', $idpub)
                            ->execute();
                  $del_author_book = db_delete('reposi_publication_author')
                            ->condition('ap_abid', $idpub)
                            ->execute();
                  drupal_set_message('The book was delete.');
                  $form_state->setRedirect('reposi.Reposi_public_list');
            }
            elseif ($ptype == 'Book Chapter') {

              $del_chap = db_delete('reposi_article_book')
                          ->condition('abid', $idpub)
                          ->execute();
                $del_detail_chap = db_delete('reposi_article_book_detail')
                          ->condition('abd_abid', $idpub)
                          ->execute();
                $del_publi = db_delete('reposi_publication')
                          ->condition('p_abid', $idpub)
                          ->execute();
                $del_date_chap = db_delete('reposi_date')
                          ->condition('d_abid', $idpub)
                          ->execute();
                $del_author_chap = db_delete('reposi_publication_author')
                          ->condition('ap_abid', $idpub)
                          ->execute();
                drupal_set_message('The book chapter was delete.');
                $form_state->setRedirect('reposi.Reposi_public_list');
            }
            elseif ($ptype == 'Conference') {
                  $del_publi = db_delete('reposi_publication')
                            ->condition('p_cpid',$idpub2)
                            ->execute();
                  $del_con = db_delete('reposi_confer_patent')
                            ->condition('cpid',$idpub2)
                            ->execute();

                  $del_date_con = db_delete('reposi_date')
                            ->condition('d_cpid',$idpub2)
                            ->execute();
                  $del_author_con = db_delete('reposi_publication_author')
                            ->condition('ap_cpid',$idpub2)
                            ->execute();
                  $del_keyword_art = db_delete('reposi_publication_keyword')
                            ->condition('pk_cpid',$idpub2)
                            ->execute();
                  drupal_set_message('The Conference Paper was delete. Now');
                $form_state->setRedirect('reposi.Reposi_public_list');
            }
            elseif ($ptype == 'Patent') {
                  $del_pat = db_delete('reposi_confer_patent')
                            ->condition('cpid', $idpub2)
                            ->execute();
                  $del_publi = db_delete('reposi_publication')
                            ->condition('p_cpid', $idpub2)
                            ->execute();
                  $del_date_pat = db_delete('reposi_date')
                            ->condition('d_cpid', $idpub2)
                            ->execute();
                  $del_author_pat = db_delete('reposi_publication_author')
                            ->condition('ap_cpid', $idpub2)
                            ->execute();
                  drupal_set_message('The patent was delete.');
                $form_state->setRedirect('reposi.Reposi_public_list');
            }
            elseif ($ptype == 'Thesis') {

                  $del_thesis = db_delete('reposi_thesis_sw')
                            ->condition('tsid', $idpub3)
                            ->execute();
                  $del_publi = db_delete('reposi_publication')
                            ->condition('p_tsid', $idpub3)
                            ->execute();
                  $del_date_thesis = db_delete('reposi_date')
                            ->condition('d_tsid', $idpub3)
                            ->execute();
                  $del_author_thesis = db_delete('reposi_publication_author')
                            ->condition('ap_tsid', $idpub3)
                            ->execute();
                  $del_keyword_thesis = db_delete('reposi_publication_keyword')
                            ->condition('pk_tsid', $idpub3)
                            ->execute();
                  drupal_set_message('The thesis was delete.');
                $form_state->setRedirect('reposi.Reposi_public_list');
            }
            elseif ($ptype == 'Software') {
                $del_sw = db_delete('reposi_thesis_sw')
                          ->condition('tsid', $idpub3)
                          ->execute();
                $del_publi = db_delete('reposi_publication')
                          ->condition('p_tsid', $idpub3)
                          ->execute();
                $del_date_sw = db_delete('reposi_date')
                          ->condition('d_tsid', $idpub3)
                          ->execute();
                $del_author_sw = db_delete('reposi_publication_author')
                          ->condition('ap_tsid', $idpub3)
                          ->execute();
                drupal_set_message('The software was delete.');
                $form_state->setRedirect('reposi.Reposi_public_list');
            }

    }//////end submit
}
