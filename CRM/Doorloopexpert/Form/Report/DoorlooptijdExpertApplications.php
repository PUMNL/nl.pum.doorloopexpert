<?php

/**
 * Class CRM_Casereports_Form_Report_ExpertApplications for PUM report ExpertApplications
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 5 Apr 2016
 * @license AGPL-3.0
 */

class CRM_Doorloopexpert_Form_Report_DoorlooptijdExpertApplications extends CRM_Report_Form {

  protected $_summary = NULL;
  protected $_add2groupSupported = FALSE;
  protected $_customGroupExtends = array();
  protected $_userSelectList = array();
  protected $_caseStatusList = array();
  protected $_sectorList = array();
  protected $_deletedLabels = array();

  /**
   * Constructor method
   */
  function __construct() {
    $this->_caseStatusList = CRM_Case_PseudoConstant::caseStatus();
    $this->setUserSelectList();
    $this->setSectorList();

    $this->_deletedLabels = array('' => ts('- select -'), 0 => ts('No'), 1 => ts('Yes'));

    $this->_columns = array(
      'pum_expert' =>
        array(
          'fields' =>
            array(
              'case_id' =>
                array(
                  'no_display' => TRUE,
                  'required' => TRUE,
                ),
              'expert_name' =>
                array(
                  'name' => 'expert_name',
                  'title' => ts('Expert'),
                  'required' => TRUE,
                ),
              'sector_coordinator_name' =>
                array(
                  'name' => 'sector_coordinator_name',
                  'title' => ts('Sector Coordinator')
                ),
              'status' =>
                array(
                  'name' => 'status',
                  'title' => ts('Status'),
                  'default' => TRUE,
                ),
              'sector_coordinator_id' =>
                array(
                  'no_display' => TRUE,
                  'required' => TRUE
                ),
              'expert_id' =>
                array(
                  'no_display' => TRUE,
                  'required' => TRUE
                ),
            ),
          'filters' => array(
            'user_id' => array(
              'title' => ts('Expert Applications for User'),
              'default' => 0,
              'pseudofield' => 1,
              'type' => CRM_Utils_Type::T_INT,
              'operatorType' => CRM_Report_Form::OP_SELECT,
              'options' => $this->_userSelectList,
            ),
            'status_id' => array(
              'title' => ts('Status'),
              'operatorType' => CRM_Report_Form::OP_MULTISELECT,
              'options' => $this->_caseStatusList,
            ),
          ),
          'order_bys' =>
            array(
              'status' =>
                array(
                  'title' => ts('Case Status'),
                  'name' => 'status',
                  'default' => 1,
                  'default_is_section' => true,
                  'default_weight' => 1,
                ),
            ),
        ),
      'civicrm_case' => array(
        'fields' => array(
          'start_date' => array(
            'title' => ts('Start Date'), 'default' => TRUE, 'required' => true,
            'type' => CRM_Utils_Type::T_DATE,
          ),
          'end_date' => array(
            'title' => ts('End Date'), 'default' => TRUE, 'required' => true,
            'type' => CRM_Utils_Type::T_DATE,
          ),
        ),
        'filters' =>  array(
          'start_date' => array('title' => ts('Start Date'),
            'operatorType' => CRM_Report_Form::OP_DATE,
            'type' => CRM_Utils_Type::T_DATE,
          ),
          'end_date' => array('title' => ts('End Date'),
            'operatorType' => CRM_Report_Form::OP_DATE,
            'type' => CRM_Utils_Type::T_DATE,
          ),
        ),
        'order_bys' =>
          array(
            'start_date' =>
              array(
                'title' => ts('Start date'),
                'name' => 'start_date',
                'default' => 1,
                'default_weight' => 2
              ),
          ),
      ),
    );

    parent::__construct();

    $this->_customGroupExtends = 'Case';
    $permCustomGroupIds = array();
    $permCustomGroupIds[] = civicrm_api3('CustomGroup', 'getvalue', array('return' => 'id', 'name' => 'doorlooptijden_expert_application'));
    $this->addCustomDataToColumns(TRUE, $permCustomGroupIds);

    $table_name = civicrm_api3('CustomGroup', 'getvalue', array('return' => 'table_name', 'name' => 'doorlooptijden_expert_application'));
    foreach($this->_columns[$table_name]['fields'] as $field_name => $field) {
      $this->_columns[$table_name]['fields'][$field_name]['default'] = true;
    }
  }

