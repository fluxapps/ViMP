<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit64f7216efcdcf8ddae98e6499b2ca798
{
    public static $prefixLengthsPsr4 = array (
        's' => 
        array (
            'srag\\Plugins\\ViMP\\' => 18,
            'srag\\LibrariesNamespaceChanger\\' => 31,
            'srag\\DIC\\ViMP\\' => 14,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'srag\\Plugins\\ViMP\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
        'srag\\LibrariesNamespaceChanger\\' => 
        array (
            0 => __DIR__ . '/..' . '/srag/librariesnamespacechanger/src',
        ),
        'srag\\DIC\\ViMP\\' => 
        array (
            0 => __DIR__ . '/..' . '/srag/dic/src',
        ),
    );

    public static $prefixesPsr0 = array (
        'D' => 
        array (
            'Detection' => 
            array (
                0 => __DIR__ . '/..' . '/mobiledetect/mobiledetectlib/namespaced',
            ),
        ),
    );

    public static $classMap = array (
        'Detection\\MobileDetect' => __DIR__ . '/..' . '/mobiledetect/mobiledetectlib/namespaced/Detection/MobileDetect.php',
        'Mobile_Detect' => __DIR__ . '/..' . '/mobiledetect/mobiledetectlib/Mobile_Detect.php',
        'ilMultiSelectSearchInputGUI' => __DIR__ . '/../..' . '/classes/GUI/Form/Input/class.ilMultiSelectSearchInputGUI.php',
        'ilObjViMP' => __DIR__ . '/../..' . '/classes/class.ilObjViMP.php',
        'ilObjViMPAccess' => __DIR__ . '/../..' . '/classes/class.ilObjViMPAccess.php',
        'ilObjViMPGUI' => __DIR__ . '/../..' . '/classes/class.ilObjViMPGUI.php',
        'ilObjViMPListGUI' => __DIR__ . '/../..' . '/classes/class.ilObjViMPListGUI.php',
        'ilViMPConfigGUI' => __DIR__ . '/../..' . '/classes/class.ilViMPConfigGUI.php',
        'ilViMPPlugin' => __DIR__ . '/../..' . '/classes/class.ilViMPPlugin.php',
        'srDateDurationInputGUI' => __DIR__ . '/../..' . '/classes/GUI/Form/Input/class.srDateDurationInputGUI.php',
        'srGenericMultiInputGUI' => __DIR__ . '/../..' . '/classes/GUI/Form/Input/class.srGenericMultiInputGUI.php',
        'srag\\DIC\\ViMP\\Cron\\FixUITemplateInCronContext' => __DIR__ . '/..' . '/srag/dic/src/Cron/FixUITemplateInCronContext.php',
        'srag\\DIC\\ViMP\\DICStatic' => __DIR__ . '/..' . '/srag/dic/src/DICStatic.php',
        'srag\\DIC\\ViMP\\DICStaticInterface' => __DIR__ . '/..' . '/srag/dic/src/DICStaticInterface.php',
        'srag\\DIC\\ViMP\\DICTrait' => __DIR__ . '/..' . '/srag/dic/src/DICTrait.php',
        'srag\\DIC\\ViMP\\DIC\\AbstractDIC' => __DIR__ . '/..' . '/srag/dic/src/DIC/AbstractDIC.php',
        'srag\\DIC\\ViMP\\DIC\\DICInterface' => __DIR__ . '/..' . '/srag/dic/src/DIC/DICInterface.php',
        'srag\\DIC\\ViMP\\DIC\\Implementation\\ILIAS54DIC' => __DIR__ . '/..' . '/srag/dic/src/DIC/Implementation/ILIAS54DIC.php',
        'srag\\DIC\\ViMP\\DIC\\Implementation\\ILIAS60DIC' => __DIR__ . '/..' . '/srag/dic/src/DIC/Implementation/ILIAS60DIC.php',
        'srag\\DIC\\ViMP\\Database\\AbstractILIASDatabaseDetector' => __DIR__ . '/..' . '/srag/dic/src/Database/AbstractILIASDatabaseDetector.php',
        'srag\\DIC\\ViMP\\Database\\DatabaseDetector' => __DIR__ . '/..' . '/srag/dic/src/Database/DatabaseDetector.php',
        'srag\\DIC\\ViMP\\Database\\DatabaseInterface' => __DIR__ . '/..' . '/srag/dic/src/Database/DatabaseInterface.php',
        'srag\\DIC\\ViMP\\Database\\PdoContextHelper' => __DIR__ . '/..' . '/srag/dic/src/Database/PdoContextHelper.php',
        'srag\\DIC\\ViMP\\Database\\PdoStatementContextHelper' => __DIR__ . '/..' . '/srag/dic/src/Database/PdoStatementContextHelper.php',
        'srag\\DIC\\ViMP\\Exception\\DICException' => __DIR__ . '/..' . '/srag/dic/src/Exception/DICException.php',
        'srag\\DIC\\ViMP\\Loader\\AbstractLoaderDetector' => __DIR__ . '/..' . '/srag/dic/src/Loader/AbstractLoaderDetector.php',
        'srag\\DIC\\ViMP\\Output\\Output' => __DIR__ . '/..' . '/srag/dic/src/Output/Output.php',
        'srag\\DIC\\ViMP\\Output\\OutputInterface' => __DIR__ . '/..' . '/srag/dic/src/Output/OutputInterface.php',
        'srag\\DIC\\ViMP\\PHPVersionChecker' => __DIR__ . '/..' . '/srag/dic/src/PHPVersionChecker.php',
        'srag\\DIC\\ViMP\\Plugin\\Plugin' => __DIR__ . '/..' . '/srag/dic/src/Plugin/Plugin.php',
        'srag\\DIC\\ViMP\\Plugin\\PluginInterface' => __DIR__ . '/..' . '/srag/dic/src/Plugin/PluginInterface.php',
        'srag\\DIC\\ViMP\\Plugin\\Pluginable' => __DIR__ . '/..' . '/srag/dic/src/Plugin/Pluginable.php',
        'srag\\DIC\\ViMP\\Version\\PluginVersionParameter' => __DIR__ . '/..' . '/srag/dic/src/Version/PluginVersionParameter.php',
        'srag\\DIC\\ViMP\\Version\\Version' => __DIR__ . '/..' . '/srag/dic/src/Version/Version.php',
        'srag\\DIC\\ViMP\\Version\\VersionInterface' => __DIR__ . '/..' . '/srag/dic/src/Version/VersionInterface.php',
        'srag\\LibrariesNamespaceChanger\\LibrariesNamespaceChanger' => __DIR__ . '/..' . '/srag/librariesnamespacechanger/src/LibrariesNamespaceChanger.php',
        'srag\\Plugins\\ViMP\\Content\\MediumMetadataDTO' => __DIR__ . '/../..' . '/src/UIComponents/Content/MediumMetadataDTO.php',
        'srag\\Plugins\\ViMP\\Content\\MediumMetadataDTOBuilder' => __DIR__ . '/../..' . '/src/UIComponents/Content/MediumMetadataDTOBuilder.php',
        'srag\\Plugins\\ViMP\\Content\\MediumMetadataParser' => __DIR__ . '/../..' . '/src/UIComponents/Content/MediumMetadataParser.php',
        'srag\\Plugins\\ViMP\\Cron\\ViMPJob' => __DIR__ . '/../..' . '/src/Cron/ViMPJob.php',
        'srag\\Plugins\\ViMP\\Database\\Config\\ConfigAR' => __DIR__ . '/../..' . '/src/Database/Config/ConfigAR.php',
        'srag\\Plugins\\ViMP\\Database\\Config\\ConfigRepository' => __DIR__ . '/../..' . '/src/Database/Config/ConfigRepository.php',
        'srag\\Plugins\\ViMP\\Database\\EventLog\\EventLogAR' => __DIR__ . '/../..' . '/src/Database/EventLog/EventLogAR.php',
        'srag\\Plugins\\ViMP\\Database\\SelectedMedia\\SelectedMediaAR' => __DIR__ . '/../..' . '/src/Database/SelectedMedia/SelectedMediaAR.php',
        'srag\\Plugins\\ViMP\\Database\\Settings\\SettingsAR' => __DIR__ . '/../..' . '/src/Database/Settings/SettingsAR.php',
        'srag\\Plugins\\ViMP\\Database\\UploadedMedia\\UploadedMediaAR' => __DIR__ . '/../..' . '/src/Database/UploadedMedia/UploadedMediaAR.php',
        'srag\\Plugins\\ViMP\\Database\\UserLPStatus\\UserLPStatusAR' => __DIR__ . '/../..' . '/src/Database/UserLPStatus/UserLPStatusAR.php',
        'srag\\Plugins\\ViMP\\Database\\UserProgress\\UserProgressAR' => __DIR__ . '/../..' . '/src/Database/UserProgress/UserProgressAR.php',
        'srag\\Plugins\\ViMP\\UIComponents\\PlayerModal\\MediumAttribute' => __DIR__ . '/../..' . '/src/UIComponents/PlayerModal/MediumAttribute.php',
        'srag\\Plugins\\ViMP\\UIComponents\\PlayerModal\\PlayerContainerDTO' => __DIR__ . '/../..' . '/src/UIComponents/PlayerModal/PlayerContainerDTO.php',
        'srag\\Plugins\\ViMP\\UIComponents\\Player\\VideoPlayer' => __DIR__ . '/../..' . '/src/UIComponents/Player/VideoPlayer.php',
        'srag\\Plugins\\ViMP\\UIComponents\\Renderer\\ContentElementRenderer' => __DIR__ . '/../..' . '/src/UIComponents/Renderer/ContentElementRenderer.php',
        'srag\\Plugins\\ViMP\\UIComponents\\Renderer\\Factory' => __DIR__ . '/../..' . '/src/UIComponents/Renderer/Factory.php',
        'srag\\Plugins\\ViMP\\UIComponents\\Renderer\\ListElementRenderer' => __DIR__ . '/../..' . '/src/UIComponents/Renderer/ListElementRenderer.php',
        'srag\\Plugins\\ViMP\\UIComponents\\Renderer\\PlayerInSiteRenderer' => __DIR__ . '/../..' . '/src/UIComponents/Renderer/PlayerInSiteRenderer.php',
        'srag\\Plugins\\ViMP\\UIComponents\\Renderer\\PlayerModalRenderer' => __DIR__ . '/../..' . '/src/UIComponents/Renderer/PlayerModalRenderer.php',
        'srag\\Plugins\\ViMP\\UIComponents\\Renderer\\TileRenderer' => __DIR__ . '/../..' . '/src/UIComponents/Renderer/TileRenderer.php',
        'srag\\Plugins\\ViMP\\UIComponents\\Renderer\\TileSmallRenderer' => __DIR__ . '/../..' . '/src/UIComponents/Renderer/TileSmallRenderer.php',
        'xoctPlupload' => __DIR__ . '/../..' . '/classes/GUI/Form/Input/class.xvmpFileUploadInputGUI.php',
        'xoctPluploadException' => __DIR__ . '/../..' . '/classes/GUI/Form/Input/class.xvmpFileUploadInputGUI.php',
        'xvmp' => __DIR__ . '/../..' . '/classes/class.xvmp.php',
        'xvmpCache' => __DIR__ . '/../..' . '/classes/Cache/v52/class.xvmpCache.php',
        'xvmpCacheFactory' => __DIR__ . '/../..' . '/classes/Cache/class.xvmpCacheFactory.php',
        'xvmpCategory' => __DIR__ . '/../..' . '/classes/Model/API/class.xvmpCategory.php',
        'xvmpChangeOwnerFormGUI' => __DIR__ . '/../..' . '/classes/GUI/Form/class.xvmpChangeOwnerFormGUI.php',
        'xvmpChapters' => __DIR__ . '/../..' . '/classes/Model/API/class.xvmpChapters.php',
        'xvmpConfFormGUI' => __DIR__ . '/../..' . '/classes/GUI/Form/class.xvmpConfFormGUI.php',
        'xvmpConfig' => __DIR__ . '/../..' . '/classes/Model/API/class.xvmpConfig.php',
        'xvmpContentGUI' => __DIR__ . '/../..' . '/classes/GUI/class.xvmpContentGUI.php',
        'xvmpContentListGUI' => __DIR__ . '/../..' . '/classes/GUI/class.xvmpContentListGUI.php',
        'xvmpContentPlayerGUI' => __DIR__ . '/../..' . '/classes/GUI/class.xvmpContentPlayerGUI.php',
        'xvmpContentTilesGUI' => __DIR__ . '/../..' . '/classes/GUI/class.xvmpContentTilesGUI.php',
        'xvmpCron' => __DIR__ . '/../..' . '/classes/class.xvmpCron.php',
        'xvmpCurl' => __DIR__ . '/../..' . '/classes/Request/class.xvmpCurl.php',
        'xvmpCurlError' => __DIR__ . '/../..' . '/classes/Request/class.xvmpCurlError.php',
        'xvmpCurlLog' => __DIR__ . '/../..' . '/classes/Util/class.xvmpCurlLog.php',
        'xvmpDeletedMedium' => __DIR__ . '/../..' . '/classes/Model/API/class.xvmpDeletedMedium.php',
        'xvmpEditVideoFormGUI' => __DIR__ . '/../..' . '/classes/GUI/Form/class.xvmpEditVideoFormGUI.php',
        'xvmpEventLogGUI' => __DIR__ . '/../..' . '/classes/GUI/class.xvmpEventLogGUI.php',
        'xvmpEventLogTableGUI' => __DIR__ . '/../..' . '/classes/GUI/Table/class.xvmpEventLogTableGUI.php',
        'xvmpException' => __DIR__ . '/../..' . '/classes/Exception/class.xvmpException.php',
        'xvmpFileUploadInputGUI' => __DIR__ . '/../..' . '/classes/GUI/Form/Input/class.xvmpFileUploadInputGUI.php',
        'xvmpFormGUI' => __DIR__ . '/../..' . '/classes/GUI/Form/class.xvmpFormGUI.php',
        'xvmpGUI' => __DIR__ . '/../..' . '/classes/GUI/Abstract/class.xvmpGUI.php',
        'xvmpLearningProgressGUI' => __DIR__ . '/../..' . '/classes/GUI/class.xvmpLearningProgressGUI.php',
        'xvmpLearningProgressTableGUI' => __DIR__ . '/../..' . '/classes/GUI/Table/class.xvmpLearningProgressTableGUI.php',
        'xvmpLog' => __DIR__ . '/../..' . '/classes/Util/class.xvmpLog.php',
        'xvmpMedium' => __DIR__ . '/../..' . '/classes/Model/API/class.xvmpMedium.php',
        'xvmpObject' => __DIR__ . '/../..' . '/classes/Model/API/class.xvmpObject.php',
        'xvmpOwnVideosGUI' => __DIR__ . '/../..' . '/classes/GUI/class.xvmpOwnVideosGUI.php',
        'xvmpOwnVideosTableGUI' => __DIR__ . '/../..' . '/classes/GUI/Table/class.xvmpOwnVideosTableGUI.php',
        'xvmpProgressBarUI' => __DIR__ . '/../..' . '/classes/Util/class.xvmpProgressBarUI.php',
        'xvmpRequest' => __DIR__ . '/../..' . '/classes/Request/class.xvmpRequest.php',
        'xvmpSearchVideosGUI' => __DIR__ . '/../..' . '/classes/GUI/class.xvmpSearchVideosGUI.php',
        'xvmpSearchVideosTableGUI' => __DIR__ . '/../..' . '/classes/GUI/Table/class.xvmpSearchVideosTableGUI.php',
        'xvmpSelectedVideosGUI' => __DIR__ . '/../..' . '/classes/GUI/class.xvmpSelectedVideosGUI.php',
        'xvmpSelectedVideosTableGUI' => __DIR__ . '/../..' . '/classes/GUI/Table/class.xvmpSelectedVideosTableGUI.php',
        'xvmpSettingsFormGUI' => __DIR__ . '/../..' . '/classes/GUI/Form/class.xvmpSettingsFormGUI.php',
        'xvmpSettingsGUI' => __DIR__ . '/../..' . '/classes/GUI/class.xvmpSettingsGUI.php',
        'xvmpTableGUI' => __DIR__ . '/../..' . '/classes/GUI/Table/class.xvmpTableGUI.php',
        'xvmpUploadFile' => __DIR__ . '/../..' . '/classes/Request/class.xvmpUploadFile.php',
        'xvmpUploadService' => __DIR__ . '/../..' . '/classes/Util/class.xvmpUploadService.php',
        'xvmpUploadVideoFormGUI' => __DIR__ . '/../..' . '/classes/GUI/Form/class.xvmpUploadVideoFormGUI.php',
        'xvmpUser' => __DIR__ . '/../..' . '/classes/Model/API/class.xvmpUser.php',
        'xvmpUserRoles' => __DIR__ . '/../..' . '/classes/Model/API/class.xvmpUserRoles.php',
        'xvmpVideoFormGUI' => __DIR__ . '/../..' . '/classes/GUI/Form/class.xvmpVideoFormGUI.php',
        'xvmpVideosGUI' => __DIR__ . '/../..' . '/classes/GUI/Abstract/class.xvmpVideosGUI.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit64f7216efcdcf8ddae98e6499b2ca798::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit64f7216efcdcf8ddae98e6499b2ca798::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit64f7216efcdcf8ddae98e6499b2ca798::$prefixesPsr0;
            $loader->classMap = ComposerStaticInit64f7216efcdcf8ddae98e6499b2ca798::$classMap;

        }, null, ClassLoader::class);
    }
}
