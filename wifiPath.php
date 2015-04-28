<?php

require_once "sqlConfig.php";
	

function getStart()
{
$sql = getSql();
	
	$res = sqlsrv_query($sql, "SELECT TOP 1 tfrom FROM clientLocation where   1=1 order by tfrom"); //eventually we will filter by tfrom and tto
	if( $res === false) {
		die( print_r( sqlsrv_errors(), true) );
	}
	
	$table = array();
	while( $row = sqlsrv_fetch_array( $res, SQLSRV_FETCH_ASSOC) ) 
	{
		array_push($table, $row);
	}
	
	sqlsrv_free_stmt($res);
	
	
	return $table[0]['tfrom']->getTimestamp();

}
	
 function getPaths($start = 0, $end = 0)
 {
	$sql = getSql();
	
	$res = sqlsrv_query($sql, "SELECT  * FROM clientLocation where 1=1 and clientID in (select clientID from clientLocation where 1=1 group by clientID having count(id) > 5)  order by clientid, tfrom"); //eventually we will filter by tfrom and tto
	if( $res === false) {
		die( print_r( sqlsrv_errors(), true) );
	}
	
	$table = array();
	while( $row = sqlsrv_fetch_array( $res, SQLSRV_FETCH_ASSOC) ) 
	{
		array_push($table, $row);
	}
	
	sqlsrv_free_stmt($res);
	
	
	return setupPaths($table);
}


function setupPaths($table)
{
	
	$paths = array();
	$clientID = -1;
	$tpath = array();
	
	
	
	foreach($table as $row)
	{
	
		//Check if we are on a new path
		
		if($row['clientID'] != $clientID)
		{

			if($clientID >= 0)
			{
				$paths[$clientID] = $tpath;
				$tpath = array();

			}
			
			$clientID = $row['clientID'];
		}
		
		
		
		
		
		$point = array();
		$point['AP1'] = $row['AP1'];
		$point['AP2'] = $row['AP2'];
		$point['start'] = $row['tfrom']->getTimestamp();
		$point['end'] = $row['tto']->getTimestamp();
		
		//$startTime = min($startTime, $point['time']); //Get first timestamp
		
		array_push($tpath, $point);
		
		
	}
	
	$paths[$clientID] = $tpath;
	return $paths;
	
}

function getRoutes()
{
	$sql = getSql();
	
	$res = sqlsrv_query($sql, "SELECT  * FROM APRoutes where 1=1");
	if( $res === false) {
		die( print_r( sqlsrv_errors(), true) );
	}
	
	$table = array();
	while( $row = sqlsrv_fetch_array( $res, SQLSRV_FETCH_ASSOC) ) 
	{
	
		$rdata = array();	
		array_push($table, $row);
	}
	
	sqlsrv_free_stmt($res);
	
	return $table;

}



//var_dump($paths);

function addPaths($paths, $routes, $aps, $start=0)
{
	foreach($paths as $id=>$p)
	{
		print "////Routes for client #$id\n\n";
		
		$r = 5;
		$cx = 0;
		$cy = 0;
		
		
		//var_dump($p);
		
		$end = -1;
		
		print "var client$id = wifi.append('circle').attr('cx',$cx).attr('cy',$cy).attr('r',$r).style('fill','rgba(151,0,255,0.5)');\n\n";
		
			foreach($p as $sid=>$sp)
			{
				
				$route = null;
				$rev = 0;
				$ap1 = $sp['AP1'];
				$ap2 = $sp['AP2'];
				
				foreach($routes as $r) //Find the route
				{
					if($r['AP1'] == $ap1 && $r['AP2'] == $ap2)
					{
						$route = $r['pathID'];
						break;
					}
					else if($r['AP1'] == $ap2 && $r['AP2'] == $ap1) //Reversed mode
					{
						$route = $r['pathID'];
						$rev = 1;
						break;
					}
				}
				

					if($end == -1) $end = $sp['end'];
					$end = max($end, $sp['end']);
					
					
					
					$delay = ($sp['start'] - $start);
					$dur = ($sp['end'] - $sp['start']);
					
				if($route != null)
				{
					//print "client$id.transition().duration($delay).each('end', function(){d3.select(this).transition().duration($dur).ease('linear').attrTween('transform',moveClient(svg.select('path#$route').node(),$rev))});\n";
					print "client$id.transition().delay($delay).duration($dur).ease('linear').attrTween('transform',moveClient(svg.select('path#$route').node(),$rev));\n";
				}
				else
				{
					//Fall back if no route found
					$xpos = $aps[$ap2]['x'];
					$ypos = $aps[$ap2]['y'];
					//print "client$id.transition().delay($delay + $dur).duration(0).attr('transform','translate($xpos,$ypos)');\n";
				}
				
			}
		$delay = ($end - $start);
		print "client$id.transition().delay($delay).style('opacity',0)";
		
		print "\n////End Routes for client #$id\n\n";
	}
}
/*
function addPaths($paths, $starttime=0)
{
	foreach($paths as $id=>$p)
	{
		
		//Setup path
		$pathData = "var pathData" . $id . " = [ ";
		
		//setup timestamps
		$timeData = "var timeData = [";
		
		//Create circle at correct time.

		$r = 5;
		$cx = 0;
		$cy = 0;
		
		$circle = "var client$id = wifi.append('circle').attr('cx',$cx).attr('cy',$cy).attr('r',$r).style('fill','rgba(151,0,255,0.5)');\n\n";
		
		for($i = 0; $i < count($p); $i++)
		{
			$pt = $p[$i];
			$t = 0;
			if($i < count($p) - 1)
			{
				$ptn = $p[$i + 1];
				$t = $ptn['time'] - $pt['time']; //get time between points
			}
			
			$pathData .= "{ 'x' : " . $pt["x"] . ", 'y' : " . $pt["y"] . "},";
			
			$timeData .= "$t,";
		}
		
		$scale = 5000;
		$dur = ($p[count($p) - 1]['time'] -  $p[0]['time'])*(1000/$scale);
		
		
		$pathData = trim($pathData,',');
		$pathData .= "];\n\n";
		
		$timeData = trim($timeData,',');
		$timeData .= "];\n\n";
		
		print $pathData;
		
		print "var path$id  = wifi.append('path').attr('d', lineFunction(pathData$id)).attr('stroke', 'purple').attr('stroke-width',0).attr('fill','none');";
		
		print $circle;
		
		$delay = $p[0]['time'] - $starttime;
		
		$transistion = "client$id.transition().duration($delay).each('end',function(){ d3.select(this).transition().duration($dur).ease('linear').attrTween('transform',moveClient(path$id.node())).each('end',function() { d3.select(this).style('opacity',0)}) } ) ;\n\n";
		//$transistion = "client$id.transition().duration(1000).each('end',function(d, i) { \n $timeData this.transition().duration(1000).ease('linear').attrTween('transform',moveClient(path$id.node())) });\n\n";
		
		print $transistion;
		//print $timeData;
	
	}
}
*/

function addPoints($table)
{
	
	foreach($table as $row)
	{
	
	
		$cx = $row["X"] - 2.5;
		$cy = $row["Y"] - 2.5;
		$r = 5;
		print "wifi.append('circle').attr('cx',$cx).attr('cy',$cy).attr('r',$r).style('fill','rgba(255,0,255,0.2)');\n\n";
	}
}
?>