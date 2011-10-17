<?php

require_once('raceplots.php');

?>

<html>
 <head>

  <link rel="stylesheet" href="css/blueprint/screen.css" type="text/css" media="screen, projection">
  <link rel="stylesheet" href="css/blueprint/src/typography.css" type="text/css" media="screen, projection">
  <link rel="stylesheet" href="css/blueprint/print.css" type="text/css" media="print">
  <!--[if lt IE 8]><link rel="stylesheet" href="css/blueprint/ie.css" type="text/css" media="screen, projection"><![endif]-->   
  <!-- horizontal menu css: http://groups.google.com/group/blueprintcss/browse_thread/thread/a45e288643b692da -->
  <!-- more here: http://www.alistapart.com/articles/taminglists/ -->
  <link type="text/css" href="css/smoothness/jquery-ui-1.8.10.custom.css" rel="Stylesheet" />

  <style>
   path {
    stroke: steelblue;
    stroke-width: 2;
    fill: lightblue;
   }

   div#hists svg {
    padding: 2;
   }
  </style>

  <script src="jquery-1.5.1.min.js"></script>
  <script src="jquery-ui-1.8.10.custom.min.js"></script>
  <!-- <script src="jquery.tablesorter.min.js"></script>-->
  <script src="d3.min.js"></script>
  <script src="raceplots.js"></script>
 </head>

 <body>
  <div class="container">

   <div class="span-16 prepend-4 append-4 prepend-top last">
    <fieldset>
     <label for=event>Event</label>
     <input id=event type=text style="width: 300px">
     <!--
     <select id=event onChange="update_races()" style="width:150px">
      <option value="" selected></option>
      <option value="-1">Search OnlineRaceResults</option>
      <?php
        $results = mysql_query("SELECT id,name,year(ymd) as year FROM events", $db);
        while ($row = mysql_fetch_assoc($results))
	  echo sprintf('<option value=%d>%s %s</option>\n', $row['id'], $row['name'], $row['year']);
      ?>
     </select>
     -->
     <br/>

    <div id=event_fields style="display: none">
     <label for=race>Race</label>
     <select id=race onChange="update_divisions()" style="width:150px">
      <option value="" selected></option>
     </select>

     &nbsp; 

     <label for=division>Division</label>
     <select id=division onChange="update_athletes()" style="width:75px">
      <option value="All" selected>All</option>
     </select>

     &nbsp; 

     <label for=athlete>Athlete</label>
     <input id=athlete type=text width=100%></input>

    </div>
   </div>

   <div class="span-16 prepend-4 append-4 last">
     <center>
     <table class="tablesorter" id="events"></table>

     <div id="hists" style="width:100%;height:100px;"></div>
     <table class="tablesorter" id="times"></table>
     </center>
   </div>

  </div>

 </body>

</html>