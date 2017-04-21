<?php

// Tutorial code from wiki.ktdms.com

require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
require_once(KT_DIR.'/lib/users/User.inc');

class PatoLeonReportsPlugin extends KTPlugin {
	var $sNamespace = 'PatoLeon.Reports.plugin';
	function PatoLeonReportsPlugin($sFilename = null) {
		$res = parent::KTPlugin($sFilename);
		$this->sFriendlyName = _kt("PatoLeon - Reports plugin");
	}

	function setup() { 
		$oConfig =& KTConfig::getSingleton();
		$this->registerAdminCategory("PatoLeon.reports", _("Reports"),_("Reports about different topics.."));
		$this->registerAdminPage("patoleonstatisticsuser",'PatoLeonStatisticsUsers','PatoLeon.reports', _('User statistics'),_('Connection statistics.'),  'PatoLeon.Statistics.php');
		$this->registerAdminPage("patoleontransactionsreport",'PatoLeonTransactionsReport','PatoLeon.reports', _('Transactions report'),_('Transactions resume between dates.'),  'TransactionsReport.php');
	}
}

$oRegistry =& KTPluginRegistry::getSingleton();
$oRegistry->registerPlugin('PatoLeonReportsPlugin','PatoLeon.Reports.plugin', __FILE__);


?>