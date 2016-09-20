<?php

/**
 * EmbeddedObject
 *
 * @package silverstripe-linkable
 * @license BSD License http://www.silverstripe.org/bsd-license
 * @author <marcus@silverstripe.com.au>
 **/
class EmbeddedObject extends DataObject
{
    private static $db = array(
        'Title' => 'Varchar(255)',
        'Type' => 'Varchar',
        'SourceURL' => 'Varchar(255)',
        'Width' => 'Varchar',
        'Height' => 'Varchar',
        'Description' => 'HTMLText',
        'ThumbURL' => 'Varchar(255)',
        'ExtraClass' => 'Varchar(64)',
        'EmbedHTML' => 'Text',
    );

    public function Embed()
    {
        $options = array(
            'width' => $this->Width,
            'height' => $this->Height,
        );
        $this->setFromURL($this->SourceURL);
        return $this;
    }

    public function onBeforeWrite()
    {
        $changes = $this->getChangedFields();
        
        if (isset($changes['SourceURL']) && $changes['SourceURL']['after']) {
            $this->updateEmbedHTML();
        }

        parent::onBeforeWrite();
    }

    public function updateEmbedHTML()
    {
        $this->setFromURL($this->SourceURL);
    }

    public function setFromURL($url)
    {
        if ($url) {
            $info = Embed\Embed::create($url); // , array('image' => array('minImageWidth' => $this->Width, 'minImageHeight' => $this->Height)));
            $this->setFromEmbed($info);
        }
    }

    public function setFromEmbed(\Embed\Adapters\Adapter $info)
    {
        $this->Title = $info->getTitle();
        $this->SourceURL = $info->getUrl();
        $this->Width = $info->getWidth();
        $this->Height = $info->getHeight();
        $this->ThumbURL = $info->getImage();
        $this->Description = $info->getDescription() ? $info->getDescription(): $info->getTitle();
        $this->Type = $info->getType();
        $embed = $info->getCode();
        $this->EmbedHTML = $embed ? $embed : $this->EmbedHTML;
    }

    public function forTemplate()
    {
        switch ($this->Type) {
            case 'video':
            case 'rich':
                if ($this->ExtraClass) {
                    return "<div class='$this->ExtraClass'>$this->EmbedHTML</div>";
                } else {
                    return $this->EmbedHTML;
                }
                break;
            case 'link':
                return '<a class="' . $this->ExtraClass . '" href="' . $this->SourceURL . '">' . $this->Title . '</a>';
                break;
            case 'photo':
                return "<img src='$this->SourceURL' width='$this->Width' height='$this->Height' class='$this->ExtraClass' />";
                break;
        }
    }
}
