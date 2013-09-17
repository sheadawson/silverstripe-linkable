<?php

/**
 * EmbeddedObject
 *
 * @package silverstripe-linkable
 * @license BSD License http://www.silverstripe.org/bsd-license
 * @author <marcus@silverstripe.com.au>
 **/
class EmbeddedObject extends DataObject {
	private static $db = array(
		'Title'				=> 'Varchar(255)',
		'Type'				=> 'Varchar',
		'SourceURL'			=> 'Varchar(255)',
		'Width'				=> 'Varchar',
		'Height'			=> 'Varchar',
		'Description'		=> 'HTMLText',
		'ThumbURL'			=> 'Varchar(255)',
		'ExtraClass'		=> 'Varchar(64)',
		'EmbedHTML'			=> 'Text',
	);
	
	public function Embed() {
		$options = array(
			'width'	=> $this->Width,
			'height' => $this->Height,
		);
		$result = Oembed::get_oembed_from_url($url, false, $options);
		return $result;
	}
	
	public function onBeforeWrite() {
		$changes = $this->getChangedFields();
		
		if (isset($changes['Width']) && $changes['Width']['before']) {
			$this->updateEmbedHTML();
		}
		
		if (isset($changes['Height']) && $changes['Height']['before']) {
			$this->updateEmbedHTML();
		}
		
		parent::onBeforeWrite();
		
	}

	public function updateEmbedHTML() {
		$options = array(
			'width'	=> $this->Width,
			'height' => $this->Height,
		);
		$info = Oembed::get_oembed_from_url($this->SourceURL, false, $options);
		if ($info && $info->exists()) {
			$this->EmbedHTML = $info->forTemplate();
		}
	}

	public function forTemplate() {
		switch($this->Type) {
			case 'video':
			case 'rich':
				if($this->ExtraClass) {
					return "<div class='$this->ExtraClass'>$this->EmbedHTML</div>";
				} else {
					return $this->EmbedHTML;
				}
				break;
			case 'link':
				return '<a class="' . $this->ExtraClass . '" href="' . $this->SourceURL . '">' . $this->Title . '</a>';
				break;
			case 'photo':
				return "<img src='$this->SourceURL' width='$this->Width' height='$this->Height' class='$this->ExtraClass' />";
				break;
		}
	}
}
