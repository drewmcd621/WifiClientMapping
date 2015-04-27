<?php
	require_once "sqlConfig.php";
	
	function getAPs()
	{
		$query = "select a.id apid, c.timestamp, c.clients, a.apname, a.x, a.y from clientsAtAP c left join APs a on (a.id = c.apID) where a.x is not null and floor=2 order by a.apname";
		
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
				
			}
			$time = $row['timestamp']->getTimestamp();
			$tap[intval($time)] = $row['clients'];		
		
		}
		
		return $apdata;
	
	}
	
	function addAPs($data)
	{
		//Create array to hold APs
		print "var aps = new Array();\n\n";
		//Get ap data
		print "var apData = " . json_encode ($data) . ";\n\n";
		
		foreach($data as $apID=>$ap)
		{
				//Get a single AP
				$r = 20;
				$x = $ap['x'];
				$y = $ap['y'];
				
				$circle = "aps[$apID] = wifi.append('circle').attr('cx',$x).attr('cy',$y).attr('r',$r).style('fill','rgba(118,238,194,0.1)');\n\n";

				print $circle;
				
		}
	}
?>