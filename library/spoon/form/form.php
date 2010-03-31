<?php

/**
 * Spoon Library
 *
 * This source file is part of the Spoon Library. More information,
 * documentation and tutorials can be found @ http://www.spoon-library.be
 *
 * @package		spoon
 * @subpackage	form
 *
 *
 * @author		Davy Hellemans <davy@spoon-library.be>
 * @author 		Tijs Verkoyen <tijs@spoon-library.be>
 * @author		Dave Lens <dave@spoon-library.be>
 * @since		0.1.1
 */


/**
 * The class that handles forms.
 *
 * @package		spoon
 * @subpackage	form
 *
 *
 * @author		Davy Hellemans <davy@spoon-library.be>
 * @since		0.1.1
 */
class SpoonForm
{
	/**
	 * Action the form goes to
	 *
	 * @var	string
	 */
	private $action;


	/**
	 * Form status
	 *
	 * @var	bool
	 */
	private $correct = true;


	/**
	 * Errors (optional)
	 *
	 * @var	string
	 */
	private $errors;


	/**
	 * Allowed field in the $_POST or $_GET array
	 *
	 * @var	array
	 */
	private $fields = array();


	/**
	 * Form method
	 *
	 * @var	string
	 */
	private $method = 'post';


	/**
	 * Name of the form
	 *
	 * @var	string
	 */
	private $name;


	/**
	 * List of added objects
	 *
	 * @var	array
	 */
	private $objects = array();


	/**
	 * Extra parameters for the form tag
	 *
	 * @var	array
	 */
	private $parameters = array();


	/**
	 * Already validated?
	 *
	 * @var	bool
	 */
	private $validated = false;


	/**
	 * Class constructor.
	 *
	 * @return	void
	 * @param	string $name
	 * @param	string[optional] $action
	 * @param	string[optional] $method
	 */
	public function __construct($name, $action = null, $method = 'post')
	{
		// required field
		$this->setName($name);
		$this->add(new SpoonFormHidden('form', $this->name));

		// optional fields
		$this->setAction($action);
		$this->setMethod($method);
	}


	/**
	 * Add one or more objects to the stack.
	 *
	 * @return	void
	 * @param	object $object
	 */
	public function add($object)
	{
		// more than one argument
		if(func_num_args() != 0)
		{
			// iterate arguments
			foreach(func_get_args() as $argument)
			{
				// array of objects
				if(is_array($argument)) foreach($argument as $object) $this->add($object);

				// object
				else
				{
					// not an object
					if(!is_object($argument)) throw new SpoonFormException('The provided argument is not a valid object.');

					// valid object
					$this->objects[$argument->getName()] = $argument;
					$this->objects[$argument->getName()]->setFormName($this->name);
					$this->objects[$argument->getName()]->setMethod($this->method);

					// automagically add enctype if needed & not already added
					if($argument instanceof SpoonFormFile && !isset($this->parameters['enctype'])) $this->setParameter('enctype', 'multipart/form-data');
				}
			}
		}
	}


	/**
	 * Adds a single button.
	 *
	 * @return	void
	 * @param	string $name
	 * @param	string $value
	 * @param	string[optional] $type
	 * @param	string[optional] $class
	 */
	public function addButton($name, $value, $type = null, $class = 'inputButton')
	{
		// add element
		$this->add(new SpoonFormButton($name, $value, $type, $class));

		// return the element
		return $this->getField($name);
	}


	/**
	 * Adds a single checkbox.
	 *
	 * @return	void
	 * @param	string $name
	 * @param	bool[optional] $checked
	 * @param	string[optional] $class
	 * @param	string[optional] $classError
	 */
	public function addCheckbox($name, $checked = false, $class = 'inputCheckbox', $classError = 'inputCheckboxError')
	{
		// add element
		$this->add(new SpoonFormCheckbox($name, $checked, $class, $classError));

		// return element
		return $this->getField($name);
	}


