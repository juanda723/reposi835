<?php

namespace Drupal\reposi\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\reposi\Controller\Reposi_info_publication;
use Drupal\reposi\Controller\reposi_validate;
use Drupal\Component\Utility\UrlHelper;
/**
 * Implements an example form.
 */
class Reposi_validate_form extends FormBase {

  public function getFormId() {
      return 'validate_form';
    }
    public function buildForm(array $form, FormStateInterface $form_state) {
}
public function validateForm(array &$form, FormStateInterface $form_state) {
}
function reposi_book_form_submit($form, &$form_state){
}

  public static function reposi_article_title_validate($title_validate){

    $search_art = db_select('reposi_article_book', 'ab');
    $search_art->fields('ab')
            ->condition('ab.ab_type', 'Article', '=')
            ->condition('ab.ab_title', $title_validate, '=');
    $info_art = $search_art->execute();
    $new_title=Reposi_info_publication::reposi_string($title_validate);
    foreach ($info_art as $titles) {
      $new_titles=Reposi_info_publication::reposi_string($titles->ab_title);
      if (strcasecmp($new_title, $new_titles) == 0) {
        $form_state->setErrorByName('publi_title', t('This Article exists on Data Base.'));
      }
    }
  }


}
