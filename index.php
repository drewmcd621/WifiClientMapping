<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);


require_once "wifiPath.php";
require_once "wifiAPs.php";

$table = getData();
$paths = setupPaths($table);
$aps =  getAPs();

$start = getStart($table);
	
	


?>
<!DOCTYPE html>
<html>
<head>
	<script type="text/javascript" src="http://d3js.org/d3.v3.min.js" charset="utf-8"></script>
	<script type="text/javascript">
		//d3.select("body").append("svg").attr("width", 50).attr("height", 50).append("circle").attr("cx", 25).attr("cy", 25).attr("r", 25).style("fill", "purple");
		window.onload = function(){
		var svg = d3.select("#main_svg");
		var wifi = d3.select("#wifi");
		
		var lineFunction = d3.svg.line().x(function(d) { return d.x; }).y(function(d) { return d.y; }).interpolate('linear');
		
		function moveClient(path, timeData)
		{
			var l = path.getTotalLength();
			return function(d, i, a) {
				//d = datum, i = index, a = current attribute
				//var r = (d3.select(this).attr("r")) /2;
				//console.log(r);
				return function(t) {
				//t = time (0 - 1)

					var p = path.getPointAtLength(t * l);
					return "translate(" + (p.x)  + "," + (p.y)  + ")";//Move marker
				}
			}
		
		}
		
		
		
		<?php //addPoints($table); 
		addAPs($aps);
		addPaths($paths, $start);
		?>
		}
	</script>
