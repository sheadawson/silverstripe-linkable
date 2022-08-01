<?php

namespace Sheadawson\Linkable\Models;

use SilverStripe\Assets\File;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\TreeDropdownField;
use SilverStripe\ORM\FieldType\DBHTMLText;
use UncleCheese\DisplayLogic\Forms\Wrapper;
use SilverStripe\Core\Convert;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\ORM\DataObject;

/**
 * Class Link
 * @license BSD License http://www.silverstripe.org/bsd-license
 * @author <shea@silverstripe.com.au>
 * @property string Title
 * @property string Type
 * @property string URL
 * @property string Email
 * @property string Phone
 * @property bool OpenInNewWindow
 * @property string Template
 * @package Sheadawson\Linkable\Models
 */
class Link extends DataObject
{
    /**
     * @var string custom CSS classes for template
     */
    protected $cssClass;

    /**
     * @var array
     */
    private static $db = [
        'Title' => 'Varchar(255)',
        'Type' => 'Varchar',
        'URL' => 'Varchar(255)',
        'Email' => 'Varchar(255)',
        'Phone' => 'Varchar(255)',
        'OpenInNewWindow' => 'Boolean',
        'Template' => 'Varchar(255)',
    ];

    /**
     * @var array
     */
    private static $has_one = [
        'File' => File::class,
    ];

    /**
     * @var string
     */
    private static $table_name = 'LinkableLink';

    /**
     * @var array
     */
    private static $summary_fields = [
        'Title',
        'LinkType',
        'LinkURL',
    ];

    /**
     * @var array
     */
    private static $searchable_fields = [
        'Title' => 'PartialMatchFilter',
        'URL' => 'PartialMatchFilter',
        'Phone' => 'PartialMatchFilter',
        'Email' => 'PartialMatchFilter',
    ];

    /**
     * A map of templates that are available for rendering
     * Link objects with
     *
     * @var array
     */
    private static $templates = [];

    /**
     * A map of object types that can be linked to
     * Custom dataobjects can be added to this
     *
     * @var array
     */
    private static $types = [
        'URL' => 'URL',
        'Email' => 'Email address',
        'Phone' => 'Phone number',
        'File' => 'File on this website',
    ];

    /**
     * List the allowed included link types.  If null all are allowed.
     * global config
     *
     * @var array
     * @config
     */
    private static $allowed_types = null;

    private static $casting = [
        'ClassAttr' => 'HTMLFragment',
        'TargetAttr' => 'HTMLFragment',
    ];

    /**
     * List the allowed included link types.  If null all are allowed.
     * Instance specific override
     *
     * @var array
     */
    protected $allowed_types_override = null;

