<?php
	$url = "http://ios.cmoa.org/api/v2/sync.json";
	
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	$json = curl_exec($curl);
	curl_close($curl);
	
	//print mb_detect_encoding($json);
	
	
	

?>
<!doctype html>
<html lang="en">
<head>
  
  <script src="https://code.jquery.com/jquery-1.10.2.js"></script>
  <script>
	var json = <?php //print $json; ?>'';
	$( document ).ready(function(){
		var res = $(json).filter(function (i,n){return n.media.kind === "image";});
		//$('#result').html(res);
	});
  </script>
 </head>
 <body>

</body>