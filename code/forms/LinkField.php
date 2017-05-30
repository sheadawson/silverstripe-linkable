<?php

/**
 * LinkField
 *
 * @package silverstripe-linkable
 * @license BSD License http://www.silverstripe.org/bsd-license
 * @author <shea@silverstripe.com.au>
 **/
class LinkField extends TextField
{

    /**
     * @var Boolean
     **/
    protected $isFrontend = false;

    /**
     * @var Link
     **/
    protected $linkObject;

    /**
     * List the allowed included link types.  If null all are allowed.
     *
     * @var array
     **/
    protected $allowed_types = null;

    public static $allowed_actions = array(
        'LinkForm',
        'LinkFormHTML',
        'doSaveLink',
        'doRemoveLink'
    );


    public function Field($properties = array())
    {
        Requirements::javascript(LINKABLE_PATH . '/javascript/linkfield.js');
        return parent::Field();
    }


    /**
     * The LinkForm for the dialog window
     *
     * @return Form
     **/
    public function LinkForm()
    {
        $link = $this->getLinkObject();

        $action = FormAction::create('doSaveLink', _t('Linkable.SAVE', 'Save'))->setUseButtonTag('true');

        if (!$this->isFrontend) {
            $action->addExtraClass('ss-ui-action-constructive')->setAttribute('data-icon', 'accept');
        }

        $link = null;
        if ($linkID = (int) $this->request->getVar('LinkID')) {
            $link = Link::get()->byID($linkID);
        }
        $link = $link ? $link : singleton('Link');
        $link->setAllowedTypes($this->getAllowedTypes());
        $fields = $link->getCMSFields();

        $title = $link ? _t('Linkable.EDITLINK', 'Edit Link') : _t('Linkable.ADDLINK', 'Add Link');
        $fields->insertBefore(HeaderField::create('LinkHeader', $title), _t('Linkable.TITLE', 'Title'));
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
     * @return string
     **/
    public function doSaveLink($data, $form)
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
     * @return string
     **/
    public function doRemoveLink()
    {
        $this->setValue('');
        return $this->FieldHolder();
    }


    /**
     * Returns the current link object
     *
     * @return Link
     **/
    public function getLinkObject()
    {
        $requestID = Controller::curr()->request->requestVar('LinkID');

        if ($requestID == '0' && !$this->Value()) {
            return;
        }

        if (!$this->linkObject) {
            $id = $this->Value() ? $this->Value() : $requestID;
            if ((int) $id) {
                $this->linkObject = Link::get()->byID($id);
            }
        }
        return $this->linkObject;
    }


    /**
     * Returns the HTML of the LinkForm for the dialog
     *
     * @return string
     **/
    public function LinkFormHTML()
    {
        return $this->LinkForm()->forTemplate();
    }


    public function getIsFrontend()
    {
        return $this->isFrontend;
    }


    public function setIsFrontend($bool)
    {
        $this->isFrontend = $bool;
        return $this->this;
    }

    public function setAllowedTypes($types = array())
    {
        $this->allowed_types = $types;
        return $this;
    }

    public function getAllowedTypes()
    {
        return $this->allowed_types;
    }
}
