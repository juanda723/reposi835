<?php

namespace Drupal\reposi\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Query;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\reposi\Controller\Reposi_info_publication;
use Drupal\Component\Utility\UrlHelper;
use Drupal\reposi\Form\Url;
/**
 * Implements an example form.
 */
class reposi_info_thessoft_free extends FormBase {

  public function getFormId() {
    return 'info_thessoft_free';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
      $arg=\Drupal::routeMatch()->getParameter('node');

      $serch_p = db_select('reposi_publication', 'p');
      $serch_p->fields('p')
        ->condition('p.p_tsid', $arg, '=');
      $serch_publi = $serch_p->execute()->fetchField();
      $info_publi = $serch_p->execute()->fetchAssoc();
      $idpub = $info_publi['p_type'];
      if ($idpub=='Thesis') {
        $hola=Reposi_info_publication::reposi_info_thesis_free();
      }elseif ($idpub=='Software') {
        $hola=Reposi_info_publication::reposi_info_sw_free();
      }
      $form['body'] = array($hola);
      $form['export'] = array(
        '#markup' => '',
      );
      return $form;
}
//end buildForm

public function validateForm(array &$form, FormStateInterface $form_state){

}

/**
 * {@inheritdoc}
 */
public function submitForm(array &$form, FormStateInterface $form_state) {

}


}//end class
