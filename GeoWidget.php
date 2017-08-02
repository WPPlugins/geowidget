<?php
/*
Plugin Name: Geo Widget
Plugin URI: http://www.u-g-h.com/index.php/wordpress-plugins/wordpress-plugin-geowidget/
Description: Shows a map of where you in in your sidebar
Version: 1.0
Author: Owen Cutajar
Author URI: http://www.u-g-h.com
*/

/* History:
  v0.1 - OwenC - Created base version
  v1.0 - OwenC - Public release
*/

// cater for stand-alone calls
if (!function_exists('get_option'))
	require_once('../../../wp-config.php');

$wpa_db_version = "1.0";

// Consts
define('PLUGIN_EXTERNAL_PATH', '/wp-content/plugins/GeoWidget/');
define('PLUGIN_NAME', 'GeoWidget.php');
define('PLUGIN_PATH', 'GeoWidget/GeoWidget.php');

// Echo Dynamic Javascript (.js) - technique borrowed from ajax-comments (http://www.mikesmullin.com)
if (strstr($_SERVER['PHP_SELF'],PLUGIN_EXTERNAL_PATH.PLUGIN_NAME) && isset($_GET['js'])):
header("Content-Type:text/javascript"); 

$options = get_option('GeoWidget');
$title = $options['title'];
$lat = $options['lat'];
$long = $options['long'];
$width = $options['width'];
$height = $options['height'];
$mode = $options['mode'];
$zoom = $options['zoom'];

?>

var map=null;

// Spoof onload .. haven't managed to find a more elegant way to do this in WordPress ..
if (window.addEventListener) 
{
   window.addEventListener('load', function() { setTimeout(PopulateMap, 0); }, false);
} 
else 
{
   window.attachEvent('onload', function() { setTimeout(PopulateMap, 0); } );
}

//Safari Handler - don't you love exceptions?  (Thanks for this Mike)
if(/Safari/i.test(navigator.userAgent)){ 
   var _timer=setInterval(function(){
   if(/loaded|complete/.test(document.readyState)){
      clearInterval(_timer);
      PopulateMap();
   } }, 10)
}

function PopulateMap() {   
    map = new VEMap('GeoWidget');
    map.SetDashboardSize(VEDashboardSize.Small);
    map.LoadMap(new VELatLong(<?php echo $lat ?>,<?php echo $long ?>), <?php echo $zoom ?>, '<?php echo $mode ?>' , false);
   
    var shape = new VEShape(VEShapeType.Pushpin, map.GetCenter());
    shape.SetTitle('I live here');
    shape.SetDescription('Close anyway');
    map.AddShape(shape);   
}
 
<?php

endif;


