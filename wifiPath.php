<?php

require_once "sqlConfig.php";
	
	
 function getData()
 {
	$sql = getSql();
	
	$res = sqlsrv_query($sql, "SELECT  * FROM clientLocation where  floor>=1.5 order by clientid, timestamp"); //clientid = 529 and
	if( $res === false) {
		die( print_r( sqlsrv_errors(), true) );
	}
	
	$table = array();
	while( $row = sqlsrv_fetch_array( $res, SQLSRV_FETCH_ASSOC) ) 
	{
		array_push($table, $row);
	}
	
	sqlsrv_free_stmt($res);
	return $table;
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
		$point['x'] = $row['X'];
		$point['y'] = $row['Y'];
		$point['time'] = $row['timestamp']->getTimestamp();
		
		//$startTime = min($startTime, $point['time']); //Get first timestamp
		
		array_push($tpath, $point);
		
		
	}
	
	$paths[$clientID] = $tpath;
	return $paths;
	
}

function getStart($table)
{
	$s = time();
	foreach($table as $row)
	{
		$s=min($s,$row['timestamp']->getTimestamp());
	}
	return $s;

}

//var_dump($paths);

function addPaths($paths, $starttime=0)
{
	//Setup SVG functions
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