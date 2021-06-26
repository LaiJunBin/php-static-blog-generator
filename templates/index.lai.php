<!doctype html>
<html lang="zh-TW">
<head>
  <meta charset="UTF-8">
  <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>{{ env('SITE_TITLE') }}</title>
  <meta name="title" content="{{ env('SITE_TITLE') }}">
  <meta name="description" content="{{ env('SITE_DESCRIPTION') }}">
  <meta name="keywords" content="{{ env('SITE_KEYWORDS') }}">
  <meta name="robots" content="index, follow">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <meta name="language" content="{{ env('SITE_LANGUAGE') }}">
  <meta name="author" content="{{ env('SITE_AUTHOR') }}">

  <meta name="og:title" content="{{ env('SITE_TITLE') }}">
  <meta name="og:description" content="{{ env('SITE_DESCRIPTION') }}">

  <script src="/js/app.bundle.js"></script>
</head>
<body>
<div id="app">
  @include(navbar)
  @include(sidebar)
  <main>
    <div class="search-group">
      <label for="search-input"><i class="fas fa-search"></i></label>
      <input type="text" class="form-control" id="search-input" v-model="searchText" placeholder="Search..">
    </div>
    <template v-if="listPosts.length">

      <div class="panel" v-for="post in listPosts">
        <a :href="`posts/${post.url}`">
          <div class="cover-image" :style="{ 'background-image': `url(${post.cover_image})`}"></div>
          <div class="post-meta-wrapper">
            <h2 class="text-center" v-text="post.title"></h2>
            <hr>
            <div>
              <i class="fas fa-calendar-day"></i>
              <span v-text="post.last_datetime"></span>
            </div>
            <div class="tags">
              <i class="fas fa-tags"></i>
              <template v-if="post.tags">
                <span v-text="tag" class="tag" v-for="tag in post.tags"></span>
              </template>
              <template v-else>
                <span class="tag">No Tag</span>
              </template>
            </div>
          </div>
        </a>
      </div>
    </template>
    <div class="panel d-flex align-items-center justify-content-center" v-else>
      <h2 class="my-3">沒有找到任何符合條件的文章哦!!</h2>
    </div>

    <div class="paginate d-flex justify-content-center">
      <nav aria-label="Page navigation example">
        <ul class="pagination">
          <li class="page-item" :class="{disabled: page === 1}">
            <a class="page-link" :href="generatePageUrl(Math.max(page-1, 1))" aria-label="Previous">
              <span aria-hidden="true">&laquo;</span>
            </a>
          </li>
          <li v-for="pageI in pageRange" class="page-item" :class="{active: page === pageI}">
            <a class="page-link" :href="generatePageUrl(pageI)" v-text="pageI"></a>
          </li>
          <li class="page-item" :class="{disabled: page === maxPage}">
            <a class="page-link" :href="generatePageUrl(page+1)" aria-label="Next">
              <span aria-hidden="true">&raquo;</span>
            </a>
          </li>
        </ul>
      </nav>
    </div>
  </main>

  @include(footer)
</div>
<script>
    const app = new Vue({
        el: '#app',
        data: {
            posts: JSON.parse('!{{ $posts }}'),
            pages: JSON.parse('!{{ $pages }}'),
            filteredResult: {},
            listPosts: [],
            searchText: '',
            page: 1,
            pageSize: {{ env('PAGE_SIZE') }},
        },
        computed: {
            categories() {
                return new Set(this.posts.map(post => post.category).filter(x => x));
            },

            tags() {
                return new Set(this.posts.map(post => Object.values(post.tags || {})).flat());
            },

            maxPage() {
                return Math.ceil(this.filteredResult.posts?.length / this.pageSize) || 0;
            },

            pageRange() {
                let min = Math.max(1, this.page - 2);
                let max = Math.min(this.maxPage, min + 4);
                min = Math.max(1, Math.min(min, max - 4));
                return new Array(max - min + 1).fill(0).map((x, i) => min + i);
            }
        },
        methods: {
            refreshPage() {
                let pageMatch = location.hash.match(/page=([^&\s]+)/);
                if (pageMatch) {
                    this.page = Number(decodeURI(pageMatch[1])) || 1;
                } else {
                    this.page = 1;
                }
            },

            refreshPosts() {
                let category, tag;

                let categoryMatch = location.hash.match(/category=([^&\s]+)/);
                if (categoryMatch) {
                    category = decodeURI(categoryMatch[1]);
                }

                let tagMatch = location.hash.match(/tag=([^&\s]+)/);
                if (tagMatch) {
                    tag = decodeURI(tagMatch[1]);
                }

                let isNewQuery = false;
                if (
                    this.filteredResult.category !== category ||
                    this.filteredResult.tag !== tag ||
                    this.filteredResult.searchText !== this.searchText
                ) {
                    let posts = this.posts;
                    if (category) {
                        isNewQuery = true;
                        posts = this.posts.filter(post => post.category === category);
                    }

                    if (tag) {
                        isNewQuery = true;
                        posts = this.posts.filter(post => post.tags?.includes(tag));
                    }

                    this.filteredResult = {
                        category, tag, searchText: this.searchText,
                        posts: posts.filter(post => post.title.toLowerCase().includes(this.searchText.toLowerCase()))
                    };

                    if (isNewQuery) {
                        this.searchText = '';
                    }
                }

                this.listPosts = this.filteredResult.posts.slice((this.page - 1) * this.pageSize, this.page * this.pageSize);
            },

            closeSidebar() {
                this.$refs.sidebarToggleCheckbox.checked = false;
            },

            generatePageUrl(page) {
                let otherQueryUrl = '&';

                let categoryMatch = location.hash.match(/category=([^&\s]+)/);
                if (categoryMatch) {
                    otherQueryUrl += `category=${decodeURI(categoryMatch[1])}`;
                }
                let tagMatch = location.hash.match(/tag=([^&\s]+)/);
                if (tagMatch) {
                    otherQueryUrl += `tag=${decodeURI(tagMatch[1])}`;
                }

                return `/#/page=${page}${otherQueryUrl}`
            }
        },
        mounted() {
            this.filteredResult = {posts: this.posts};
            this.refreshPage();
            this.refreshPosts();

            window.addEventListener('hashchange', () => {
                this.refreshPage();
                this.refreshPosts();
            });
        },
        watch: {
            searchText() {
                location.hash = '/';
                this.refreshPage();
                this.refreshPosts();
            }
        }
    })

    window.addEventListener('load', () => {
        document.querySelector('main').addEventListener('mousedown', () => {
            document.querySelector('#sidebar-toggle-checkbox').checked = false
        })

        document.querySelector('#close-sidebar-link').addEventListener('mousedown', e => {
            e.preventDefault()
            document.querySelector('#sidebar-toggle-checkbox').checked = false
        })
    })
</script>
</body>
</html>
