<?php

class ImportListeContact {
  public static function create($params) {
    $result = civicrm_api3('Contact', 'create', $params);
    return $result['id'];
  }

  public static function createAddress($params) {
    $result = civicrm_api3('Address', 'create', $params);
    return $result['id'];
  }

  public static function createPhone($params) {
    $result = civicrm_api3('Phone', 'create', $params);
    return $result['id'];
  }

  public static function createEmail($params) {
    $result = civicrm_api3('Email', 'create', $params);
    return $result['id'];
  }
}