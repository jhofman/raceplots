<?php

function div_pl($s) {
  $parts = split('/',$s);
  return $parts[0];
}

function mysql_time($s) {
  $parts = split(':',$s);
  while (sizeof($parts) < 3)
    array_unshift($parts,'00');

  return sprintf('%02d:%02d:%02d', $parts[0], $parts[1], $parts[2]);
}

function time_to_sec($s) {
  $parts = split(':',$s);
  while (sizeof($parts) < 3)
    array_unshift($parts,'00');

  $null = array();
  for ($i=0; $i<sizeof($parts); $i++) {
    $parts[$i] = sprintf('%02d', $parts[$i]);
    array_push($null, '00');
  }
  
  return strtotime(join(':',$parts)) - strtotime(join(':',$null));
}

function to_str($s) {
  return (string) $s;
}

function to_int($s) {
  return (int) $s;
}

?>