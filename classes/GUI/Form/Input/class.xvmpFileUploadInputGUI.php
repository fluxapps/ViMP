<?php

/**
 * Class xvmpFileUploadInputGUI
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class xvmpFileUploadInputGUI extends ilSubEnabledFormPropertyGUI {

    /**
     * @var ilViMPPlugin
     */
    private $pl;

    /**
	 * @var array
	 */
	protected $suffixes = array();
	/**
	 * @var string
	 */
	protected $url = '';
	/**
	 * @var string
	 */
	protected $chunk_size = '2M';
	/**
	 * @var bool
	 */
	protected $unique_names = true;
	/**
	 * @var string
	 */
	protected $max_file_size = '20000mb';
	/**
	 * @var bool
	 */
	protected $log = false;
	/**
	 * @var string
	 */
	protected $form_id = '';
	/**
	 * @var string
	 */
	protected $cmd = '';
	/**
	 * @var array
	 */
	protected $mime_types = array();


	/**
	 * xoctFileUploadInputGUI constructor.
	 *
	 * @param ilPropertyFormGUI $ilPropertyFormGUI
	 * @param string $a_title
	 * @param $a_postvar
	 */
	public function __construct(ilPropertyFormGUI $ilPropertyFormGUI, $cmd, $a_title, $a_postvar) {
		global $DIC;
		$tpl = $DIC['tpl'];
		$this->pl = ilViMPPlugin::getInstance();
		$tpl->addJavaScript($this->pl->getAssetURL('/js/waiter.js'));
		$tpl->addCss($this->pl->getAssetURL('default/waiter.css'));

		$ilPropertyFormGUI->setId($ilPropertyFormGUI->getId() ? $ilPropertyFormGUI->getId() : md5(rand(1, 99)));
		$this->setFormId($ilPropertyFormGUI->getId());
		$this->setCmd($cmd);
		$tpl->addJavaScript($this->pl->getAssetURL('js/plupload-2.1.8/js/plupload.full.min.js'));

		parent::__construct($a_title, $a_postvar);
	}


	/**
	 * @return string
	 */
	public function render() {
		$tpl = new ilTemplate('./Customizing/global/plugins/Services/Repository/RepositoryObject/ViMP/templates/default/form/tpl.uploader.html', false, true);
		$this->initJS();
		$tpl->setVariable('BUTTON_SELECT', $this->pl->txt('upload_select'));
		$tpl->setVariable('BUTTON_CLEAR', $this->pl->txt('upload_clear'));
		$tpl->setVariable('POSTVAR', $this->getPostVar());
		$tpl->setVariable('FILETYPES', $this->pl->txt('supported_filetypes') . ': ' . implode(', ', $this->getSuffixes()));
		$tpl->setVariable('MAX_FILESIZE', $this->pl->txt('max_filesize') . ': ' . $this->getMaxFileSize());

		return $tpl->get();
	}


	protected function initJS() {
		global $DIC;
		$tpl = $DIC['tpl'];
		$tpl->addJavaScript($this->pl->getAssetURL('default/form/uploader.min.js'));
		$settings = new stdClass();
		$settings->lng = new stdClass();
		$settings->lng->msg_select = $this->pl->txt('form_msg_select');
		$settings->lng->msg_not_supported = $this->pl->txt('form_msg_not_supported');
		$settings->log = $this->isLog();
		$settings->cmd = $this->getCmd();
		$settings->form_id = $this->getFormId();
		$settings->url = $this->getUrl();
		$settings->runtimes = 'html5,html4';
		$settings->pick_button = 'xoct_pickfiles';
		$settings->chunk_size = $this->getChunkSize();
		$settings->max_file_size = $this->max_file_size;
		$settings->supported_suffixes = implode(',', $this->getSuffixes());
		$settings->supported_suffixes_array = $this->getSuffixes();
		$settings->mime_types = implode(',', $this->getMimeTypes());
		$settings->mime_types_array = $this->getMimeTypes();
		$settings->required = $this->getRequired();

		$tpl->addOnLoadCode('xoctFileuploaderSettings.initFromJSON(\'' . json_encode($settings) . '\');');
	}


	/**
	 * @return string
	 */
	public function getCmd() {
		return $this->cmd;
	}


	/**
	 * @param string $cmd
	 */
	public function setCmd($cmd) {
		$this->cmd = $cmd;
	}


	/**
	 * @param array $suffixes
	 */
	public function setSuffixes(array $suffixes) {
		$this->suffixes = $suffixes;
	}


	/**
	 * @return array
	 */
	public function getSuffixes() {
		return $this->suffixes;
	}


	public function setValueByArray(array $value) {
	}


	/**
	 * @param ilTemplate $a_tpl
	 */
	public function insert(ilTemplate &$a_tpl) {
		$html = $this->render();

		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $html);
		$a_tpl->parseCurrentBlock();
	}


	/**
	 * Check input, strip slashes etc. set alert, if input is not ok.
	 *
	 * @return    boolean        Input ok, true/false
	 */
	function checkInput(): bool
    {
		return true;
	}


	/**
	 * @return string
	 */
	public function getUrl() {
		return $this->url;
	}


	/**
	 * @param string $url
	 */
	public function setUrl($url) {
		$this->url = $url;
	}


	/**
	 * @return string
	 */
	public function getChunkSize() {
		return $this->chunk_size;
	}


	/**
	 * @param string $chunk_size
	 */
	public function setChunkSize($chunk_size) {
		$this->chunk_size = $chunk_size;
	}


	/**
	 * @return boolean
	 */
	public function isUniqueNames() {
		return $this->unique_names;
	}


	/**
	 * @param boolean $unique_names
	 */
	public function setUniqueNames($unique_names) {
		$this->unique_names = $unique_names;
	}


	/**
	 * @return string
	 */
	public function getMaxFileSize() {
		return $this->max_file_size;
	}


	/**
	 * @param string $max_file_size
	 */
	public function setMaxFileSize($max_file_size) {
		$this->max_file_size = $max_file_size;
	}


	/**
	 * @return boolean
	 */
	public function isLog() {
		return $this->log;
	}


	/**
	 * @param boolean $log
	 */
	public function setLog($log) {
		$this->log = $log;
	}


	/**
	 * @return string
	 */
	public function getFormId() {
		return $this->form_id;
	}


	/**
	 * @param string $form_id
	 */
	public function setFormId($form_id) {
		$this->form_id = $form_id;
	}


	/**
	 * @return array
	 */
	public function getMimeTypes() {
		return $this->mime_types;
	}


	/**
	 * @param array $mime_types
	 */
	public function setMimeTypes($mime_types) {
		$this->mime_types = $mime_types;
	}
}

