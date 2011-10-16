<?php

require_once('raceplots.php');

$race_id = mysql_real_escape_string($_REQUEST['race_id']);
$athlete = mysql_real_escape_string($_REQUEST['athlete']);

$response = array('counts' => array());
//$sports = array('swim' => 
foreach (array('swim','bike','run','total') as $sport) {
  $query = sprintf('SELECT count(id) as count, round(time_to_sec(%s)/120)*120 as time FROM race_times WHERE race_id=%s GROUP BY time', $sport, $race_id);
  $results = mysql_query($query, $db);
  
  $response['counts'][$sport] = array();
  while ($row = mysql_fetch_array($results))
    array_push($response['counts'][$sport], array((float) $row['time'], (int) $row['count']));

  $query = sprintf('select count(*) as rank from race_times as all_times, race_times as athlete_time where all_times.race_id=%d and all_times.race_id=athlete_time.race_id and athlete_time.athlete="%s" and all_times.%s < athlete_time.%s', $race_id, $athlete, $sport, $sport);
  $results = mysql_query($query, $db);
  $row = mysql_fetch_array($results);
  $response[$sport . '_rank'] = (int) $row['rank'];
}

$query = sprintf('select count(*) as athletes from race_times where race_times.race_id=%d', $race_id);
$results = mysql_query($query, $db);
$row = mysql_fetch_array($results);
$response['athletes'] = (int) $row['athletes'];

echo json_encode($response);

?>