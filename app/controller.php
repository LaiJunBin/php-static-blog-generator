<?php

function index()
{
    echo shell_exec('php app/index.php');
}

function post($post)
{
    echo shell_exec('php app/post.php ' . $post);
}

function page($page)
{
    echo shell_exec('php app/page.php ' . $page);
}
