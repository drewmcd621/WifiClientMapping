<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
ini_set("html_errors", 1);

require_once "sqlConfig.php";
require_once "config.php";

/* source: https://jonsuh.com/blog/jquery-ajax-call-to-php-script-with-json-return/ */
if (is_ajax() || true) {
  if (isset($_GET["action"]) && !empty($_GET["action"])) { //Checks if action value exists

    $action = $_GET["action"];
		switch($action) { //Switch case for value of action
			case "clients" : clientJSON(); break;
			case  "aps" : apJSON(); break;
		}
	}
}
function is_ajax() {
  return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}
/* end */
	
	
//  clientJSON: returns JSON for client animation
function clientJSON($start=0, $end=0)
{

	if(isset($_GET["start"]) && isset($_GET["end"]))
	{
		$start = $_GET["start"];
		$end = $_GET["end"];
	}
	$routes = getRoutes();
	$aps = getAPs($start, $end);
	$paths = getPaths($start, $end);
	
	
	$sstart = scaleTime($start);
	$send =  scaleTime($start);
	
	$missing = array();
	
	$nPaths = 0;
	
	$clients = array(); //This is the array that will be converted to JSON
	$clients["start"] = $start;
	$clients["end"] = $end;
	$clients["clients"] = array();
	
	
	foreach($paths as $id=>$p) //Go through the clients
	{
		//Some variables
		$cma = 0;
		$cmnh = 0;
		$rts = 0;
		$iend = -1;
		$floor = null;
		
		$client = array();
		$client["data"] = array();
		$client["id"] = $id;
			
			foreach($p as $sid=>$sp) //Go through the animation
			{
				
				$route = null;
				$rev = 0;
				$ap1 = $sp['AP1'];
				$ap2 = $sp['AP2'];
				$nFloor = null;
				$rn = null;


				
				foreach($routes as $n=>$r) //Find the route
				{
					if($r['AP1'] == $ap1 && $r['AP2'] == $ap2)
					{
						$route = $r['pathID'];
						$floor = $r['floor1'];
						$nFloor = $r['floor2'];
						$rn = $n;
						break;
					}
					else if($r['AP1'] == $ap2 && $r['AP2'] == $ap1) //Reversed mode
					{
						$route = $r['pathID'];
						$floor = $r['floor2'];
						$nFloor = $r['floor1'];
						$rn = $n;
						$rev = 1;
						break;
					}
				}
				

				
				
					
				if($iend == -1) $iend = $sp['end'];
				$iend = max($iend, $sp['end']);
					
					
					
				//Get times
				$delay = $sp['start'] - $start;
				$dur = $sp['end'] - $sp['start'];
					
				
				//Check if the client has gone to cma or cmnh
				if($rn != null)
				{
					$cma += $routes[$rn]['CMA'];
					$cmnh += $routes[$rn]['CMNH'];
				}
					
				
				if($route != null)
				{
					$rts++;
					$nPaths++;

						if($floor == null) $floor = $nFloor;
						//Create the next step in the animation
						$step = array();
						$step["floor1"] = $floor;
						$step["floor2"] = $nFloor;
						$step["delay"] = $delay;
						$step["duration"] = $dur;
						$step["route"] = $route;
						$step['rev'] = $rev;
						array_push($client["data"], $step);
						
				}
				else
				{
					//If a route isn't found we record the data for analytics
			
					$f1 = $aps[$ap1]['z'];
					$f2 = $aps[$ap2]['z'];
					
					
					if($ap1 != $ap2 )
					{
						$name1 = $aps[$ap1]['name'];
						$name2 = $aps[$ap2]['name'];
						if(isset($missing["$name1 to $name2"]))
						{
							$missing["$name1 to $name2"] ++;
						}
						else if(isset($missing["$name2 to $name1"]))
						{
							$missing["$name2 to $name1"] ++;
						}
						else
						{
							$missing["$name1 to $name2"] = 1;
						}
						$nPaths ++;
					}
					
				}
				
				$floor = $nFloor;  //Set 'old floor' to 'new floor'
				
			}
			//If there is no data remove this client
			if(count($client["data"]) == 0)
			{
			}
			else
			{
				$client["cma"] = 0;
				$client["cmnh"] = 0;
				if($cma > 0) $client["cma"] = 1;
				if($cmnh > 0) $client["cmnh"] = 1;
				array_push($clients["clients"], $client); //Push the client into the overall clients array
			}
			
		
	}
	
	

	//Log the missing routes
	arsort($missing);
	$count = 0;
	$log = "Route,N,%\n";
	foreach($missing as $k=>$v)
	{
		$p = ($v / $nPaths) *100;
		$log .= "$k,$v,$p\n";
		$count += $v;
	}
	$p = ($count / $nPaths) *100;
	$log .= "Total,$count,$p\n";
	file_put_contents('MissingRoutes.csv',$log);
	
	echo json_encode($clients);
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
