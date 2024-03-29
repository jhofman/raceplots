<?php

require_once('config.inc');
require_once('fields.php');

$fmt_val = array('PLACE' => to_int,
		 'NAME' => to_str,
		 'DIV' => to_str,
		 'DIV PL' => div_pl,
		 'SWIM' => mysql_time,
		 'T1' => mysql_time,
		 'BIKE' => mysql_time,
		 'T2'  => mysql_time,
		 'RUN' => mysql_time,
		 'PENALTY' => mysql_time,
		 'TIME' => mysql_time);

$fmt_key = array('PLACE' => 'place',
		 'NAME' => 'athlete',
		 'DIV' => 'division',
		 'DIV PL' => 'division_place',
		 'SWIM' => 'swim',
		 'T1' => 't1',
		 'BIKE' => 'bike',
		 'T2'  => 't2',
		 'RUN' => 'run',
		 'PENALTY' => 'penalty',
		 'TIME' => 'total');

function download_race($id) {
  GLOBAL $DATA_DIR;

  $pdf = sprintf('%s/race_%d.pdf', $DATA_DIR, $id);
  if (!file_exists($pdf)) {
    $cmd = sprintf('curl -o %s "http://onlineraceresults.com/race/view_printable.php?race_id=%d"', $pdf, $id);
    system($cmd);
  }

  $txt = sprintf('%s/race_%d.txt', $DATA_DIR, $id);
  if (!file_exists($txt)) {
    $cmd = sprintf('pdftotext -layout %s %s', $pdf, $txt);
    system($cmd);
  }
 
  return $txt;
}

function parse_race($txt) {
  # read file into array oflines
  $lines = file($txt);

  # page header
  $header = $lines[0];
  array_shift($lines);

  # column names
  $colline = $lines[0];
  array_shift($lines);

  # row of dashes, used to calculate column start and width
  $dashes = str_split($lines[0]);
  array_shift($lines);

  # calculate column start and end
  $incol = true;
  $colstart = array(0);
  $colend = array();
  for ($i=0; $i<sizeof($dashes); $i++) {
    if ($dashes[$i] == ' ' && $incol) {
      $incol = false;
      array_push($colend, $i-1);
    }
    
    if ($dashes[$i] == '-' && !$incol) {
      $incol = true;
      array_push($colstart, $i);
    }
  }
  $ncol = sizeof($colstart);
  array_push($colend, $i);

  # calculate column width
  # also extract column names
  $colwidth = array();
  $colnames = array();
  for ($i=0; $i<$ncol; $i++) {
    $colwidth[$i] = $colend[$i] - $colstart[$i] + 1;
    array_push($colnames, trim(substr($colline, $colstart[$i], $colwidth[$i])));
  }

  # loop over each remaining line
  $p = 1;
  $rows = array();
  foreach($lines as $line) {
    if ($line[0] == '') {
      # new page
      $p++;
    } elseif (preg_match('/^[0-9]/', $line)) {
      # data row
      $row = array();
      for($i=0; $i<sizeof($colnames); $i++)
	$row[$colnames[$i]] = trim(substr($line, $colstart[$i], $colwidth[$i]));
      array_push($rows, $row);
    }  
  }
  
  return $rows;  
}

function store_race($db, $table, $race_id, $rows, $fmt_key, $fmt_val) {
  $n = 0;
  foreach ($rows as $row) {
    $keys = array('race_id');
    $values = array($race_id);    

    foreach ($row as $key => $val) {
      array_push($keys, $fmt_key[$key]);
      array_push($values, mysql_real_escape_string($fmt_val[$key]($val)));
    }

    $query = sprintf('INSERT INTO %s (%s) VALUES ("%s")', $table, join(',',$keys), join('","',$values));
    if (mysql_query($query, $db))
      $n++;
  }

  return $n;
}

function binned_times($db, $col, $sec, $where) {
  $query = sprintf('select count(distinct(id)) as count, round(time_to_sec(%s)/%d)*%d as time from race_times where %s group by time', $col, $sec, $sec, $where);
  $result = mysql_query($query);

  $times = array();
  while ($row = mysql_fetch_array($result)) 
    array_push($times, array((float) $row['time'], (int) $row['count']));
  
  return $times;
}

