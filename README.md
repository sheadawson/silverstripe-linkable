# SilverStripe Linkable

## Requirements

* SilverStripe 4.x
* [Display Logic](https://github.com/unclecheese/silverstripe-display-logic)

See 1.x branch/releases for SilverStripe 3.x support

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
class Page extends SiteTree
{
	private static $has_one = [
		'ExampleLink' => 'Link',
	];

	public function getCMSFields()
	{
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

### Adding custom class to link

The anchor tag can be rendered with a class or classes of your choosing by passing the class string to the `setCSSClass()` method within your template.

```html
$ExampleLink.setCSSClass(your-css-class)
```

### Customising link templates

Link tags are rendered with the Link.ss template. You can override this template by copying it into your theme or project folder and modifying as required.

You can also specify a custom template to render any Link with by calling the renderWith function and passing in the name of your custom template

```html
$ExampleLink.renderWith(Link_button)
```

Finally, you can optionally offer CMS users the ability to select from a list of templates, allowing them to choose how their Link should be rendered. To enable this feature, create your custom template files and register them in your site config.yml file as below.

```YAML
Sheadawson\Linkable\Models\Link:
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
Sheadawson\Linkable\Models\Link:
  allowed_types:
    - URL
    - SiteTree
```


The default types available are:

```YAML
URL: URL
Email: Email address
Phone: Phone number
File: File on this website
SiteTree: Page on this website
```

### Adding custom Link types

Sometimes you might have custom DataObject types that you would like CMS users to be able to create Links to. This can be achieved by adding a DataExtension to the Link DataObject, see the below example for making Product objects Linkable.

```php
class CustomLink extends DataExtension
{
    private static $has_one = [
        'Product' => 'Product',
    ];

    private static $types = [
        'Product' => 'A Product on this site',
    ];

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
Sheadawson\Linkable\Models\Link:
  extensions:
    - CustomLink
```

Please see the [wiki](https://github.com/sheadawson/silverstripe-linkable/wiki) for more customisation examples.

## EmbeddedObject/Field

Use the EmbeddedObject/Field to easily add oEmbed content to a DataObject or Page.

### Example usage

```php
class Page extends SiteTre
 {
	private static $has_one = [
		'Video' => 'EmbeddedObject',
	];

	public function getCMSFields()
	{
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

## Custom query params

Sometimes you may want to add custom query params to the GET request which fetches the `LinkEditForm`.
This is very useful in a situation where you want to customise the form based on specific situation.
Custom query params are a way how to provide context for your `LinkEditForm`.

To add custom params you need to add `data-extra-query`.

```
$linkField->setAttribute('data-extra-query', '&param1=value1');
```

You can then use the `updateLinkForm` extension point and extract the param value with following code:

```
$param1 = Controller::curr()->getRequest()->requestVar('param1');
```

## Development

Front end uses pre-processing and requires the use of `Yarn`.