  /**
   * Overridden parent method to build select part of query
   */
  function select() {
    $select = array();
    $this->_dateFields = array();
    $this->_columnHeaders = array();
    $this->_columnHeaders['pum_expert_case_id']['type'] = null;
    foreach ($this->_columns as $tableName => $table) {
      if (array_key_exists('fields', $table)) {
        foreach ($table['fields'] as $fieldName => $field) {
          if ($fieldName != 'case_id') {
            if (CRM_Utils_Array::value('required', $field) ||
              CRM_Utils_Array::value($fieldName, $this->_params['fields'])
            ) {
              $select[] = "{$field['dbAlias']} AS {$tableName}_{$fieldName}";
              $this->_columnHeaders["{$tableName}_{$fieldName}"]['type'] = CRM_Utils_Array::value('type', $field);
              if (isset($field['title'])) {
                $this->_columnHeaders["{$tableName}_{$fieldName}"]['title'] = $field['title'];
              }
            }
          }
        }
      }
    }
    $this->_select = "SELECT DISTINCT(".$this->_aliases['pum_expert'].".case_id) AS pum_expert_case_id, " . implode(', ', $select) . ", case_manager.display_name as case_manager_name, case_manager.id as case_manager_id ";
  }

  /**
   * Overridden parent method to build from part of query
   */

  function from() {
    $this->_from = "FROM pum_expert_applications {$this->_aliases['pum_expert']} 
      INNER JOIN civicrm_case {$this->_aliases['civicrm_case']} ON {$this->_aliases['civicrm_case']}.id = {$this->_aliases['pum_expert']}.case_id
      LEFT JOIN civicrm_contact case_manager ON case_manager.id = {$this->_aliases['pum_expert']}.case_manager_id";
  }

