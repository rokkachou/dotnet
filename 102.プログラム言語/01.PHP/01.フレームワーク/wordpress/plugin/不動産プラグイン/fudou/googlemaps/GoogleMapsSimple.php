<?php
	$mode = '';
	$jusho = isset($_GET['ju']) ? $_GET['ju'] : '';
	if($jusho=="")
		$jusho = "東京都";

	$lat = isset($_GET['lat']) ? $_GET['lat'] : '';
	if($lat==""){
		$lat = "35.689488";
		$mode = "geo";
	}

	$lng = isset($_GET['lng']) ? $_GET['lng'] : '';
	if($lng==""){
		$lng = "139.691706";
		$mode = "geo";
	}

?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="initial-scale=1.0, user-scalable=no"/>
<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
<title></title>
<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
<script type="text/javascript">
	var geocoder;
	var map;
	function initialize() {
		geocoder = new google.maps.Geocoder();
		var latlng = new google.maps.LatLng(<?php echo $lat;?>, <?php echo $lng;?>);
		var myOptions = {
		zoom: 16,
		center: latlng,
		mapTypeId: google.maps.MapTypeId.ROADMAP
//		,streetViewControl: true
		}
		map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);

		var centerIcon = new google.maps.Marker({
			icon: image
		});
		var image = new google.maps.MarkerImage(
			'centerMark.gif'
			, new google.maps.Size(39, 39)
			, new google.maps.Point(0,0)
			, new google.maps.Point(19,19)
		);
		var centerIcon = new google.maps.Marker({
			position: latlng,
			icon: image,
			map: map
		});


		function drawMarker(centerLocation){
			centerIcon.setPosition(centerLocation);
		}

		var centerd = map.getCenter();
		document.frm.lat.value=centerd.lat().toFixed(6); 
		document.frm.lng.value=centerd.lng().toFixed(6); 

		google.maps.event.addListener(map, 'center_changed', function(event) {
			var center = map.getCenter();
			document.frm.lat.value=center.lat().toFixed(6); 
			document.frm.lng.value=center.lng().toFixed(6); 
			drawMarker(map.getCenter());

		});

<?php 
		if($mode == "geo")
			echo '	codeAddress();';
?>

	}


	function codeAddress() {
		var address = document.getElementById("address").value;
		geocoder.geocode( { 'address': address}, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				map.setCenter(results[0].geometry.location);
			} else {
			//	alert("Geocode was not successful for the following reason: " + status);
				alert("座標が見つかりませんでした。\n「 " + address + "」\n手動で座標を設定してください。" );
			}
		});
	}
</script>
</head>
<style>
	html { height: 100% }
	body { height: 100%; margin: 0px; padding: 0px }
	body,input{
		font-family: "メイリオ", Meiryo,"ヒラギノ角ゴ Pro W3", "Hiragino Kaku Gothic Pro",  Osaka, "ＭＳ Ｐゴシック", "MS PGothic", sans-serif;
		font-size: 12px;
	}
	
</style>
<body onload="initialize()">
  <div>
	<form name="frm">
	<input id="address" type="textbox" size="20" value="<?php echo $jusho;?>">
	<input type="button" value="Geo" onclick="codeAddress()">

	緯度：<input type="text" name="lat" size="6">
	経度：<input type="text" name="lng" size="6">
	<input type="button" value="決定" onclick="notifyParent();">
	<div style="padding: 1; display:none" id="msgPreview"> </div>
	</form>
  </div>
<div id="map_canvas" style="width:100%; height:90%; z-index:1"></div>


<script type="text/javascript">
// <![CDATA[
function notifyParent(){
	var obj = {"album" : document.frm.lat.value };
	var obj2 = {"album2" : document.frm.lng.value};

	window.opener.update(obj,obj2);
	window.close();
}
// ]]>
</script>

</body>
</html>

