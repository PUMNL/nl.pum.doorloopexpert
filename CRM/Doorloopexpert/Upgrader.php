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

}