  /**
   * Overridden parent method to build where clause
   */
  function where() {
    $clauses = array();
    $this->_having = '';
    foreach ($this->_columns as $tableName => $table) {
      if (array_key_exists('filters', $table)) {
        foreach ($table['filters'] as $fieldName => $field) {
          $clause = NULL;
          if (CRM_Utils_Array::value("operatorType", $field) & CRM_Report_Form::OP_DATE) {
            $relative = CRM_Utils_Array::value("{$fieldName}_relative", $this->_params);
            $from     = CRM_Utils_Array::value("{$fieldName}_from", $this->_params);
            $to       = CRM_Utils_Array::value("{$fieldName}_to", $this->_params);

            $clause = $this->dateClause($field['dbAlias'], $relative, $from, $to,
              CRM_Utils_Array::value('type', $field)
            );
          } else {
            $op = CRM_Utils_Array::value("{$fieldName}_op", $this->_params);
            if ($fieldName == 'user_id') {
              $value = $this->setUserClause();
              if (!empty($value) && $value > 0) {
                $clause = "({$this->_aliases['pum_expert']}.case_manager_id = {$value} 
                  OR {$this->_aliases['pum_expert']}.recruitment_team_id = {$value})";
              }
              $op = NULL;
            }

            if ($op) {
              $clause = $this->whereClause($field,
                $op,
                CRM_Utils_Array::value("{$fieldName}_value", $this->_params),
                CRM_Utils_Array::value("{$fieldName}_min", $this->_params),
                CRM_Utils_Array::value("{$fieldName}_max", $this->_params)
              );
            }
          }

          if (!empty($clause)) {
            $clauses[] = $clause;
          }
        }
      }
    }
    if (empty($clauses)) {
      $this->_where = "WHERE ( 1 ) ";
    } else {
      $this->_where = "WHERE " . implode(' AND ', $clauses);
    }
  }

  /**
   * Overridden parent method to set the column headers
   */
  function modifyColumnHeaders() {
    $this->_columnHeaders['case_manager_id'] = array('no_display' => true);
    $this->_columnHeaders['case_manager_name'] = array('no_display' => true);
    $this->_columnHeaders['duration'] = array('title' => 'Duration','type' => CRM_Utils_Type::T_STRING,);
    $this->_columnHeaders['manage_case'] = array('title' => '','type' => CRM_Utils_Type::T_STRING,);

    $keys_first = array(
      'pum_expert_case_id',
      'case_manager_id',
      'case_manager_name',
      'pum_expert_expert_name',
      'pum_expert_sector_coordinator_name',
      'pum_expert_status',
      'pum_expert_sector_coordinator_id',
      'pum_expert_expert_id',
      'civicrm_case_start_date',
    );
    $keys_last = array(
      'civicrm_case_end_date',
      'duration',
      'manage_case',
    );
    $headers = array();
    $headers_last = array();
    foreach($keys_first as $key) {
      if (isset($this->_columnHeaders[$key])) {
        $headers[$key] = $this->_columnHeaders[$key];
        unset($this->_columnHeaders[$key]);
      }
    }
    foreach($keys_last as $key) {
      if (isset($this->_columnHeaders[$key])) {
        $headers_last[$key] = $this->_columnHeaders[$key];
        unset($this->_columnHeaders[$key]);
      }
    }
    foreach($this->_columnHeaders as $key => $header) {
      $headers[$key] = $header;
    }
    $this->_columnHeaders = $headers;
    foreach($headers_last as $key => $header) {
      $this->_columnHeaders[$key] = $header;
    }
  }

  /**
   * Overridden parent method to process criteria into report with data
   */
  function postProcess() {

    $this->beginPostProcess();

    $sql = $this->buildQuery(TRUE);

    $rows = $graphRows = array();
    $this->buildRows($sql, $rows);

    $this->formatDisplay($rows);
    $this->doTemplateAssignment($rows);
    $this->endPostProcess($rows);
  }

  /**
   * Overridden parent method to alter the display of each row
   * @param array $rows
   */
  function alterDisplay(&$rows) {
    $norm = civicrm_api3('Doorloopnormen', 'getvalue', array('name' => 'expert_application', 'return' => 'norm'));

    foreach ($rows as $rowNum => $row) {
      // build manage case url
      if (array_key_exists('pum_expert_case_id', $row) && array_key_exists('pum_expert_expert_id', $row)) {
        $caseUrl = CRM_Utils_System::url("civicrm/contact/view/case", 'reset=1&action=view&cid='
          . $row['pum_expert_expert_id'] . '&id=' . $row['pum_expert_case_id'], $this->_absoluteUrl);
        $rows[$rowNum]['manage_case'] = ts('Manage');
        $rows[$rowNum]['manage_case_link'] = $caseUrl;
        $rows[$rowNum]['manage_case_hover'] = ts("Manage Case");
      }

      if (CRM_Utils_Array::value('pum_expert_expert_id', $rows[$rowNum])) {
        $url = CRM_Utils_System::url("civicrm/contact/view" , "action=view&reset=1&cid=". $row['pum_expert_expert_id'], $this->_absoluteUrl);
        $rows[$rowNum]['pum_expert_expert_name_link'] = $url;
        $rows[$rowNum]['pum_expert_expert_name_hover'] = ts("View Expert");
      }

      if (CRM_Utils_Array::value('pum_expert_sector_coordinator_name', $rows[$rowNum])) {
        $url = CRM_Utils_System::url("civicrm/contact/view" , "action=view&reset=1&cid=". $row['case_manager_id'], $this->_absoluteUrl);
        $rows[$rowNum]['pum_expert_sector_coordinator_name'] = $rows[$rowNum]['case_manager_name'];
        $rows[$rowNum]['pum_expert_sector_coordinator_name_link'] = $url;
        $rows[$rowNum]['pum_expert_sector_coordinator_name_hover'] = ts("View Sector Coordinator");
      }

      if (isset($rows[$rowNum]['civicrm_case_start_date'])) {
        $startDate = new DateTime($rows[$rowNum]['civicrm_case_start_date']);
        foreach($rows[$rowNum] as $field => $value) {
          if ($field == 'civicrm_case_start_date' || $field == 'civicrm_case_end_date' || empty($value)) {
            continue;
          } elseif (isset($this->_columnHeaders[$field]['type']) && $this->_columnHeaders[$field]['type'] & CRM_Utils_Type::T_DATE) {
            $date = new DateTime($value);
            $rows[$rowNum][$field] = $date->diff($startDate)->format('%a') . ' days';
            $this->_columnHeaders[$field]['type'] = CRM_Utils_Type::T_STRING;
          }
        }
        $endDate = new DateTime();
        if (!empty($rows[$rowNum]['civicrm_case_end_date'])) {
          $endDate = new DateTime($rows[$rowNum]['civicrm_case_end_date']);
        }
        $rows[$rowNum]['duration'] = $endDate->diff($startDate)->format('%a').' days';
        if ($endDate->diff($startDate)->format('%a') > $norm) {
          $rows[$rowNum]['duration'] = '<span style="color: red;">'.$rows[$rowNum]['duration']."</span>";
        }

      }
    }
  }

  /**
   * Method to get the users list for the user filter
   *
   * @access private
   */
  private function setUserSelectList() {
    $allContacts = $this->getGroupMembers('Sector_Coordinators_55') + $this->getGroupMembers('Recruitment_Team_13');
    $sortedContacts = array();
    foreach ($allContacts as $contact) {
      $sortedContacts[$contact] = CRM_Threepeas_Utils::getContactName($contact);
    }
    asort($sortedContacts);
    $this->_userSelectList = array(-1 => 'Any user', 0 => 'current user') + $sortedContacts;
  }

  /**
   * Method to get sector coordinators
   *
   * @return array
   */
  private function getGroupMembers($groupName) {
    $result = array();
    try {
      $groupId = civicrm_api3('Group', 'Getvalue', array('name' => $groupName, 'return' => 'id'));
      $groupContactParams = array('group_id' => $groupId, 'options' => array('limit' => 9999));
      try {
        $groupMembers = civicrm_api3('GroupContact', 'Get', $groupContactParams);
        foreach ($groupMembers['values'] as $groupMember) {
          $result[$groupMember['contact_id']] = $groupMember['contact_id'];
        }
      } catch (CiviCRM_API3_Exception $ex) {}
    } catch (CiviCRM_API3_Exception $ex) {}
    return $result;
  }

  /**
   * Method to get the list of sectors
   *
   * @return array
   */
  private function setSectorList() {
    $this->_sectorList = array();
    try {
      $sectors = civicrm_api3('Segment', 'Get', array('parent_id' => 'null'));
      foreach ($sectors['values'] as $sector) {
        $this->_sectorList[$sector['id']] = $sector['label'];

      }
    } catch (CiviCRM_API3_Exception $ex) {}
    asort($this->_sectorList);
  }

  /**
   * Method to add the user clause for where
   */
  private function setUserClause() {
    if (!isset($this->_params['user_id_value']) || empty($this->_params['user_id_value'])) {
      $session = CRM_Core_Session::singleton();
      $userId = $session->get('userID');
    } else {
      $userId = $this->_params['user_id_value'];
    }
    return $userId;
  }
}
