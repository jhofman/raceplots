<?php

require_once('raceplots.php');

$race_id = mysql_real_escape_string($_REQUEST['race_id']);

$query = sprintf('SELECT athlete FROM race_times WHERE race_id=%s', $race_id);
$results = mysql_query($query, $db);

$response = array();
while ($row = mysql_fetch_array($results))
  array_push($response, $row['athlete']);

echo json_encode($response);

?>