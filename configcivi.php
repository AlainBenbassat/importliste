<?php

require __DIR__ . '/classes/ImportListeCiviCRM.php';
require __DIR__ . '/classes/ImportListeConfig.php';

function checkCommandLineArgCount() {
  global $argc;

  if ($argc != 2) {
    showUsage(1);
  }
}

function showUsage($exitCode) {
  echo "Configuration automatique de CiviCRM:\n\n";
  echo "  php configcivi.php <répertoire Wordpress>\n\n";
  echo "Par example :\n\n";
  echo "  php configcivi.php /var/www\n\n";
  exit($exitCode);
}

function getWordpressDirectory() {
  global $argv;

  return removeTrailingSlash($argv[1]);
}

function setLanguageAndCurrency() {
  Civi::settings()->set('lcMessages', 'fr_FR');
  Civi::settings()->set('uiLanguages', ['fr_FR']);

}

function setCountry() {
  Civi::settings()->set('defaultContactCountry', 1076);
  Civi::settings()->set('defaultContactCountry', 1076);
  Civi::settings()->set('countryLimit', [1076]);
}

function setDateFormat() {
  civicrm_api4('Setting', 'set', [
    'values' => [
      'dateformatDatetime' => '%E %B %Y %k:%M',
      'dateformatFull' => '%E %B %Y',
      'dateformatTime' => '%H:%M',
      'dateformatFinancialBatch' => '%d/%m/%Y',
      'dateformatshortdate' => '%d/%m/%Y',
      'dateInputFormat' => 'dd/mm/yy',
      'timeInputFormat' => '2',
      'weekBegins' => '1',
    ],
    'domainId' => 1,
    'checkPermissions' => FALSE,
  ]);
}

function setMoneyFormat() {
  civicrm_api4('Setting', 'set', [
    'values' => [
      'monetaryThousandSeparator' => '.',
      'monetaryDecimalPoint' => ',',
      'moneyformat' => '%c %a',
      'moneyvalueformat' => '%!i',
      'defaultCurrency' => 'EUR',
    ],
    'checkPermissions' => FALSE,
  ]);

  // delete usd from available currencies
  $optionGroupId = CRM_Core_DAO::singleValueQuery("select id from civicrm_option_group where name = 'currencies_enabled'");
  $sql = "delete from civicrm_option_value where option_group_id = $optionGroupId and value in ('USD', 'CAD', 'GBP', 'JPY')";
  CRM_Core_DAO::executeQuery($sql);
}

function setGreeting() {
  $sql = "update civicrm_option_value set name = replace(name, 'Dear ', 'Bonjour '), label = replace(label, 'Dear ', 'Bonjour ') where option_group_id in (40,41)";
  CRM_Core_DAO::executeQuery($sql);
}

function setDisablePoweredByCiviCRM() {
  \Civi\Api4\Setting::set(FALSE)
    ->addValue('empoweredBy', 0)
    ->setDomainId(1)
    ->execute();
}

function createTags() {
  $sql = "delete from civicrm_tag where id > 1";
  CRM_Core_DAO::executeQuery($sql);

  $sql = "update civicrm_tag set name = 'Contact', description = NULL where id = 1";
  CRM_Core_DAO::executeQuery($sql);
}

function removeTrailingSlash($s) {
  if (substr($s, -1, 1) == '/') {
    return substr($s, 0, strlen($s) - 1);
  }
  else {
    return $s;
  }
}

function main() {
  checkCommandLineArgCount();

  try {
    $wordPressDirectory = getWordpressDirectory();
    ImportListeCiviCRM::initialize($wordPressDirectory);

    setDateFormat();
    setCountry();
    setLanguageAndCurrency();
    setMoneyFormat();
    setGreeting();
    setDisablePoweredByCiviCRM();
    createTags();

    $cf = new ImportListeConfig();
    $cf->dropCustomGroupTable();
    $cf->createCustomFields();

    echo "\nOK\n";
  }
  catch (Exception $e) {
    echo "======\n";
    echo "ERREUR\n";
    echo "======\n";

    echo $e->getMessage();

    echo "\n\n";
  }
}

main();