</head>
	<body>
		<svg id="main_svg" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns="http://www.w3.org/2000/svg" height="600mm" viewBox="0 0 1500 1250" width="825mm" version="1.1" xmlns:cc="http://creativecommons.org/ns#" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:dc="http://purl.org/dc/elements/1.1/">
		<g transform="scale(2,2)">
		
		 <g id="floor2" transform="translate(0 0)">
			<g 	transform="translate(0,600)">
			<g id='wifi' transform="scale(1,-1)">
			
			</g>
		</g>
		  <g id="layer7" display="none">
		   <path id="floor2-base" d="m525.11 241.33v-125.51h-127.08c0.37958 58.428 0.0625 65.326-0.19087 124.8zm-324.67 223.05v34.947h-41.429v53.992h66.999c0 34.074 22.06 47.393 43.749 47.035 21.689-0.3578 43.19-16.433 43.19-47.035l64.896-0.4046v-52.164h-42.581v-35.444h89.763c-0.0275-3.5962 0-53.973 0-53.973h-27.332c-0.0674-34.404-0.0952-98.096-0.0953-119.04 109.24 1.2022 234.89 0.52506 344.14-0.0997l5.7142-240.57h-198.09l-30.453 11.429-0.147-11.429-30.909 11.429 0.309-11.429-30.6 11.429v-11.429l-30.601 11.429v-11.429h-42.632v-24.915h-88.534v27.308h-174.28v-27.308h-86.54v24.118l-32.795 0.282s0.1168 0.1168-0.2819 60.735l16.726 0.3988v130.41h-16.75v51.047h34.297v119.64h-35.095v51.047l56.63-0.0001c0 10.438 6.2585 17.686 16.338 17.714 10.079 0.028 18.358-7.2473 18.358-17.714z" fill-rule="evenodd" display="inline" fill="#d3d3d3"/>
		   <path id="floor2-cma" d="m202.7 291.19h538.78l5.7142-240.53h-198.09l-30.453 11.429-0.147-11.429-30.909 11.429 0.309-11.429-30.6 11.429v-11.429l-30.601 11.429v-11.429h-49.239v62.887h-75.141v-60.494h-159.02v189.07h59.393zm322.16-50.819v-125.51h-127.08c0.37958 58.428 0.0625 65.326-0.19087 124.8z" fill-rule="evenodd" display="inline" fill="#ff8719"/>
		   <path id="floor2-mnh" d="m397.69 411.33h27.332s-0.0275 50.377 0 53.973h-89.763v35.444h42.581v52.164l-64.896 0.4046c0 30.602-21.5 46.677-43.19 47.035-21.689 0.3579-43.749-12.961-43.749-47.035h-66.999v-53.992h41.429v-86.465l29.017-0.00026 0.00043-44.249h80.357v15.357h36.429v11.786h51.453z" display="inline" fill="#ffb619"/>
		   <path id="floor2-music" display="inline" fill="#15a4b8" d="m143.56 54.028h-22.045v-27.308h-86.54v24.118l-32.973 0.282-0.2819 60.735 16.904 0.3988v130.84h124.98z"/>
		   <path id="floor2-lib" d="m200.44 462.84h-108.04c0 10.466-8.279 17.741-18.358 17.714-10.079-0.028-16.338-7.2759-16.338-17.714l-56.63 0.0001v-49.457h35.095v-119.64h-34.297v-52.591h201.21v49.542h26.328l0.0397 122.26h-29.015z" display="inline" fill="#a9a9a9"/>
		   <path id="path4235-2-3-8" d="m596.88 78.715v-21.25" display="inline" stroke="#475569" stroke-width="2" fill="none"/>

		  
		  </g>
		  <g id="layer3">
		   <path id="floor2-outline" d="m524.73 239.83-0.00022-125.51h-127.08c0.37958 58.428 0.0625 65.326-0.19087 124.8zm-324.67 223.05v34.947h-41.429v53.992h66.999c0 34.074 22.06 47.393 43.749 47.035 21.689-0.3578 43.19-16.433 43.19-47.035l64.896-0.4046v-52.164h-42.581v-35.444h89.763c-0.0275-3.5962 0-53.973 0-53.973h-27.332c-0.0674-34.404-0.0953-117.64-0.0953-117.64l344.14-0.00026v-242.07h-192.38l-30.453 11.429-0.147-11.429-30.6 11.429v-11.429l-30.6 11.429v-11.429l-30.601 11.429v-11.429h-42.632v-24.915h-88.534v27.308h-174.28v-27.308h-86.54v24.118l-33.095 0.282v61.133h16.744v130.41l-16.744-0.00005v51.047h34.291v119.64h-34.291v51.047l55.827-0.0001c0 10.438 6.2585 17.686 16.338 17.714 10.079 0.028 18.358-7.2473 18.358-17.714z" stroke="#475569" stroke-width="3" fill="none"/>
		  </g>
		  <g id="layer6" stroke="#475569" fill="none">
		   <g stroke-width="2">
			<path id="path4228" d="m526.43 292.03v-20.375"/>
			<path id="path4233" d="m525.88 260.03v-20.375"/>
			<path id="path4235" d="m566.36 290.9v-33.875"/>
			<path id="path4237" d="m567.08 271.28h6.7171"/>
			<path id="path4237-9" d="m587.38 271.28h121.63"/>
			<path id="path4235-6" d="m726.01 292.09v-44.375"/>
			<path id="path4237-4" d="m719.88 271.15h6.7501"/>
			<path id="path4237-9-8" d="m620.19 241.65 105.44-0.50001v-56.501"/>
			<path id="path4237-99" d="m607.88 241.65h-41.25v-32.5h41.375"/>
			<path id="path4235-2" d="m598.01 242.59v-11.125"/>
			<path id="path4235-2-2" d="m598.01 219.43v-21.512"/>
			<path id="path4237-7" d="m525.29 240.57h12.592"/>
			<path id="path4233-2" d="m536.96 241.5v-128.56"/>
			<path id="path4233-2-1" d="m550.76 241.5v-128.56"/>
			<path id="path4237-9-7" d="m618.55 209.18h76.325"/>
			<path id="path4235-2-3" d="m694.74 240.92v-11.125"/>
			<path id="path4235-2-5" d="m694.74 220.77v-23.146"/>
			<path id="path4237-9-85" d="m618.55 177.2h109.07"/>
			<path id="path4235-2-2-1" d="m597.86 187.6v-21.512"/>
			<path id="path4237-90" d="m598.04 177.02h11.313"/>
			<path id="path4235-2-5-4" d="m694.92 188.6v-23.146"/>
			<path id="path4235-2-5-4-2" d="m694.74 156.65v-23.146"/>
			<path id="path4237-8" d="m551.73 233.06h14.319"/>
			<path id="path4235-2-2-1-0" d="m597.81 155.78v-21.512"/>
			<path id="path4237-9-7-0" d="m618.76 145.05h76.325"/>
			<path id="path4237-99-1" d="m608.94 145.15h-41.25v-31.375h41.375"/>
			<path id="path4237-8-9" d="m551.46 177.02h14.319"/>
			<path id="path4235-5" d="m565.93 201.33v-47.875"/>
			<path id="path4237-9-7-0-2" d="m525.76 113.78h42.03"/>
			<path id="path4235-2-7" d="m597.88 124.28v-11.125"/>
			<path id="path4237-9-7-0-0" d="m618.75 113.21h107.44"/>
			<path id="path4235-5-8" d="m725.85 169.32v-64.316"/>
			<path id="path4237-4-1" d="m712.4 145.03h13.291"/>
			<path id="path4235-2-5-4-2-7" d="m694.27 124.52v-22.459"/>
			<path id="path4235-6-7" d="m725.76 93.232v-44.375"/>
			<path id="path4235-2-5-4-2-2" d="m693.76 94.476v-23.146"/>
			<path id="path4237-9-7-0-3" d="m579.34 83.153h115.58"/>
			<path id="path4237-8-9-1" d="m551.35 217.15h14.319"/>
			<path id="path4237-8-9-2" d="m550.85 135.9h16.194"/>
			<path id="path4235-2-7-5" d="m570.13 113.09v-11.125"/>
			<path id="path4841" d="m570.01 93.903v-10.75h-3.75v-32.5"/>
			<path id="path4237-8-9-2-2" d="m548.3 83.153h18.747"/>
			<path id="path4235-5-3" d="m549.3 83.934-0.00005-33.733"/>
		   </g>
		   <g stroke-width="3">
			<path id="path4235-2-2-1-0-9" d="m518.58 83.922v-21.512"/>
			<path id="path4235-2-2-1-0-9-6" d="m487.78 83.922v-21.512"/>
			<path id="path4235-2-2-1-0-9-5" d="m457.15 83.922v-21.512"/>
		   </g>
		   <path id="path4235-2-3-7" d="m693.76 64.402v-13.063" stroke-width="2"/>
		   <path id="path4235-2-2-1-0-9-5-1" d="m413.17 89.235v-39.387" stroke-width="3"/>
		   <path id="path4235-2-2-1-0-9-5-7" d="m412.88 114.03v-13.762" stroke-width="3"/>
		  </g>
		 
		 </g>
		 </g>
		</svg>

	</body>
</html>