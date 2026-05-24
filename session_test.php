<?php
session_name("SPAC_SUPERADMIN");
$start = microtime(true);
session_start();
$t1 = round((microtime(true)-$start)*1000);
session_write_close();
echo "session_start took: {$t1}ms";
?>