    /**
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = $this->scaffoldFormFields([
            // Don't allow has_many/many_many relationship editing before the record is first saved
            'includeRelations' => ($this->ID > 0),
            'tabbed' => true,
            'ajaxSafe' => true,
        ]);

        $fields->removeByName([
            'SiteTreeID',
            // seem to need to remove both of these for different SS versions...
            'FileID',
            'File',

            'Template',
            'Anchor',
        ]);

        // remove default fields
        $dbFields = $this->config()->get('db');

        if (!empty($dbFields) && is_array($dbFields)) {
            $fields->removeByName(array_keys($dbFields));
        }

        $templates = $this->config()->get('templates');

        if ($templates) {
            $i18nTemplates = [];

            foreach ($templates as $key => $label) {
                $i18nTemplates[$key] = _t('Linkable.STYLE' . strtoupper($key), $label);
            }

            $fields->addFieldToTab(
                'Root.Main',
                DropdownField::create(
                    'Template',
                    _t('Linkable.STYLE', 'Style'),
                    $i18nTemplates
                )->setEmptyString('Default')
            );
        }

        $fields->addFieldsToTab('Root.Main', [
            $title = TextField::create('Title'),
            $type = DropdownField::create(
                'Type',
                _t('Linkable.LINKTYPE', 'Link Type'),
                $this->getTypes()
            ),
            $url = TextField::create('URL', 'URL'),
            $file = Wrapper::create(
                TreeDropdownField::create(
                    'FileID',
                    _t('Linkable.FILE', 'File'),
                    File::class,
                    'ID',
                    'Title'
                )
            ),
            $email = TextField::create('Email', _t('Linkable.EMAILADDRESS', 'Email Address')),
            $phone = TextField::create('Phone', _t('Linkable.PHONENUMBER', 'Phone Number')),
            $openInNewWindow = CheckboxField::create(
                'OpenInNewWindow',
                _t('Linkable.OPENINNEWWINDOW', 'Open link in a new window')
            ),
        ]);

        $title
            ->setTitle(_t('Linkable.TITLE', 'Title'))
            ->setRightTitle(
                _t('Linkable.OPTIONALTITLE',
                    'Optional. Will be auto-generated from link if left blank')
            );

        $openInNewWindow
            ->displayIf('Type')->isEqualTo('URL')
            ->orIf()->isEqualTo('File')
            ->orIf()->isEqualTo('SiteTree')
            ->end();

        $file
            ->displayIf('Type')
            ->isEqualTo('File')
            ->end();

        $url
            ->displayIf('Type')
            ->isEqualTo('URL');

        $email
            ->displayIf('Type')
            ->isEqualTo('Email');

        $phone
            ->displayIf('Type')
            ->isEqualTo('Phone');

        $this->extend('updateCMSFields', $fields);

        return $fields;
    }

    /**
     * If the title is empty, set it to getLinkURL()
     */
    public function onAfterWrite()
    {
        parent::onAfterWrite();

        if (!$this->Title) {
            switch ($this->Type) {
                case 'URL':
                case 'Email':
                case 'Phone':
                    $this->Title = $this->{$this->Type};

                    break;
                case 'SiteTree':
                    $this->Title = $this->SiteTree()->MenuTitle;

                    break;
                default:
                    if ($this->Type && $component = $this->getComponent($this->Type)) {
                        $this->Title = $component->Title;
                    }

                    break;
            }

            if (!$this->Title) {
                $this->Title = 'Link-' . $this->ID;
            }

            $this->write();
        }
    }

    /**
     * @param string $class
     * @return $this
     */
    public function setCSSClass($class)
    {
        $this->cssClass = $class;

        return $this;
    }

    /**
     * Sets allowed link types
     *
     * @param array $types
     * @return $this
     */
    public function setAllowedTypes($types = null)
    {
        $this->allowed_types_override = $types;

        return $this;
    }

    /**
     * Returns allowed link types
     *
     * @return array
     */
    public function getTypes()
    {
        $types = $this->config()->get('types');
        $i18nTypes = [];
        $allowedTypes = $this->config()->get('allowed_types');

        if ($this->allowed_types_override) {
            // Prioritise local field over global settings
            $allowedTypes = $this->allowed_types_override;
        }

        if ($allowedTypes) {
            foreach ($allowedTypes as $type) {
                if (!array_key_exists($type, $types)) {
                    user_error("{$type} is not a valid link type");
                }
            }

            foreach (array_diff_key($types, array_flip($allowedTypes)) as $key => $value) {
                unset($types[$key]);
            }
        }

        // Get translatable labels
        foreach ($types as $key => $label) {
            $i18nTypes[$key] = _t('Linkable.TYPE' . strtoupper($key), $label);
        }

        return $i18nTypes;
    }

    /**
     * Renders an HTML anchor tag for this link
     *
     * @return DBHTMLText|string
     */
    public function forTemplate()
    {
        if ($this->LinkURL) {
            $link = $this->renderWith([
                // Render link with this template if its found. eg Link_button.ss
                Link::class . '_' . $this->Template,
                Link::class
            ]);

            // Legacy. Recommended to use templating above.
            $this->extend('updateLinkTemplate', $this, $link);

            return $link;
        }

        return '';
    }

    /**
     * Works out what the URL for this link should be based on it's Type
     *
     * @return bool|mixed|null|string
     */
    public function getLinkURL()
    {
        if (!$this->ID) {
            return '';
        }

        $type = $this->Type;

        switch ($type) {
            case 'URL':
                $LinkURL = $this->URL;

                break;
            case 'Email':
                $LinkURL = $this->Email ? "mailto:$this->Email" : null;

                break;
            case 'Phone':
                $LinkURL = $this->Phone ? "tel:$this->Phone" : null;

                break;
            default:
                if ($this->getTypeHasDbField()) {
                    $LinkURL = $this->{$type};
                } else {
                    if ($type && $component = $this->getComponent($type)) {
                        if (!$component->exists()) {
                            $LinkURL = false;
                        } elseif ($component->hasMethod('Link')) {
                            $LinkURL = $component->Link() . $this->Anchor;
                        } else {
                            $LinkURL = "Please implement a Link() method on your dataobject \"$type\"";
                        }
                    }
                }

                break;
        }

        $this->extend('updateLinkURL', $LinkURL);

        return $LinkURL;
    }

