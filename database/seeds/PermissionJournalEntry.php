<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;


class PermissionJournalEntry extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            // Modul journal entry
            'general_journal.access',

            // data journal entry access modul
            'journal_entry.access',
            'journal_entry.data',
            'journal_entry.create',
            'journal_entry.view',
            'journal_entry.update',
            'journal_entry.delete',

        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}
