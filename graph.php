<?php
require_once('../../config/dmsDefaults.php');

function dateDiff($start, $end) {
	$start_ts = strtotime($start);
	$end_ts = strtotime($end);	
	$diff = $end_ts - $start_ts;
	return round($diff / 86400);	
}
/*
     Example1 : A simple line chart
 */

 $begin_date = $_GET['begin_date'];
 $end_date = $_GET['end_date'];
 $users = str_replace("|", ",",$_GET['users']);
 if($users == '0'){
	$user_condition_data = "";
	$user_condition_name = "";
 }else{
	$user_condition = " AND user_id in (".$users.") ";
	$user_condition_name = "AND id in (".$users.")";
 }
 
 //build sql querys
 $sqlNames = "SELECT id, username, name from users  WHERE id>0 AND disabled=0 ".$user_condition_name." ORDER BY id";
 
 $sqlData = "SELECT count(datetime) as logins, user_id, DATE(datetime) as datetime
	FROM `user_history` 
	WHERE action_namespace = 'ktcore.user_history.login' "
	.$user_condition.
	"AND datetime between '".$begin_date."' AND '".$end_date."'
	GROUP BY DATE(datetime)
	ORDER BY user_id, datetime";	
 
 //llena series
 $users_names = DBUtil::getResultArray($sqlNames);
 $users_data = DBUtil::getResultArray($sqlData);

 $users_arr = array();
 for($u = 0;$u<count($users_names);$u++){
	$users_arr[$users_names[$u]['id']] = $users_names[$u]['username']." (".$users_names[$u]["name"].")";
	//$users_arr[$u]['name'] = $users_names[$u]['username'];
 }
 
	$data_arr = array();

	$begin_date = strtotime($begin_date);
	$end_date = strtotime($end_date);
	
	$date = $begin_date;
	while($date < $end_date)
	{
		$data_arr['date'][] = date("Y-m-d" ,$date);
		foreach($users_arr as $key=>$value){
			$data_arr[$key][date("Y-m-d" ,$date)] = 0;
		}		
	   $date = strtotime("+1 day", $date);	   
	} 

	foreach($users_data as $row){
		$data_arr[$row["user_id"]][$row["datetime"]] = $row["logins"];		
	}

 // Standard inclusions     
	include_once("class/pDraw.class.php");
	include_once("class/pImage.class.php");
	include_once("class/pData.class.php");

 // Dataset definition   
 $DataSet = new pData;
 $DataSet->loadPalette("palettes/summer.color", TRUE);
 foreach($users_arr as $key=>$value){
	$DataSet->AddPoints($data_arr[$key], $key);
	$DataSet->setSerieDescription($key, $value);
 }
 $DataSet->setAxisName(0, "Logins");
 $DataSet->setAxisDisplay(0, AXIS_FORMAT_NUMBER);
 
 //series
 $DataSet->AddPoints($data_arr['date'], "Fecha");
 $DataSet->setSerieDescription("Fecha", "Fechas");
 $DataSet->setAbscissa("Fecha"); 
  
 $graph = new pImage(950,260, $DataSet); 
 $Settings = array("StartR"=>14, "StartG"=>39, "StartB"=>120, "EndR"=>84, "EndG"=>88, "EndB"=>138, "Alpha"=>50);
 $graph->drawGradientArea(0,0,950,260,DIRECTION_VERTICAL,$Settings);
 $graph->setFontProperties(array("FontName"=>"fonts/Forgotte.ttf", "FontSize"=>11));
 $graph->setGraphArea(200,20,930,190);
 $graph->drawFilledRectangle(200,20,930,190,array("R"=>255,"G"=>255,"B"=>255,"Surrounding"=>-200,"Alpha"=>10));
 $Settings = array("Pos"=>SCALE_POS_LEFTRIGHT
, "Mode"=>SCALE_MODE_FLOATING
, "LabelingMethod"=>LABELING_ALL
, "GridR"=>255, "GridG"=>255, "GridB"=>255, "GridAlpha"=>50, "TickR"=>0, "TickG"=>0, "TickB"=>0, "TickAlpha"=>50, "LabelRotation"=>90, "LabelSkip"=>1, "CycleBackground"=>1, "DrawXLines"=>1, "DrawSubTicks"=>1, "SubTickR"=>255, "SubTickG"=>0, "SubTickB"=>0, "SubTickAlpha"=>50, "DrawYLines"=>ALL);
 $graph->drawScale($Settings);

 $graph->drawSplineChart();
 //series en un cuadrado
 $Config = array("FontR"=>0, "FontG"=>0, "FontB"=>0, "FontName"=>"fonts/pf_arma_five.ttf", "FontSize"=>6, "Margin"=>6, "Alpha"=>30, "BoxSize"=>5, "Style"=>LEGEND_ROUND, "Mode"=>LEGEND_VERTICAL);
$graph->drawLegend(20,40,$Config);
 $graph->Stroke(); 
 //var_dump($sqlData);
?>