/**
 * Class plupload
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class xoctPlupload {

	/**
	 * @var bool
	 */
	protected $finished = false;
	/**
	 * @var string
	 */
	protected $target_dir = '';
	/**
	 * @var string
	 */
	protected $file_path = '';
	/**
	 * @var bool
	 */
	protected $clean_up = false;


	/**
	 * xoctPlupload constructor.
	 */
	public function __construct() {
		$this->setTargetDir(ilUtil::getDataDir() . "/temp/plupload");
	}


	/**
	 * @return boolean
	 */
	public function isFinished() {
		return $this->finished;
	}


	/**
	 * @param boolean $finished
	 */
	public function setFinished($finished) {
		$this->finished = $finished;
	}


	/**
	 * @return string
	 */
	public function getFilePath() {
		return $this->file_path;
	}


	/**
	 * @param string $file_path
	 */
	public function setFilePath($file_path) {
		$this->file_path = $file_path;
	}


	/**
	 * @return string
	 */
	public function getTargetDir() {
		return $this->target_dir;
	}


	/**
	 * @param string $target_dir
	 */
	public function setTargetDir($target_dir) {
		$this->target_dir = $target_dir;
	}


	/**
	 * @return boolean
	 */
	public function isCleanUp() {
		return $this->clean_up;
	}


	/**
	 * @param boolean $clean_up
	 */
	public function setCleanUp($clean_up) {
		$this->clean_up = $clean_up;
	}


	public function handleUpload() {
		$this->setHeaders();

		// 15 minutes execution time
		@set_time_limit(15 * 60);

		// Settings
		$targetDir = $this->getTargetDir();

		// Create target dir
		if (!file_exists($targetDir)) {
			if (!mkdir($targetDir, 0777, true)) {
				throw new ilException('Could not create directory');
			}
		}

		// Get a file name
		if (isset($_REQUEST["name"])) {
			$fileName = $_REQUEST["name"];
		} elseif (!empty($_FILES)) {
			$fileName = $_FILES["file"]["name"];
		} else {
			$fileName = uniqid("file_");
		}

		$filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;
		global $DIC;
		$ilLog = $DIC['ilLog'];
		$ilLog->write('plupload chunks');
		$ilLog->write($filePath);
		$this->setFilePath($filePath);

		// Chunking might be enabled
		$chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
		$chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;

		// Open temp file
		if (!$out = @fopen("{$filePath}.part", $chunks ? "ab" : "wb")) {
			die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
		}

		if (!empty($_FILES)) {
			if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
				die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
			}

			// Read binary input stream and append it to temp file
			if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {
				die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
			}
		} else {
			if (!$in = @fopen("php://input", "rb")) {
				die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
			}
		}

		while ($buff = fread($in, 4096)) {
			fwrite($out, $buff);
		}

		@fclose($out);
		@fclose($in);

		// Check if file has been uploaded
		if (!$chunks || $chunk == $chunks - 1) {
			// Strip the temp .part suffix off
			rename("{$filePath}.part", $filePath);
		}

		// Return Success JSON-RPC response

		die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
	}


	protected function setHeaders() {
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
	}
}

/**
 * Class xoctPluploadException
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class xoctPluploadException extends xvmpException {

	/**
	 * @param string $code
	 * @param string $additional_message
	 */
	public function __construct($code, $additional_message) {
		parent::__construct($code, $additional_message);
	}
}
