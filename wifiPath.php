<?php

require_once "sqlConfig.php";
require_once "config.php";
	

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
	
	$wStart = date('Y-m-d H:i:s',$start);
	$wEnd = date('Y-m-d H:i:s',$end);
	
	$res = sqlsrv_query($sql, "SELECT  * FROM clientLocation where 1=1 and clientID in (select clientID from clientLocation group by clientID having count(id) > 1) and tfrom >= '$wStart' and tto <= '$wEnd'  order by clientid, tfrom"); //eventually we will filter by tfrom and tto
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

function addPaths($start=0, $end=0)
{
	$routes = getRoutes();
	$aps = getAPs($start, $end);
	$paths = getPaths($start, $end);
	
	
	$sstart = scaleTime($start);
	$send =  scaleTime($start);
	
	print "var clients = new Array();\n\n\n";
	
	foreach($paths as $id=>$p) //Go through the clients
	{
		print "////Routes for client #$id\n\n";
		
		$r = 5;
		$cx = 0;
		$cy = 0;
		
		
		//var_dump($p);
		
		
		$iend = -1;
		
		//Create a circle for each client on each floor (makes changing floors easier)
		print "clients[$id] = new Array();\n\n";
		print "clients[$id][0] = wifi[0].append('circle').attr('cx',$cx).attr('cy',$cy).attr('r',$r).style('fill','rgba(151,0,255,0.5)').attr('id','client$id').attr('class','client').style('opacity',1);\n";
		print "clients[$id][1] = wifi[1].append('circle').attr('cx',$cx).attr('cy',$cy).attr('r',$r).style('fill','rgba(151,0,255,0.5)').attr('id','client$id').attr('class','client').style('opacity',1);\n";
		print "clients[$id][2]= wifi[2].append('circle').attr('cx',$cx).attr('cy',$cy).attr('r',$r).style('fill','rgba(151,0,255,0.5)').attr('id','client$id').attr('class','client').style('opacity',1);\n";
		print "clients[$id][3]= wifi[3].append('circle').attr('cx',$cx).attr('cy',$cy).attr('r',$r).style('fill','rgba(151,0,255,0.5)').attr('id','client$id').attr('class','client').style('opacity',1);\n\n";

		
		
			$floor = null;
			
			foreach($p as $sid=>$sp) //Go through the animation
			{
				
				$route = null;
				$rev = 0;
				$ap1 = $sp['AP1'];
				$ap2 = $sp['AP2'];
				$nFloor = null;

				
				foreach($routes as $r) //Find the route
				{
					if($r['AP1'] == $ap1 && $r['AP2'] == $ap2)
					{
						$route = $r['pathID'];
						$nFloor = $r['floor'];
						break;
					}
					else if($r['AP1'] == $ap2 && $r['AP2'] == $ap1) //Reversed mode
					{
						$route = $r['pathID'];
						$nFloor = $r['floor'];
						$rev = 1;
						break;
					}
				}
				
					
					if($iend == -1) $iend = $sp['end'];
					$iend = max($iend, $sp['end']);
					
					
					
					//print "//" . $sp['start'] . " - " .$start . " = " . ($sp['start'] - $start) . " \n";
					$delay = scaleTime($sp['start'] - $start);
					$dur = scaleTime($sp['end'] - $sp['start']);
					
					//$opac = 1;
					
					
					
				if($route != null)
				{
					//Check if on same floor
					if($nFloor != $floor)
					{
						changeFloor($id, $nFloor,$delay,$dur);
					}
					

					if(!isLongTime($dur))
					{
						print "clients[$id][$nFloor].transition().delay($delay).duration($dur).ease('linear').attrTween('transform',moveClient('$route',$rev, $dur));\n";
						if($nFloor != $floor && $floor != null)
						{
							print "clients[$id][$floor].transition().delay($delay).duration($dur).ease('linear').attrTween('transform',moveClient('$route',$rev, $dur));\n";
						}
					}
					else
					{
						print "clients[$id][$nFloor].transition().delay($delay).style('opacity',0).duration(0);\n";
						print "clients[$id][$nFloor].transition().delay($delay + $dur).style('opacity',1).duration(0);\n";
					}
				}
				else
				{
					$nFloor = $aps[$ap2]['z'];
					if($nFloor != $floor)
					{
						changeFloor($id, $nFloor,$delay,$dur);
					}
				
					if(!isLongTime($dur))
					{
						//Fall back if no route found
						$xpos = $aps[$ap2]['x'];
						$ypos = $aps[$ap2]['y'];
						print "clients[$id][$nFloor].transition().delay($delay + $dur).duration(0).attr('transform','translate($xpos,$ypos)');\n";
					}
					else
					{
						print "clients[$id][$nFloor].transition().delay($delay).style('opacity',0).duration(0);\n";
						print "clients[$id][$nFloor].transition().delay($delay + $dur).style('opacity',1).duration(0);\n";
					}
				}
				
				$floor = $nFloor;
				
			}
		$delay = scaleTime($iend - $start);
		print "clients[$id][0].transition().delay($delay).style('opacity',0);\n";
		print "clients[$id][1].transition().delay($delay).style('opacity',0);\n";
		print "clients[$id][2].transition().delay($delay).style('opacity',0);\n";
		print "clients[$id][3].transition().delay($delay).style('opacity',0);\n";
		
		print "\n////End Routes for client #$id\n\n";
	}
}

