<?php

/**
 * Link
 *
 * @package silverstripe-linkable
 * @license BSD License http://www.silverstripe.org/bsd-license
 * @author <shea@silverstripe.com.au>
 **/
class Link extends DataObject
{
    /**
     * @var string custom CSS classes for template
     */
    protected $cssClass;

    /**
     * @var array
     */
    private static $db = array(
        'Title' => 'Varchar(255)',
        'Type' => 'Varchar',
        'URL' => 'Varchar(255)',
        'Email' => 'Varchar(255)',
        'Phone' => 'Varchar(255)',
        'Anchor' => 'Varchar(255)',
        'OpenInNewWindow' => 'Boolean',
        'Template' => 'Varchar(255)'
    );

    /**
     * @var array
     */
    private static $has_one = array(
        'File' => 'File',
        'SiteTree' => 'SiteTree'
    );

    /**
     * @var array
     */
    private static $summary_fields = array(
        'Title',
        'LinkType',
        'LinkURL'
    );

    /**
     * A map of templates that are available for rendering
     * Link objects with
     *
     * @var array
     */
    private static $templates = array();

    /**
     * A map of object types that can be linked to
     * Custom dataobjects can be added to this
     *
     * @var array
     **/
    private static $types = array(
        'URL' => 'URL',
        'Email' => 'Email address',
        'Phone' => 'Phone number',
        'File' => 'File on this website',
        'SiteTree' => 'Page on this website'
    );

    /**
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = $this->scaffoldFormFields(array(
            // Don't allow has_many/many_many relationship editing before the record is first saved
            'includeRelations' => ($this->ID > 0),
            'tabbed' => true,
            'ajaxSafe' => true
        ));

        $fields->removeByName(
            array(
                'SiteTreeID',
                // seem to need to remove both of these for different SS versions...
                'FileID',
                'File',
                'Template',
                'Anchor'
            )
        );

        $templates = $this->config()->get('templates');
        if ($templates) {
            $i18nTemplates = array();
            foreach ($templates as $key => $label) {
                $i18nTemplates[$key] = _t('Linkable.STYLE'.strtoupper($key), $label);
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

        $fields->dataFieldByName('Title')
            ->setTitle(_t('Linkable.TITLE', 'Title'))
            ->setRightTitle(_t('Linkable.OPTIONALTITLE', 'Optional. Will be auto-generated from link if left blank'));

        $types = $this->config()->get('types');
        $i18nTypes = array();
        foreach ($types as $key => $label) {
            $i18nTypes[$key] = _t('Linkable.TYPE'.strtoupper($key), $label);
        }
        $fields->replaceField(
            'Type',
            DropdownField::create(
                'Type',
                _t('Linkable.LINKTYPE', 'Link Type'),
                $i18nTypes
            )->setEmptyString(' '),
            'OpenInNewWindow'
        );

        $fields->addFieldsToTab(
            'Root.Main',
            CheckboxField::create(
                'OpenInNewWindow',
                _t('Linkable.OPENINNEWWINDOW','Open link in a new window')
            )->displayIf('Type')->isEqualTo("URL")
                ->orIf()->isEqualTo("File")
                ->orIf()->isEqualTo("SiteTree")
                ->end()
        );

        $fields->addFieldsToTab(
            'Root.Main',
            array(
                DisplayLogicWrapper::create(
                    TreeDropdownField::create(
                        'FileID',
                        _t('Linkable.FILE', 'File'),
                        'File',
                        'ID',
                        'Title'
                    )
                )->displayIf("Type")->isEqualTo("File")->end(),

                DisplayLogicWrapper::create(
                    TreeDropdownField::create(
                        'SiteTreeID',
                        _t('Linkable.PAGE', 'Page'),
                        'SiteTree'
                    ),
                    TextField::create(
                        'Anchor',
                        _t('Linkable.ANCHOR', 'Anchor')
                    )->setRightTitle(_t('Linkable.ANCHORINFO', 'Include # at the start of your anchor name'))
                )->displayIf("Type")->isEqualTo("SiteTree")->end()
            ),
            'OpenInNewWindow'
        );

        $fields->dataFieldByName('URL')
            ->displayIf("Type")->isEqualTo("URL");

        $fields->dataFieldByName('Email')
            ->setTitle(_t('Linkable.EMAILADDRESS', 'Email Address'))
            ->displayIf("Type")
            ->isEqualTo("Email");

        $fields->dataFieldByName('Phone')
            ->setTitle(_t('Linkable.PHONENUMBER', 'Phone Number'))
            ->displayIf("Type")
            ->isEqualTo("Phone");

        if ($this->SiteTreeID && !$this->SiteTree()->isPublished()) {
            $fields->dataFieldByName('SiteTreeID')
                ->setRightTitle(_t('Linkable.DELETEDWARNING', 'Warning: The selected page appears to have been deleted or unpublished. This link may not appear or may be broken in the frontend'));
        }

        $this->extend('updateCMSFields', $fields);

        return $fields;
    }


    /**
     * If the title is empty, set it to getLinkURL()
     *
     * @return string
     **/
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
     * Add CSS classes.
     *
     * @param string $class CSS classes.
     * @return Link
     **/
    public function setCSSClass($class)
    {
        $this->cssClass = $class;
        return $this;
    }

