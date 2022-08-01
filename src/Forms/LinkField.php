<?php

namespace Sheadawson\Linkable\Forms;

use Sheadawson\Linkable\Models\Link;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\View\Requirements;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\HiddenField;
use SilverStripe\ORM\ValidationException;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\TextField;

/**
 * Class LinkField
 * @license BSD License http://www.silverstripe.org/bsd-license
 * @author <shea@silverstripe.com.au>
 * @package Sheadawson\Linkable\Forms
 */
class LinkField extends TextField
{
    /**
     * @var array
     */
    private static $allowed_actions = [
        'LinkForm',
        'LinkFormHTML',
        'doSaveLink',
        'doRemoveLink',
    ];

    /**
     * @var bool
     */
    protected $isFrontend = false;

    /**
     * @var Link
     **/
    protected $linkObject;

    /**
     * List the allowed included link types.  If null all are allowed.
     * @var array
     */
    protected $allowed_types = null;

    /**
     * @param array $properties
     * @return DBHTMLText
     */
    public function Field($properties = [])
    {
        Requirements::javascript('sheadawson/silverstripe-linkable: client/dist/js/bundle.js');

        return parent::Field();
    }

    /**
     * The LinkForm for the dialog window
     *
     * @return Form
     **/
    public function LinkForm()
    {
        $this->getLinkObject();

        $action = FormAction::create('doSaveLink', _t('Linkable.SAVE', 'Save'))
            ->setUseButtonTag('true');

        if (!$this->isFrontend) {
            $action
                ->addExtraClass('ss-ui-action-constructive')
                ->setAttribute('data-icon', 'accept');
        }

        $link = null;
        if ($linkID = (int)$this->request->getVar('LinkID')) {
            $link = Link::get()->byID($linkID);
        }

        $link = $link ? $link : singleton(Link::class);
        $link->setAllowedTypes($this->getAllowedTypes());

        /** @var $fields FieldList */
        $fields = $link->getCMSFields();

        $title = $link ? _t('Linkable.EDITLINK', 'Edit Link') : _t('Linkable.ADDLINK', 'Add Link');
        $fields->insertBefore(
            _t('Linkable.TITLE', 'Title'), HeaderField::create('LinkHeader', $title)
        );

        $actions = FieldList::create($action);
        $form = Form::create($this, 'LinkForm', $fields, $actions);

        if ($link) {
            $form->loadDataFrom($link);
            $fields->push(HiddenField::create('LinkID', 'LinkID', $link->ID));
        }

        $this->owner->extend('updateLinkForm', $form);

        return $form;
    }

    /**
     * Either updates the current link or creates a new one
     * Returns field template to update the interface
     *
     * @param $data
     * @param $form
     * @return DBHTMLText
     */
    public function doSaveLink($data, Form $form)
    {
        $link = $this->getLinkObject() ? $this->getLinkObject() : Link::create();
        $form->saveInto($link);

        try {
            $link->write();
        } catch (ValidationException $e) {
            $form->sessionMessage($e->getMessage(), 'bad');

            return $form->forTemplate();
        }

        $this->setValue($link->ID);
        $this->setForm($form);

        return $this->FieldHolder();
    }

    /**
     * Delete link action - TODO
     *
     * @return DBHTMLText
     */
    public function doRemoveLink()
    {
        $this->setValue('');

        return $this->FieldHolder();
    }

    /**
     * Returns the current link object
     * @return Link
     */
    public function getLinkObject()
    {
        $requestID = Controller::curr()->request->requestVar('LinkID');

        if ($requestID == '0' && !$this->Value()) {
            return null;
        }

        if (!$this->linkObject) {
            $id = $this->Value() ? $this->Value() : $requestID;

            if ((int)$id) {
                $this->linkObject = Link::get()->byID($id);
            }
        }

        return $this->linkObject;
    }

    /**
     * Returns the HTML of the LinkForm for the dialog
     *
     * @return DBHTMLText
     */
    public function LinkFormHTML()
    {
        return $this->LinkForm()->forTemplate();
    }

    /**
     * @return bool
     */
    public function getIsFrontend()
    {
        return $this->isFrontend;
    }

    /**
     * @param bool $isFrontend
     * @return $this
     */
    public function setIsFrontend($isFrontend)
    {
        $this->isFrontend = $isFrontend;

        return $this;
    }

    /**
     * @param array $types
     * @return $this
     */
    public function setAllowedTypes($types = [])
    {
        $this->allowed_types = $types;

        return $this;
    }

    /**
     * @return array
     */
    public function getAllowedTypes()
    {
        return $this->allowed_types;
    }
}