	/**
	 * Adds one or more checkboxes.
	 *
	 * @return	void
	 */
	public function addCheckboxes()
	{
		// loop fields
		foreach(func_get_args() as $argument)
		{
			// not an array
			if(!is_array($argument)) $this->add(new SpoonFormCheckbox($argument));

			// array
			else
			{
				foreach($argument as $name => $checked) $this->add(new SpoonFormCheckbox($name, (bool) $checked));
			}
		}
	}


	/**
	 * Adds a single datefield.
	 *
	 * @return	void
	 * @param	string $name
	 * @param	int[optional] $value
	 * @param	string[optional] $mask
	 * @param	string[optional] $class
	 * @param	string[optional] $classError
	 */
	public function addDate($name, $value = null, $mask = null, $class = 'inputDate', $classError = 'inputDateError')
	{
		// add element
		$this->add(new SpoonFormDate($name, $value, $mask, $class, $classError));

		// return element
		return $this->getField($name);
	}


	/**
	 * Adds a single dropdown.
	 *
	 * @return	void
	 * @param	string $name
	 * @param	array $values
	 * @param	string[optional] $selected
	 * @param	bool[optional] $multipleSelection
	 * @param	string[optional] $class
	 * @param	string[optional] $classError
	 */
	public function addDropdown($name, array $values, $selected = null, $multipleSelection = false, $class = 'inputDropdown', $classError = 'inputDropdownError')
	{
		// add element
		$this->add(new SpoonFormDropdown($name, $values, $selected, $multipleSelection, $class, $classError));

		// return element
		return $this->getField($name);
	}


	/**
	 * Adds an error to the main error stack.
	 *
	 * @return	void
	 * @param	string $error
	 */
	public function addError($error)
	{
		$this->errors .= trim((string) $error);
	}


	/**
	 * Adds a single file field.
	 *
	 * @return	void
	 * @param	string $name
	 * @param	string[optional] $class
	 * @param	string[optional] $classError
	 */
	public function addFile($name, $class = 'inputFile', $classError = 'inputFileError')
	{
		// add element
		$this->add(new SpoonFormFile($name, $class, $classError));

		// return element
		return $this->getField($name);
	}


	/**
	 * Adds one or more file fields.
	 *
	 * @return	void
	 */
	public function addFiles()
	{
		foreach(func_get_args() as $argument) $this->add(new SpoonFormFile((string) $argument));
	}


	/**
	 * Adds a single hidden field.
	 *
	 * @return	void
	 * @param	string $name
	 * @param	string[optional] $value
	 */
	public function addHidden($name, $value = null)
	{
		// add element
		$this->add(new SpoonFormHidden($name, $value));

		// return element
		return $this->getField($name);
	}


	/**
	 * Adds one or more hidden fields.
	 *
	 * @return	void
	 */
	public function addHiddens()
	{
		// loop fields
		foreach(func_get_args() as $argument)
		{
			// not an array
			if(!is_array($argument)) $this->add(new SpoonFormHidden($argument));

			// array
			else
			{
				foreach($argument as $name => $defaultValue) $this->add(new SpoonFormHidden($name, $defaultValue));
			}
		}
	}


	/**
	 * Adds a single image field.
	 *
	 * @return	void
	 * @param	string $name
	 * @param	string[optional] $class
	 * @param	string[optional] $classError
	 */
	public function addImage($name, $class = 'inputFile', $classError = 'inputFileError')
	{
		// add element
		$this->add(new SpoonFormImage($name, $class, $classError));

		// return element
		return $this->getField($name);
	}


	/**
	 * Adds one or more image fields.
	 *
	 * @return	void
	 */
	public function addImages()
	{
		foreach(func_get_args() as $argument) $this->add(new SpoonFormImage((string) $argument));
	}


	/**
	 * Adds a single multiple checkbox.
	 *
	 * @return	void
	 * @param	string $name
	 * @param	array $values
	 * @param	bool[optional] $checked
	 * @param	string[optional] $class
	 */
	public function addMultiCheckbox($name, array $values, $checked = null, $class = 'inputCheckbox')
	{
		// add element
		$this->add(new SpoonFormMultiCheckbox($name, $values, $checked, $class));

		// return element
		return $this->getField($name);
	}


