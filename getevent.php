<?php

require_once('raceplots.php');

$event_id = mysql_real_escape_string($_REQUEST['id']);

// check races cache against onlineraceresults
$races = get_event_races($event_id);

// get races for this event
$query_str = 'SELECT * FROM races WHERE event_id=' . $event_id;
$results = mysql_query($query_str, $db);

while ($row = mysql_fetch_array($results)) {
  $race_id = $row['id'];
  echo date('c') . " event $event_id, race $race_id\n";
  $query = sprintf('SELECT * FROM race_times WHERE race_id=%s', $race_id);

  // download, parse, and store race
  if (mysql_num_rows(mysql_query($query, $db)) == 0) {
    echo date('c') . " downloading, parsing, storing\n";
    $txt = download_race($race_id);
    $times = parse_race($txt);
    store_race($db, 'race_times', $race_id, $times, $fmt_key, $fmt_val);
  }
}

?>