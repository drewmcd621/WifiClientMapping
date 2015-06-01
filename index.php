<?php 
error_reporting(E_ALL);
ini_set("display_errors", 1);
ini_set('memory_limit', '-1');

require_once "control.php";



?>
<html>
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1">
	
		<script src="https://code.jquery.com/jquery-1.10.2.js"></script>
		<script type="text/javascript" src="http://d3js.org/d3.v3.min.js" charset="utf-8"></script>
		
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
		<link rel="stylesheet" href="resources/css/bootstrap-datepicker.min.css">
		

		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
		<script type="text/javascript" src="resources/js/bootstrap-datepicker.min.js"></script>
		
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
				//JS Time
				var startTime; 
				var currentTime; 
				var endTime; 
				//Unix Time
				var start; 
				var end;
				//Scale
				var scale = 1;
				//Intervals
				var intv = 10;
				var ajaxIntv = 1*60*60 //Set up a new load for every 1 hour of data
				
				$("#startBtn").click(function()
				{
					var lStart = strtotime($("#startTime").val()) + 9*60*60*1000;
					var lEnd = strtotime($("#endTime").val()) + 60*60*21*1000;
					console.log(lStart + " to " + lEnd);
					
					mainStart(lStart,lEnd);
					$("#control-container").hide();
				});
				
				
				/* main */
				displayFloors();
				
				function mainStart(startT, endT)
				{	
					setTimeVars(startT, endT)
					startAnimation(start, end);
				}
				
				
				
				function setTimeVars(startT, endT)
				{
					//Time vars
					//Scale
					 scale = (endT - startT) / (15 * 60 * 1000)
					//JS Time
					 startTime = getScaledTime(startT);
					 currentTime = getScaledTime(startT);
					 endTime = getScaledTime(endT);
					//Unix Time
					 start = startT/1000;
					 end = endT/1000;
					 
					 console.log(startTime + " to " + endTime);

				}
				
				
				//Setup AJAX
				function startAnimation(start, end)
				{
					for(var t = start; t <= end  ; t+=ajaxIntv)
					{
						$.getJSON("wifiPathAjax.php",
						{
							action: "clients",
							start: t,
							end: t + ajaxIntv * 1.5 //For a little overlap in the animation to make it transition smoother
						}).done(function(data) {
							
							if(data.start == start) //We can start the animation
							{
								startTick();

								dispSeq(data);
							}
							else  //Wait until it's time to run the animation
							{
								waitForSeq(data, getScaledTime(data.start*1000));
							}
						});
					}
				}
				
				/* Animation functions */
				//Start the clock
				function startTick()
				{
					var timeTick = setInterval(
					function tick()
					{
						currentTime += intv;
						svg.select('#Time').text(timeConverter(getRealTime(currentTime)));
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
							clearInterval(seqTick);
						}
					},intv);
				}
				//Animate a segment
				function dispSeq(data)
				{
					//Remove old clients
					var ageLimit = 60*60; //1 hr of inactivity
					var old = d3.selectAll(".client").filter(function(d,i){
						if(data.start - d3.select(this).attr("age") >= ageLimit) return true; 
						return false;
					});
					console.log("removing " + old[0].length + " clients");
					old.style('opacity',0);
					
					var nClients = data.clients.length;
					//Start by setting up 
					var d3Clients = [];
					var color;
					//console.log(data);
					for(var c = 0; c < nClients; c ++)
					{
						var floor = data.clients[c].data[0].floor2;

						if(d3.select("#client" + data.clients[c].id).empty()) //If the client does not exist create it
						{
							color = getColor(data.clients[c].cma, data.clients[c].cmnh);
							wifi[floor].append('circle').attr("id", "client" + data.clients[c].id ).attr('cx',0).attr('cy',0).attr('r',5).attr('floor',floor).attr('class','client').attr('cma',data.clients[c].cma).attr('cmnh', data.clients[c].cmnh).style('fill',color);
						}
						else //otherwise update the museums and recolor it.
						{
							var cma = parseFloat(d3.select("#client" + data.clients[c].id).attr('cma')) + data.clients[c].cma;
							var cmnh = parseFloat(d3.select("#client" + data.clients[c].id).attr('cmnh')) + data.clients[c].cmnh;
							color = getColor(cma, cmnh);
							d3.select("#client" + data.clients[c].id).style('fill',color).attr('cma',cma).attr('cmnh', cmnh).style("opacity",1);
						}
						
						
						var dlength = data.clients[c].data.length;
						
						for(var d = 0; d < dlength; d++)
						{



							d3.select("#client" + data.clients[c].id).transition().delay(getScaledTime(data.clients[c].data[d].delay * 1000)).duration(getScaledTime(data.clients[c].data[d].duration * 1000)).ease('linear').attrTween('transform',moveClient(data.clients[c].data[d].route, data.clients[c].data[d].rev, data.clients[c].data[d].floor1, data.clients[c].data[d].floor2)).attr('path',data.clients[c].data[d].route).attr('age',parseFloat(data.start) + parseFloat(data.clients[c].data[d].delay) );							
						}
					}
				}
				
				/* Move the client across floors */
				function moveClientFloor(clientID, newFloor)
				{
						var client = '#' + clientID;
						d3.select(client).attr('floor',newFloor);
						var tempClient = d3.select(client).remove();
						try
						{
							wifi[newFloor].append(function() { return tempClient.node(); });
						}
						catch(e)
						{
							console.log(e);
						}

				
				}
				
				/* get the dot color */
				function getColor(cma,cmnh)
				{
					if(cma && cmnh)
					{
						return "rgba(255,0,255,0.5)";
					}
					else if (cma)
					{
						return "rgba(255,0,0,0.5)";
					}
					else if(cmnh)
					{
						return "rgba(0,0,255,0.5)";
					}
					else
					{
						return "rgba(255,151,0,0.5)";
					}
				
				}
				//colorSky();
				function colorSky()
				{

					var csHrs = 1;
					var csIntv = getScaledTime(csHrs * 60 * 60 * 1000);
					console.log(csIntv);
					var sky = setInterval(function()
					{
							var d = new Date(getRealTime(currentTime));
							var h = d.getHours();
							var color;
							
							if(h + csHrs <= 6)//Dawn
							{
								color = "rgb(16,59,97)";
							}
							else if(h + csHrs <= 8)//Sunrise
							{
								color = "rgb(244,210,129)";
							}
							else if(h + csHrs <= 12)//Morning
							{
								color = "rgb(247,249,119)";
							}
							else if(h + csHrs <= 18)//Afternoon
							{
								color = "rgb(249,251,48)";
							}
							else if(h + csHrs <= 20)//Sunset
							{
								color = "rgb(244,210,129)";
							}
							else //Night
							{
								color = "rgb(16,59,97)";
							}
							//console.log(color);
							d3.select("#svgHolder").transition().duration(csIntv).style('background-color',color);
							
					},csIntv);
					
				}
				
				
				//Move the client
				function moveClient(path, rev, ffrom, fto)
				{
				
					var nPath = svg.select('path#' + path).node();
					
					if(nPath === null)
					{
						console.log('No Path Found: ' + path);
						return;
					}
					
					var l = nPath.getTotalLength();

					
					
					return function(d, i, a) {
					
						d3.select(this).attr('path',path);
						d3.select(this).attr('reverse',rev);
						
						var id = d3.select(this).attr("id");

						var cFlr = d3.select(this).attr('floor'); //current floor
						
						var dFloor;  //change in floors
						if(rev)
						{
							dFloor = fto - ffrom;
						}
						else
						{
							dFloor = ffrom - fto;
						}
						
						//if(cFlr != ffrom) //If the client isn't on the starting floor
						//{
							moveClientFloor(id, ffrom);
						//}
						d3.select(this).attr('aFloor',ffrom);
						d3.select(this).attr('bFloor',fto);
						var change = true;
						//d = datum, i = index, a = current attribute
						return function(t) {
						//t = time (0 - 1)
							var pos;
							var time;
							
							if(rev)
							{
								time = (1-t);
							}
							else
							{
								time = t;
							}
							pos = l*time;
							
							var p = nPath.getPointAtLength(pos);
							var x = p.x;
							var y = p.y;
							
							d3.select('#' + id).attr('time',t);
							if(time >= 0.5)
							{
								//y += -70*(dFloor);
							}							
							if(t >= 0.5)
							{
								if(ffrom != fto && change) //ffrom != fto && 
								{
									change = false;
									moveClientFloor(id, fto); //Change floors 1/2 way
								}
							}
							if(time == 1 || time == 0)  //Improve visuals of multiple people
							{
								x = fuzz(x);
								y = fuzz(y);
							}
							
							
							
							return "translate(" + x  + "," + y  + ")";//Move marker
						}
					}
				
				}
				
				function fuzz(val)
				{
					var fVal = 7
					return val - fVal + 2 * fVal * Math.random(); // +/- 7 px
					
				}

				/* Mouse over area */
				var floors = ["G","1","2","3"];
				for(var i = 0; i < 4; i ++)
				{
					svg.selectAll('#floor' + floors[i] + '-areas').selectAll('g').style('opacity',0);
					svg.selectAll('#floor' + floors[i] + '-areas').selectAll('g').on('mouseover',function(d,i)
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
					});
				}

				
		
				
				
				
				
				
				/* D3 client icons */
				dispClientCounts();
				function dispClientCounts()
				{
					var ccIntv = getScaledTime( 10 * 60 * 1000);
					var iIntv = 0;
					//Both
					var color = getColor(1,1);
					var both = 	d3.select("#cBoth")
					both.style('fill',color).append("svg:title").text("Visitors of both museums");
					var bothText = d3.select("#colorIndicators").append("text");
					bothText.attr("x",both.attr("cx")).attr("y",both.attr("cy")).attr("title",both.attr("title")).attr("text-anchor","middle").attr("dominant-baseline", "central").attr('fill','black').attr("font-family", "sans-serif").attr("font-size", "14px").text('0').attr('class','counterText').attr("stroke-width",0);
					bothText.append("svg:title").text("Visitors of both museums");
					
					//CMA
					color = getColor(1,0);
					var cma = d3.select("#cCMA")
					cma.style('fill',color).append("svg:title").text("Visitors of CMOA only");
					var cmaText = d3.select("#colorIndicators").append("text");
					cmaText.attr("x",cma.attr("cx")).attr("y",cma.attr("cy") ).attr("title",cma.attr("title")).attr("text-anchor","middle").attr("dominant-baseline", "central").attr('fill','black').attr("font-family", "sans-serif").attr("font-size", "14px").text('0').attr('class','counterText').attr("stroke-width",0);
					cmaText.append("svg:title").text("Visitors of CMOA only");
					
					//CMNH
					color = getColor(0,1);
					var cmnh = d3.select("#cCMNH")
					cmnh.style('fill',color).append("svg:title").text("Visitors of CMNH only");
					var cmnhText = d3.select("#colorIndicators").append("text");
					cmnhText.attr("x",cmnh.attr("cx")).attr("y",cmnh.attr("cy") ).attr("title",cmnh.attr("title")).attr("text-anchor","middle").attr("dominant-baseline", "central").attr('fill','white').attr("font-family", "sans-serif").attr("font-size", "14px").text('0').attr('class','counterText').attr("stroke-width",0);
					cmnhText.append("svg:title").text("Visitors of CMNH only");
					
					//Neither
					color = getColor(0,0);
					var neither = d3.select("#cNeither")
					neither.style('fill',color).append("svg:title").text("Visitors of neither museum");
					var neitherText = d3.select("#colorIndicators").append("text");
					neitherText.attr("x",neither.attr("cx")).attr("y",neither.attr("cy") ).attr("text-anchor","middle").attr("dominant-baseline", "central").attr('fill','black').attr("font-family", "sans-serif").attr("font-size", "14px").text('0').attr('class','counterText').attr("stroke-width",0);
					neitherText.append("svg:title").text("Visitors of neither museum");
					
					var cliCount = setInterval(function ()
					{

						var cBoth = d3.selectAll(".client").filter(function(d,i){
						if(d3.select(this).attr("cma") >= 1 && d3.select(this).attr("cmnh") >= 1) return true; 
						return false;
						});
						bothText.text(cBoth[0].length );
						
						var cCMA = d3.selectAll(".client").filter(function(d,i){
						if(d3.select(this).attr("cma") >= 1 && d3.select(this).attr("cmnh") == 0) return true; 
						return false;
						});
						cmaText.text(cCMA[0].length);
						
						var cCMNH = d3.selectAll(".client").filter(function(d,i){
						if(d3.select(this).attr("cma") == 0 && d3.select(this).attr("cmnh") >= 1) return true; 
						return false;
						});
						cmnhText.text(cCMNH[0].length);
						
						var cNei = d3.selectAll(".client").filter(function(d,i){
						if(d3.select(this).attr("cma") == 0 && d3.select(this).attr("cmnh") == 0) return true; 
						return false;
						});
						neitherText.text(cNei[0].length);
						iIntv = ccIntv;
					
					
					},iIntv);
					
					
					
					
				}

				
				
				/* Displaying the floors */
						
				
				function displayFloors()
				{
					
					var floors = ['floorG','floor1','floor2','floor3']
					for(var i = 0; i < 4; i++)
					{
						var floor = d3.select('#' + floors[i]).remove();
						svg.append('g').attr('id',floors[i] + '-move');
						
						d3.select('#' + floors[i] + '-move').append(function() { return floor.node(); });
						
						isometric('#' + floors[i]);
						
						var y = getFloorDis(i);
						//console.log(i + ' = ' + y);
						 d3.select('#' + floors[i] + '-move').attr('transform', ' translate(-70,' + y + ')');
					}
				}
				
				
				function getFloorDis(floor)
				{
					return 90*(3 - floor) - 120;
				}
				
				function changeFloor(flr, dur)
				{
					var floors = ['#floorG-move','#floor1-move','#floor2-move','#floor3-move']
					for(var i = 0; i <= flr; i ++)
					{
						var f = d3.select(floors[i]);
						var y = 90*((flr)-i) - 120;
						f.attr('display',null);
						f.transition().duration(dur).attr('transform', ' translate(-70,' + y + ')').style('opacity',1);
						
						
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
				
				function isometric(select)
				{
					var obj = d3.select(select);
					
					var bbox = obj.node().getBBox();
					
					var midX = bbox.width / 2.0;
					var midY = bbox.height / 2.0;
					
					var t = obj.attr('transform');
					//.transition().duration(dur)
					// matrix(0.707 0.409 -0.707 0.409 0 -0.816)
					
					
					obj.attr("transform", t + "translate(" + midX + "," + midY +")  matrix(0.9063077870366499, 0.24421318475056708, -0.42261826174069944, 0.5237168647772704, 0, -0.8161375900801602) translate(" + -1*midX + "," + -1*midY +")");
					
					
				
				}
				
				
				var floor = 3;
				$('#upBtn').click(function()
				{
					floor++;
					if(floor > 3) floor = 3;
					console.log(floor);
					changeFloor(floor,1000);
				});
				
				$('#downBtn').click(function()
				{
					floor--;
					if(floor < 0) floor = 0;
					console.log(floor);
					changeFloor(floor,1000);
				});
				
				svg.selectAll('#floorBtns').selectAll('g').on('mousedown',function(d,i)
				{
					d3.select(this).selectAll('circle').attr('fill','silver');
				}).on('mouseup',function(d,i)
				{
					d3.select(this).selectAll('circle').attr('fill','white');
				})
				
				/*.on('mouseout',function(d,i)
				{
					d3.select(this).attr('fill',1);
				}).on('mouseleave',function(d,i)
				{
					d3.select(this).attr('fill',1);
				});
				*/
			
						//Datepicker
		$('#control-container .input-daterange').datepicker({
			startDate: "<?php print getStart(); ?>",
			endDate: "<?php print getEnd(); ?>",
			todayHighlight: true
		});

							var aspect = 1366 / 900,
				chart = $("#svg2");
			$(window).on("resize", function() {
				var targetWidth = chart.parent().width();
				chart.attr("width", targetWidth);
				chart.attr("height", targetWidth / aspect);
			});
			

			
			

			/* Time functions */
			function strtotime(string)
			{
				return new Date(string).getTime();
			}
			function getRealTime(time)
			{
				return time * scale;
			}
			
			function getScaledTime(time)
			{
				return time  / scale;
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
		

		}); //End jQ main
			
		</script>
		<style>
			#svgHolder 
			{
				position:absolute;
				top:1%;
				left:10%;
				right:10%;
				bottom:1%;
			}
			#time
			{

				
			}
			#controls
			{

			}
		</style>
	</head>
	<body>
	<!-- Date picker -->
		<div id="control-container" class="span5 col-md-5 ">
			<div class="input-daterange input-group" id="datepicker">
				<input id="startTime" type="text" class="input-sm form-control" name="start" />
				<span class="input-group-addon">to</span>
				<input id="endTime" type="text" class="input-sm form-control" name="end" />
			</div>
			<div class="btn-group">
				<button  id="startBtn" type="button" class="btn btn-default">
					<span class="glyphicon glyphicon-play" aria-hidden="true"></span> Play
				</button>
			</div>
		</div>	
		<div id ='svgHolder' class="">
			<?php echo file_get_contents('map.svg'); ?>
		</div>
	</body>
</html>