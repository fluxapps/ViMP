<?php

use ILIAS\Filesystem\Filesystems;
use ILIAS\FileUpload\FileUpload;
use ILIAS\FileUpload\Location;
use ILIAS\Filesystem\Exception\IOException;
use ILIAS\FileUpload\Exception\IllegalStateException;

/**
 * Class xvmpUploadService
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmpUploadService
{
    /**
     * @var Filesystems
     */
    protected $file_system;
    /**
     * @var FileUpload
     */
    protected $file_upload;
    /**
     * @var string[]
     */
    protected $temp_directories = [];

    /**
     * xvmpUploadService constructor.
     * @param Filesystems $file_system
     * @param FileUpload  $file_upload
     */
    public function __construct(Filesystems $file_system, FileUpload $file_upload)
    {
        $this->file_system = $file_system;
        $this->file_upload = $file_upload;
    }

    /**
     * moves file upload to web dir and returns WAC-signed url
     * @param string $tmp_name
     * @param string $tmp_id
     * @return string name (could have been changed by the upload processor)
     * @throws IOException
     * @throws IllegalStateException
     */
    public function moveUploadToWebDir(string $tmp_name, string $tmp_id) : string
    {
        $dir = '/vimp/' . $tmp_id;
        $this->createDirIfNotExists($dir);
        $this->file_upload->process();
        $uploadResult = $this->file_upload->getResults()[$tmp_name];
        $this->file_upload->moveOneFileTo(
            $uploadResult,
            $dir,
            Location::WEB,
            $uploadResult->getName()
        );
        return $uploadResult->getName();
    }

    /**
     * @param string $tmp_name
     * @param string $tmp_id
     * @return string
     * @throws ilWACException
     */
    public function getSignedUrl(string $tmp_name, string $tmp_id) : string
    {
        $dir = '/vimp/' . $tmp_id;
        $path = $dir . '/' . $tmp_name;
        $this->temp_directories[] = $dir;
        $path = $this->signWithWAC($path);
        return ILIAS_HTTP_PATH . ltrim($path, '.');
    }

    /**
     * @param string $dir
     * @throws IOException
     */
    protected function createDirIfNotExists(string $dir)
    {
        if (!$this->file_system->web()->hasDir($dir)) {
            $this->file_system->web()->createDir($dir);
        }
    }

    /**
     * @param string $path
     * @return string
     * @throws ilWACException
     */
    protected function signWithWAC(string $path) : string
    {
        ilWACSignedPath::setTokenMaxLifetimeInSeconds(ilWACSignedPath::MAX_LIFETIME);
        $thumbnail_path = ilWACSignedPath::signFile(ilUtil::getWebspaceDir() . $path);
        $thumbnail_path .= '&' . ilWebAccessChecker::DISPOSITION . '=' . ilFileDelivery::DISP_ATTACHMENT;
        return $thumbnail_path;
    }

    public function cleanUp()
    {
        foreach ($this->temp_directories as $temp_directory) {
            ilUtil::delDir(ilUtil::getWebspaceDir() . $temp_directory);
        }
    }

}
