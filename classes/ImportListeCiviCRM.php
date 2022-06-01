<?php

class ImportListeCiviCRM {
  public static function initialize($pathToWordpress) {
    $civiConfigFileFullPath = "$pathToWordpress/wp-content/plugins/civicrm/civicrm/civicrm.config.php";
    $civiSettingsDirectory = "$pathToWordpress/wp-content/uploads/civicrm";

    if (!file_exists($civiConfigFileFullPath)) {
      throw new Exception("Le fichier $civiConfigFileFullPath n'existe pas.");
    }

    if (!file_exists($civiSettingsDirectory)) {
      throw new Exception("Le répertoire $civiSettingsDirectory n'existe pas.");
    }

    define('CIVICRM_CONFDIR', $civiSettingsDirectory);
    require_once $civiConfigFileFullPath;
    require_once 'CRM/Core/Config.php';
    $config = CRM_Core_Config::singleton();
  }
}