	/**
	 * Adds a single password field.
	 *
	 * @return	void
	 * @param	string $name
	 * @param	string[optional] $value
	 * @param	int[optional] $maxlength
	 * @param	string[optional] $class
	 * @param	string[optional] $classError
	 * @param	bool[optional] $HTML
	 */
	public function addPassword($name, $value = null, $maxlength = null, $class = 'inputPassword', $classError = 'inputPasswordError', $HTML = false)
	{
		// add element
		$this->add(new SpoonFormPassword($name, $value, $maxlength, $class, $classError, $HTML));

		// return element
		return $this->getField($name);
	}


	/**
	 * Adds one or more password fields.
	 *
	 * @return	void
	 */
	public function addPasswords()
	{
		// loop fields
		foreach(func_get_args() as $argument)
		{
			// not an array
			if(!is_array($argument)) $this->add(new SpoonFormPassword($argument));

			// array
			else
			{
				foreach($argument as $name => $defaultValue) $this->add(new SpoonFormPassword($name, $defaultValue));
			}
		}
	}


	/**
	 * Adds a single radiobutton.
	 *
	 * @return	void
	 * @param	string $name
	 * @param	array $values
	 * @param	string[optional] $checked
	 * @param	string[optional] $class
	 */
	public function addRadiobutton($name, array $values, $checked = null, $class = 'inputRadiobutton')
	{
		// add element
		$this->add(new SpoonFormRadiobutton($name, $values, $checked, $class));

		// return element
		return $this->getField($name);
	}


	/**
	 * Adds a single textarea.
	 *
	 * @return	void
	 * @param	string $name
	 * @param	string[optional] $value
	 * @param	string[optional] $class
	 * @param	string[optional] $classError
	 * @param	bool[optional] $HTML
	 */
	public function addTextarea($name, $value = null, $class = 'inputTextarea', $classError = 'inputTextareaError', $HTML = false)
	{
		// add element
		$this->add(new SpoonFormTextarea($name, $value, $class, $classError, $HTML));

		// return element
		return $this->getField($name);
	}


	/**
	 * Adds one or more textareas.
	 *
	 * @return	void
	 */
	public function addTextareas()
	{
		// loop fields
		foreach(func_get_args() as $argument)
		{
			// not an array
			if(!is_array($argument)) $this->add(new SpoonFormTextarea($argument));

			// array
			else
			{
				foreach($argument as $name => $defaultValue) $this->add(new SpoonFormTextarea($name, $defaultValue));
			}
		}
	}


	/**
	 * Adds a single textfield.
	 *
	 * @return	void
	 * @param	string $name
	 * @param	string[optional] $value
	 * @param	int[optional] $maxlength
	 * @param	string[optional] $class
	 * @param	string[optional] $classError
	 * @param	bool[optional] $HTML
	 */
	public function addText($name, $value = null, $maxlength = null, $class = 'inputText', $classError = 'inputTextError', $HTML = false)
	{
		// add element
		$this->add(new SpoonFormText($name, $value, $maxlength, $class, $classError, $HTML));

		// return element
		return $this->getField($name);
	}


	/**
	 * Adds one or more textfields.
	 *
	 * @return	void
	 */
	public function addTexts()
	{
		// loop fields
		foreach(func_get_args() as $argument)
		{
			// not an array
			if(!is_array($argument)) $this->add(new SpoonFormText($argument));

			// array
			else
			{
				foreach($argument as $name => $defaultValue) $this->add(new SpoonFormText($name, $defaultValue));
			}
		}
	}


	/**
	 * Adds a single timefield.
	 *
	 * @return	void
	 * @param	string $name
	 * @param	string[optional] $value
	 * @param	string[optional] $class
	 * @param	string[optional] $classError
	 */
	public function addTime($name, $value = null, $class = 'inputTime', $classError = 'inputTimeError')
	{
		// add element
		$this->add(new SpoonFormTime($name, $value, $class, $classError));

		// return element
		return $this->getField($name);
	}


	/**
	 * Adds one or more timefields.
	 *
	 * @return	void
	 */
	public function addTimes()
	{
		// loop fields
		foreach(func_get_args() as $argument)
		{
			// not an array
			if(!is_array($argument)) $this->add(new SpoonFormTime($argument));

			// array
			else
			{
				foreach($argument as $name => $defaultValue) $this->add(new SpoonFormTime($name, $defaultValue));
			}
		}
	}


