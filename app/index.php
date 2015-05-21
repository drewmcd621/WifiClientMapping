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
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
  
  <style>
	.imgs{
		float: left;
	}
	.audio{
	
	}
	.video
	{
	
	}
  </style>
  <script>
  $( document ).ready(function(){
		var json = getJSON();
		var artwork = $.grep(json.artwork, function (elem,index) {return elem.code == '<?php print $artCode; ?>';});
		console.log(artwork);
		console.log(artwork[0].uuid);
		var nCar = 0;
		var imgs =	$.grep(json.media, function (elem,index) {return elem.artwork_uuid == artwork[0].uuid;});
		$(imgs).each(function(i,v)
		{
			if(v.kind == 'image')
			{
				$('#img').append("<img class='imgs' src='" + v.urlThumb +"' title = '" + v.title + "'></img>");
			}
			else 
			{
				
				addMedia(v.urlFull, v.title, v.kind, nCar);
				nCar ++;
			}

		});
		$('#carousel-media').carousel({
			interval: 2000
		})
	});
	
	function addMedia(src, description, type, n)
	{
		var active = false;
		if(n == 0)
		{
			active = true;
		}
		
		var ind = $("<li data-target='#carousel-example-generic' data-slide-to='" + n + "'></li>");
		if(active)
		{
			ind.addClass("active");
		}
		$(".carousel-indicators").append(ind);
		
		var itmDiv = $("<div class='item'></div>");
		if(active)
		{
			itmDiv.addClass("active");
		}
		var itmCaption = $("<div class='carousel-caption'><p>" + description + "</p></div>");
		var itmMedia;
		
		if(type == 'video')
		{
			//itmMedia = $(
		}
		else if (type == 'image')
		{
			itmMedia = $("<img src='" + src + "'>");
		}
		
		itmDiv.append(itmCaption);
		$(".carousel-inner").append(itmDiv);
	}
	function getJSON()
	{
		return <?php print $json; ?> ;
	}
  </script>

 </head>
 <body>
	<div id="carousel-media" class="carousel slide" data-ride="carousel">
	<!-- Indicators -->
		<ol class="carousel-indicators">
			<!-- Add indicators here -->
			
		</ol>

		<!-- Wrapper for slides -->
		<div class="carousel-inner" role="listbox">
			<!-- Add items here -->

		</div>

		<!-- Controls -->
		<a class="left carousel-control" href="#carousel-media" role="button" data-slide="prev">
			<span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
			<span class="sr-only">Previous</span>
		</a>
		<a class="right carousel-control" href="#carousel-media" role="button" data-slide="next">
			<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
			<span class="sr-only">Next</span>
		</a>
	</div>
 
	<pre id='print'></pre>
	<div id='img'>
	</div>
	<div id='audio'>
	</div>
	<div id='video'>
	</div>
</body>