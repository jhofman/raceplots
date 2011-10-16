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

// check database for event
$results = mysql_query($query_str, $db);
$events = array();
while ($row = mysql_fetch_assoc($results))
  array_push($events, $row);

// if not found, check onlineraceresults
if (sizeof($events) == 0) {
  $events = search_events($query);

  foreach ($events as $event) {
    $query_str = sprintf('INSERT INTO events (id,name,location,ymd) VALUES (%d,"%s","%s","%s")',
			 $event['id'], $event['name'], $event['location'], $event['ymd']);
    mysql_query($query_str, $db);
  }

}

// get race ids for these events
//foreach ($events as $event) {
for ($i=0; $i<sizeof($events); $i++) {
  $event = $events[$i];

  $query_str = 'SELECT * FROM races WHERE event_id=' . $event['id'];
  $results = mysql_query($query_str, $db);

  $races = array();
  while ($row = mysql_fetch_assoc($results))
    array_push($races, $row);
  
  if (sizeof($races) == 0) {
    $races = get_event_races($event['id']);

    foreach ($races as $race) {
      $query_str = sprintf('INSERT INTO races (id,event_id,name) VALUES (%d,%d,"%s")',
			   $race['id'], $event['id'], $race['name']);
      mysql_query($query_str, $db);
    }
  }

  $events[$i]["races"] = $races;
}

echo json_encode($events);

?>