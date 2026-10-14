<?php
$f = fopen('database_backups/backup_rembulancitraabadi_2026_db_PRE_UPGRADE_20260918_023400.sql', 'r');
$insideDetails = false;
while (($line = fgets($f)) !== false) {
    if (strpos($line, 'INSERT INTO `journal_entry_details`') !== false) {
        $insideDetails = true;
    }
    if ($insideDetails) {
        // match tuples: (id, journal_entry_id, ...)
        preg_match_all('/\((\d+),18990,([^)]+)\)/', $line, $matches, PREG_SET_ORDER);
        foreach ($matches as $m) {
            echo "Detail: (" . $m[1] . ", 18990, " . $m[2] . ")\n";
        }
        if (strpos($line, ';') !== false && !empty($matches)) {
            // done
        }
    }
}
fclose($f);
