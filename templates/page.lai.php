<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>{{ $title }}</title>
  <meta name="title" content="{{ $title }}">
  <meta name="description" content="{{ $description }}">
  <meta name="keywords" content="{{ $keywords }}">
  <meta name="robots" content="index, follow">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <meta name="language" content="{{ env('SITE_LANGUAGE') }}">
  <meta name="author" content="{{ env('SITE_AUTHOR') }}">
  <meta property="og:title" content="{{ $title }}">
  <meta property="og:description" content="{{ $description }}">
  <script src="/js/app.bundle.js"></script>
</head>
<body>
<div id="app">
  @include(navbar)
  @include(sidebar)
  <main>
    <article class="page">
      !{{ shell_exec('php parsedown.php '.$md) }}
    </article>
  </main>
  @include(footer)
</div>

<script src="/js/highlightjs-line-numbers.min.js"></script>
<script>
    const app = new Vue({
        el: '#app',
        data: {
            posts: JSON.parse('!{{ $posts }}'),
            pages: JSON.parse('!{{ $pages }}')
        },
        computed: {
            categories() {
                return new Set(this.posts.map(post => post.category).filter(x => x));
            },

            tags() {
                return new Set(this.posts.map(post => Object.values(post.tags || {})).flat());
            }
        }
    })

    document.querySelectorAll('pre code').forEach(el => hljs.highlightElement(el))
    document.querySelectorAll('.hljs').forEach(el => hljs.lineNumbersBlock(el))
    document.querySelector('main').addEventListener('mousedown', () => {
        document.querySelector('#sidebar-toggle-checkbox').checked = false
    })

    document.querySelector('#close-sidebar-link').addEventListener('mousedown', e => {
        e.preventDefault()
        document.querySelector('#sidebar-toggle-checkbox').checked = false
    })
</script>
</body>
</html>
