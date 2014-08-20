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
		'Email' => 'Varchar(255)',
		'Anchor' => 'Varchar(255)',
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
		'URL' => 'URL',
		'Email' => 'Email address',
		'File' => 'File on this website',
		'SiteTree' => 'Page on this website'
	);


	public function getCMSFields(){
		$fields = parent::getCMSFields();

        $types = $this->config()->get('types');
        $i18nTypes = array();
        foreach ($types as $key => $label) {
            $i18nTypes[$key] = _t('Linkable.TYPE'.strtoupper($key), $label);
        }

		$fields->dataFieldByName('Title')->setTitle(_t('Linkable.TITLE', 'Title'))->setRightTitle(_t('Linkable.OPTIONALTITLE', 'Optional. Will be auto-generated from link if left blank'));
		$fields->replaceField('Type', DropdownField::create('Type', _t('Linkable.LINKTYPE', 'Link Type'), $i18nTypes)->setEmptyString(' ')->addExtraClass('no-chzn'), 'OpenInNewWindow');
		$fields->replaceField('File', TreeDropdownField::create('FileID', _t('Linkable.FILE', 'File'), 'File', 'ID', 'Title'), 'OpenInNewWindow');
		
		$fields->addFieldToTab('Root.Main', TreeDropdownField::create('SiteTreeID', _t('Linkable.PAGE', 'Page'), 'SiteTree'));

		$fields->addFieldToTab('Root.Main', $newWindow = CheckboxField::create('OpenInNewWindow', _t('Linkable.OPENINNEWWINDOW', 'Open link in a new window')));
		$newWindow->displayIf('Type')->isNotEmpty();
		
		$fields->dataFieldByName('URL')->displayIf("Type")->isEqualTo("URL");
		$fields->dataFieldByName('Email')->setTitle(_t('Linkable.EMAILADDRESS', 'Email Address'))->displayIf("Type")->isEqualTo("Email");
		$fields->dataFieldByName('FileID')->displayIf("Type")->isEqualTo("File");
		$fields->dataFieldByName('SiteTreeID')->displayIf("Type")->isEqualTo("SiteTree");

		if($this->SiteTreeID && !$this->SiteTree()->isPublished()){
			$fields->dataFieldByName('SiteTreeID')->setRightTitle(_t('Linkable.DELETEDWARNING', 'Warning: The selected page appears to have been deleted or unpublished. This link may not appear or may be broken in the frontend'));			
		}	

		$fields->addFieldToTab('Root.Main', $anchor = TextField::create('Anchor', _t('Linkable.ANCHOR', 'Anchor')), 'OpenInNewWindow');
		$anchor->setRightTitle('Include # at the start of your anchor name');
		$anchor->displayIf("Type")->isEqualTo("SiteTree");	

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
			if($this->Type == 'URL' || $this->Type == 'Email'){
				$this->Title = $this->{$this->Type};
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
		if($url = $this->getLinkURL()){
			$title = $this->Title ? $this->Title : $url; // legacy
			$target = $this->getTargetAttr();
			return "<a href='$url' $target>$title</a>";	
		}
	}


	/**
	 * Works out what the URL for this link should be based on it's Type
	 * @return String
	 **/
	public function getLinkURL(){
		if(!$this->ID) return;
		if($this->Type == 'URL'){
			return $this->URL;
		}elseif($this->Type == 'Email'){
			return $this->Email ? "mailto:$this->Email" : null;
		}else{
			if($this->Type && $component = $this->getComponent($this->Type)){
				if(!$component->exists()){
					return false;
				} 

				if($component->hasMethod('Link')){
					return $component->Link() . $this->Anchor;
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
				$message = _t('Linkable.VALIDATIONERROR_EMPTYURL', 'You must enter a URL for a link type of "URL"');
			}else{
				$allowedFirst = array('#', '/');
				if(!in_array(substr($this->URL, 0, 1), $allowedFirst) && !filter_var($this->URL, FILTER_VALIDATE_URL)){
					$valid = false;
					$message = _t('Linkable.VALIDATIONERROR_VALIDURL', 'Please enter a valid URL. Be sure to include http:// for an external URL. Or begin your internal url/anchor with a "/" character');
				}
			}
		}elseif($this->Type == 'Email'){
			if($this->Email ==''){
				$valid = false;
				$message = _t('Linkable.VALIDATIONERROR_EMPTYEMAIL', 'You must enter an Email Address for a link type of "Email"');
			}else{
				if(!filter_var($this->Email, FILTER_VALIDATE_EMAIL)){
					$valid = false;
					$message = _t('Linkable.VALIDATIONERROR_VALIDEMAIL', 'Please enter a valid Email address');
				}
			}
		}else{
			if($this->Type && !$this->getComponent($this->Type)->exists()){
				$valid = false;
				$message = _t('Linkable.VALIDATIONERROR_OBJECT', "Please select a {value} object to link to", array('value' => $this->Type));
			}
		}

		$result = ValidationResult::create($valid, $message);
		$this->extend('validate', $result);
		return $result;
	}



}
