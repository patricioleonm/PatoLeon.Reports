<?php

class PatoLeonTransactionsReport extends KTAdminDispatcher {
	
	function check() {
		$this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _kt('Transactions'));
		return parent::check();
	}

	function do_main() {

		$this->oPage->setTitle(_kt("Transactions Report"));

		$oTemplating =& KTTemplating::getSingleton();
		$oTemplating->addLocation('PatoLeon.statistics', '/plugins/PatoLeon.Reports/templates');
		$oTemplate =& $this->oValidator->validateTemplate("DocumentTransactions");

		//verifica si no es llamada para crear Excel		
		$aTemplateData["action"] = "update";
		$aTemplateData["begin_date"] = (isset($_POST['begin_date']) ? $_POST['begin_date'] : date("Y-m-d", mktime(0,0,0,date('m')-1,date('d')-1,date('Y'))));
		$aTemplateData["end_date"] = (isset($_POST['end_date']) ? $_POST['end_date']: date("Y-m-d"));		
			
		if($_POST['action'] == 'excel'){
			$aTemplateData["action"] = "update";
			$this->send_excel();
		}
		
			
		//url
		$aTemplateData["url"] = $_SERVER["PHP_SELF"];
		
		return $oTemplate->render($aTemplateData);
	}
	
	function send_excel(){		
		$sql = "SELECT T.document_id, T.version, U.name as UserName, T.datetime, T.filename, TP.name as DocumentType, T.comment, TY.name as Action
				FROM document_transactions T
					, document_transaction_types_lookup TY
					, users U
					, documents D
					, document_types_lookup TP
					, document_metadata_version V
				WHERE DATE(datetime) BETWEEN '".$_POST['begin_date']."' AND '".$_POST['end_date']."'
				AND T.transaction_namespace = TY.namespace
				AND T.user_id = U.id
				AND T.document_id = D.id
				AND D.metadata_version_id = V.id
				AND V.document_type_id = TP.id
				ORDER BY document_id, datetime";
/*		$sql = "SELECT users.name, users.username, datetime, SUBSTRING(comments,16) as IP ".
					"FROM user_history, users ".
					"WHERE user_history.user_id = users.id ".
					"AND datetime BETWEEN '".(isset($_POST['begin_date']) ? $_POST['begin_date'] : date("Y-m-d", mktime(0,0,0,date('m')-1,date('d')-1,date('Y'))))."'  and '".(isset($_POST['end_date']) ? $_POST['end_date']: date("Y-m-d"))." '".
					"AND action_namespace = 'ktcore.user_history.login'";		
			//$sql .=" AND user_history in (".replace("|", ",", $aTemplateData["selected_users"]).")";
			*/
		
		
		$res = DBUtil::getResultArray($sql);
		if(!PEAR::isError($res)){
			$export_file = "Transactions-report-".date('Y-m-d').".csv";
			header("Cache-Control: must-revalidate");
			header("Pragma: must-revalidate");
			header("Content-type: application/vnd.ms-excel");

			header('Content-Disposition: attachment; filename="'.basename($export_file).'"');
			//echo($sql."<br />");
			echo iconv("UTF-8", "ISO-8859-1", "Document ID;Version;File Name;Document Type;User;datetime;Comment;Action\n");
			foreach($res as $row){
			//	echo iconv("UTF-8", "ISO-8859-1", $row['action']);
				echo iconv("UTF-8", "ISO-8859-1", $row['document_id'].";".$row['version'].";".str_replace(";", ",", $row['filename']).";".$row['DocumentType'].";".$row['UserName'].";".$row['datetime'].";".str_replace(";",",",$row['comment']).";".$row['Action']."\n");
			}
		}else{
			return sprintf("Error generando reporte : {0}", $res);
		}
		exit(0);
	}
}
?>