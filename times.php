<?php

require_once('raceplots.php');

$race_id = mysql_real_escape_string($_REQUEST['race_id']);
$athlete = mysql_real_escape_string($_REQUEST['athlete']);

$query = sprintf('SELECT * FROM race_times WHERE race_id=%s AND athlete="%s"', $race_id, $athlete);
$results = mysql_query($query, $db);

$response = array();
while ($row = mysql_fetch_array($results))
  array_push($response, $row);

echo json_encode($response);

?>