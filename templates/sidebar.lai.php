<input type="checkbox" id="sidebar-toggle-checkbox" ref="sidebarToggleCheckbox">
<div id="sidebar" class="d-flex flex-column flex-shrink-0 p-3 bg-dark">

  <div class="d-flex align-items-center justify-content-center mb-3">
    <div class="avatar d-flex flex-column">
      <img src="/src/images/avatar.png" alt="avatar">
      <div class="text-white mt-1">{{ env('SIDEBAR_NAME') }}</div>
      <small class="text-white-50">{{ env('SIDEBAR_DESCRIPTION') }}</small>
    </div>
  </div>

  <hr>

  <ul class="nav nav-pills flex-column">
    <li>
      <a href="/" class="nav-link">
        首頁
      </a>
    </li>
    <li v-for="page in pages">
      <a :href="`/${page.url}`" class="nav-link" v-text="page.title"></a>
    </li>
  </ul>
  <hr>
  <ul class="nav nav-pills flex-column">
    <li class="mb-1">
    <li class="btn btn-toggle align-items-center rounded collapsed" data-bs-toggle="collapse"
        data-bs-target="#category-collapse" aria-expanded="false">
      分類
    </li>
    <div class="collapse" id="category-collapse">
      <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
        <li v-for="category in categories"><a :href="`/#/?category=${category}`" @click="closeSidebar()"
                                              class="nav-link rounded"
                                              v-text="category"></a></li>
      </ul>
    </div>
    </li>
    <li class="mb-1">
    <li class="btn btn-toggle align-items-center rounded collapsed" data-bs-toggle="collapse"
        data-bs-target="#tag-collapse" aria-expanded="false">
      標籤
    </li>
    <div class="collapse" id="tag-collapse">
      <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
        <li v-for="tag in tags"><a :href="`/#/?tag=${tag}`" @click="closeSidebar()" class="nav-link rounded"
                                   v-text="tag"></a></li>
      </ul>
    </div>
    </li>
  </ul>

  <ul class="nav nav-pills flex-column mt-auto">
    <li>
      <a class="nav-link" id="close-sidebar-link">
        關閉選單
      </a>
    </li>
  </ul>
</div>

<label for="sidebar-toggle-checkbox" id="sidebar-toggle">
  <div class="icon"></div>
</label>
