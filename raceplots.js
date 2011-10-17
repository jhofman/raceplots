var athletes = [];
var event_ids = {};
$(document).ready(function() { 

  update_events();
  $("input#event").keyup(update_races);

  // search on enter if no autocomplete match
  $("input#event").keydown(function(event){
	  if(event.keyCode == 13){

	      var event_name = $('input#event').val();
	      var event_id = event_ids[event_name];
	      
	      if (event_id != undefined)
		  return;

	      $.ajax({url: "events.php",
	        type: "GET",
		data: {name: $("input#event").val(), remote: 1},
	        success: function(data) {
	         var events = JSON.parse(data);
	    
		 console.log(events);

		 var fields = {'Event': 'name', 'Location': 'location', 'Date': 'ymd'};
		 print_events_table(events, 'table#events', fields);
		 
		}
	      });

	  }
      });  
});

function update_events() {
  $.ajax({url: "events.php",
	  type: "GET",
	  success: function(data) {
	    var response = JSON.parse(data);
	    
	    var events = [];
	    $.each(response, function (i, event) {
		    var year = event['ymd'].substring(0,4);
		    //events.push({label: event['name'] + " " + year, value: event['id']});
		    
		    var key = event['name'] + " " + year;
		    events.push(key);

		    event_ids[key] = event['id'];
	    });

	    $("input#event").autocomplete({
		source: events
	    });	
	  }
      });
}

function update_races() {
  var event_name = $('input#event').val();
  var event_id = event_ids[event_name];

  if (event_name != "" && event_id == undefined)
      return;

  $('table#events').html('');

  $.ajax({url: "races.php",
	  type: "GET",
	  data: { event_id: event_id },
	  success: function(data) {
	    var races = JSON.parse(data);

	    if (races.length > 0)
		$('div#event_fields').slideDown('fast');
	    else
		$('div#event_fields').hide();

	    races.sort();
	    $('select#race').html('<option value=""></option>');
	    $.each(races, function (i, race) {
	      $('select#race').append('<option value="'+race['id']+'">'+race['name']+'</option>');
	    });

	  }
        });

}

function update_divisions() {
  var race_id=$('select#race option:selected').val();

  $.ajax({url: "divisions.php",
	  type: "GET",
	  data: { race_id: race_id },
	  success: function(data) {
	    var divisions = JSON.parse(data);
	    
	    divisions.sort();
	    $.each(divisions, function (i, div) {
	      $('select#division').append('<option value="'+div+'">'+div+'</option>');
	    });

	    update_athletes();
	    update_times();
	  }
        });

}

function update_athletes() {
  var race_id=$('select#race option:selected').val();

  $.ajax({url: "athletes.php",
	  type: "GET",
	  data: { race_id: race_id },
	  success: function(data) {
	    athletes = JSON.parse(data);
	    
	    $("input#athlete").autocomplete({
	      source: athletes
	    });

	    $("input#athlete").keyup(update_times);

	    update_times();
	  }
        });

}

function update_times() {
  var athlete = $("input#athlete").val();

  if (athlete != "" && $.inArray(athlete, athletes) < 0)
    return;

  var race_id=$('select#race option:selected').val();

  $.ajax({url: "times.php",
	  type: "GET",
	  data: { race_id: race_id, athlete: athlete },
	  success: function(data) {
	    var times = JSON.parse(data);

	    update_hists();

	    var fields = {'Place': 'place', 'Div': 'division', 'Div Place': 'division_place', 'Swim': 'swim', 'T1': 't1', 'Bike': 'bike', 'T2': 't2', 'Run': 'run', 'Penalty': 'penalty', 'Total': 'total'};
	    print_times_table(times, 'table#times', fields);
	  }
        });
}

