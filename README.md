# SilverStripe Linkable

## Requirements

* SilverStripe 3.2.x
* [Display Logic](https://github.com/unclecheese/silverstripe-display-logic)

## Maintainers

* shea@silverstripe.com.au

## Description

This module contains a couple of handy FormFields / DataObjects for managing external and internal links on DataObjects, including oEmbed links.

## Installation with [Composer](https://getcomposer.org/)

```composer require "sheadawson/silverstripe-linkable"```

## Link / LinkField

A Link Object can be linked to a URL or, an internal Page or File in the SilverStripe instance. A DataObject, such as a Page can have many Link objects managed with a grid field, or one Link managed with LinkField. 

### Example usage

```php
class Page extends SiteTree{
	
	static $has_one = array(
		'ExampleLink' => 'Link'
	);		

	public function getCMSFields(){
		$fields = parent::getCMSFields();
		$fields->addFieldToTab('Root.Link', LinkField::create('ExampleLinkID', 'Link to page or file'));
	}

	...
```

In your template, you can render the links anchor tag with
	
	$ExampleLink 

Or roll your own tag, making sure that the url is set first to avoid broken links

```html
<% if $ExampleLink.LinkURL %>
	<a href="$ExampleLink.LinkURL" $ExampleLink.TargetAttr>$ExampleLink.Title</a>
<% end_if %>
```

## EmbeddedObject/Field

Use the EmbeddedObject/Field to easily add oEmbed content to a DataObject or Page. 

### Example usage

```php
class Page extends SiteTree{
	
	static $has_one = array(
		'Video' => 'EmbeddedObject'
	);		

	public function getCMSFields(){
		$fields = parent::getCMSFields();
		$fields->addFieldToTab('Root.Video', EmbeddedObjectField::create('Video', 'Video from oEmbed URL', $this->Video()));
		return $fields;
	}

	...
```

In your template, you can render the object with the name of the has_one relation

```html
$Video
```

You can also access other metadata on the object via

```html
<h1>$Video.Title</h1>
$Video.Description
$Video.ThumbURL
```

See EmbeddedObject.php for a list of properties saved available in $db.
