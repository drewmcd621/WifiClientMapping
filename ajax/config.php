<?php

$timeScale = 1000.0; //1.0 = realtime, 10.0 = 10x 0.1 = 1/10x...
$longTime = 1000*30*60; //30 min in realtime

function scaleTime($time)
{
	global $timeScale;
	return $time / ($timeScale / 1000);
}

function unscaleTime($time)
{
	global $timeScale;
	return $time * ($timeScale / 1000);
}

function isLongTime($time)
{
	global $longTime;
	$time = unscaleTime($time);
	if($time >= $longTime) return true;

	return false;
}

function setScale($seconds) //scaled seconds per real second
{
	global $timeScale;
	$timeScale = $seconds;

}




?>