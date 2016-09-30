<?php
// This file declares a managed database record of type "ReportTemplate".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC42/Hook+Reference
return array (
  0 => 
  array (
    'name' => 'CRM_Doorloopexpert_Form_Report_DoorlooptijdExpertApplications',
    'entity' => 'ReportTemplate',
    'params' => 
    array (
      'version' => 3,
      'label' => 'Doorlooptijden Expert Application',
      'description' => 'Doorlooptijden expert application (nl.pum.doorlooptijdexpert)',
      'class_name' => 'CRM_Doorloopexpert_Form_Report_DoorlooptijdExpertApplications',
      'report_url' => 'nl.pum.doorlooptijdexpert/doorlooptijdexpertapplications',
      'component' => 'CiviCase',
    ),
  ),
);