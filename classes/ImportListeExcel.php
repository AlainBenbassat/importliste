<?php

class ImportListeExcel {
  private $spreadsheet;
  private $colNum = [];
  private $colHeaderMapping;

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
    ];
  }

  public function import($fileName) {
    if (!file_exists($fileName)) {
      throw new Exception("Le fichier $fileName n'existe pas.");
    }

    $numLines = $this->openExcelFile($fileName);
    $this->readColumnHeaders();

    echo "Num lines = $numLines\n";
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
      if ($cellValue == 'nom de naissance') {

      }
      $col++;
    }
  }
}