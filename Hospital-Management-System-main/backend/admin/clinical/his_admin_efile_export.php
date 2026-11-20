<?php
$nex_admin_base = '../';
require __DIR__ . '/../_init.php';
require_once __DIR__ . '/../../assets/inc/efile_data.php';
  require_once __DIR__ . '/../../assets/inc/efile_fhir.php';
  check_login();
  $pat_id = (int) ($_GET['pat_id'] ?? 0);
  $efile = $pat_id > 0 ? nex_efile_bundle($mysqli, $pat_id) : null;

  header('Content-Type: application/json; charset=UTF-8');
  if (!$efile) {
      http_response_code(404);
      echo json_encode(['error' => 'Patient not found']);
      exit;
  }

  echo json_encode(nex_fhir_bundle($efile), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