    /**
     * Gets the classes for this link.
     *
     * @return array|string
     */
    public function getClasses()
    {
        $classes = explode(' ', $this->cssClass ?? '');
        $this->extend('updateClasses', $classes);
        $classes = implode(' ', $classes);

        return $classes;
    }

    /**
     * Gets the html class attribute for this link.
     *
     * @return string
     */
    public function getClassAttr()
    {
        $class = $this->Classes ? Convert::raw2att($this->Classes) : '';

        return $class ? " class='$class'" : '';
    }

    /**
     * Gets the html target attribute for the anchor tag
     *
     * @return string
     */
    public function getTargetAttr()
    {
        return $this->OpenInNewWindow ? " target='_blank'" : '';
    }

    /**
     * Gets the description label of this links type
     *
     * @return null|string
     */
    public function getLinkType()
    {
        $types = $this->config()->get('types');

        return isset($types[$this->Type]) ? _t('Linkable.TYPE' . strtoupper($this->Type), $types[$this->Type]) : null;
    }

    /**
     * Check if the selected type has a db field otherwise assume its a related object.
     *
     * @return bool
     */
    public function getTypeHasDbField()
    {
        return in_array(
            $this->Type,
            array_keys($this->Config()->get('db'))
        );
    }

    /**
     * Validate
     *
     * @return ValidationResult
     */
    public function validate()
    {
        $valid = true;
        $message = null;
        $type = $this->Type;

        // Check if empty strings
        switch ($type) {
            case 'URL':
            case 'Email':
            case 'Phone':
                if ($this->{$type} == '') {
                    $valid = false;
                    $message = _t(
                        'Linkable.VALIDATIONERROR_EMPTY' . strtoupper($type),
                        "You must enter a $type for a link type of \"$this->LinkType\""
                    );
                }

                break;
            default:
                if ($this->getTypeHasDbField()) {
                    if ($type && empty($this->{$type})) {
                        $valid = false;
                        $message = _t(
                            'Linkable.VALIDATIONERROR_EMPTY',
                            "You must enter a $type for a link type of \"$this->LinkType\""
                        );
                    }
                } else {
                    if ($type && empty($this->{$type . 'ID'})) {
                        $valid = false;
                        $message = _t(
                            'Linkable.VALIDATIONERROR_OBJECT',
                            "Please select a {value} object to link to", array('value' => $type)
                        );
                    }
                }

                break;
        }

        // if its already failed don't bother checking the rest
        if ($valid) {
            switch ($type) {
                case 'URL':
                    $allowedFirst = array('#', '/');
                    if (!in_array(substr($this->URL, 0, 1), $allowedFirst) && !filter_var($this->URL, FILTER_VALIDATE_URL)) {
                        $valid = false;
                        $message = _t(
                            'Linkable.VALIDATIONERROR_VALIDURL',
                            'Please enter a valid URL. Be sure to include http:// for an external URL. Or begin your internal url/anchor with a "/" character'
                        );
                    }

                    break;
                case 'Email':
                    if (!filter_var($this->Email, FILTER_VALIDATE_EMAIL)) {
                        $valid = false;
                        $message = _t(
                            'Linkable.VALIDATIONERROR_VALIDEMAIL',
                            'Please enter a valid Email address'
                        );
                    }

                    break;
                case 'Phone':
                    if (!preg_match("/^\+?[0-9]{1,5}[- ]{0,1}[0-9]{3,4}[- ]{0,1}[0-9]{4}$/", $this->Phone)) {
                        $valid = false;
                        $message = _t(
                            'Linkable.VALIDATIONERROR_VALIDPHONE',
                            'Please enter a valid Phone number'
                        );
                    }

                    break;
            }
        }

        $result = ValidationResult::create();

        if (!$valid) {
            $result->addError($message);
        }

        $this->extend('updateValidate', $result);

        return $result;
    }
}
