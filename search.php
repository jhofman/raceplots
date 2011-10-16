<?php

libxml_use_internal_errors(true);

$url = "http://onlineraceresults.com/search/index.php?search_term=nautica";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$doc = new DOMDocument();
$doc->strictErrorChecking = false;
$doc->loadHTML($response);
$xml = simplexml_import_dom($doc);

$rows = $xml->xpath('//table[@class="search-results"]/tr');

$events = array();
foreach ($rows as $row) {
  $event = array();

  $a = $row->xpath('td[@class="data one"]/a');
  $event['name'] = (string) $a[0];
  $attrib = $a[0]->attributes();
  $event['url'] = 'http://onlineraceresults.com' . $attrib['href'];
  preg_match('/event_id=(\d+)/', $attrib['href'], $matches);
  $event['id'] = $matches[1];
  //list($url, $event['id']) = array_map(strval, split('=', $attrib['href']));

  $p = $row->xpath('td[@class="data two"]');
  $event['date'] = (string) $p[0];
  $p = $row->xpath('td[@class="data three"]');
  $event['location'] = (string) $p[0];

  array_push($events, $event);
}

print_r($events);

$event = $events[0];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $event['url']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$doc = new DOMDocument();
$doc->strictErrorChecking = false;
$doc->loadHTML($response);
$xml = simplexml_import_dom($doc);

$lis = $xml->xpath('//ul/li/a[contains(@href,"view_race")]');
$races = array();
foreach ($lis as $li) {
  $race = array();

  $race['name'] = (string) $li;
  $attrib = $li[0]->attributes();
  $race['url'] = 'http://onlineraceresults.com' . $attrib['href'];
  preg_match('/race_id=(\d+)/', $attrib['href'], $matches);
  $race['id'] = $matches[1];
  //list($url, $race['id']) = array_map(strval, split('=', $attrib['href']));

  array_push($races, $race);
}


print_r($races);



?>