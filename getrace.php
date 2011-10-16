<?php

require_once('raceplots.php');

$race_id = mysql_real_escape_string($_REQUEST['race_id']);

echo "downloading and converting race " . $race_id . "<br>\n";
$txt = download_race($race_id);

echo "parsing race data" . $race_id . "<br>\n";
$rows = parse_race($txt);

echo "inserting race data into database " . $race_id . "<br>\n";
$query = sprintf('SELECT * FROM race_times WHERE race_id=%s', $race_id);
if (sizeof($rows) != mysql_num_rows(mysql_query($query, $db)))
  echo store_race($db, 'race_times', $race_id, $rows, $fmt_key, $fmt_val);

?>