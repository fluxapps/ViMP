<#1>
<?php
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/ViMP/classes/Model/class.xvmpConf.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/ViMP/classes/Model/class.xvmpSelectedMedia.php');
xvmpConf::updateDB();
xvmpSelectedMedia::updateDB();
?>