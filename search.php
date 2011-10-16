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
  <script src="jquery.xdomainajax.js"></script>


  <script>
function search_events(query) {

  events = [];
  $.ajax({url: "http://onlineraceresults.com/search/index.php",
	type: "GET",
	data: query,
	success: function(data) {
          var rows = $('table.search-results tr', $(data.responseText));
	  
	  $.each(rows, function (i, row) {
	      var event = {};

	      var a = $('td[class="data one"] a', row);
	      event['name'] = a.text();
	      event['id'] = a.attr('href').split('=')[1];

	      event['date'] = $('td[class="data two"] p', row).text();

	      event['location'] = $('td[class="data three"] p', row).text();

	      events.push(event);
	    });

	  }

        });

  return events;
}

$(document).ready(function() { 

    var events = search_events({search_term: 'nautica'});

});
  </script>

 </head>

 <body>
  <div id=events class="container">


  </div>

 </body>

<?php

?>

</html>