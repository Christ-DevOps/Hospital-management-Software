<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/assets/inc/mailer.php';

header('Content-Type: text/plain; charset=UTF-8');

$res = $mysqli->query('SELECT pat_id FROM his_patients WHERE efile_uuid IS NULL');
$count = 0;
while ($row = $res->fetch_object()) {
    $uuid = nex_generate_uuid_v4();
    $upd = $mysqli->prepare('UPDATE his_patients SET efile_uuid = ? WHERE pat_id = ?');
    $upd->bind_param('si', $uuid, $row->pat_id);
    $upd->execute();
    $upd->close();
    $count++;
}

echo "Backfilled efile_uuid for {$count} patient(s).\n";
