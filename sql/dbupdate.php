<#1>
<?php
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/ViMP/classes/Model/class.xvmpConf.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/ViMP/classes/Model/class.xvmpSelectedMedia.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/ViMP/classes/Model/class.xvmpUploadedMedia.php');
xvmpConf::updateDB();
xvmpSelectedMedia::updateDB();
xvmpUploadedMedia::updateDB();
?>