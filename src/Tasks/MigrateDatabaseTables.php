<?php

namespace Sheadawson\Linkable\Models;


use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\DB;

/**
 * Created by PhpStorm.
 * User: shawn
 * Date: 2018-08-09
 * Time: 08:31
 */

class MigrateDatabaseTables extends BuildTask
{
    protected $title = 'Migrate Linkable DataObject Tables.';

    protected $description = 'Rename dataobject tables for SilverStripe 4 upgrade. The existing database "LinkableEmbed" and "LinkableLink" will be removed.';

    private $renameTables = [
        'EmbeddedObject' => 'LinkableEmbed',
        'Link' => 'LinkableLink',
    ];

    public function run($request)
    {
        foreach ($this->renameTables as $old => $new) {
            if(!DB::query("SHOW TABLES LIKE '$old';")->value()) {
                DB::alteration_message("There is no old table \"$old\" exists.");
            } else {
                DB::query("DROP TABLE IF EXISTS $new;");
                DB::query("RENAME TABLE $old TO $new;");
                DB::alteration_message("Renamed database table \"$old\" to \"$new\".");
            }
        }
    }
}
