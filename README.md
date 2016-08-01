# SilverStripe Linkable 1.3

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

### Customising link templates

Link tags are rendered with the Link.ss template. You can override this template by copying it into your theme or project folder and modifying as required.

You can also specify a custom template to render any Link with by calling the renderWith function and passing in the name of your custom template

```html
$ExampleLink.renderWith(Link_button)
```

Finally, you can optionally offer CMS users the ability to select from a list of templates, allowing them to choose how their Link should be rendered. To enable this feature, create your custom template files and register them in your site config.yml file as below.

```YAML
Link:
  templates:
    button: Description of button template # looks for Link_button.ss template
    iconbutton: Description of iconbutton template # looks for  Link_iconbutton.ss template
```

### Limit allowed Link types

To limit link types for each field.

```php
LinkField::create('ExampleLinkID', 'Link Title')->setAllowedTypes(array('URL','Phone'))
```

You can also globally limit link types.  To limit types define them in your site config.yml file as below.

```YAML
Link:
  allowed_types:
    - URL
    - SiteTree
```

### Adding custom Link types

Sometimes you might have custom DataObject types that you would like CMS users to be able to create Links to. This can be achieved by adding a DataExtension to the Link DataObject, see the below example for making Product objects Linkable.

```php
class CustomLink extends DataExtension
{
    private static $has_one = array(
        'Product' => 'Product'
    );

    private static $types = array(
        'Product' => 'A Product on this site'
    );

    public function updateCMSFields(FieldList $fields)
    {
		// update the Link Type dropdown to contain your custom Link types
        $fields->dataFieldByName('Type')->setSource($this->owner->config()->types);

		// Add a dropdown field containing your ProductList
		$fields->addFieldToTab(
            'Root.Main',
            DropdownField::create('ProductID', 'Product', Product::get()->map('ID', 'Title')->toArray())
                ->setHasEmptyDefault(true)
                ->displayIf('Type')->isEqualTo('Product')->end()
        );
	}
```

In your config.yml

```YAML
Link:
  extensions:
    - CustomLink
```

Please see the [wiki](https://github.com/sheadawson/silverstripe-linkable/wiki) for more customisation examples.

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
