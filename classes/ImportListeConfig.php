<?php

class ImportListeConfig {
  public function dropCustomGroupTable() {
    $sql = "drop table if exists civicrm_value_bureau_de_vote";
    CRM_Core_DAO::executeQuery($sql);
  }

  public function createCustomFields() {
    $this->getCustomField_codeBureau();
    $this->getCustomField_libelleBureau();
    $this->getCustomField_numOrdre();
    $this->getCustomField_circonscription();
    $this->getCustomField_canton();
  }
  
  public function getCustomGroup_BureauDeVote() {
    $params = [
      'name' => 'bureau_de_vote',
      'title' => 'Bureau de vote',
      'extends' => 'Individual',
      'style' => 'Inline',
      'collapse_display' => '0',
      'weight' => '1',
      'is_active' => '1',
      'table_name' => 'civicrm_value_bureau_de_vote',
      'is_multiple' => '0',
      'collapse_adv_display' => '0',
      'is_reserved' => '0',
      'is_public' => '0'
    ];
    return $this->createOrGetCustomGroup($params);
  }

  public function getCustomField_codeBureau() {
    $params = [
      'custom_group_id' => $this->getCustomGroup_BureauDeVote()['id'],
      'name' => 'code_bureau',
      'label' => 'Code du bureau de vote',
      'data_type' => 'Int',
      'html_type' => 'Text',
      'is_searchable' => '1',
      'is_search_range' => '1',
      'weight' => '1',
      'is_active' => '1',
      'text_length' => '255',
      'column_name' => 'code_bureau',
      'in_selector' => '0'
    ];
    return $this->createOrGetCustomField($params);
  }

  public function getCustomField_libelleBureau() {
    $params = [
      'custom_group_id' => $this->getCustomGroup_BureauDeVote()['id'],
      'name' => 'libelle_bureau',
      'label' => 'Libellé du bureau de vote',
      'data_type' => 'String',
      'html_type' => 'Text',
      'is_searchable' => '1',
      'is_search_range' => '0',
      'weight' => '2',
      'is_active' => '1',
      'text_length' => '255',
      'column_name' => 'libelle_bureau',
      'in_selector' => '0'
    ];
    return $this->createOrGetCustomField($params);
  }

  public function getCustomField_numOrdre() {
    $params = [
      'custom_group_id' => $this->getCustomGroup_BureauDeVote()['id'],
      'name' => 'numero_ordre',
      'label' => "Numéro d'ordre dans le bureau de vote",
      'data_type' => 'Int',
      'html_type' => 'Text',
      'is_searchable' => '0',
      'is_search_range' => '0',
      'weight' => '3',
      'is_active' => '1',
      'text_length' => '255',
      'column_name' => 'numero_ordre',
      'in_selector' => '0'
    ];
    return $this->createOrGetCustomField($params);
  }

  public function getCustomField_circonscription() {
    $params = [
      'custom_group_id' => $this->getCustomGroup_BureauDeVote()['id'],
      'name' => 'circonscription',
      'label' => 'Circonscription législative du bureau de vote',
      'data_type' => 'String',
      'html_type' => 'Text',
      'is_searchable' => '1',
      'is_search_range' => '0',
      'weight' => '4',
      'is_active' => '1',
      'text_length' => '255',
      'column_name' => 'circonscription',
      'in_selector' => '0'
    ];
    return $this->createOrGetCustomField($params);
  }

  public function getCustomField_canton() {
    $params = [
      'custom_group_id' => $this->getCustomGroup_BureauDeVote()['id'],
      'name' => 'canton',
      'label' => 'Canton du bureau de vote',
      'data_type' => 'String',
      'html_type' => 'Text',
      'is_searchable' => '1',
      'is_search_range' => '0',
      'weight' => '5',
      'is_active' => '1',
      'text_length' => '255',
      'column_name' => 'canton',
      'in_selector' => '0'
    ];
    return $this->createOrGetCustomField($params);
  }

  public function createOrGetCustomGroup($params) {
    try {
      $customGroup = civicrm_api3('CustomGroup', 'getsingle', [
        'name' => $params['name'],
      ]);
    }
    catch (Exception $e) {
      $customGroup = civicrm_api3('CustomGroup', 'create', $params);
    }

    return $customGroup;
  }

  public function createOrGetCustomField($params) {
    try {
      $customField = civicrm_api3('CustomField', 'getsingle', [
        'custom_group_id' => $params['custom_group_id'],
        'name' => $params['name'],
      ]);
    }
    catch (Exception $e) {
      $customField = civicrm_api3('CustomField', 'create', $params);
    }

    return $customField;
  }
}