    /**
     * Renders an HTML anchor tag for this link
     *
     * @return string
     **/
    public function forTemplate()
    {
        if ($this->LinkURL) {
            $link = $this->renderWith(
                array(
                    'Link_' . $this->Template, // Render link with this template if its found. eg Link_button.ss
                    'Link'
                )
            );

            // Legacy. Reccommended to use templating above.
            $this->extend('updateLinkTemplate', $this, $link);

            return $link;
        }
    }

    /**
     * Works out what the URL for this link should be based on it's Type
     *
     * @return string
     **/
    public function getLinkURL()
    {
        if (!$this->ID) {
            return;
        }
        switch ($this->Type) {
            case 'URL':
                return $this->URL;
            case 'Email':
                return $this->Email ? "mailto:$this->Email" : null;
            case 'Phone':
                return $this->Phone ? "tel:$this->Phone" : null;
            default:
                if ($this->Type && $component = $this->getComponent($this->Type)) {
                    if (!$component->exists()) {
                        return false;
                    }
                    if ($component->hasMethod('Link')) {
                        return $component->Link() . $this->Anchor;
                    } else {
                        return "Please implement a Link() method on your dataobject \"$this->Type\"";
                    }
                }
                break;
        }
    }

    /**
     * Gets the classes for this link.
     *
     * @return string
     **/
    public function getClasses()
    {
        $classes = explode(' ', $this->cssClass);
        $this->extend('updateClasses', $classes);
        $classes = implode(' ', $classes);
        return $classes;
    }

    /**
     * Gets the html class attribute for this link.
     *
     * @return string
     **/
    public function getClassAttr()
    {
        $class = $this->Classes ? Convert::raw2att($this->Classes) : '';
        return $class ? " class='$class'" : '';
    }

    /**
     * Gets the html target attribute for the anchor tag
     *
     * @return string
     **/
    public function getTargetAttr()
    {
        return $this->OpenInNewWindow ? " target='_blank'" : '';
    }

    /**
     * Gets the description label of this links type
     *
     * @return string
     **/
    public function getLinkType()
    {
        $types = $this->config()->get('types');
        return isset($types[$this->Type]) ? _t('Linkable.TYPE'.strtoupper($this->Type), $types[$this->Type]) : null;
    }

    /**
     * Validate
     *
     * @return ValidationResult
     **/
    protected function validate()
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
                    $message = _t('Linkable.VALIDATIONERROR_EMPTY'.strtoupper($type), "You must enter a $type for a link type of \"$type\"");
                }
                break;
            default:
                if ($type && empty($this->{$type.'ID'})) {
                    $valid = false;
                    $message = _t('Linkable.VALIDATIONERROR_OBJECT', "Please select a {value} object to link to", array('value' => $type));
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
                        $message = _t('Linkable.VALIDATIONERROR_VALIDURL', 'Please enter a valid URL. Be sure to include http:// for an external URL. Or begin your internal url/anchor with a "/" character');
                    }
                    break;
                case 'Email':
                    if (!filter_var($this->Email, FILTER_VALIDATE_EMAIL)) {
                        $valid = false;
                        $message = _t('Linkable.VALIDATIONERROR_VALIDEMAIL', 'Please enter a valid Email address');
                    }
                    break;
                case 'Phone':
                    if (!preg_match("/^\+?[0-9]{3,4}[- ]{0,1}[0-9]{3,4}[- ]{0,1}[0-9]{4}$/", $this->Phone)) {
                        $valid = false;
                        $message = _t('Linkable.VALIDATIONERROR_VALIDPHONE', 'Please enter a valid Phone number');
                    }
                    break;
            }
        }

        $result = ValidationResult::create($valid, $message);
        $this->extend('updateValidate', $result);
        return $result;
    }
}
