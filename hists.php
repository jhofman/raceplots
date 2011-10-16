<?php

require_once('raceplots.php');

$race_id = mysql_real_escape_string($_REQUEST['race_id']);
$athlete = mysql_real_escape_string($_REQUEST['athlete']);
$division = mysql_real_escape_string($_REQUEST['division']);

if ($division == "All")
  $division = "";

$response = array('counts' => array());
foreach (array('swim','bike','run','total') as $sport) {
  $query = "SELECT count(id) as count, round(time_to_sec($sport)/120)*120 as time FROM race_times WHERE race_id=$race_id ";
  if ($division != "")
    $query .= "AND division='$division' ";
  $query .= "GROUP BY time ";
  $results = mysql_query($query, $db);
  
  $response['counts'][$sport] = array();
  while ($row = mysql_fetch_array($results))
    array_push($response['counts'][$sport], array((float) $row['time'], (int) $row['count']));

  if ($athlete != "") {
    $query = sprintf('select count(*) as rank from race_times as all_times, race_times as athlete_time where all_times.race_id=%d and all_times.race_id=athlete_time.race_id and athlete_time.athlete="%s" and all_times.%s < athlete_time.%s', $race_id, $athlete, $sport, $sport);
    if ($division != "")
      $query .= " and all_times.division='$division'";
    error_log($query);
    $results = mysql_query($query, $db);
    $row = mysql_fetch_array($results);
    $response[$sport . '_rank'] = (int) $row['rank'];

    $query = "select count(*) as athletes from race_times where race_id=$race_id and $sport > 0";
    if ($division != "")
      $query .= " and division='$division'";
    error_log($query);
    $results = mysql_query($query, $db);
    $row = mysql_fetch_array($results);
    $response[$sport . '_athletes'] = (int) $row['athletes'];

    $query = sprintf('SELECT round(time_to_sec(%s)/120)*120 as time FROM race_times WHERE race_id=%s AND athlete="%s"', $sport, $race_id, $athlete);
    $results = mysql_query($query, $db);
    $row = mysql_fetch_array($results);
    $response[$sport . '_time'] = (int) $row['time'];
  }
}

$query = sprintf('select count(*) as athletes from race_times where race_times.race_id=%d', $race_id);
if ($division != "")
  $query .= " and division='$division'";
$results = mysql_query($query, $db);
$row = mysql_fetch_array($results);
$response['athletes'] = (int) $row['athletes'];

echo json_encode($response);


?>