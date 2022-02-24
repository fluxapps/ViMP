<?php

class xvmpFileInputGUI extends ilFileInputGUI
{

    protected $download_url;

    public function setDownloadUrl(string $download_url)
    {
        $this->download_url = $download_url;
    }

    /**
     * Render html
     */
    public function render($a_mode = "")
    {
        $lng = $this->lng;

        $quota_exceeded = $quota_legend = false;
        if (self::$check_wsp_quota) {
            include_once "Services/DiskQuota/classes/class.ilDiskQuotaHandler.php";
            if (!ilDiskQuotaHandler::isUploadPossible()) {
                $lng->loadLanguageModule("file");
                $quota_exceeded = $lng->txt("personal_workspace_quota_exceeded_warning");
            } else {
                $quota_legend = ilDiskQuotaHandler::getStatusLegend();
            }
        }

        $f_tpl = new ilTemplate("tpl.prop_file.html", true, true, "Services/Form");


        // show filename selection if enabled
        if ($this->isFileNameSelectionEnabled()) {
            $f_tpl->setCurrentBlock('filename');
            $f_tpl->setVariable('POST_FILENAME', $this->getFileNamePostVar());
            $f_tpl->setVariable('VAL_FILENAME', $this->getFilename());
            $f_tpl->setVariable('FILENAME_ID', $this->getFieldId());
            $f_tpl->setVAriable('TXT_FILENAME_HINT', $lng->txt('if_no_title_then_filename'));
            $f_tpl->parseCurrentBlock();
        } else {
            if (trim($this->getValue() != "")) {
                if (!$this->getDisabled() && $this->getALlowDeletion()) {
                    $f_tpl->setCurrentBlock("delete_bl");
                    $f_tpl->setVariable("POST_VAR_D", $this->getPostVar());
                    $f_tpl->setVariable(
                        "TXT_DELETE_EXISTING",
                        $lng->txt("delete_existing_file")
                    );
                    $f_tpl->parseCurrentBlock();
                }

                $f_tpl->setCurrentBlock('prop_file_propval');
                /** BEGIN PATCH */
//                $f_tpl->setVariable('FILE_VAL', $this->getValue());
                try {
                    $value = $this->download_url ?
                        '<a href="data:text/vtt;base64,'
                        . base64_encode(xvmpRequest::get($this->download_url)->getResponseBody())
                        . '" target="blank" download="' . $this->getValue() . '">' . $this->getValue() . '</a>' :
                        $this->getValue();
                } catch (xvmpException $e) {
                    xvmpCurlLog::getInstance()->writeWarning('could not download subtitle file from '
                        . $this->download_url . ', message: ' . $e->getMessage());
                    $value = $this->getValue();
                }
                $f_tpl->setVariable('FILE_VAL', $value);
                /** END PATCH */
                $f_tpl->parseCurrentBlock();
            }
        }

        if ($a_mode != "toolbar") {
            if (!$quota_exceeded) {
                $this->outputSuffixes($f_tpl);

                $f_tpl->setCurrentBlock("max_size");
                $f_tpl->setVariable("TXT_MAX_SIZE", $lng->txt("file_notice") . " " .
                    $this->getMaxFileSizeString());
                $f_tpl->parseCurrentBlock();

                if ($quota_legend) {
                    $f_tpl->setVariable("TXT_MAX_SIZE", $quota_legend);
                    $f_tpl->parseCurrentBlock();
                }
            } else {
                $f_tpl->setCurrentBlock("max_size");
                $f_tpl->setVariable("TXT_MAX_SIZE", $quota_exceeded);
                $f_tpl->parseCurrentBlock();
            }
        } elseif ($quota_exceeded) {
            return $quota_exceeded;
        }

        $pending = $this->getPending();
        if ($pending) {
            $f_tpl->setCurrentBlock("pending");
            $f_tpl->setVariable("TXT_PENDING", $lng->txt("file_upload_pending") .
                ": " . $pending);
            $f_tpl->parseCurrentBlock();
        }

        if ($this->getDisabled() || $quota_exceeded) {
            $f_tpl->setVariable(
                "DISABLED",
                " disabled=\"disabled\""
            );
        }

        $f_tpl->setVariable("POST_VAR", $this->getPostVar());
        $f_tpl->setVariable("ID", $this->getFieldId());
        $f_tpl->setVariable("SIZE", $this->getSize());


        /* experimental: bootstrap'ed file upload */
        $f_tpl->setVariable("TXT_BROWSE", $lng->txt("select_file"));


        return $f_tpl->get();
    }
}