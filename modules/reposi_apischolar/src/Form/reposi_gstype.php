<?php

namespace Drupal\reposi_apischolar\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\reposi\Controller\Reposi_info_publication;
use Drupal\Core\Url;
use Drupal\Core\Link;
/**
 * Implements an example form.
 */

class reposi_gstype extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'reposi_gstype_id';
  }

  /**
   * {@inheritdoc}
   */
public function buildForm(array $form, FormStateInterface $form_state) {
      $uid=\Drupal::routeMatch()->getParameter('node');
      //$uid=1;
      $serch_p = db_select('reposi_publication', 'p');
      $serch_p->fields('p')
              ->condition('p.p_unde', $uid, '=');
      $search_pub = $serch_p->execute()->fetchAssoc();
      $p_unde=$search_pub['p_unde'];
      $p_title=$search_pub['p_title'];
      $p_year=$search_pub['p_year'];

      $serch_ap = db_select('reposi_publication_author', 'a');
      $serch_ap->fields('a', array('ap_author_id', 'ap_unde'))
              ->condition('a.ap_unde', $p_unde);
      $p_a = $serch_ap->execute();

      $p_a -> allowRowCount = TRUE;
      $num_aut = $p_a->rowCount();
      $list_aut_abc='';
      $flag_aut = 0;
      $authors_art = '';
      foreach ($p_a as $aut_art) {
        $flag_aut++;
        $search_aut = db_select('reposi_author', 'a');
        $search_aut->fields('a')
                   ->condition('a.aid', $aut_art->ap_author_id, '=');
        $each_aut = $search_aut->execute()->fetchAssoc();
        if ($flag_aut <> $num_aut) {
          $f_name = Reposi_info_publication::reposi_string($each_aut['a_first_name']);
          if (!empty($each_aut['a_second_name'])) {
            $s_name = Reposi_info_publication::reposi_string($each_aut['a_second_name']);
            $authors_art = $authors_art . \Drupal::l($each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] .
                          ' ' . $f_name[0] . '. ' . $s_name[0] . '.',
                          Url::fromRoute('reposi.author_aid',['node'=>$aut_art->ap_author_id])) . '.';
          } else {
            $authors_art = $authors_art . \Drupal::l($each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] .
                          ' ' . $f_name[0] . '. ',Url::fromRoute('reposi.author_aid',['node'=>$aut_art->ap_author_id])) . '.';
          }
        } else {
          $search_aut = db_select('reposi_author', 'a');
          $search_aut->fields('a')
                     ->condition('a.aid', $aut_art->ap_author_id, '=');
          $each_aut = $search_aut->execute()->fetchAssoc();
          $f_name = Reposi_info_publication::reposi_string($each_aut['a_first_name']);
          if (!empty($each_aut['a_second_name'])) {
            $s_name = Reposi_info_publication::reposi_string($each_aut['a_second_name']);
            $authors_art = $authors_art . \Drupal::l($each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] .
                          ' ' . $f_name[0] . '. ' . $s_name[0] . '.',Url::fromRoute('reposi.author_aid',['node'=>$aut_art->ap_author_id])) . '.';
          } else {
            $authors_art = $authors_art . \Drupal::l($each_aut['a_first_lastname'] . ' ' . $each_aut['a_second_lastname'] .
                          ' ' . $f_name[0] . '. ',Url::fromRoute('reposi.author_aid',['node'=>$aut_art->ap_author_id])) . '.';
          }
        }
      }

      $form['uid'] = array(
    		'#type' => 'value',
    		'#value' => $p_unde,
    	);
    	$form['au_id'] = array(
    		'#type' => 'value',
    		'#value' => $p_title,
    	);
      $form['aua_id'] = array(
    		'#type' => 'value',
    		'#value' => $p_year,
    	);
      $markup = '<p>' .'Title: '.'<b>'.$p_title.'</b>'.
      '<p>'.'Year: '.$p_year.'<p>'.'Author: '. $authors_art.'<p>' .'</b>';
      $form['type_publication'] = array(
        '#title' => t('Type Publication'),
        '#type' => 'select',
        '#options' => array(
                t('Article'),
        				t('Book'),
                t('Chapter Book'),
                t('Conference'),
                t('Thesis'),
                t('Patent'),
              ),
        '#required' => TRUE,
        );
      $form['body'] = array('#markup' => $markup);
      $form['accept'] = array(
        '#type' => 'submit',
        '#value' => t('Import data GS'),
      );
      $form['save'] = array(
        '#type' => 'submit',
        '#value' => t('Save'),
        '#submit' => array([$this, 'Cancel']),
      );
      $form['cancel'] = array(
        '#type' => 'submit',
        '#value' => t('Cancel'),
        '#submit' => array([$this, 'Save']),
      );
    return $form;
  }

  /**
   * {@inheritdoc}
   */

      function Cancel($form, &$form_state){
          $form_state->setRedirect('reposi.gspub');
      }
      function Save($form, &$form_state){
          $form_state->setRedirect('reposi.gspub');
      }

  public function validateForm(array &$form, FormStateInterface $form_state)
  {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}
