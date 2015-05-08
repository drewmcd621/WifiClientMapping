<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);


require_once "wifiPath.php";
require_once "config.php";

setScale(1*60); //1 min / s
$start = strtotime("March 28, 2015 1:00 pm");
$end = strtotime("March 28, 2015 5:00 pm");
	


?>
<!DOCTYPE html>
<html>
<head>
	<script type="text/javascript" src="http://d3js.org/d3.v3.min.js" charset="utf-8"></script>
	<script type="text/javascript" src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
	<script type="text/javascript">
		//d3.select("body").append("svg").attr("width", 50).attr("height", 50).append("circle").attr("cx", 25).attr("cy", 25).attr("r", 25).style("fill", "purple");
		$( document ).ready(function(){
		var svg = d3.select("#svg2");
		var wifi = d3.select("#floor2-ani");
		
		
		var lineFunction = d3.svg.line().x(function(d) { return d.x; }).y(function(d) { return d.y; }).interpolate('linear');
	
		var currentTime = getScaledTime(<?php print $start * 1000;?>);
		var intv = 10;
		var endTime = getScaledTime(<?php print $end * 1000;?>);
	
		function start()
		{
			currentTime = getScaledTime(<?php print $start * 1000;?>);
			endTime = getScaledTime(<?php print $end * 1000;?>);
		
		}
		
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
		
		//isometric('#floor2',1000);
		displayFloors();
		
		function displayFloors()
		{
			
			var floors = ['floorG','floor1','floor2','floor3']
			for(var i = 0; i < 4; i++)
			{
				var floor = d3.select('#' + floors[i]).remove();
				svg.append('g').attr('id',floors[i] + '-move');
				
				d3.select('#' + floors[i] + '-move').append(function() { return floor.node(); });
				
				isometric('#' + floors[i]);
				
				var y = 70*(4-i) - 170;
				 d3.select('#' + floors[i] + '-move').attr('transform', ' translate(-75,' + y + ')');
			}
		}
		
		function changeFloor(flr, dur)
		{
			var floors = ['#floorG-move','#floor1-move','#floor2-move','#floor3-move']
			for(var i = 0; i <= flr; i ++)
			{
				var f = d3.select(floors[i]);
				var y = 70*((flr+1)-i) - 170;
				f.attr('display',null);
				f.transition().duration(dur).attr('transform', ' translate(-75,' + y + ')').style('opacity',1);
				
				
			}
			if(flr < 3)
			{
				for(var j = flr + 1; j <= 3; j++)
				{
					var f = d3.select(floors[i]);
					f.transition().duration(dur).style('opacity',0).each('end',function(){ f.attr('display','none');});
				}
			
			}
		}
		
		function isometric(select, dur)
		{
			var obj = d3.select(select);
			
			var bbox = obj.node().getBBox();
			
			var midX = bbox.width / 2.0;
			var midY = bbox.height / 2.0;
			
			var t = obj.attr('transform');
			//.transition().duration(dur)
			// matrix(0.707 0.409 -0.707 0.409 0 -0.816)
			
			
			obj.attr("transform", t + "translate(" + midX + "," + midY +")  matrix(0.6 0.4 -0.6 0.4 0 -0.5) translate(" + -1*midX + "," + -1*midY +")");
			
			
		
		}
		
		
		

		
		
		
		
		
		function moveClient(path, rev, dur)
		{
			if(path === null)
			{
				console.log('No Path Found');
				return;
			}
			var l = path.getTotalLength();
			
			
			
			return function(d, i, a) {
			
				d3.select(this).attr('speed',l/dur);
				//d = datum, i = index, a = current attribute
				return function(t) {
				//t = time (0 - 1)
					var pos;
					
					if(rev)
					{
						pos = (1-t) * l;
					}
					else
					{
						pos = t*l;
					}
					
					
					var p = path.getPointAtLength(pos);
					return "translate(" + (p.x)  + "," + (p.y)  + ")";//Move marker
				}
			}
		
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
		
	
		
		//Start PHP generated section		
		<?php 
		
		addAPs($start, $end);
		addPaths($start, $end);		
		?>
		//End PHP generated section
		
		wifi.selectAll(".client").on('click',function(d,i){
			if(d3.select(this).attr('r') <= 5)
			{
				d3.select(this).attr('r', 10 ).style('fill','rgba(255,20,20,0.5)');
			}
			else
			{
				d3.select(this).attr('r', 5 ).style('fill','rgba(151,0,255,0.3)');
			}
		});
		
		
		svg.selectAll('#floor2-areas').selectAll('g').style('opacity',0);
		
		svg.selectAll('#floor2-areas').selectAll('g').on('mouseover',function(d,i)
		{
			d3.select(this).style('opacity',1);
		}).on('mouseenter',function(d,i)
		{
			d3.select(this).style('opacity',1);
		}).on('mouseout',function(d,i)
		{
			d3.select(this).style('opacity',0);
		}).on('mouseleave',function(d,i)
		{
			d3.select(this).style('opacity',0);
		})
		;
		
		var floor = 3;
		$('#up').click(function()
		{
			floor++;
			if(floor > 3) floor = 3;
			console.log(floor);
			changeFloor(floor,1000);
		});
		
		$('#down').click(function()
		{
			floor--;
			if(floor < 0) floor = 0;
			console.log(floor);
			changeFloor(floor,1000);
		});
		
		
		
		
		wifi.selectAll(".AP").on('mouseover',function(d,i)
		{
				
		
		});
		
		
		
		});
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