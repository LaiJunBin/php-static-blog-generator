<?php

$md = $argv[1] ?? null;
if (!is_file($md)) {
    die($md . " isn't markdown format.");
}

require_once './lib/Parsedown.php';
$parsedown = new Parsedown();
$md = file_get_contents($md);
echo $parsedown->text($md);