	/**
	 * Loop all the fields and remove the ones that dont need to be in the form.
	 *
	 * @return	void
	 */
	public function cleanupFields()
	{
		// create list of fields
		foreach($this->objects as $object)
		{
			// file field should not be added since they are kept within the $_FILES
			if(!($object instanceof SpoonFormFile)) $this->fields[] = $object->getName();
		}

		/**
		 * The form key should always automagically be added since the
		 * isSubmitted method counts on this field to check whether or
		 * not the form has been submitted
		 */
		if(!in_array('form', $this->fields)) $this->fields[] = 'form';

		// post method
		if($this->method == 'post')
		{
			// delete unwanted keys
			foreach($_POST as $key => $value) if(!in_array($key, $this->fields)) unset($_POST[$key]);

			// create needed keys
			foreach($this->fields as $field) if(!isset($_POST[$field])) $_POST[$field] = '';
		}

		// get method
		else
		{
			// delete unwanted keys
			foreach($_GET as $key => $value) if(!in_array($key, $this->fields)) unset($_GET[$key]);

			// create needed keys
			foreach($this->fields as $field) if(!isset($_GET[$field])) $_GET[$field] = '';
		}
	}


	/**
	 * Retrieve the action.
	 *
	 * @return	string
	 */
	public function getAction()
	{
		return $this->action;
	}


	/**
	 * Retrieve the errors.
	 *
	 * @return	string
	 */
	public function getErrors()
	{
		return $this->errors;
	}


	/**
	 * Fetches a field.
	 *
	 * @return	SpoonFormElement
	 * @param	string $name
	 */
	public function getField($name)
	{
		// doesn't exist?
		if(!isset($this->objects[(string) $name])) throw new SpoonFormException('The field "'. (string) $name .'" does not exist.');

		// all is fine
		return $this->objects[(string) $name];
	}


	/**
	 * Retrieve all fields.
	 *
	 * @return	array
	 */
	public function getFields()
	{
		return $this->objects;
	}


	/**
	 * Retrieve the method post/get.
	 *
	 * @return	string
	 */
	public function getMethod()
	{
		return $this->method;
	}


	/**
	 * Retrieve the name of this form.
	 *
	 * @return	string
	 */
	public function getName()
	{
		return $this->name;
	}


	/**
	 * Retrieve the parameters.
	 *
	 * @return	array
	 */
	public function getParameters()
	{
		return $this->parameters;
	}


	/**
	 * Retrieve the parameters as html.
	 *
	 * @return	string
	 */
	public function getParametersHTML()
	{
		// start html
		$HTML = '';

		// build & return html
		foreach($this->parameters as $key => $value) $HTML .= ' '. $key .'="'. $value .'"';
		return $HTML;
	}


