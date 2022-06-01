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
      'PORTABLE' => 'mobile_phone',
      'FIXE' => 'home_phone',
      'MAIL' => 'email',
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

      $this->importLineAddressData($contactId);
      $this->importLineMobilePhoneData($contactId);
      $this->importLineHomePhoneData($contactId);
      $this->importLineEmailData($contactId);

      $this->currentRow++;
    }
  }

  private function importLineContactData() {
    $contactParams = $this->currentRowGetContactParams();
    return ImportListeContact::create($contactParams);
  }

  private function importLineAddressData($contactId) {
    $addressParams = $this->currentRowGetAddressParams($contactId);
    if ($addressParams) {
      ImportListeContact::createAddress($addressParams);
    }
  }

  private function importLineMobilePhoneData($contactId) {
    $phoneParams = $this->currentRowGetMobilePhoneParams($contactId);
    if ($phoneParams) {
      ImportListeContact::createPhone($phoneParams);
    }
  }

  private function importLineHomePhoneData($contactId) {
    $phoneParams = $this->currentRowGetHomePhoneParams($contactId);
    if ($phoneParams) {
      ImportListeContact::createPhone($phoneParams);
    }
  }

  private function importLineEmailData($contactId) {
    $emailParams = $this->currentRowGetEmailParams($contactId);
    if ($emailParams) {
      ImportListeContact::createEmail($emailParams);
    }
  }

  private function currentRowGetContactParams() {
    $params = [];

    $params['contact_type'] = 'Individual';
    $params['sequential'] = 1;
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

  private function currentRowGetAddressParams($contactId) {
    $params = [];

    $params['contact_id'] = $contactId;
    $params['location_type_id'] = 1;
    $params['sequential'] = 1;

    $streetNumber = $this->getCellValue($this->colNum['street_number'], $this->currentRow);
    $streetName = $this->getCellValue($this->colNum['street_name'], $this->currentRow);

    if ($streetNumber && $streetName) {
      $params['street_address'] = "$streetNumber $streetName";
    }
    elseif ($streetName) {
      $params['street_address'] = $streetName;
    }

    $params['postal_code'] = $this->getCellValue($this->colNum['postal_code'], $this->currentRow);
    if (strlen($params['postal_code']) == 4) {
      $params['postal_code'] = '0' . $params['postal_code'];
    }

    $params['state_province_id'] = $this->getStateProvinceId($params['postal_code']);

    $params['city'] = $this->getCellValue($this->colNum['city'], $this->currentRow);
    $params['country_id'] = 1076;

    $sup1 = $this->getCellValue($this->colNum['supplemental_address_1'], $this->currentRow);
    $sup2 = $this->getCellValue($this->colNum['supplemental_address_2'], $this->currentRow);
    $sup3 = $this->getCellValue($this->colNum['supplemental_address_3'], $this->currentRow);

    $i = 1;
    if ($sup1) {
      $params["supplemental_address_$i"] = $sup1;
      $i++;
    }

    if ($sup2) {
      $params["supplemental_address_$i"] = $sup2;
      $i++;
    }

    if ($sup3) {
      $params["supplemental_address_$i"] = $sup3;
    }

    return $params;
  }

  private function getStateProvinceId($postalCode) {
    $stateId = '';

    $postalCodePrefix = substr($postalCode, 0, 2);
    if ($postalCodePrefix) {
      if ($postalCodePrefix == '06') {
        $stateId = 2502;
      }
      elseif ($postalCodePrefix == '75') {
        $stateId = 2567;
      }
      else {
        if ($postalCodePrefix == '20') {
          $postalCodePrefix = substr($postalCode, 0, 3);
        }

        $stateId = CRM_Core_DAO::singleValueQuery("select id from civicrm_state_province where country_id = 1076 and abbreviation = '$postalCodePrefix'");
      }
    }

    return $stateId;
  }

  private function currentRowGetMobilePhoneParams($contactId) {
    $phone = $this->getCellValue($this->colNum['mobile_phone'], $this->currentRow);
    if (empty($phone)) {
      return FALSE;
    }

    $params = [];

    $params['contact_id'] = $contactId;
    $params['phone'] = $phone;
    $params['location_type_id'] = 1;
    $params['phone_type_id'] = 2;
    $params['sequential'] = 1;

    return $params;
  }

  private function currentRowGetHomePhoneParams($contactId) {
    $phone = $this->getCellValue($this->colNum['home_phone'], $this->currentRow);
    if (empty($phone)) {
      return FALSE;
    }

    $params = [];

    $params['contact_id'] = $contactId;
    $params['phone'] = $phone;
    $params['location_type_id'] = 1;
    $params['phone_type_id'] = 1;
    $params['sequential'] = 1;

    return $params;
  }

  private function currentRowGetEmailParams($contactId) {
    $email = $this->getCellValue($this->colNum['email'], $this->currentRow);
    if (empty($phone)) {
      return FALSE;
    }

    $params = [];

    $params['contact_id'] = $contactId;
    $params['email'] = $email;
    $params['location_type_id'] = 1;
    $params['sequential'] = 1;

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