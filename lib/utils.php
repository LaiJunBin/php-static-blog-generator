<?php
require_once './lib/lai.php';

function str_replace_first($from, $to, $content)
{
    $from = '/' . preg_quote($from, '/') . '/';
    return preg_replace($from, $to, $content, 1);
}

function recursive_copy($src, $dst)
{
    $dir = opendir($src);
    @mkdir($dst, 0777, true);
    while (($file = readdir($dir))) {
        if (($file != '.') && ($file != '..')) {
            if (is_dir($src . '/' . $file)) {
                recursive_copy($src . '/' . $file, $dst . '/' . $file);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}

function rmdir_recursive($dir)
{
    foreach (scandir($dir) as $file) {
        if ('.' === $file || '..' === $file) continue;
        if (is_dir("$dir/$file")) rmdir_recursive("$dir/$file");
        else unlink("$dir/$file");
    }
    rmdir($dir);
}

function clear_empty(&$array)
{
    $array = array_values(array_filter($array));
}

function get_posts()
{
    $raw_posts = glob('./src/posts/*.md');

    $posts = array_map(function ($md) {
        $arguments = get_markdown_arguments($md);
        return array_merge($arguments, [
            'last_datetime' => date("Y年m月d日.", filemtime($md)),
        ]);
    }, $raw_posts);

    return $posts;
}

function get_pages()
{
    $raw_pages = glob('./src/pages/*.md');
    $pages = array_filter(array_map(function ($md) {
        $arguments = get_markdown_arguments($md);
        if (in_array('hide', $arguments['properties'] ?? [])) {
            return null;
        }
        return $arguments;
    }, $raw_pages));
    return $pages;
}

function get_private_page()
{
    $raw_pages = glob('./src/pages/*.md');
    $pages = array_filter(array_map(function ($md) {
        $arguments = get_markdown_arguments($md);
        if (in_array('hide', $arguments['properties'] ?? [])) {
            return $arguments;
        }
        return null;
    }, $raw_pages));
    return $pages;
}

function get_markdown_arguments($md)
{
    $raw_data = file_get_contents($md);
    preg_match_all('/######\s(\S+):(.*)/', $raw_data, $matches);
    $arguments = [
        'md' => $md
    ];
    for ($i = 0; $i < count($matches[0]); $i++) {
        preg_match_all('/`(.*?)`/', $matches[2][$i], $value_matches);
        $arguments[$matches[1][$i]] = $value_matches[1];
    }

    preg_match('/^#{1}\s(.*)/', $raw_data, $match);
    $arguments['title'] = trim($match[1] ?? 'Unknown title');

    preg_match('/!\[cover image]\((.*)\)/', $raw_data, $match);
    $arguments['cover_image'] = trim($match[1] ?? './src/images/default.jpg');
    $arguments['url'] = pathinfo($md, PATHINFO_FILENAME) . '.html';

    if (isset($arguments['tags'])) {
        $arguments['keywords'] = implode(',', $arguments['tags']);
    }

    if (isset($arguments['description'])) {
        $arguments['description'] = $arguments['description'][0];
    }

    if (isset($arguments['category'])) {
        $arguments['category'] = $arguments['category'][0];
    }

    return $arguments;
}

function generate_index_html()
{
    $posts = get_posts();
    $pages = get_pages();

    return Lai::decryptFile('./templates/index.lai.php', [
        'posts' => json_encode($posts),
        'pages' => json_encode($pages),
    ]);
}

function generate_post_html($arguments)
{
    $posts = get_posts();
    $pages = get_pages();

    return Lai::decryptFile('./templates/post.lai.php', [
        'title' => $arguments['title'],
        'keywords' => $arguments['keywords'] ?? '',
        'description' => $arguments['description'] ?? '',
        'md' => $arguments['md'],
        'url' => $arguments['url'],
        'posts' => json_encode($posts),
        'pages' => json_encode($pages),
        'arguments' => json_encode($arguments),
    ]);
}


function generate_page_html($arguments)
{
    $posts = get_posts();
    $pages = get_pages();

    return Lai::decryptFile('./templates/page.lai.php', [
        'title' => $arguments['title'],
        'keywords' => $arguments['keywords'] ?? '',
        'description' => $arguments['description'] ?? '',
        'md' => $arguments['md'],
        'posts' => json_encode($posts),
        'pages' => json_encode($pages),
        'arguments' => json_encode($arguments),
    ]);
}

function env($key)
{
    require_once 'env.php';
    return @constant($key) ?? null;
}
