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

    public function run($request)
    {
        DB::query('DROP TABLE IF EXISTS LinkableEmbed, LinkableLink;');
        DB::query('RENAME TABLE EmbeddedObject TO LinkableEmbed;');
        DB::alteration_message('Renamed database table "EmbeddedObject" to "LinkableEmbed".');
        DB::query('RENAME TABLE Link TO LinkableLink;');
        DB::alteration_message('Renamed database table "Link" to "LinkableLink".');
    }
}
