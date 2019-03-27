<?php

namespace Sheadawson\Linkable\Task;

use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\DB;

class MigrateLinks extends BuildTask
{
    protected $title = 'Migrate linkable links';

    protected $description = 'Migrate linkable links to new table';

    public function run($request)
    {
        $rows = DB::query('SELECT * FROM Link');
        $count = 0;

        foreach ($rows as $row) {
            $record = DB::prepared_query('SELECT * FROM LinkableLink WHERE ID = ?', [$row['ID']]);

            if (!$record->first()) {
                $fields = implode(',', array_keys($row));
                $values = implode(',', array_pad([], count($row), '?'));

                $row['ClassName'] = 'Sheadawson\Linkable\Models\Link';

                DB::prepared_query(
                    'INSERT INTO linkablelink (' . $fields . ') VALUES (' . $values . ')',
                    array_values($row)
                );

                $count++;
            }
        }

        echo 'Migrated: ' . $count . ' linkable links';
    }
}
