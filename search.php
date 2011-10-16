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
$(document).ready(function() { 

  $.ajax({url: "http://onlineraceresults.com/search/index.php",
	type: "GET",
	data: { search_term: "nation's" },
	success: function(data) {
          var results = $('table.search-results', $(data.responseText));
	  
	  console.log(results.('table'));

	  }
        });

});
  </script>

 </head>

 <body>
  <div class="container">


  </div>

 </body>

<?php

?>

</html>