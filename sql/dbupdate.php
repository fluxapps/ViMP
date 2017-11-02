<#1>
<?php
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/ViMP/classes/Model/class.xvmpConf.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/ViMP/classes/Model/class.xvmpSelectedMedia.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/ViMP/classes/Model/class.xvmpUploadedMedia.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/ViMP/classes/Model/class.xvmpSettings.php');
xvmpConf::updateDB();
xvmpSelectedMedia::updateDB();
xvmpUploadedMedia::updateDB();
xvmpSettings::updateDB();
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