	/**
	 * Generates an example template, based on the elements already added.
	 *
	 * @return	string
	 */
	public function getTemplateExample()
	{
		// start form
		$value = "\n";
		$value .= '{form:'. $this->name ."}\n";

		/**
		 * At first all the hidden fields need to be added to this form, since
		 * they're not shown and are best to be put right beneath the start of the form tag.
		 */
		foreach($this->objects as $object)
		{
			// is a hidden field
			if(($object instanceof SpoonFormHidden) && $object->getName() != 'form')
			{
				$value .= "\t". '{$hid'. SpoonFilter::toCamelCase($object->getName()) ."}\n";
			}
		}

		/**
		 * Add all the objects that are NOT hidden fields. Based on the existance of some methods
		 * errors will or will not be shown.
		 */
		foreach($this->objects as $object)
		{
			// NOT a hidden field
			if(!($object instanceof SpoonFormHidden))
			{
				// buttons
				if($object instanceof SpoonFormButton)
				{
					$value .= "\t<p>{\$btn". SpoonFilter::toCamelCase($object->getName()) ."}</p>\n";
				}

				// single checkboxes
				elseif($object instanceof SpoonFormCheckbox)
				{
					$value .= "\t". '<label for="'. $object->getAttribute('id') .'">'. SpoonFilter::toCamelCase($object->getName()) ."</label>\n";
					$value .= "\t<p>\n";
					$value .= "\t\t{\$chk". SpoonFilter::toCamelCase($object->getName()) ."}\n";
					$value .= "\t\t{\$chk". SpoonFilter::toCamelCase($object->getName()) ."Error}\n";
					$value .= "\t</p>\n";
				}

				// multi checkboxes
				elseif($object instanceof SpoonFormMultiCheckbox)
				{
					$value .= "\t<p>\n";
					$value .= "\t\t". SpoonFilter::toCamelCase($object->getName()) ."<br />\n";
					$value .= "\t\t{iteration:". $object->getName() ."}\n";
					$value .= "\t\t\t". '<label for="{$'. $object->getName() .'.id}">{$'. $object->getName() .'.chk'. SpoonFilter::toCamelCase($object->getName()) .'} {$'. $object->getName() .'.label}</label>' ."\n";
					$value .= "\t\t{/iteration:". $object->getName() ."}\n";
					$value .= "\t\t". '{$chk'. SpoonFilter::toCamelCase($object->getName()) ."Error}\n";
					$value .= "\t<p>\n";
				}

				// dropdowns
				elseif($object instanceof SpoonFormDropdown)
				{
					$value .= "\t". '<label for="'. $object->getAttribute('id') .'">'. SpoonFilter::toCamelCase($object->getName()) ."</label>\n";
					$value .= "\t<p>\n";
					$value .= "\t\t". '{$ddm'. SpoonFilter::toCamelCase($object->getName()) ."}\n";
					$value .= "\t\t". '{$ddm'. SpoonFilter::toCamelCase($object->getName()) ."Error}\n";
					$value .= "\t</p>\n";
				}

				// filefields
				elseif($object instanceof SpoonFormFile)
				{
					$value .= "\t". '<label for="'. $object->getAttribute('id') .'">'. SpoonFilter::toCamelCase($object->getName()) ."</label>\n";
					$value .= "\t<p>\n";
					$value .= "\t\t". '{$file'. SpoonFilter::toCamelCase($object->getName()) ."}\n";
					$value .= "\t\t". '{$file'. SpoonFilter::toCamelCase($object->getName()) ."Error}\n";
					$value .= "\t</p>\n";
				}

				// radiobuttons
				elseif($object instanceof SpoonFormRadiobutton)
				{
					$value .= "\t<p>\n";
					$value .= "\t\t". SpoonFilter::toCamelCase($object->getName()) ."<br />\n";
					$value .= "\t\t{iteration:". $object->getName() ."}\n";
					$value .= "\t\t\t". '<label for="{$'. $object->getName() .'.id}">{$'. $object->getName() .'.rbt'. SpoonFilter::toCamelCase($object->getName()) .'} {$'. $object->getName() .'.label}</label>' ."\n";
					$value .= "\t\t{/iteration:". $object->getName() ."}\n";
					$value .= "\t\t". '{$rbt'. SpoonFilter::toCamelCase($object->getName()) ."Error}\n";
					$value .= "\t<p>\n";
				}

				// textfields
				elseif(($object instanceof SpoonFormDate) || ($object instanceof SpoonFormPassword) || ($object instanceof SpoonFormTextarea) || ($object instanceof SpoonFormText) || ($object instanceof SpoonFormTime))
				{
					$value .= "\t". '<label for="'. $object->getAttribute('id') .'">'. SpoonFilter::toCamelCase($object->getName()) ."</label>\n";
					$value .= "\t<p>\n";
					$value .= "\t\t". '{$txt'. SpoonFilter::toCamelCase($object->getName()) ."}\n";
					$value .= "\t\t". '{$txt'. SpoonFilter::toCamelCase($object->getName()) ."Error}\n";
					$value .= "\t</p>\n";
				}
			}
		}

		// close form tag
		return $value .'{/form:'. $this->name .'}';
	}


