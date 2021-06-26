<?php

date_default_timezone_set('Asia/Taipei');

require_once './lib/utils.php';

$file = $argv[1] . '.md';
$fullpath = './src/posts/' . $file;
if (!is_file($fullpath)) {
    die($fullpath . ' not found.');
}

$arguments = get_markdown_arguments($fullpath);
echo generate_post_html($arguments);

