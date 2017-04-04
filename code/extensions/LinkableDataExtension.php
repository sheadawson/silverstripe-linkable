<?php
/**
 *
 * @author Ryan
 */
class LinkableDataExtension extends DataExtension {
    
    public function onBeforeDuplicate(){       
        $hasOne = Config::inst()->get($this->owner->ClassName, 'has_one');
        
        //loop through has_one relationships and reset any Link fields
        if($hasOne){
            foreach ($hasOne as $field => $fieldType) {
                if ($fieldType === 'Link') {
                    $this->owner->{$field.'ID'} = 0;
                }
            }
        }
    }

}
