<?php

/**
 * Link
 *
 * @package silverstripe-linkable
 * @license BSD License http://www.silverstripe.org/bsd-license
 * @author <shea@silverstripe.com.au>
 **/
class Link extends DataObject{

	private static $db = array(
		'Title' => 'Varchar(255)',
		'Type' => 'Varchar',
		'URL' => 'Varchar(255)',
		'OpenInNewWindow' => 'Boolean'
	);

	private static $has_one = array(
		'File' => 'File',
		'SiteTree' => 'SiteTree'
	);

	private static $summary_fields = array(
		'Title',
		'LinkType',
		'LinkURL'
	);

	/**
	 * A map of object types that can be linked to
	 * Custom dataobjects can be added to this
	 * @var array
	 **/
	private static $types = array(
		'URL' => 'External URL',
		'File' => 'File on this website',
		'SiteTree' => 'Page on this website'
	);


	public function getCMSFields(){
		$fields = parent::getCMSFields();
		$fields->dataFieldByName('Title')->setRightTitle('Optional. Will be auto-generated from link if left blank');
		$fields->replaceField('Type', DropdownField::create('Type', 'Link Type', $this->config()->get('types'))->setEmptyString(' '), 'OpenInNewWindow');
		$fields->replaceField('File', TreeDropdownField::create('FileID', 'File', 'File'), 'OpenInNewWindow');
		$fields->replaceField('SiteTreeID', TreeDropdownField::create('SiteTreeID', 'Page', 'SiteTree'), 'OpenInNewWindow');
		
		$fields->addFieldToTab('Root.Main', $newWindow = CheckboxField::create('OpenInNewWindow', 'Open link in a new window'));
		$newWindow->displayIf('Type')->isNotEmpty();
		
		$fields->dataFieldByName('URL')->displayIf("Type")->isEqualTo("URL");
		$fields->dataFieldByName('FileID')->displayIf("Type")->isEqualTo("File");
		$fields->dataFieldByName('SiteTreeID')->displayIf("Type")->isEqualTo("SiteTree");

		$this->extend('updateCMSFields', $fields);

		return $fields;
	}


	/**
	 * If the title is empty, set it to getLinkURL()
	 * @return String
	 **/
	public function onAfterWrite(){
		parent::onAfterWrite();
		if(!$this->Title){
			if($this->Type == 'URL'){
				$this->Title = $this->URL;
			}elseif($this->Type == 'SiteTree'){
				$this->Title = $this->SiteTree()->MenuTitle;
			}else{
				if($component = $this->getComponent($this->Type)){
					$this->Title = $component->Title;
				}
			}

			if(!$this->Title){
				$this->Title = 'Link-' . $this->ID;
			}

			$this->write();
		}

		

	}


	/**
	 * Renders an HTML anchor tag for this link
	 * @return String
	 **/
	public function forTemplate(){
		$url = $this->getLinkURL();
		$title = $this->Title ? $this->Title : $url; // legacy
		$target = $this->getTargetAttr();
		return "<a href='$url' $target>$title</a>";
	}


	/**
	 * Works out what the URL for this link should be based on it's Type
	 * @return String
	 **/
	public function getLinkURL(){
		if(!$this->ID) return;
		if($this->Type == 'URL'){
			return $this->URL;
		}else{
			if($this->Type && $component = $this->getComponent($this->Type)){
				if($component->hasMethod('Link')){
					return $component->Link();	
				}else{
					return "Please implement a Link() method on your dataobject \"$this->Type\"";
				}
			}
		}
	}


	/**
     * Gets the html target attribute for the anchor tag
     * @return String
     **/
    public function getTargetAttr(){
        return $this->OpenInNewWindow ? "target='_blank'" : '';
	}


	/**
	 * Gets the description label of this links type
	 * @return String
	 **/
	public function getLinkType(){
		$types = $this->config()->get('types');
		return isset($types[$this->Type]) ? $types[$this->Type] : null;
	}


	/**
	 * Validate
	 * @return ValidationResult
	 **/
	protected function validate(){
		$valid = true;
		$message = null;
		if($this->Type == 'URL'){
			if($this->URL ==''){
				$valid = false;
				$message = 'You must enter a URL for a link type of "URL"';
			}elseif(!filter_var($this->URL, FILTER_VALIDATE_URL)){
				$valid = false;
				$message = 'Please enter a valid URL and be sure to include http://';
			}
		}else{
			if(!$this->getComponent($this->Type)->exists()){
				$valid = false;
				$message = "Please select a {$this->Type} object to link to";
			}
		}
		$result = ValidationResult::create($valid, $message);
		$this->extend('validate', $result);
		return $result;
	}



}