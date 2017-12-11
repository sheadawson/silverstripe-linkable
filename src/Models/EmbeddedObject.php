<?php

namespace Sheadawson\Linkable\Models;

use Embed\Adapters\Adapter;
use Embed\Embed;
use SilverStripe\ORM\DataObject;

/**
 * Class EmbeddedObject
 * @license BSD License http://www.silverstripe.org/bsd-license
 * @author <marcus@silverstripe.com.au>
 * @property string Title
 * @property string Type
 * @property string SourceURL
 * @property string Width
 * @property string Height
 * @property string Description
 * @property string ThumbURL
 * @property string ExtraClass
 * @property string EmbedHTML
 * @package Sheadawson\Linkable\Models
 */
class EmbeddedObject extends DataObject
{
    /**
     * @var array
     */
    private static $db = [
        'Title' => 'Varchar(255)',
        'Type' => 'Varchar',
        'SourceURL' => 'Varchar(255)',
        'Width' => 'Varchar',
        'Height' => 'Varchar',
        'Description' => 'HTMLText',
        'ThumbURL' => 'Varchar(255)',
        'ExtraClass' => 'Varchar(64)',
        'EmbedHTML' => 'Text',
    ];

    /**
     * @var string
     */
    private static $table_name = 'LinkableEmbed';

    /**
     * @return $this
     */
    public function Embed()
    {
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

    /**
     * @param $url
     */
    public function setFromURL($url)
    {
        if ($url) {
            // array('image' => array('minImageWidth' => $this->Width, 'minImageHeight' => $this->Height)));
            $info = Embed::create($url);
            $this->setFromEmbed($info);
        }
    }

    /**
     * @param Adapter $info
     */
    public function setFromEmbed(Adapter $info)
    {
        $this->Title = $info->getTitle();
        $this->SourceURL = $info->getUrl();
        $this->Width = $info->getWidth();
        $this->Height = $info->getHeight();
        $this->ThumbURL = $info->getImage();
        $this->Description = $info->getDescription() ? $info->getDescription() : $info->getTitle();
        $this->Type = $info->getType();
        $embed = $info->getCode();
        $this->EmbedHTML = $embed ? $embed : $this->EmbedHTML;
    }

    /**
     * @return string
     */
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

        return '';
    }
}