function update_hists() {
  var race_id=$('select#race option:selected').val();
  var athlete = $("input#athlete").val();
  var division=$('select#division option:selected').val();

  var tmax = {swim: 3600,
	      bike: 2*3600,
	      run: 1.5*3600,
	      total: 3*3600};
  $.ajax({url: "hists.php",
	  type: "GET",
	  data: { race_id: race_id, athlete: athlete, division: division },
	  success: function(data) {
	      var hists = JSON.parse(data);

	      $.each(hists.counts, function (sport, hist) {
	        var times = [];
	        var counts = [];

		var athlete_bin = null;
		var cumsum = [];
	        $.each(hist, function (i, p) {
		      times.push(p[0]);
		      counts.push(p[1]);

		      if (i == 0)
			  cumsum.push(p);
		      else
			  cumsum.push([p[0], cumsum[cumsum.length-1][1]+p[1]]);

		      var sport_bin = sport + '_time';
		      if (sport_bin in hists && p[0] == hists[sport_bin])
			  athlete_bin = p;			  
		});
		times.unshift(times[0]);
		counts.unshift(0);
		times.push(times[times.length-1]);
		counts.push(0);

		if (cumsum.length > 1) {
		    var tot = cumsum[cumsum.length-1][1];
		    var p = 0.01;

		    for (var t = 0; t < cumsum.length; t++)
			if (cumsum[t][1] >= p*tot)
			    break;
		    var tmin = cumsum[t][0];

		    for (var t = cumsum.length-1; t >= 0; t--)
			if (cumsum[t][1] < (1-p)*tot)
			    break;
		    var tmax = cumsum[t][0];
		} else {
		    var tmin = d3.min(times);
		    var tmax = d3.max(times);
		}
		var tlim = [tmin, tmax];

		var clim = [d3.min(counts), d3.max(counts)];

	        d3_line('div#hists', times, counts, sport, tlim);

		if (athlete != "") {
		    		    
		    var plot = d3.select('svg#' + sport);
		    
		    var height = plot.attr('height');
		    var width = plot.attr('width');

		    var x = d3.scale.linear().domain(tlim).range([0, width]);
		    var y = d3.scale.linear().domain(clim).range([height, 0]);

		    var rank = hists[sport+'_rank'];
		    var total = hists[sport+'_athletes'];
		    var percent = Math.round(rank/total*100);

		    var tooltip = d3.select("body").
			append("div").
			style("position", "absolute").
			style("z-index", "10").
			style("visibility", "hidden").
			style("color", "darkgreen").
			text(rank + "/" + total + " (" + percent + "%)");

		    plot.append('svg:circle').
			attr("cx", x(athlete_bin[0])).
			attr("cy", y(athlete_bin[1])).
			attr("r", 3).
			attr("fill", "darkgreen").
			on("mouseover", function(){return tooltip.style("visibility", "visible");}).
			on("mousemove", function(){return tooltip.style("top", (d3.event.pageY+10)+"px").style("left",(d3.event.pageX+10)+"px");}).
			on("mouseout", function(){return tooltip.style("visibility", "hidden");});
			//on('mouseover', function(d) { d3.select(this).attr('r',5); }).
			//on('mouseout', function(d) { d3.select(this).attr('r',3); });

		    plot.append('svg:line').
			attr("x1", x(athlete_bin[0])).
			attr("y1", y(0)).
			attr("x2", x(athlete_bin[0])).
			attr("y2", y(athlete_bin[1])).
			attr("stroke", "darkgreen").
			attr("stroke-width", 2).
			attr("stroke-dasharray", "5, 3");
		}
	     });
	  }
      });
}

function print_times_table(times, table, fields) {
    $(table).empty();
    $(table).append('<thead>');
    $(table).append('<tbody>');
    $(table).find('thead').append('<tr>');
    $.each(fields, function(f, field) {
	    $(table).find('thead tr').append('<th>' + f + '</th>');
	});
    $.each(times, function(g, time) {
	    row = "<tr>";
	    $.each(fields, function(f, field) {
		    row += "<td>" + time[field] + "</td>";
		});
	    row += "</tr>";
	    $(table).find('tbody').append(row);
	}); 

    //$(table).tablesorter();
}

