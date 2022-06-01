<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/classes/ImportListeCiviCRM.php';
require __DIR__ . '/classes/ImportListeExcel.php';

function checkCommandLineArgCount() {
  global $argc;

  if ($argc != 3) {
    showUsage(1);
  }
}

function showUsage($exitCode) {
  echo "Importation du fichier Excel:\n\n";
  echo "  php start.php <nom du fichier Excel> <répertoire Wordpress>\n\n";
  echo "Par example :\n\n";
  echo "  php start.php /tmp/liste.xlsx /var/www\n\n";
  exit($exitCode);
}

function removeTrailingSlash($s) {
  if (substr($s, -1, 1) == '/') {
    return substr($s, 0, strlen($s) - 1);
  }
  else {
    return $s;
  }
}

function getExcelFileName() {
  global $argv;

  return $argv[1];
}

function getWordpressDirectory() {
  global $argv;

  return removeTrailingSlash($argv[2]);
}

function main() {
  checkCommandLineArgCount();

  try {
    $excelFile = getExcelFileName();
    $wordPressDirectory = getWordpressDirectory();

    ImportListeCiviCRM::initialize($wordPressDirectory);

    $excel = new ImportListeExcel();
    $excel->import($excelFile);

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