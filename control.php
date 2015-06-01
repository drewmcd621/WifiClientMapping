<?php

require_once "sqlConfig.php";


function getStart()
{
	$sql = getSql();
	
	
	$res = sqlsrv_query($sql, "SELECT min(tfrom) as min FROM clientLocation");
	if( $res === false) {
		die( print_r( sqlsrv_errors(), true) );
	}
	
	$table = array();
	while( $row = sqlsrv_fetch_array( $res, SQLSRV_FETCH_ASSOC) ) 
	{
		array_push($table, $row);
	}
	
	sqlsrv_free_stmt($res);
	
	//var_dump($table);
	
	return date("m/d/Y", $table[0]["min"]->getTimestamp());
}

function getEnd()
{
	$sql = getSql();
	
	
	$res = sqlsrv_query($sql, "SELECT max(tto) as max FROM clientLocation");
	if( $res === false) {
		die( print_r( sqlsrv_errors(), true) );
	}
	
	$table = array();
	while( $row = sqlsrv_fetch_array( $res, SQLSRV_FETCH_ASSOC) ) 
	{
		array_push($table, $row);
	}
	
	sqlsrv_free_stmt($res);
	
	//var_dump($table);
	
	return date("m/d/Y", $table[0]["max"]->getTimestamp());
}


?>