<?php
require_once("./Services/Form/classes/class.ilMultiSelectInputGUI.php");
require_once("./Services/User/classes/class.ilObjUser.php");

/**
 * Class ilMultiSelectSearchInputGUI
 *
 * @author: Oskar Truffer <ot@studer-raimann.ch>
 * @author: Martin Studer <ms@studer-raimann.ch>
 *
 */
class ilMultiSelectSearchInputGUI extends ilMultiSelectInputGUI
{
	/**
	 * @var string
	 */
	protected $width;

	/**
	 * @var string
	 */
	protected $height;

	/**
	 * @var string
	 */
	protected $css_class;

	/**
	 * @var int
	 */
	protected $minimum_input_length = 0;

	/**
	 * @var string
	 */
	protected $ajax_link;

	/**
	 * @var ilTemplate
	 */
	protected $input_template;

	public function __construct($title, $post_var){
		global $DIC;
		$tpl = $DIC['tpl'];
		$ilUser = $DIC['ilUser'];
		$lng = $DIC['lng'];

		if(substr($post_var, -2) != "[]")
			$post_var = $post_var."[]";
		parent::__construct($title, $post_var);

		$this->lng = $lng;
		$tpl->addJavaScript("./Customizing/global/plugins/Services/Repository/RepositoryObject/ViMP/templates/default/form/select2/select2.min.js");
		$tpl->addJavaScript("./Customizing/global/plugins/Services/Repository/RepositoryObject/ViMP/templates/default/form/select2/select2_locale_".$ilUser->getCurrentLanguage().".js");
		$tpl->addCss("./Customizing/global/plugins/Services/Repository/RepositoryObject/ViMP/templates/default/form/select2/select2.css");
		$this->setInputTemplate(new ilTemplate("tpl.multiple_select.html", true, true,"Customizing/global/plugins/Services/Repository/RepositoryObject/ViMP"));
		$this->setWidth("308px");
	}

	/**
	 * Check input, strip slashes etc. set alert, if input is not ok.
	 *
	 * @return	boolean		Input ok, true/false
	 */
	function checkInput()
	{
		global $DIC;
		$lng = $DIC['lng'];

		//var_dump($this->getValue());
		if ($this->getRequired() && count($this->getValue()) == 0)
		{
			$this->setAlert($lng->txt("msg_input_is_required"));

			return false;
		}
		return true;
	}

	public function getSubItems(){
		return array();
	}

	public function render(){
		$tpl = $this->getInputTemplate();
		$values = $this->getValue();
		$options = $this->getOptions();

        $postvar = $this->getPostVar();
        /*if(substr($postvar, -3) == "[]]")
        {
            $postvar = substr($postvar, 0, -3)."]";
        }*/

		$tpl->setVariable("POST_VAR", $postvar);

		//Multiselect Bugfix
		//$id = substr($this->getPostVar(), 0, -2);
		$tpl->setVariable("ID", $this->getFieldId());
        //$tpl->setVariable("ID", $this->getPostVar());

		$tpl->setVariable("WIDTH", $this->getWidth());
		$tpl->setVariable("HEIGHT", $this->getHeight());
		$tpl->setVariable("PLACEHOLDER", "");
		$tpl->setVariable("MINIMUM_INPUT_LENGTH", $this->getMinimumInputLength());
		$tpl->setVariable("Class", $this->getCssClass());

		if(isset($this->ajax_link)) {
			$tpl->setVariable("AJAX_LINK", $this->getAjaxLink());
		}

		if($this->getDisabled()) {
			$tpl->setVariable("ALL_DISABLED", "disabled=\"disabled\"");
		}

		if($options)
		{
			foreach($options as $option_value => $option_text)
			{
				$tpl->setCurrentBlock("item");
				if ($this->getDisabled())
				{
					$tpl->setVariable("DISABLED",
						" disabled=\"disabled\"");
				}
				if (in_array($option_value, $values))
				{
					$tpl->setVariable("SELECTED",
						"selected");
				}

				$tpl->setVariable("VAL", ilUtil::prepareFormOutput($option_value));
				$tpl->setVariable("TEXT", $option_text);
				$tpl->parseCurrentBlock();
			}
		}
		return $tpl->get();
	}

	/**
	 * @deprecated setting inline style items from the controller is bad practice. please use the setClass together with an appropriate css class.
	 * @param string $height
	 */
	public function setHeight($height)
	{
		$this->height = $height;
	}

	/**
	 * @return string
	 */
	public function getHeight()
	{
		return $this->height;

	}

	/**
	 * @deprecated setting inline style items from the controller is bad practice. please use the setClass together with an appropriate css class.
	 * @param string $width
	 */
	public function setWidth($width)
	{
		$this->width = $width;
	}

	/**
	 * @return string
	 */
	public function getWidth()
	{
		return $this->width;
	}

	/**
	 * @param string $css_class
	 */
	public function setCssClass($css_class)
	{
		$this->css_class = $css_class;
	}

	/**
	 * @return string
	 */
	public function getCssClass()
	{
		return $this->css_class;
	}

	/**
	 * @param int $minimum_input_length
	 */
	public function setMinimumInputLength($minimum_input_length)
	{
		$this->minimum_input_length = $minimum_input_length;
	}

	/**
	 * @return int
	 */
	public function getMinimumInputLength()
	{
		return $this->minimum_input_length;
	}

	/**
	 * @param string $ajax_link setting the ajax link will lead to ignoration of the "setOptions" function as the link given will be used to get the
	 */
	public function setAjaxLink($ajax_link)
	{
		$this->ajax_link = $ajax_link;
	}

	/**
	 * @return string
	 */
	public function getAjaxLink()
	{
		return $this->ajax_link;
	}

	/**
	 * @param \srDefaultAccessChecker $access_checker
	 */
	public function setAccessChecker($access_checker)
	{
		$this->access_checker = $access_checker;
	}/**
	 * @return \srDefaultAccessChecker
	 */
	public function getAccessChecker()
	{
		return $this->access_checker;
	}

	/**
	 * @param \ilTemplate $input_template
	 */
	public function setInputTemplate($input_template)
	{
		$this->input_template = $input_template;
	}

	/**
	 * @return \ilTemplate
	 */
	public function getInputTemplate()
	{
		return $this->input_template;
	}


	/**
	 * This implementation might sound silly. But the multiple select input used parses the post vars differently if you use ajax. thus we have to do this stupid "trick". Shame on select2 project ;)
	 * @return string the real postvar.
	 */
	protected function searchPostVar(){
		if(substr($this->getPostVar(), -2) == "[]")
			return substr($this->getPostVar(), 0 , -2);
		else
			return $this->getPostVar();
	}

	public function setValueByArray($array){
//		print_r($array);

		$val = $array[$this->searchPostVar()];
		if(is_array($val))
			$val;
		elseif(!$val)
			$val =  array();
		else
			$val = explode(",", $val);
		$this->setValue($val);
	}
}