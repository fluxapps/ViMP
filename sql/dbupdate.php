<#1>
<?php
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/ViMP/classes/Model/AR/class.xvmpConf.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/ViMP/classes/Model/AR/class.xvmpSelectedMedia.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/ViMP/classes/Model/AR/class.xvmpUploadedMedia.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/ViMP/classes/Model/AR/class.xvmpSettings.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/ViMP/classes/Model/AR/class.xvmpUserProgress.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/ViMP/classes/Model/AR/class.xvmpUserLPStatus.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/ViMP/classes/Model/AR/class.xvmpEventLog.php');
xvmpConf::updateDB();
xvmpSelectedMedia::updateDB();
xvmpUploadedMedia::updateDB();
xvmpSettings::updateDB();
xvmpUserProgress::updateDB();
xvmpUserLPStatus::updateDB();
xvmpEventLog::updateDB();
?>
<#2>
<?php
require_once("./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php");
$xvmp_type_id = ilDBUpdateNewObjectType::addNewType('xvmp', 'Plugin ViMP');

//Adding a new Permission rep_robj_xvmp_upload ("Upload")
$offering_admin = ilDBUpdateNewObjectType::addCustomRBACOperation( //$a_id, $a_title, $a_class, $a_pos
	'rep_robj_xvmp_perm_upload', 'upload', 'object', 2010);
if($offering_admin)
{
	ilDBUpdateNewObjectType::addRBACOperation($xvmp_type_id, $offering_admin);
}

?>
<#3>
<?php
$transfer_dir = ilUtil::getWebspaceDir() . '/vimp';
if (!is_dir($transfer_dir)) {
	ilUtil::makeDir($transfer_dir);
}
?>
<#4>
<?php
global $DIC;
$query = $DIC->database()->query('select * from lng_data where module = "rep_robj_xvmp" and identifier = "rep_robj_xvmp_obj_xvmp"');
if (!$query->numRows()) {
	$DIC->database()->insert('lng_data', array(
		'module' => array('text', 'rep_robj_xvmp'),
		'identifier' => array('text', 'rep_robj_xvmp_obj_xvmp'),
		'lang_key' => array('text', 'de'),
		'value' => array('text', 'ViMP Video Container')
	));
	$DIC->database()->insert('lng_data', array(
		'module' => array('text', 'rep_robj_xvmp'),
		'identifier' => array('text', 'rep_robj_xvmp_obj_xvmp'),
		'lang_key' => array('text', 'en'),
		'value' => array('text', 'ViMP Video Container')
	));
}
$query = $DIC->database()->query('select * from lng_data where module = "rep_robj_xvmp" and identifier = "rep_robj_xvmp_objs_xvmp"');
if (!$query->numRows()) {
	$DIC->database()->insert('lng_data', array(
		'module' => array('text', 'rep_robj_xvmp'),
		'identifier' => array('text', 'rep_robj_xvmp_objs_xvmp'),
		'lang_key' => array('text', 'de'),
		'value' => array('text', 'ViMP Video Containers')
	));
	$DIC->database()->insert('lng_data', array(
		'module' => array('text', 'rep_robj_xvmp'),
		'identifier' => array('text', 'rep_robj_xvmp_objs_xvmp'),
		'lang_key' => array('text', 'en'),
		'value' => array('text', 'ViMP Video Containers')
	));
}
?>
<#5>
<?php
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/ViMP/classes/Model/AR/class.xvmpConf.php');
xvmpConf::set(xvmpConf::F_NOTIFICATION_SUBJECT_SUCCESSFULL, 'Transkodierung abgeschlossen');
xvmpConf::set(xvmpConf::F_NOTIFICATION_BODY_SUCCESSFULL, 'Guten Tag {FIRSTNAME} {LASTNAME},

die Transkodierung eines von Ihnen hochgeladenen Videos wurde abgeschlossen:

Titel: {TITLE}
Beschreibung: {DESCRIPTION}
Link zum Video: {VIDEO_LINK}

Das Video kann nun in ILIAS verwendet werden.');
xvmpConf::set(xvmpConf::F_NOTIFICATION_SUBJECT_FAILED, 'Transkodierung fehlgeschlagen');
xvmpConf::set(xvmpConf::F_NOTIFICATION_BODY_FAILED, 'Guten Tag {FIRSTNAME} {LASTNAME},

die Transkodierung eines von Ihnen hochgeladenen Videos ist fehlgeschlagen:

Titel: {TITLE}
Beschreibung: {DESCRIPTION}

Bitte versuchen Sie es erneut oder kontaktieren Sie einen Administrator.');
xvmpConf::set(xvmpConf::F_CACHE_TTL_VIDEOS, 0);
xvmpConf::set(xvmpConf::F_CACHE_TTL_USERS, 0);
xvmpConf::set(xvmpConf::F_CACHE_TTL_TOKEN, 0);
xvmpConf::set(xvmpConf::F_CACHE_TTL_CATEGORIES, 86400);
?>
<#6>
<?php
xvmpConf::set(xvmpConf::F_CACHE_TTL_CONFIG, 0);
?>
<#7>
<?php
xvmpUploadedMedia::updateDB();
?>