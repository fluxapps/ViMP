<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once __DIR__ . '/../vendor/autoload.php';
/**
 * Class ilObjViMP
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 *
 */
class ilObjViMP extends ilObjectPlugin implements ilLPStatusPluginInterface {

	protected function initType(): void
    {
		$this->setType(ilViMPPlugin::XVMP);
	}


	protected function doCreate(bool $clone_mode = false): void
    {
		$xvmpSettings = new xvmpSettings();
		$xvmpSettings->setObjId($this->getId());
		$xvmpSettings->create();
	}


	protected function doDelete(): void
    {
		xvmpSettings::find($this->getId())->delete();
		foreach (xvmpSelectedMedia::where(array('obj_id' => $this->getId()))->get() as $selected_media) {
			$selected_media->delete();
		}
		foreach (xvmpUserLPStatus::where(array('obj_id' => $this->getId()))->get() as $user_status) {
			$user_status->delete();
		}
		foreach (xvmpEventLog::where(array('obj_id' => $this->getId()))->get() as $event_log) {
			$event_log->delete();
		}
	}


	public function getLPCompleted(): array
    {
		return xvmpUserLPStatus::where(array(
			'status' => ilLPStatus::LP_STATUS_COMPLETED_NUM,
			'obj_id' => $this->getId()
		))->getArray(null, 'user_id');
	}


	public function getLPNotAttempted(): array
    {
		$operators = array(
			'status' => '!=',
			'obj_id' => '='
		);
		$other_than_not_attempted = xvmpUserLPStatus::where(array(
			'status' => ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM,
			'obj_id' => $this->getId()
		), $operators)->getArray(null, 'user_id');

		return array_diff(xvmp::getCourseMembers($this->getId(), false), $other_than_not_attempted);

	}


	public function getLPFailed(): array
    {
		return array(); // it's not possible to fail
	}


	public function getLPInProgress(): array
    {
		return xvmpUserLPStatus::where(array(
			'status' => ilLPStatus::LP_STATUS_IN_PROGRESS_NUM,
			'obj_id' => $this->getId()
		))->getArray(null, 'user_id');
	}


	public function getLPStatusForUser($a_user_id): int
    {
		$user_status = xvmpUserLPStatus::where(array(
			'user_id' => $a_user_id,
			'obj_id' => $this->getId()
		))->first();
		if ($user_status) {
			return $user_status->getStatus();
		}
		return ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
	}
}