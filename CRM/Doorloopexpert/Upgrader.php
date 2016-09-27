<?php

/**
 * Collection of upgrade steps.
 */
class CRM_Doorloopexpert_Upgrader extends CRM_Doorloopexpert_Upgrader_Base {


  public function install() {
    $this->executeCustomDataFile('xml/doorlooptijden_expertapplication.xml');
  }

}
