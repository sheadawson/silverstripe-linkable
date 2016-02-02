<?php

/**
 * EmbeddedObjectField
 *
 * @package silverstripe-linkable
 * @license BSD License http://www.silverstripe.org/bsd-license
 * @author <marcus@silverstripe.com.au>
 **/
class EmbeddedObjectField extends FormField
{

    private static $allowed_actions = array(
        'update'
    );

    protected $editableEmbedCode = false;

    protected $object;

    protected $message;

    public function setValue($value)
    {
        if ($value instanceof EmbeddedObject) {
            $this->object = $value;
            parent::setValue($value->toMap());
        }
        parent::setValue($value);
    }

    public function setEditableEmbedCode($v)
    {
        $this->editableEmbedCode = $v;
        return $this;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function FieldHolder($properties = array())
    {
        Requirements::css(LINKABLE_PATH . '/css/embeddedobjectfield.css');
        Requirements::javascript(LINKABLE_PATH . '/javascript/embeddedobjectfield.js');

        if ($this->object && $this->object->ID) {
            $properties['SourceURL'] = TextField::create($this->getName() . '[sourceurl]', '')->setAttribute('placeholder', _t('Linkable.SOURCEURL', 'Source URL'));

            if (strlen($this->object->SourceURL)) {
                $properties['ObjectTitle'] = TextField::create($this->getName() . '[title]', _t('Linkable.TITLE', 'Title'));
                $properties['Width'] = TextField::create($this->getName() . '[width]', _t('Linkable.WIDTH', 'Width'));
                $properties['Height'] = TextField::create($this->getName() . '[height]', _t('Linkable.HEIGHT', 'Height'));
                $properties['ThumbURL'] = HiddenField::create($this->getName() . '[thumburl]', '');
                $properties['Type'] = HiddenField::create($this->getName() . '[type]', '');
                if ($this->editableEmbedCode) {
                    $properties['EmbedHTML'] = TextareaField::create($this->getName() . '[embedhtml]', 'Embed code');
                } else {
                    $properties['EmbedHTML'] = HiddenField::create($this->getName() . '[embedhtml]', '');
                }

                $properties['ObjectDescription'] = TextAreaField::create($this->getName() . '[description]', _t('Linkable.DESCRIPTION', 'Description'));
                $properties['ExtraClass'] = TextField::create($this->getName() . '[extraclass]', _t('Linkable.CSSCLASS', 'CSS class'));

                foreach ($properties as $key => $field) {
                    if ($key == 'ObjectTitle') {
                        $key = 'Title';
                    } elseif ($key == 'ObjectDescription') {
                        $key = 'Description';
                    }
                    $field->setValue($this->object->$key);
                }

                if ($this->object->ThumbURL) {
                    $properties['ThumbImage'] = LiteralField::create($this->getName(), '<img src="' . $this->object->ThumbURL . '" />');
                }
            }
        } else {
            $properties['SourceURL'] = TextField::create($this->getName() . '[sourceurl]', '')->setAttribute('placeholder', _t('Linkable.SOURCEURL', 'Source URL'));
        }

        $field = parent::FieldHolder($properties);
        return $field;
    }

    public function saveInto(DataObjectInterface $record)
    {
        $val = $this->Value();
        $field = $this->getName() . 'ID';

        if (!strlen($val['sourceurl']) && $this->object) {
            if ($this->object->exists()) {
                $this->object->delete();
            }
            $record->$field = 0;
            return;
        }

        if (!$this->object) {
            $this->object = EmbeddedObject::create();
        }

        $props = array_keys(Config::inst()->get('EmbeddedObject', 'db'));
        foreach ($props as $prop) {
            $this->object->$prop = isset($val[strtolower($prop)]) ? $val[strtolower($prop)] : null;
        }

        $this->object->write();
        $record->$field = $this->object->ID;
    }

    public function update(SS_HTTPRequest $request)
    {
        if (!SecurityToken::inst()->checkRequest($request)) {
            return '';
        }
        $url = $request->postVar('URL');
        if (strlen($url)) {
            $info = Embed\Embed::create($url);
            if ($info) {
                $object = EmbeddedObject::create();
                $object->setFromEmbed($info);

                $this->object = $object;
                // needed to make sure the check in FieldHolder works out
                $object->ID = -1;
                return $this->FieldHolder();
            } else {
                $this->message = _t('EmbeddedObjectField.ERROR', 'Could not look up provided URL: ' . Convert::raw2xml($url));
                return $this->FieldHolder();
            }
        } else {
            $this->object = null;
            return $this->FieldHolder();
        }
    }
}
