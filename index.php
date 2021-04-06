<?php
/*
Plugin Name: Lectionary
Plugin URI: http://www.iantearle.com/
Description: Gives the Lectionary of the current day of the week
Version: 1.0
Author: Ian Tearle
Author URL: http://www.iantearle.com/
*/

require_once('Lectionary.class.php');

$lectionary = new Lectionary(date('Y'));

$l = $lectionary->get_calendar_day(time());

if(!$l) {
	$sat = 7; //saturday = end of week
	$current_day=date('N');
	$days_remaining_until_sat = $sat - $current_day;

	$ts_start = strtotime("-$current_day days");
	$ts_end = strtotime("+$days_remaining_until_sat days");

	$next_sunday = strtotime(date('d-m-Y',$ts_end));

	$year = strftime('%Y',$next_sunday);
	$month = strftime('%m',$next_sunday);
	$day = strftime('%d',$next_sunday);

	$l = $lectionary->get_calendar_day(gmmktime(0,0,0,$month,$day,$year));
}

$page = '';

foreach($l as $key => $val) {
	$page .= '<div class="title"><h3>'.$lectionary->get_title($key, $val).' (Year: '.$lectionary->get_cycle($key).")</h3></div>";
	$page .= '<div class="date"><p><sup>'.date('D jS', strtotime($lectionary->get_long_date($key))).'</sup><br /><span class="month">'.date('M', strtotime($lectionary->get_long_date($key))).'</span></p></div>';
	$page .= '<b>Old Testament&#58;</b> '. $lectionary->get_scripture($key,$val,'old') . ' '. urlencode($lectionary->get_scripture($key,$val,'old')) . "<br />";//
	$page .= '<b>Psalms&#58;</b> '. $lectionary->get_scripture($key,$val,'psalms') . "<br />"; //urlencode($lectionary->get_scripture($key,$val,'psalms'))
	$page .= '<b>Epistle&#58;</b> '. $lectionary->get_scripture($key,$val,'new')    . "<br />"; //urlencode($lectionary->get_scripture($key,$val,'new'))
	$page .= '<b>Gospel&#58;</b> '. $lectionary->get_scripture($key,$val,'gospel') . "<br />"; //urlencode($lectionary->get_scripture($key,$val,'gospel'))
	$page .= "</p></div>";
}

print $page;
