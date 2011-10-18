<?php

require_once('raceplots.php');

$event_id = mysql_real_escape_string($_REQUEST['event_id']);
$query_str = 'SELECT * FROM races WHERE event_id=' . $event_id;
$results = mysql_query($query_str, $db);

// if no races found, fetch from onlineraceresults
if (mysql_num_rows($results) == 0)  {
    $races = get_event_races($event_id);
    $results = mysql_query($query_str, $db);
}

$races = array();
while ($row = mysql_fetch_assoc($results)) {
  $race_id = $row['id'];

  $query = sprintf('SELECT * FROM race_times WHERE race_id=%s', $race_id);

  // if no race times found, download, parse, and store race
  if (mysql_num_rows(mysql_query($query, $db)) == 0) {
    $txt = download_race($race_id);
    $times = parse_race($txt);
    store_race($db, 'race_times', $race_id, $times, $fmt_key, $fmt_val);
  }

  array_push($races, $row);
}

echo json_encode($races);

?>