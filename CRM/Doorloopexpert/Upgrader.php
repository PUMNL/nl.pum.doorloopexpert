<?php

/**
 * Collection of upgrade steps.
 */
class CRM_Doorloopexpert_Upgrader extends CRM_Doorloopexpert_Upgrader_Base {


  public function install() {
    $this->executeCustomDataFile('xml/doorlooptijden_expertapplication.xml');
  }

  /**
   * Change the label of custom field Datum afwijzing expert and make this field only viewable.
   *
   * @return bool
   */
  public function upgrade_1001() {
    $customGroupId = civicrm_api3('CustomGroup', 'getvalue', array('name' => 'doorlooptijden_expert_application', 'return' => 'id'));
    $customField = civicrm_api3('CustomField', 'getsingle', array('name' => 'datum_afwijzing', 'custom_group_id' => $customGroupId));
    $customField['label'] = 'Datum afwijzing expert';
    $customField['is_view'] = '1';
    civicrm_api3('CustomField', 'create', $customField);
    return true;
  }

  /**
   * Change the field Datum aciveren expert and make this field only viewable.
   *
   * @return bool
   */
  public function upgrade_1002() {
    $customGroupId = civicrm_api3('CustomGroup', 'getvalue', array('name' => 'doorlooptijden_expert_application', 'return' => 'id'));
    $customField = civicrm_api3('CustomField', 'getsingle', array('name' => 'datum_activatie', 'custom_group_id' => $customGroupId));
    $customField['is_view'] = '1';
    civicrm_api3('CustomField', 'create', $customField);
    return true;
  }

  /**
   * Change the labels of the fields
   *
   * @return bool
   */
  public function upgrade_1003() {
    $customGroupId = civicrm_api3('CustomGroup', 'getvalue', array('name' => 'doorlooptijden_expert_application', 'return' => 'id'));

    $customField = civicrm_api3('CustomField', 'getsingle', array('name' => 'datum_positieve_reactie', 'custom_group_id' => $customGroupId));
    $customField['label'] = 'Date assesment intake';
    civicrm_api3('CustomField', 'create', $customField);

    $customField = civicrm_api3('CustomField', 'getsingle', array('name' => 'datum_candidate_expert_account', 'custom_group_id' => $customGroupId));
    $customField['label'] = 'Date Candidate expert account';
    civicrm_api3('CustomField', 'create', $customField);

    $customField = civicrm_api3('CustomField', 'getsingle', array('name' => 'datum_cv', 'custom_group_id' => $customGroupId));
    $customField['label'] = 'Date filled out CV';
    civicrm_api3('CustomField', 'create', $customField);

    $customField = civicrm_api3('CustomField', 'getsingle', array('name' => 'datum_activatie', 'custom_group_id' => $customGroupId));
    $customField['label'] = 'Date expert activation';
    civicrm_api3('CustomField', 'create', $customField);

    $customField = civicrm_api3('CustomField', 'getsingle', array('name' => 'datum_afwijzing', 'custom_group_id' => $customGroupId));
    $customField['label'] = 'Date rejection';
    civicrm_api3('CustomField', 'create', $customField);

    return true;
  }

  /**
   * Change the labels of the date assessment field
   *
   * @return bool
   */
  public function upgrade_1004() {
    try {
       $customField = civicrm_api3('CustomField', 'getsingle', array(
         'name' => 'datum_positieve_reactie',
         'custom_group_id' => 'doorlooptijden_expert_application'
       ));;
       $customField['label'] = "Date assessment intake";
       civicrm_api3('CustomField', 'create', $customField);
    } catch (CiviCRM_API3_Exception $ex) {}
    return true;
  }

  /**
   * Add date onboarding expert
   *
   * @return bool
   */
  public function upgrade_1005() {
    try {
      $customGroupId = civicrm_api3('CustomGroup', 'getvalue', array('name' => 'doorlooptijden_expert_application', 'return' => 'id'));

      $customField = array(
        'name' => 'datum_onboarding_expert',
        'label' => 'Date onboarding expert',
        'custom_group_id' => $customGroupId,
        'data_type' => 'Date',
        'html_type' => 'Select Date',
        'is_required' => 0,
        'is_searchable' => 1,
        'is_search_range' => 1,
        'weight' => 56,
        'is_active' => 1,
        'is_view' => 0,
        'text_length' => 255,
        'date_format' => 'dd-mm-yy',
        'note_columns' => 60,
        'note_rows' => 4,
        'column_name' => 'datum_onboarding_expert',
        'custom_group_name' => 'doorlooptijden_expert_application'
      );

      civicrm_api3('CustomField', 'create', $customField);
    } catch (CiviCRM_API3_Exception $ex) {
      return false;
    }
    return true;

  }
}
