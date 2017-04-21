<?php
class PatoLeonStatisticsUsers extends KTAdminDispatcher {
	
	function check() {
		$this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _kt('User statistics'));
		return parent::check();
	}

	function do_main() {

		$this->oPage->setTitle(_kt("User statistics"));

		$oTemplating =& KTTemplating::getSingleton();
		$oTemplating->addLocation('PatoLeon.statistics', '/plugins/PatoLeon.Reports/templates');
		$oTemplate =& $this->oValidator->validateTemplate("UserStatistics");

		//verifica si no es llamada para crear Excel		
		$aTemplateData["action"] = "update";
		$aTemplateData["begin_date"] = (isset($_POST['begin_date']) ? $_POST['begin_date'] : date("Y-m-d", mktime(0,0,0,date('m')-1,date('d')-1,date('Y'))));
		$aTemplateData["end_date"] = (isset($_POST['end_date']) ? $_POST['end_date']: date("Y-m-d"));
		$aTemplateData["selected_users"] = (isset($_POST["users"]) ? join($_POST['users'],"|") : 0);
			
		if($_POST['action'] == 'excel'){
			$aTemplateData["action"] = "update";
			$this->send_excel();
		}
		
		//lista de usuarios de KT
		$oUsers = User::getList("id>0 AND disabled=0");
		$aUsers = array();
		if(PEAR::isError($oUsers)){
			//error			
		}else{
			foreach($oUsers as $oUser){
				$aUsers[] = array(
					"UserName"=>$oUser->getUserName()." (".$oUser->getName().")",
					"Id"=>$oUser->getId(), 
					"selected" => (in_array($oUser->getId(), $_POST['users']) ? "selected" : "")
				);
			}
		}
		
		$aTemplateData["users"] = $aUsers;
		
		//url
		$aTemplateData["url"] = $_SERVER["PHP_SELF"];
		
		return $oTemplate->render($aTemplateData);
	}
	
	function send_excel(){		
		$sql = "SELECT users.name, users.username, datetime, SUBSTRING(comments,16) as IP ".
					"FROM user_history, users ".
					"WHERE user_history.user_id = users.id ".
					"AND datetime BETWEEN '".(isset($_POST['begin_date']) ? $_POST['begin_date'] : date("Y-m-d", mktime(0,0,0,date('m')-1,date('d')-1,date('Y'))))."'  and '".(isset($_POST['end_date']) ? $_POST['end_date']: date("Y-m-d"))." '".
					"AND action_namespace = 'ktcore.user_history.login'";
		$sql .= (join($_POST['users'], ",") != 0 ? " AND user_history.user_id in (".join($_POST['users'], ",").")" : "" );
			//$sql .=" AND user_history in (".replace("|", ",", $aTemplateData["selected_users"]).")";
		
		
		$res = DBUtil::getResultArray($sql);
		if(!PEAR::isError($res)){
			$export_file = "reporte-".date('Y-m-d').".csv";
			header("Cache-Control: must-revalidate");
			header("Pragma: must-revalidate");
			header("Content-type: application/vnd.ms-excel");

			header('Content-Disposition: attachment; filename="'.basename($export_file).'"');
			//echo($sql."<br />");
			echo iconv("UTF-8", "ISO-8859-1", "name;username;datetime;IP\n");
			foreach($res as $row){
				echo iconv("UTF-8", "ISO-8859-1", $row['name'].";".$row['username'].";".$row['datetime'].";".$row['IP']."\n");
			}
		}else{
			return sprintf("Error generando reporte : {0}", $res);
		}
		exit(0);
	}
}
?>