<?php

namespace Drupal\reposi\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements an example form.
 */

class reposi_add_content_form extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'reposi_add_content_form';
  }

  /**
   * {@inheritdoc}
   */
public function buildForm(array $form, FormStateInterface $form_state) {  

    $header = array( t('OPTIONS'), );

    $form['add'] = array(
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => t('R. B. REPOSITORY'),
    );

    $rows[1] = [['options' => 'USER'],];
    $rows[2] = [['options' => 'ARTICLE'],];
    $rows[3] = [['options' => 'BOOK'],];
    $rows[4] = [['options' => 'BOOK CHAPTER'],];
    $rows[5] = [['options' => 'CONFERENCE PAPER'],];
    $rows[6] = [['options' => 'THESIS'],];
    $rows[7] = [['options' => 'PATENT'],];
    $rows[8] = [['options' => 'SOFTWARE'],];

    $form['add']['table'] = array('#type'     => 'tableselect',
			    '#title' => $this->t('Add content'),
                            '#header'   => $header,
                            '#options'  => $rows,
                            '#multiple' => FALSE,
                            '#empty'    => t('No records.')
                            );
    $form['add']['content'] = array(
      '#type' => 'submit',
      '#value' => t('Add'),
    );      
    $form['pager'] = ['#type' => 'pager'];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) 
  {
  
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

        $result = $form_state->getValue('table');
        if($result==1)
        {
		$form_state->setRedirect('reposi.admuser');
        }
        elseif($result==2)
        {
		$form_state->setRedirect('reposi.add_article');
        }
        elseif($result==3){
		$form_state->setRedirect('reposi.add_book');
	}
        elseif($result==4){
		$form_state->setRedirect('reposi.add_chap_book');
	}
        elseif($result==5){
		$form_state->setRedirect('reposi.add_conference');
	}
        elseif($result==6){
		$form_state->setRedirect('reposi.add_thesis');
	}
        elseif($result==7){
		$form_state->setRedirect('reposi.add_patent');
	}
        elseif($result==8){
		$form_state->setRedirect('reposi.add_software');
	}
  }
 
}