	/**
	 * Fetches all the values for this form as key/value pairs.
	 *
	 * @return	array
	 * @param	mixed[optional] $excluded
	 */
	public function getValues($excluded = null)
	{
		// redefine var
		$excluded = array();

		// has arguments
		if(func_num_args() != 0)
		{
			// iterate arguments
			foreach(func_get_args() as $argument)
			{
				if(is_array($argument)) foreach($argument as $value) $excluded[] = (string) $value;
				else $excluded[] = (string) $argument;
			}
		}

		// values
		$values = array();

		// loop objects
		foreach($this->objects as $object)
		{
			if(method_exists($object, 'getValue') && !in_array($object->getName(), $excluded)) $values[$object->getName()] = $object->getValue();
		}

		// return data
		return $values;
	}


	/**
	 * Returns the form's status.
	 *
	 * @return	bool
	 */
	public function isCorrect()
	{
		// not parsed
		if(!$this->validated) $this->validate();

		// return current status
		return $this->correct;
	}


	/**
	 * Returns whether this form has been submitted.
	 *
	 * @return	bool
	 */
	public function isSubmitted()
	{
		// default array
		$aForm = array();

		// post
		if($this->method == 'post' && isset($_POST)) $aForm = $_POST;

		// get
		elseif($this->method == 'get' && isset($_GET)) $aForm = $_GET;

		// name given
		if($this->name != '' && isset($aForm['form']) && $aForm['form'] == $this->name) return true;

		// no name given
		elseif($this->name == '' && $_SERVER['REQUEST_METHOD'] == strtoupper($this->method)) return true;

		// everything else
		return false;
	}


	/**
	 * Parse this form in the given template.
	 *
	 * @return	void
	 * @param	SpoonTemplate $template
	 */
	public function parse(SpoonTemplate $template)
	{
		// loop objects
		foreach($this->objects as $name => $object) $object->parse($template);

		// parse form tag
		$template->addForm($this);
	}


	/**
	 * Set the action.
	 *
	 * @return	void
	 * @param	string $action
	 */
	public function setAction($action)
	{
		$this->action = (string) $action;
	}


	/**
	 * Sets the correct value.
	 *
	 * @return	void
	 * @param	bool[optional] $correct
	 */
	private function setCorrect($correct = true)
	{
		$this->correct = (bool) $correct;
	}


	/**
	 * Set the form method.
	 *
	 * @return	void
	 * @param	string[optional] $method
	 */
	public function setMethod($method = 'post')
	{
		$this->method = SpoonFilter::getValue((string) $method, array('get', 'post'), 'post');
	}


	/**
	 * Set the name.
	 *
	 * @return	void
	 * @param	string $name
	 */
	private function setName($name)
	{
		$this->name = (string) $name;
	}


	/**
	 * Set a parameter for the form tag.
	 *
	 * @return	void
	 * @param	string $key
	 * @param	string $value
	 */
	public function setParameter($key, $value)
	{
		$this->parameters[(string) $key] = (string) $value;
	}


	/**
	 * Set multiple form parameters.
	 *
	 * @return	void
	 * @param	array $parameters
	 */
	public function setParameters(array $parameters)
	{
		foreach($parameters as $key => $value) $this->setParameter($key, $value);
	}


	/**
	 * Validates the form. This is an alternative for isCorrect, but without retrieve the status of course.
	 *
	 * @return	void
	 */
	public function validate()
	{
		// not parsed
        if(!$this->validated)
        {
        	// define errors
        	$errors = '';

			// loop objecjts
			foreach($this->objects as $oElement)
			{
				// check, since some objects don't have this method!
				if(method_exists($oElement, 'getErrors')) $errors .= $oElement->getErrors();
			}

			// affect correct status
			if(trim($errors) != '') $this->correct = false;

            // main form errors?
            if(trim($this->getErrors()) != '') $this->correct = false;

            // update parsed status
            $this->validated = true;
        }
	}
}


/**
 * This exception is used to handle form related exceptions.
 *
 * @package		spoon
 * @subpackage	form
 *
 *
 * @author		Davy Hellemans <davy@spoon-library.be>
 * @since		0.1.1
 */
class SpoonFormException extends SpoonException {}

?>