function d3_line(placeholder, xdata, ydata, title, xlim, ylim) {
    var width = 150;
    var height = 75;

    if (!xlim)
	xlim = [d3.min(xdata), d3.max(xdata)];
    if (!ylim)
	ylim = [d3.min(ydata), d3.max(ydata)];

    var data = [];
    var i = 0;
    $.each(xdata, function(i, xi) {
	    data[i] = [xi, ydata[i]];
	});

    var x = d3.scale.linear().domain(xlim).range([0, width]);
    var y = d3.scale.linear().domain(ylim).range([height, 0]);

    d3.select(placeholder + ' svg#' + title).remove();

    var plot = d3.select(placeholder).
	    append('svg:svg').
	    attr('width',width).
	    attr('height',height).
	    attr('class', 'hist').
	    attr('id', title);

    plot.selectAll('path.line').
	data([data]).
        enter().
        append('svg:path').
        attr("d", d3.svg.line().
             x(function (d,i) { return x(d[0]) }).
             y(function (d,i) { return y(d[1]) }));

    /*
    plot.selectAll('.point').
	data([data]).
	enter().
	append('svg:circle').
	attr('r','1').
	attr('cx', function (d,i) { return x(d)}).
	attr('cy', function (d,i) { return y(d)}).
	on('mouseover', function(d) { console.log('on')}).
	on('mouseout',  function(d) { console.log('off')});
    */

    /*
    plot.selectAll("line").
	data(x.ticks(5)).
	enter().append("svg:line").
	attr("x1", x).
	attr("y1", 0).
	attr("x2", x).
	attr("y2", height).
	attr("stroke", "lightgray").
	attr("class", "xTicks");
    */

    /*
    plot.selectAll("text.rule").
	data(x.ticks(4)).
	enter().append("svg:text").
	attr("class", "rule").
	//attr("transform", "rotate(-45)").
	attr("x", x).
	attr("y", height).
	attr("dy", 0).
	attr("text-anchor", "middle").
	text(function(s) { 
		var h = String('00' + Math.floor(s / 3600)).slice(-2);
		var m = String('00' + Math.floor((s % 3600) / 60)).slice(-2);
		var s = String('00' + Math.ceil((s % 3600) % 60)).slice(-2);
		return h + ":" + m + ":" + s;
		    });
    */
    plot.append('svg:text').
	attr('x', width/2).
	attr('y', 10).
	text(title);

}

function histogram(placeholder, x, dx, xmin, xmax) {
    var counts = {};		  
    $.each(x, function(i, xi) {
	    var bin = Math.round(xi/dx)*dx;
	    if (xmax)
		bin = Math.min(xmax, bin);
	    if (xmin)
		bin = Math.max(xmin, bin);

	    if (bin in counts)
		counts[bin]++;
	    else
		counts[bin] = 1;
	});

    var data = [];
    $.each(counts, function(k, v) {
	    data.push([k,v]);
	});

    data.sort(function (a,b) {return a[0]-b[0]});
    var x = [];
    var y = [];
    $.each(data, function(i, d) {
	    x.push(parseInt(d[0]));
	    y.push(parseInt(d[1]));
	});

    if (!xmin)
	xmin = d3.min(x);
    if (!xmax)
	xmax = d3.max(x);

    d3_line(placeholder, x, y, [xmin,xmax]);
}

function set_event(name, ymd) {
    var year = ymd.substring(0,4);
    $('input#event').val(name + " " + year);
    update_races();
}

function print_events_table(events, table, fields) {
    $(table).empty();
    $(table).append('<thead>');
    $(table).append('<tbody>');
    $(table).find('thead').append('<tr>');
    $.each(fields, function(f, field) {
	    $(table).find('thead tr').append('<th>' + f + '</th>');
	});
    $.each(events, function(g, event) {
	    row = "<tr>";
	    $.each(fields, function(f, field) {
		    row += "<td>";
		    if (field == 'name')
			row += '<a href=# onclick=\'set_event("' + event['name'] + '","' + event['ymd'] +'")\'>' + event[field] + '</a>';
		    else
			row += event[field];
		    row += "</td>";
		});
	    row += "</tr>";
	    $(table).find('tbody').append(row);
	}); 

    //$(table).tablesorter();
}
