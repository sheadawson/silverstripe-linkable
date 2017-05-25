<?php

/**
 * An extension to add site tree option to linkable field.
 *
 * @package silverstripe-linkable
 * @license BSD License http://www.silverstripe.org/bsd-license
 * @author  <mohamed.alsharaf@chrometoaster.com>
 **/
class LinkableSiteTreeExtension extends DataExtension
{
    /**
     * @var array
     */
    private static $db = array(
        'Anchor' => 'Varchar(255)',
    );

    /**
     * @var array
     */
    private static $has_one = array(
        'SiteTree' => 'SiteTree',
    );

    /**
     * A map of object types that can be linked to
     * Custom dataobjects can be added to this
     *
     * @var array
     **/
    private static $types = array(
        'SiteTree' => 'Page on this website',
    );

    /**
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        // Site tree field as a combination of tree drop down and anchor text field
        $siteTreeField = DisplayLogicWrapper::create(
            TreeDropdownField::create(
                'SiteTreeID',
                _t('Linkable.PAGE', 'Page'),
                'SiteTree'
            ),
            TextField::create(
                'Anchor',
                _t('Linkable.ANCHOR', 'Anchor/Querystring')
            )->setRightTitle(_t('Linkable.ANCHORINFO', 'Include # at the start of your anchor name or, ? at the start of your querystring'))
        )->displayIf("Type")->isEqualTo("SiteTree")->end();

        // Insert site tree field after the file selection field
        $fields->insertAfter('Type', $siteTreeField);

        // Display warning if the selected page is deleted or unpublished
        if ($this->owner->SiteTreeID && !$this->owner->SiteTree()->isPublished()) {
            $fields
                ->dataFieldByName('SiteTreeID')
                ->setRightTitle(_t('Linkable.DELETEDWARNING', 'Warning: The selected page appears to have been deleted or unpublished. This link may not appear or may be broken in the frontend'));
        }
    }
}
