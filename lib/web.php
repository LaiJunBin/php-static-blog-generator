<?php

require_once './lib/route.php';

Route::get('/', 'index');
Route::get('/posts/{post}.html', 'post');
Route::get('/{page}.html', 'page');
