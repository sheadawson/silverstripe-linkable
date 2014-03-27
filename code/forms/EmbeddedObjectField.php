<?php

/**
 * EmbeddedObjectField
 *
 * @package silverstripe-linkable
 * @license BSD License http://www.silverstripe.org/bsd-license
 * @author <marcus@silverstripe.com.au>
 **/
class EmbeddedObjectField extends FormField {
	
	private static $allowed_actions = array(
		'update'
	);

	protected $object;
	
	protected $message;
	
	public function setValue($value) {
		if ($value instanceof EmbeddedObject) {
			$this->object = $value;
			parent::setValue($value->toMap());
		}
		parent::setValue($value);
	}
	
	public function getMessage() {
		return $this->message;
	}
	
	public function FieldHolder($properties = array()) {
		Requirements::css(LINKABLE_PATH . '/css/embeddedobjectfield.css');
		Requirements::javascript(LINKABLE_PATH . '/javascript/embeddedobjectfield.js');
		
		if ($this->object && $this->object->ID) {
			$properties['SourceURL'] = TextField::create($this->getName() . '[sourceurl]', '')->setAttribute('placeholder', _t('Linkable.SOURCEURL', 'Source URL'));

			if (strlen($this->object->SourceURL)) {
				$properties['ObjectTitle'] = TextField::create($this->getName() . '[title]', _t('Linkable.TITLE', 'Title'));
				$properties['Width'] = TextField::create($this->getName() . '[width]', _t('Linkable.WIDTH', 'Width'));
				$properties['Height'] = TextField::create($this->getName() . '[height]', _t('Linkable.HEIGHT', 'Height'));
				$properties['ThumbURL'] = HiddenField::create($this->getName() . '[thumburl]', '');
				$properties['Type'] = HiddenField::create($this->getName() . '[type]', '');
				$properties['EmbedHTML'] = HiddenField::create($this->getName() . '[embedhtml]', '');
				$properties['ObjectDescription'] = TextAreaField::create($this->getName() . '[description]', _t('Linkable.DESCRIPTION', 'Description'));
				$properties['ExtraClass'] = TextField::create($this->getName() . '[extraclass]', _t('Linkable.CSSCLASS', 'CSS class'));

				foreach ($properties as $key => $field) {
					if ($key == 'ObjectTitle') {
						$key = 'Title';
					}elseif ($key == 'ObjectDescription') {
						$key = 'Description';
					}
					$field->setValue($this->object->$key);
				}

				if ($this->object->ThumbURL) {
					$properties['ThumbImage'] = LiteralField::create($this->getName(), '<img src="' . $this->object->ThumbURL . '" />');
				}
			}
		} else {
			$properties['SourceURL'] = TextField::create($this->getName() . '[sourceurl]','')->setAttribute('placeholder', _t('Linkable.SOURCEURL', 'Source URL'));
		}

		$field = parent::FieldHolder($properties);
		return $field;
	}

	public function saveInto(DataObjectInterface $record) {
		$val = $this->Value();
		
 		if (!$this->object) {
			$this->object = EmbeddedObject::create();
		}
		
		if (!strlen($val['sourceurl'])) {
			foreach ($val as $key => $null) {
				$val[$key] = '';
			}
		}
		
		$props = array_keys(Config::inst()->get('EmbeddedObject', 'db'));
		foreach ($props as $prop) {
			$this->object->$prop = isset($val[strtolower($prop)]) ? $val[strtolower($prop)] : null;	
		}

		$this->object->write();

		$field = $this->getName() . 'ID';
		$record->$field = $this->object->ID;
	}
	
	public function update(SS_HTTPRequest $request) {
		if (!SecurityToken::inst()->checkRequest($request)) {
			return '';
		}
		$url = $request->postVar('URL');
		if (strlen($url)) {
			$info = Oembed::get_oembed_from_url($url);
			if ($info && $info->exists()) {
				$object = EmbeddedObject::create();
				$object->Title = $info->title;
				$object->SourceURL = $url;
				$object->Width = $info->width;
				$object->Height = $info->height;
				$object->ThumbURL = $info->thumbnail_url;
				$object->Description = $info->description ? $info->description : $info->title;
				$object->Type = $info->type;
				$object->EmbedHTML = $info->forTemplate();
				$this->object = $object;
				// needed to make sure the check in FieldHolder works out
				$object->ID = -1;
				return $this->FieldHolder();
			} else {
				$this->message = _t('EmbeddedObjectField.ERROR', 'Could not look up provided URL: ' . Convert::raw2xml($url));
				return $this->FieldHolder();
			}
		}else{
			$this->object = null;
			return $this->FieldHolder();
		}
	}
}
