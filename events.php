<?php

require_once('raceplots.php');

$query = array();
$query_str = 'SELECT * FROM events WHERE 1';

$name = mysql_real_escape_string($_REQUEST['name']);
if ($name != '') {
  $query['name'] = $name;
  $query_str .= " AND name LIKE '%$name%'";
}

$location = mysql_real_escape_string($_REQUEST['location']);
if ($location != '') {
  $query['location'] = $location;
  $query_str .= " AND location='$location'";
 }

$year = mysql_real_escape_string($_REQUEST['year']);
if ($year != '') {
  $query['year'] = $year;
  $query_str .= " AND year(ymd)='".$year."'";
}

$month = mysql_real_escape_string($_REQUEST['month']);
if ($month != '') {
  $query['month'] = $month;
  $query_str .= " AND month(ymd)='$month'";
}

// force remote check of onlineraceresults
$remote = mysql_real_escape_string($_REQUEST['remote']);
if ($remote == true)
  search_events($query);

// check database for events
$results = mysql_query($query_str, $db);
$events = array();
while ($row = mysql_fetch_assoc($results))
  array_push($events, $row);

echo json_encode($events);

?>