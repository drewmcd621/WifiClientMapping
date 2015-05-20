<?php
header("Content-Type: text/html; charset=utf-8"); 

	//$url = "http://ios.cmoa.org/api/v2/sync.json";
	$url = 'example.js';
	/*
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	$json = curl_exec($curl);
	curl_close($curl);
	*/
	$json = preg_replace('/^\s+|\n|\r|\s+$/m', '', file_get_contents_utf8($url));
	
	
	
	
	function file_get_contents_utf8($fn) { 
     $content = file_get_contents($fn); 
      return mb_convert_encoding($content, 'UTF-8', 
          mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true)); 
	}

	$artCode = $_GET['c'];

	
	
	

?>
<!doctype html>
<html lang="en">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8"></meta>
  
  <script src="https://code.jquery.com/jquery-1.10.2.js"></script>
  <style>
	.imgs{
		float: left;
	}
  </style>
  <script>
  $( document ).ready(function(){
		var json = getJSON();
		var artwork = $.grep(json.artwork, function (elem,index) {return elem.code == '<?php print $artCode; ?>';});
		console.log(artwork);
		console.log(artwork[0].uuid);
		var imgs =	$.grep(json.media, function (elem,index) {return elem.artwork_uuid == artwork[0].uuid;});
		$(imgs).each(function(i,v)
		{
			if(v.kind == 'image')
			{
				$('#img').append("<img class='imgs' src='" + v.urlThumb +"' title = '" + v.title + "'></img>");
			}
			else if(v.kind == 'audio')
			{
				$('#img').append("<div class = 'imgs'><p>" + v.title + "</p><audio controls><source src='" + v.urlThumb +"'></audio></div>");
			}
		});
	});
	function getJSON()
	{
		return <?php print $json; ?> ;
	}
  </script>

 </head>
 <body>
	<pre id='print'></pre>
	<div id='img'>
	</div>
</body>