function changeFloor($id, $nFloor, $delay, $dur)
{
	print "//$id changing to floor $nFloor\n\n";
	for($i = 0; $i < 4; $i++)
	{
		if($i == $nFloor)
		{
			print "clients[$id][$i].transition().delay($delay).duration($dur).style('opacity',1);\n";
		}
		else
		{
			print "clients[$id][$i].transition().delay($delay).duration($dur).style('opacity',0);\n";
		}
	
	}
	
	print "\n\n//Done changing floors\n\n";


}


function addPoints($table)
{
	/*
	foreach($table as $row)
	{
	
	
		$cx = $row["X"] - 2.5;
		$cy = $row["Y"] - 2.5;
		$r = 5;
		print "wifi.append('circle').attr('cx',$cx).attr('cy',$cy).attr('r',$r).style('fill','rgba(255,0,255,0.2)');\n\n";
	}
	*/
}


	
	function getAPs($start = 0, $end = 0)
	{
	
		$wStart = date('Y-m-d H:i:s',$start);
		$wEnd = date('Y-m-d H:i:s',$end);
	
		$query = "select a.id apid, c.timestamp, c.clients, a.apname, a.x, a.y, a.floor from APs a left join clientsAtAP c   on (a.id = c.apID) where a.x is not null and c.timestamp between '$wStart' and '$wEnd' order by a.apname, c.timestamp";
		
		$sql = getSql();
		
		$res = sqlsrv_query($sql, $query);
		if( $res === false) {
			die( print_r( sqlsrv_errors(), true) );
		}
		
		$table = array();
		while( $row = sqlsrv_fetch_array( $res, SQLSRV_FETCH_ASSOC) ) 
		{
			array_push($table, $row);
		}
		
		sqlsrv_free_stmt($res);
		
		$apID = -1;
		$apdata = array();

		
		foreach($table as $row)
		{
			//new AP
			if($row['apid'] != $apID)
			{
				if($apID != -1)
				{
					$apdata[$apID]['data'] = $tap;
					
				}
				$tap = array();
				$apID = $row['apid'];
				$apdata[$apID]['x'] = $row['x'];
				$apdata[$apID]['y'] = $row['y'];
				$apdata[$apID]['z'] = $row['floor'];
				//var_dump($row);
				$apdata[$apID]['name'] = $row['apname'];				
				
			}
			$time = $row['timestamp']->getTimestamp();
			$tap[intval($time)] = $row['clients'];		
		
		}
		
		return $apdata;
	
	}
	
	function addAPs($start = 0, $end = 0)
	{
	
		$data = getAps($start, $end);
		
		//Create array to hold APs
		print "var aps = new Array();\n\n";
		print "var apBase = new Array();\n\n";
		//Get ap data
		print "var apData = " . json_encode ($data) . ";\n\n";
		
		$max = 20;
		
		foreach($data as $apID=>$ap)
		{
				//Get a single AP
				$r = 20;
				$x = $ap['x'];
				$y = $ap['y'];
				$z = $ap['z'];
				$n = $ap['name'];
				
				print "//AP point $n\n\n";
				
				print "apBase[$apID] = wifi[$z].append('circle').attr('cx',$x).attr('cy',$y).attr('r',$r).style('fill','rgba(118,238,194,0.1)').attr('name','$n').attr('class','AP');\n";
				print "aps[$apID]    = wifi[$z].append('circle').attr('cx',$x).attr('cy',$y).attr('r',0).style('fill','rgba(118,238,194,0.25)').attr('name','$n').attr('class','AP');\n\n";
				
				if(isset($ap['data']))
				{
					$apdata = $ap['data'];
					foreach($apdata as $t=>$a)
					{
						$dur = scaleTime(60*60); //1 hr
						$delay = scaleTime($t - $start);
						$nr = ($a/$max)*$r;
						
						print "aps[$apID].transition().delay($delay).duration($dur).ease('linear').attr('r',$nr);\n";
					}
				}
				
				print "\n//End point $n\n\n";
				
		}
	}
?>