function widget_GeoWidget_init() {

	if ( !function_exists('register_sidebar_widget') )
		return;

	function widget_GeoWidget() {

		//extract($args);
		$options = get_option('GeoWidget');
		$title = $options['title'];
		$lat = $options['lat'];
		$long = $options['long'];
		$width = $options['width'];
		$height = $options['height'];
		$mode = $options['mode'];
		$zoom = $options['zoom'];

		echo $before_widget . $before_title . $title . $after_title;
		docommon_displayGeoWidget ( $lat, $long, $width, $height, $mode, $zoom);
		echo $after_widget;
	}
	
	function widget_GeoWidget_control() {

		$options = get_option('GeoWidget');
		if ( !is_array($options) )
			$options = array( 'title'=>'GeoWidget', 'lat'=>'0', 'long'=>'0', 'width'=>'0', 'height'=>'0', 'mode'=>'h', 'zoom'=>'5');

		if ( $_POST['GeoWidget-submit'] ) {
                        
            // Change sidebar title
			$options['title'] = strip_tags(stripslashes($_POST['GeoWidget-title']));
			$options['lat'] = strip_tags(stripslashes($_POST['GeoWidget-lat']));
			$options['long'] = strip_tags(stripslashes($_POST['GeoWiget-long']));
			$options['width'] = strip_tags(stripslashes($_POST['GeoWidget-width']));
			$options['height'] = strip_tags(stripslashes($_POST['GeoWidget-height']));
			$options['mode'] = strip_tags(stripslashes($_POST['GeoWiget-mode']));
			$options['zoom'] = strip_tags(stripslashes($_POST['GeoWiget-zoom']));
			update_option('GeoWidget', $options);
		}
		
		$title = htmlspecialchars($options['title'], ENT_QUOTES);
		$lat = htmlspecialchars($options['lat'], ENT_QUOTES);
		$long = htmlspecialchars($options['long'], ENT_QUOTES);
		$width = htmlspecialchars($options['width'], ENT_QUOTES);
		$height = htmlspecialchars($options['height'], ENT_QUOTES);
		$mode = htmlspecialchars($options['mode'], ENT_QUOTES);
		$zoom = htmlspecialchars($options['zoom'], ENT_QUOTES);
		
		
		echo '<p style="text-align:right;"><label for="GeoWidget-title">' . __('Title:') . ' <input style="width: 200px;" id="GeoWidget-title" name="GeoWidget-title" type="text" value="'.$title.'" /></label></p>';
		echo '<p style="text-align:right;"><label for="GeoWidget-lat">' . __('Latitude:') . ' <input style="width: 200px;" id="GeoWidget-lat" name="GeoWidget-lat" type="text" value="'.$lat.'" /></label></p>';		
		echo '<p style="text-align:right;"><label for="GeoWidget-long">' . __('Longitude:') . ' <input style="width: 200px;" id="GeoWidget-long" name="GeoWidget-long" type="text" value="'.$long.'" /></label></p>';		
		echo '<p style="text-align:right;"><label for="GeoWidget-width">' . __('Width:') . ' <input style="width: 200px;" id="GeoWidget-lat" name="GeoWidget-width" type="text" value="'.$width.'" /></label></p>';		
		echo '<p style="text-align:right;"><label for="GeoWidget-height">' . __('Height:') . ' <input style="width: 200px;" id="GeoWidget-long" name="GeoWidget-height" type="text" value="'.$height.'" /></label></p>';		
		echo '<p style="text-align:right;"><label for="GeoWidget-mode">' . __('Mode(r/a/h):') . ' <input style="width: 200px;" id="GeoWidget-lat" name="GeoWidget-mode" type="text" value="'.$mode.'" /></label></p>';		
		echo '<p style="text-align:right;"><label for="GeoWidget-zoom">' . __('Zoom:') . ' <input style="width: 200px;" id="GeoWidget-long" name="GeoWidget-zoom" type="text" value="'.$zoom.'" /></label></p>';		

        echo "You can also use the funky map selection thingy on the <a href='" . get_bloginfo('wpurl') . "/wp-admin/options-general.php?page=GeoWidget.php'>admin page</a>.";
		
		echo '<input type="hidden" id="GeoWidget-submit" name="GeoWidget-submit" value="1" />';
	}

	register_sidebar_widget(array('GeoWidget', 'widgets'), 'widget_GeoWidget');
	register_widget_control(array('GeoWidget', 'widgets'), 'widget_GeoWidget_control', 300, 320);
;
}

function GeoWidget(){

   $options = get_option('GeoWidget');
   $title = $options['title'];
   $lat = $options['lat'];
   $long = $options['long'];
   $width = $options['width'];
   $height = $options['height'];
   $mode = $options['mode'];
   $zoom = $options['zoom'];

   docommon_displayGeoWidget ( $lat, $long, $width, $height, $mode, $zoom);
}

function docommon_displayGeoWidget( $lat = '0', $long = '0', $width = '0', $height = '0', $mode = 'h', $zoom = '5' ) {

   if( $width == '0') {
      $widthr = 'width:100%; ';
   } else {
      $widthr = 'width:'.$width.'px; ';
   }

   if( $height == '0') {
      $heightr = ' ';
   } else {
      $heightr = 'height:'.height.'px; ';
   }

   echo '<div id="GeoWidget" style="position:relative; '.$widthr.$heightr.'border-style:double">';
   echo '</div>';

}

function GeoWidget_options() {

   // Note: Options for this plugin include a "Title" setting which is only used by the widget
   $options = get_option('GeoWidget');
	
   //set initial values if none exist
   if ( !is_array($options) ) {
      $options = array( 'title'=>'GeoWidget', 'lat'=>'0', 'long'=>'0', 'width'=>'0', 'height'=>'0', 'mode'=>'h', 'zoom'=>'5');
   }

   if ( $_POST['GeoWidget-submit'] ) {
      $options['lat'] = strip_tags(stripslashes($_POST['GeoWidget-lat']));
      $options['long'] = strip_tags(stripslashes($_POST['GeoWidget-long']));
      $options['width'] = strip_tags(stripslashes($_POST['GeoWidget-width']));
      $options['height'] = strip_tags(stripslashes($_POST['GeoWidget-height']));
      $options['mode'] = strip_tags(stripslashes($_POST['GeoWidget-mode']));
      $options['zoom'] = strip_tags(stripslashes($_POST['GeoWidget-zoom']));
      update_option('GeoWidget', $options);
   }

   $lat = htmlspecialchars($options['lat'], ENT_QUOTES);
   $long = htmlspecialchars($options['long'], ENT_QUOTES);
   $width = htmlspecialchars($options['width'], ENT_QUOTES);
   $height = htmlspecialchars($options['height'], ENT_QUOTES);
   $mode = htmlspecialchars($options['mode'], ENT_QUOTES);
   $zoom = htmlspecialchars($options['zoom'], ENT_QUOTES);
	
?>

<script type="text/javascript" src="/wp-includes/js/prototype.js"></script>
<script src="http://dev.virtualearth.net/mapcontrol/mapcontrol.ashx?v=6"></script>
<script type="text/javascript" src="/wp-content/plugins/GeoWidget/MapSelect.js"></script>
<style type="text/css">

#lightbox{
	display:none;
	position: absolute;
	top:50%;
	left:50%;
	z-index:9999;
	width:500px;
	height:400px;
	margin:-220px 0 0 -250px;
	border:1px solid #fff;
	background:#FDFCE9;
	text-align:left;
}
#lightbox[id]{
	position:fixed;
}

