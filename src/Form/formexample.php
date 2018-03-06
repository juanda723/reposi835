<?php

namespace Drupal\reposi\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements an example form.
 */
class formexample extends FormStateInterface {

public function getFormId() {
    return 'example_form';
  }

  function reposi_form_example_form_alter(&$form, FormStateInterface $form_state) {
  $node = $form_state->getFormObject()->getEntity();
  $nodeBundle = $node->bundle();
  if (in_array($form['#form_id'], ['node_product_edit_form', 'node_product_form']) && uc_product_is_product($nodeBundle)) {
    $form['volume_pricing'] = [
      '#type' => 'fieldset',
      '#tree' => TRUE,
      '#title' => t('Volume Pricing'),
      '#prefix' => '< div id="volume-pricing-wrapper" >',
      '#suffix' => '< /div >',
      '#weight' => 99,
    ];
 
    $volumeFields = $form_state->get('volume_fields');
    if (empty($volumeFields)) {
      $volumeFields = ($count > 0) ? $count : 1;
      $form_state->set('volume_fields', $volumeFields);
    }
 
    for ($i = $count; $i < $volumeFields; $i++) {
      $form['volume_pricing']['volume-set' . $i] = [
        '#type' => 'fieldset',
        '#title' => 'Option ' . ($i + 1),
        '#tree' => TRUE,
      ];
      $form['volume_pricing']['volume-set' . $i]['min'] = [
        '#type' => 'textfield',
        '#title' => 'Min Quantity',
      ];
      $form['volume_pricing']['volume-set' . $i]['price'] = [
        '#type' => 'textfield',
        '#title' => 'Price Each',
      ];
    }
 
    $form['volume_pricing']['add_item'] = [
      '#type' => 'submit',
      '#value' => t('Add Another Item'),
      '#submit' => ['uc_volume_pricing_add_item'],
      '#ajax' => [
        'callback' => 'uc_volume_pricing_ajax_callback',
        'wrapper' => 'volume-pricing-wrapper',
      ],
    ];
    $form['#entity_builders'][] = 'uc_volume_pricing_node_builder';
  }
}
/**
 * Implements hook_form_FORM_ID_alter().
 */

/**
 * Ajax Callback for the form.
 *
 * @param array $form
 *   The form being passed in
 * @param array $form_state
 *   The form state
 * 
 * @return array
 *   The form element we are changing via ajax
 */
function uc_volume_pricing_ajax_callback(&$form, FormStateInterface $form_state) {
  return $form['volume_pricing'];
}
 
/**
 * Functionality for our ajax callback.
 *
 * @param array $form
 *   The form being passed in
 * @param array $form_state
 *   The form state, passed by reference so we can modify
 */
function uc_volume_pricing_add_item(&$form, FormStateInterface $form_state) {
  $volumeFields = $form_state->get('volume_fields');
  $form_state->set('volume_fields', ($volumeFields+1));
  $form_state->setRebuild();
}

  public function submitForm(array &$form, FormStateInterface $form_state) {
   $volumeFields = $form_state->get('volume_fields');
  $form_state->set('volume_fields', ($volumeFields+1));
  $form_state->setRebuild();
  }
 
}


