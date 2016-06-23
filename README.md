# SilverStripe Linkable 1.2

## Requirements

* SilverStripe 3.2.x
* [Display Logic](https://github.com/unclecheese/silverstripe-display-logic)

See 1.1 branch/releases for SilverStripe 3.1 support

## Maintainers

* shea@livesource.co.nz

## Description

This module contains a couple of handy FormFields / DataObjects for managing external and internal links on DataObjects, including oEmbed links.

## Installation with [Composer](https://getcomposer.org/)

```
composer require "sheadawson/silverstripe-linkable"
```

## Link / LinkField

A Link Object can be linked to a URL, Email, Phone number, an internal Page or File in the SilverStripe instance. A DataObject, such as a Page can have many Link objects managed with a grid field, or one Link managed with LinkField.

### Example usage

```php
class Page extends SiteTree {

	private static $has_one = array(
		'ExampleLink' => 'Link'
	);

	public function getCMSFields(){
		$fields = parent::getCMSFields();

		$fields->addFieldToTab('Root.Link', LinkField::create('ExampleLinkID', 'Link to page or file'));

		return $fields;
	}
}
```

In your template, you can render the links anchor tag with

```html
$ExampleLink
```

### Custom links/tags

Roll your own tag, making sure that the url is set first to avoid broken links

```html
<% if $ExampleLink.LinkURL %>
	<% with ExampleLink %>
		<a href='{$LinkURL}'{$TargetAttr}{$ClassAttr}>{$Title}</a>
	<% end_with %>
<% end_if %>
```

### Reusable custom link styles/tags

Create a .ss file with the name Link_example.ss (replace "example" with your style name).

Link_iconbutton.ss

```html
<a href='{$LinkURL}'{$TargetAttr}{$ClassAttr}>
    <i class="fa fa-github" aria-hidden="true"></i>{$Title}
</a>
```

In your template, you can set the style to use by adding setStyle()

```html
$ExampleLink.setStyle('iconbutton')
```

### Link selectable styles

You can create styles for an administrator to select in a dropdown field.
To add these styles in to the dropdown, define them in your site config.yaml file.

```yaml
Link:
  styles:
    button: Button
    iconbutton: Button with icon
```

The example above will be rendered in Link_button.ss and Link_iconbutton.ss if available.
If the template isn't available it will fall back by adding the style as a class to Link.ss

## EmbeddedObject/Field

Use the EmbeddedObject/Field to easily add oEmbed content to a DataObject or Page.

### Example usage

```php
class Page extends SiteTree {

	private static $has_one = array(
		'Video' => 'EmbeddedObject'
	);

	public function getCMSFields(){
		$fields = parent::getCMSFields();

		$fields->addFieldToTab('Root.Video', EmbeddedObjectField::create('Video', 'Video from oEmbed URL', $this->Video()));

		return $fields;
	}
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