function search_events($query) {
  if (is_string($query))
    $query = array('search_term' => $query);

  $params = http_build_query($query);

  libxml_use_internal_errors(true);

  $url = "http://onlineraceresults.com/search/index.php?" . $params;

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $response = curl_exec($ch);
  curl_close($ch);
  
  $doc = new DOMDocument();
  $doc->strictErrorChecking = false;
  if ($response == "")
    return array();
  $doc->loadHTML($response);
  $xml = simplexml_import_dom($doc);
  
  $rows = $xml->xpath('//table[@class="search-results"]/tr');

  if (!is_array($rows))
    return array();

  $events = array();
  foreach ($rows as $row) {
    $event = array();
    
    $a = $row->xpath('td[@class="data one"]/a');
    $event['name'] = trim($a[0]);
    $attrib = $a[0]->attributes();
    $event['url'] = 'http://onlineraceresults.com' . $attrib['href'];
    preg_match('/event_id=(\d+)/', $attrib['href'], $matches);
    $event['id'] = (int) $matches[1];
    //list($url, $event['id']) = array_map(strval, split('=', $attrib['href']));
    
    $p = $row->xpath('td[@class="data two"]');
    $event['ymd'] = strftime("%Y-%m-%d", strtotime(trim($p[0])));
    $p = $row->xpath('td[@class="data three"]');
    $event['location'] = trim($p[0]);

    // cache event in database
    $query_str = sprintf('INSERT INTO events (id,name,location,ymd) VALUES (%d,"%s","%s","%s")',
			 $event['id'], $event['name'], $event['location'], $event['ymd']);
    mysql_query($query_str);
    
    array_push($events, $event);
  }

  libxml_use_internal_errors(false);
  
  return $events;
}

function get_event_races($id) {

  libxml_use_internal_errors(true);

  $url = "http://onlineraceresults.com/event/view_event.php?event_id=" . $id;

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $response = curl_exec($ch);
  curl_close($ch);
  
  $doc = new DOMDocument();
  $doc->strictErrorChecking = false;
  if ($response == "")
    return array();
  $doc->loadHTML($response);
  $xml = simplexml_import_dom($doc);
  
  $lis = $xml->xpath('//ul/li/a[contains(@href,"view_race")]');

  if (!is_array($lis))
    return array();

  $races = array();
  foreach ($lis as $li) {
    $race = array();
  
    $race['name'] = trim($li);
    $attrib = $li[0]->attributes();
    $race['url'] = 'http://onlineraceresults.com' . $attrib['href'];
    preg_match('/race_id=(\d+)/', $attrib['href'], $matches);
    $race['id'] = $matches[1];
    //list($url, $race['id']) = array_map(strval, split('=', $attrib['href']));
    
    // cache race in database
    $query_str = sprintf('INSERT INTO races (id,event_id,name) VALUES (%d,%d,"%s")',
			 $race['id'], $id, $race['name']);
    mysql_query($query_str);

    array_push($races, $race);
  }
  
  libxml_use_internal_errors(false);
  
  return $races;
}


// get race by id (txt, pdf, parse, insert into db)
/*
$id = 21268;
$txt = download_race($id);
$rows = parse_race($txt);
$query = sprintf('SELECT * FROM race_times WHERE race_id=%s', $id);
if (sizeof($rows) != mysql_num_rows(mysql_query($query, $db)))
  echo store_race($db, 'race_times', $id, $rows, $fmt_key, $fmt_val);
*/
// do we want to return individual info w/ this?
//$where = sprintf('race_id=%d and division="M 30-34"', $id);
//echo json_encode(binned_times($db, 'total', 120, $where));

/*
exit(0);

$bike = array();
foreach ($rows as $row)
//if ($row['DIV'] == 'M 30-34')
    //$bike[$fmt_field['BIKE']($row['BIKE'])]++;
    array_push($bike, $fmt_field['BIKE']($row['BIKE']));

echo json_encode($bike);
*/

/*
ksort($bike);
$cdf = array('time' => array(),
	     'count' => array());
$tot = 0;
foreach ($bike as $t => $n) {
  array_push($cdf['time'], $t);
  $tot += $n;
  array_push($cdf['count'], $tot);
}

echo json_encode($cdf);
*/
?>