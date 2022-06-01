<?php

require_once __DIR__ . '/ImportListeContact.php';

class ImportListeExcel {
  private $spreadsheet;
  private $colNum = [];
  private $colHeaderMapping;
  private $totalRows = 0;
  private $currentRow = 2;

  public function __construct() {
    $this->colHeaderMapping = [
      'A1' => 'source',
      'prénoms' => 'first_name',
      'nom de naissance' => 'last_name',
      "nom d'usage" => 'nick_name',
      'sexe' => 'gender',
      'date de naissance' => 'birth_date',
      'numéro de voie' => 'street_number',
      'libellé de voie' => 'street_name',
      'complément 1' => 'supplemental_address_1',
      'complément 2' => 'supplemental_address_2',
      'lieu-dit' => 'supplemental_address_3',
      'code postal' => 'postal_code',
      'commune' => 'city',
    ];
  }

  public function import($fileName) {
    if (!file_exists($fileName)) {
      throw new Exception("Le fichier $fileName n'existe pas.");
    }

    $this->totalRows = $this->openExcelFile($fileName);
    $this->readColumnHeaders();
    $this->validateColumnHeaders();
    $this->importLines();
  }

  private function importLines() {
    while (!empty($this->getCellValue(1, $this->currentRow))) {
      $contactId = $this->importLineContactData();
      //$this->importLineAddressData($contactId);

      $this->currentRow++;
    }
  }

  private function importLineContactData() {
    $contactParams = $this->currentRowGetContactParams();
    return ImportListeContact::create($contactParams);
  }

  private function currentRowGetContactParams() {
    $params = [];

    $params['contact_type'] = 'Individual';
    $params['source'] = $this->getCellValue($this->colNum['source'], $this->currentRow);
    $params['first_name'] = $this->getCellValue($this->colNum['first_name'], $this->currentRow);
    $params['last_name'] = $this->getCellValue($this->colNum['last_name'], $this->currentRow);
    $params['nick_name'] = $this->getCellValue($this->colNum['nick_name'], $this->currentRow);

    $birthDate = $this->getCellValue($this->colNum['birth_date'], $this->currentRow);
    if ($birthDate) {
      $params['birth_date'] = $this->convertExcelDateToYMD($birthDate);
    }

    $gender = $this->getCellValue($this->colNum['gender'], $this->currentRow);
    if ($gender) {
      $params['gender'] = $this->convertGenderToGenderId($gender);
    }

    return $params;
  }

  private function convertExcelDateToYMD($excelDate) {
    $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($excelDate);
    return date_format($date,'Y-m-d');
  }

  private function convertGenderToGenderId($gender) {
    die("GENDER NOT IMPLEMENTED YET");
  }

  private function openExcelFile($fileName) {
    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
    $reader->setReadDataOnly(TRUE);

    $worksheetData = $reader->listWorksheetInfo($fileName);
    $reader->setLoadSheetsOnly($worksheetData[0]);
    $this->spreadsheet = $reader->load($fileName);

    return $worksheetData[0]['totalRows'];
  }

  private function getCellValue($col, $row) {
    return $this->spreadsheet->getActiveSheet()->getCellByColumnAndRow($col, $row)->getValue();
  }

  private function readColumnHeaders() {
    $this->colNum['source'] = 1;

    $col = 2;
    while ($cellValue = $this->getCellValue($col, 1)) {
      if (empty($this->colHeaderMapping[$cellValue])) {
        echo "Colonne ignorée: $cellValue\n";
      }
      else {
        $this->colNum[$this->colHeaderMapping[$cellValue]] = $col;
      }

      $col++;
    }
  }

  private function validateColumnHeaders() {
    $missingColumns = FALSE;

    foreach ($this->colHeaderMapping as $k => $v) {
      if (empty($this->colNum[$v])) {
        echo "Colonne manquante: $k\n";
        $missingColumns = TRUE;
      }
    }

    if ($missingColumns) {
      throw new Exception("Colonne(s) manquante(s)");
    }
  }
}