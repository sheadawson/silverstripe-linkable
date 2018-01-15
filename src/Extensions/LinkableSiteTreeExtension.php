<?php

namespace Sheadawson\Linkable\Extensions;

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TreeDropdownField;
use SilverStripe\Forms\TextField;
use UncleCheese\DisplayLogic\Forms\Wrapper;
use SilverStripe\ORM\DataExtension;

/**
 * An extension to add site tree option to linkable field.
 *
 * Class LinkableSiteTreeExtension
 * @license BSD License http://www.silverstripe.org/bsd-license
 * @author  <mohamed.alsharaf@chrometoaster.com>
 * @package Sheadawson\Linkable\Extensions
 */
class LinkableSiteTreeExtension extends DataExtension
{
    /**
     * @var array
     */
    private static $db = [
        'Anchor' => 'Varchar(255)',
    ];

    /**
     * @var array
     */
    private static $has_one = [
        'SiteTree' => SiteTree::class,
    ];

    /**
     * A map of object types that can be linked to
     * Custom dataobjects can be added to this
     *
     * @var array
     **/
    private static $types = [
        'SiteTree' => 'Page on this website',
    ];

    /**
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        // Site tree field as a combination of tree drop down and anchor text field
        $siteTreeField = Wrapper::create(
            TreeDropdownField::create(
                'SiteTreeID',
                _t('Linkable.PAGE', 'Page'),
                SiteTree::class
            ),
            TextField::create(
                'Anchor',
                _t('Linkable.ANCHOR', 'Anchor/Querystring')
            )->setRightTitle(
                _t(
                    'Linkable.ANCHORINFO',
                    'Include # at the start of your anchor name or, ? at the start of your querystring'
                )
            )
        );

        $siteTreeField
            ->displayIf('Type')
            ->isEqualTo('SiteTree')
            ->end();

        $fields->addFieldToTab(
            'Root.Main',
            $siteTreeField,
            'OpenInNewWindow'
        );

//        // Insert site tree field after the file selection field
//        $fields->insertAfter('Type', $siteTreeField);

        // Display warning if the selected page is deleted or unpublished
        if ($this->owner->SiteTreeID && !$this->owner->SiteTree()->isPublished()) {
            $fields
                ->dataFieldByName('SiteTreeID')
                ->setRightTitle(
                    _t(
                        'Linkable.DELETEDWARNING',
                        'Warning: The selected page appears to have been deleted or unpublished. This link may not appear or may be broken in the frontend'
                    )
                );
        }
    }
}