#overlay{
	display:none;
	position:absolute;
	top:0;
	left:0;
	width:100%;
	height:100%;
	z-index:5000;
	background-color:#000;
	-moz-opacity: 0.8;
	opacity:.80;
	filter: alpha(opacity=80);
}
#overlay[id]{
	position:fixed;
}

#lightbox.done #lbLoadMessage{
	display:none;
}
#lightbox.done #lbContent{
	display:block;
}
#lightbox.loading #lbContent{
	display:none;
}
#lightbox.loading #lbLoadMessage{
	display:block;
}

</style>

<div class="wrap"> 
  <h2><?php _e('GeoWidget Options') ?></h2> 
  <form name="form1" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=GeoWidget.php">

 
    <table width="100%" cellspacing="2" cellpadding="5" class="editform"> 
      <tr valign="top"> 
        <th scope="row"><?php _e('Latitude:') ?></th> 
        <td><input name="GeoWidget-lat" type="text" id="GeoWidget-lat" value="<?php echo $lat; ?>" size="80" />
        <a href="/wp-content/plugins/GeoWidget/MapSelect.php" class="lbOn">Select Location</a>
		<br />
        <?php _e('Enter the latitude to use. Locate it') ?></td> 
      </tr> 
      <tr valign="top"> 
        <th scope="row"><?php _e('Longitude:') ?></th> 
        <td><input name="GeoWidget-long" type="text" id="GeoWidget-long" value="<?php echo $long; ?>" size="80" />
        <br />
        <?php _e('Enter the longitude to use') ?></td> 
      </tr> 
      <tr valign="top"> 
        <th scope="row"><?php _e('Width:') ?></th> 
        <td><input name="GeoWidget-width" type="text" id="GeoWidget-width" value="<?php echo $width; ?>" size="80" />
        <br />
        <?php _e('Enter the width to use. Use 0 for widget to expand to 100%') ?></td> 
      </tr> 
      <tr valign="top"> 
        <th scope="row"><?php _e('Height:') ?></th> 
        <td><input name="GeoWidget-height" type="text" id="GeoWidget-height" value="<?php echo $height; ?>" size="80" />
        <br />
        <?php _e('Enter the width to use. Use 0 for widget to expand to 100%') ?></td> 
      </tr> 
      <tr valign="top"> 
        <th scope="row"><?php _e('Mode:') ?></th> 
        <td><input name="GeoWidget-mode" type="text" id="GeoWidget-mode" value="<?php echo $mode; ?>" size="80" />
        <br />
        <?php _e('Select the mode to use. Select r (road), a (ariel) or h (hybrid)') ?></td> 
      </tr> 
      <tr valign="top"> 
        <th scope="row"><?php _e('Zoom:') ?></th> 
        <td><input name="GeoWidget-zoom" type="text" id="GeoWidget-zoom" value="<?php echo $zoom; ?>" size="80" />
        <br />
        <?php _e('Specify the zoom factor to use') ?></td> 
      </tr> 
    </table>

	<input type="hidden" id="-submit" name="GeoWidget-submit" value="1" />

    <p class="submit">
      <input type="submit" name="Submit" value="<?php _e('Update Options') ?> &raquo;" />
    </p>
  </form> 
</div>

<?php
}

function GeoWidget_header() {

   echo "\n" . '<!-- GeoWidget start -->' . "\n";
   echo '<script src="http://dev.virtualearth.net/mapcontrol/mapcontrol.ashx?v=6"></script>' . "\n";

   if (function_exists('wp_enqueue_script')) {
      wp_enqueue_script('wp_GeoWidget', get_bloginfo('wpurl') . PLUGIN_EXTERNAL_PATH . PLUGIN_NAME .'?js');
      wp_print_scripts();
   }
   echo "\n" . '<!-- GeoWidget end -->' . "\n\n";

}

function GeoWidget_adminmenu(){
   if (function_exists('add_options_page')) {
	add_options_page('GeoWidget Options', 'GeoWidget', 9, 'GeoWidget.php', 'GeoWidget_options');
   }
}
add_action('widgets_init', 'widget_GeoWidget_init');
add_action('admin_menu', 'GeoWidget_adminmenu',1);
add_action('wp_head', 'GeoWidget_header');
?>