<?php
/**
 * Web page to display current hunt on map
 */
require_once('include/common.php');
require_once('include/database.php');
startValidSession();
htmlHead();
?>
<h1>Hunt Map</h1>
<h2>Displays current location of all team</h2><br/><br/>
<?php
	$lat_lon = getLatLon();
	foreach($lat_lon as $gps){
		$_team[] = $gps['team'];
		$_lat[] = $gps['lat'];
		$_lon[] = $gps['lon'];
	}
	$team = join(",", $_team);
	$lat = join(",", $_lat);
	$lon = join(",", $_lon);
?>

<script src="https://maps.googleapis.com/maps/api/js?&sensor=false"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>
<script type="text/javascript">

	var tmp0 = '<?php echo $team;?>';
	var php_team = tmp0.split(",");
	var tmp1 = '<?php echo $lat;?>';
	var php_lat = tmp1.split(",");
	var tmp2 = '<?php echo $lon;?>';
	var php_lon = tmp2.split(",");
	var map,timer;
	var iterator=0;
	var latlngs=[];
	var markers=[];
	
	function addMarker(){
		markers.push(
			new google.maps.Marker({
			position: latlngs[iterator],
			map:map,
			title:php_team[iterator],
			animation: google.maps.Animation.DROP
			})
		)
		iterator++;
	}
	
	function initialize() {
		var myOptions = {
			zoom: 8,
			center: new google.maps.LatLng(-33.683211,150.825806),
			mapTypeId: google.maps.MapTypeId.ROADMAP
		};
		map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
		
		for (var i = 0; i < php_lat.length; i++) {
			var _lat = parseFloat(php_lat[i]);
			var _lng = parseFloat(php_lon[i]);
			var _latlng = new google.maps.LatLng(_lat, _lng);
			latlngs.push(_latlng);
		}
		for(var i = 0; i < latlngs.length; i++){
			timer=setTimeout(addMarker, i*230);
		}
	}
	google.maps.event.addDomListener(window, 'load', initialize);
	
</script>

<div id="map_canvas" style="width: 640px; height: 480px;"></div>
<br/><br/>
<h2>GPS Position</h2>
<?php
	foreach($lat_lon as $gps) {
		echo '<strong>', $gps['team'] . '</strong>';
		echo '<br/>';
		echo 'latitude = ', $gps['lat'] . ' : longitude = ', $gps['lon'];
		echo '<br/><br />';
	}
?>

<?php
htmlFoot();
?>
