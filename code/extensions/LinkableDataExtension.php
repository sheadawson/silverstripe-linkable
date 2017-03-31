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
            foreach(array_keys($hasOne) as $field) {
                if($hasOne[$field] === 'Link'){
                    $link = array_search($hasOne[$field], $hasOne);

                    $this->owner->{$link.'ID'} = 0;
                }
            }
        }
    }

}
