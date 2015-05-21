<?php 
error_reporting(E_ALL);
ini_set("display_errors", 1);
ini_set('memory_limit', '-1');

require_once "wifiPath.php";
require_once "config.php";

setScale(1*60); //1 min / s
$start = strtotime("March 30, 2015 10:00 am");
$end = strtotime("April 7, 2015 5:00 pm");



?>
<html>
	<head>
		<script src="https://code.jquery.com/jquery-1.10.2.js"></script>
		<script type="text/javascript" src="http://d3js.org/d3.v3.min.js" charset="utf-8"></script>
		<script type="text/javascript">
			$("document").ready(function(){
				
				//D3 vars
				var svg = d3.select("#svg2");
				
				var wifi = new Array();
				wifi[0] = d3.select("#floorG-ani");
				wifi[1] = d3.select("#floor1-ani");
				wifi[2] = d3.select("#floor2-ani");
				wifi[3] = d3.select("#floor3-ani");
				
				//Time vars
				var startTime = getScaledTime(<?php print $start * 1000;?>);
				var currentTime = getScaledTime(<?php print $start * 1000;?>);
				var endTime = getScaledTime(<?php print $end * 1000;?>);
				var start = <?php print $start;?>;
				var end = <?php print $end ?>;
				var intv = 10;

				var ajaxIntv = 1*60*60 //Set up a new load for every 1 hour of data
				
				//Setup AJAX
				for(var t = start; t <= end; t+=ajaxIntv)
				{
					$.getJSON("wifiPathAjax.php",
					{
						action: "clients",
						start: t,
						end: t + ajaxIntv
					}).done(function(data) {
						
						console.log(t + "?=" + start);
						if(t == start) //We can start the animation
						{
							startTick();

							dispSeq(data);
						}
						else
						{
							waitForSeq(data, getScaledTime(t*1000));
						}
					});
				}
				
				/* Animation functions */
				//Start the clock
				function startTick()
				{
					var timeTick = setInterval(
					function tick()
					{
						currentTime += intv;
						$('#time').text(timeConverter(getRealTime(currentTime)));
						if(currentTime >= endTime)
						{
							clearInterval(timeTick);
						}
						
					}
					,intv);
				}
				//Wait until the right time for animating
				function waitForSeq(data, tstart)
				{
					var seqTick = setInterval(function()
					{
						if(currentTime >= tstart)
						{
							dispSeq(data);
							alert(tstart);
							clearInterval(seqTick);
						}
					},intv);
				}
				//Animate a segment
				function dispSeq(data)
				{
					var nClients = data.clients.length;
					//Start by setting up 
					var d3Clients = [];
					var color;
					for(var c = 0; c < nClients; c ++)
					{
						var floor = data.clients[c].data[0].floor2;
						d3Clients[c] = wifi[floor].append('circle').attr('cx',0).attr('cy',0).attr('r',2);
						color = getColor(data.clients[c].cma, data.clients[c].cmnh);
						d3Clients[c].style('fill',color);
						var dlength = data.clients[c].data.length;
						
						for(var d = 0; d < dlength; d++)
						{
							//Change floors
							if(data.clients.data[d].floor2 != floor)
							{
								d3Clients[c].remove();
								floor = data.clients.data[d].floor2;
								d3Clients[c] = wifi[floor].append('circle').attr('cx',0).attr('cy',0).attr('r',2);
							}
							
							
						}
					}
				}
				/* get the dot color */
				function getColor(cma,cmnh)
				{
					if(cma && cmnh)
					{
						return "Purple";
					}
					else if (cma)
					{
						return "Purple";
					}
					else if(cmnh)
					{
						return "Purple";
					}
					else
					{
						return "Purple";
					}
				
				}

				
				
				
			
			});
			

			/* Time functions */
			function strtotime(string)
			{
				return Math.round(new Date(string).getTime()/1000);
			}
			function getRealTime(time)
			{
				var scale = <?php print unscaleTime(1); ?> * 1000;
				return time * scale;
			}
			
			function getScaledTime(time)
			{
				var scale = <?php print scaleTime(1); ?> / 1000;
				return time * scale;
			}
			/* Date functions */
			function timeConverter(timestamp){
			var a = new Date(timestamp);
		    var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
		    var year = a.getFullYear();
		    var month = months[a.getMonth()];
		    var date = a.getDate();
		    var hour = a.getHours();
		    var min = a.getMinutes();
		    var sec = a.getSeconds();
			
			
			
			
		    var time = dow(a) +", " + month + ' ' + date + ' ' + year + ' ' + padTime(hour) + ':' + padTime(min) + ':' + padTime(sec) ;
		    return time;
		}
		
		function padTime(value)
		{
			if(value >= 10)
			{
				return value;
			}
			if(value >= 1)
			{
				return '0' + value;
			}
			return '00';
		}
		
		function dow(date)
		{
			var weekday = new Array(7);
			weekday[0]=  "Sunday";
			weekday[1] = "Monday";
			weekday[2] = "Tuesday";
			weekday[3] = "Wednesday";
			weekday[4] = "Thursday";
			weekday[5] = "Friday";
			weekday[6] = "Saturday";

			var n = weekday[date.getDay()];
			return n;
		}
			
		</script>
	</head>
	<body>
	<h3 id='time'></h3>
	<div id='controls'>
		<button id='up'>Up</button>
		<button id='down'>Down</button>
	</div>
	<div id ='svgHolder'>
	<?php echo file_get_contents('map.svg'); ?>
	</div>
	</body>
</html>