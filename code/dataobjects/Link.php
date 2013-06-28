<?php
class Link extends DataObject{

	public static $db = array(
		'Title' => 'Varchar(255)',
		'Type' => 'Varchar',
		'URL' => 'Varchar(255)',
		'OpenInNewWindow' => 'Boolean'
	);

	public static $has_one = array(
		'File' => 'File',
		'SiteTree' => 'SiteTree'
	);

	public static $types = array(
		'URL' => 'External URL',
		'File' => 'File on this website',
		'SiteTree' => 'Page on this website'
	);


	public function getCMSFields(){
		$fields = parent::getCMSFields()->first()->Tabs()->First()->Fields();
		$fields->replaceField('Type', DropdownField::create('Type', 'Link Type', self::$types)->setEmptyString(' '));
		$fields->replaceField('SiteTreeID', TreeDropdownField::create('SiteTreeID', 'Page', 'SiteTree'));
		
		$fields->push(CheckboxField::create('OpenInNewWindow', 'Open link in a new window'));
		
		$fields->fieldByName('URL')->displayIf("Type")->isEqualTo("URL");
		$fields->fieldByName('File')->displayIf("Type")->isEqualTo("File");
		$fields->fieldByName('SiteTreeID')->displayIf("Type")->isEqualTo("SiteTree");

		$this->extend('updateCMSFields', $fields);

		return $fields;
	}


	public function forTemplate(){
		$url = $this->getLinkURL();
		$title = $this->Title ? $this->Title : $url; 
		$target = $this->OpenInNewWindow ? "target='_blank'" : '';
		return "<a href='$url' $target>$title</a>";
	}


	public function getLinkURL(){
		if($this->Type == 'URL'){
			return $this->URL;
		}else{
			if($this->dbObject($this->Type)){
				return $this->dbObject($this->Type)->Link();	
			}
		}
	}
}