<?php

require_once('raceplots.php');

$event_id = mysql_real_escape_string($_REQUEST['event_id']);
$query_str = 'SELECT * FROM races WHERE event_id=' . $event_id;
$results = mysql_query($query_str, $db);

$races = array();
while ($row = mysql_fetch_assoc($results))
  array_push($races, $row);

// force remote check of onlineraceresults  
if (sizeof($races) == 0)
    $races = get_event_races($event_id);

echo json_encode($races);

?>