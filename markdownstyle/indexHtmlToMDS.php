<?php

/////Для вывода ошибок на экран  ini_set('display_errors','on'); on || of
#print_r(function_exists('mb_internal_encoding')); //проверка 1-подключено, 0 - не подключено
error_reporting(E_ALL);
ini_set('display_errors','on');
mb_internal_encoding('UTF-8');

require_once 'htmltomds2.2.php';
// Пример использования (должен быть в другом файле)
/*
$html = <<<HTML
<div id="header" class="main banner" data-role="primary">
    <ul class="nav">
        <li class="item active">Home</li>
        <li>About</li>
    </ul>
    <img src="logo.png" alt="Logo">
</div>
HTML;

$mds = Html::toMDS($html);
echo $mds;
*/


#header('Content-Type: text/html; charset=utf-8');
// Пример использования
$html = <<<HTML
<div class="container px-4 py-5" id="icon-grid">
    <h2 class="pb-2 border-bottom">Сетка иконок</h2>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 py-5">
      <div class="col d-flex align-items-start">
        <svg class="bi text-muted flex-shrink-0 me-3" width="1.75em" height="1.75em"><use xlink:href="#bootstrap"></use></svg>
        <div>
          <h4 class="fw-bold mb-0">Избранный заголовок</h4>
          <p>Абзац текста под заголовком, поясняющий заголовок.</p>
        </div>
      </div>
      <div class="col d-flex align-items-start">
        <svg class="bi text-muted flex-shrink-0 me-3" width="1.75em" height="1.75em"><use xlink:href="#cpu-fill"></use></svg>
        <div>
          <h4 class="fw-bold mb-0">Избранный заголовок</h4>
          <p>Абзац текста под заголовком, поясняющий заголовок.</p>
        </div>
      </div>
      <div class="col d-flex align-items-start">
        <svg class="bi text-muted flex-shrink-0 me-3" width="1.75em" height="1.75em"><use xlink:href="#calendar3"></use></svg>
        <div>
          <h4 class="fw-bold mb-0">Избранный заголовок</h4>
          <p>Абзац текста под заголовком, поясняющий заголовок.</p>
        </div>
      </div>
      <div class="col d-flex align-items-start">
        <svg class="bi text-muted flex-shrink-0 me-3" width="1.75em" height="1.75em"><use xlink:href="#home"></use></svg>
        <div>
          <h4 class="fw-bold mb-0">Избранный заголовок</h4>
          <p>Абзац текста под заголовком, поясняющий заголовок.</p>
        </div>
      </div>
      <div class="col d-flex align-items-start">
        <svg class="bi text-muted flex-shrink-0 me-3" width="1.75em" height="1.75em"><use xlink:href="#speedometer2"></use></svg>
        <div>
          <h4 class="fw-bold mb-0">Избранный заголовок</h4>
          <p>Абзац текста под заголовком, поясняющий заголовок.</p>
        </div>
      </div>
      <div class="col d-flex align-items-start">
        <svg class="bi text-muted flex-shrink-0 me-3" width="1.75em" height="1.75em"><use xlink:href="#toggles2"></use></svg>
        <div>
          <h4 class="fw-bold mb-0">Избранный заголовок</h4>
          <p>Абзац текста под заголовком, поясняющий заголовок.</p>
        </div>
      </div>
      <div class="col d-flex align-items-start">
        <svg class="bi text-muted flex-shrink-0 me-3" width="1.75em" height="1.75em"><use xlink:href="#geo-fill"></use></svg>
        <div>
          <h4 class="fw-bold mb-0">Избранный заголовок</h4>
          <p>Абзац текста под заголовком, поясняющий заголовок.</p>
        </div>
      </div>
      <div class="col d-flex align-items-start">
        <svg class="bi text-muted flex-shrink-0 me-3" width="1.75em" height="1.75em"><use xlink:href="#tools"></use></svg>
        <div>
          <h4 class="fw-bold mb-0">Избранный заголовок</h4>
          <p>Абзац текста под заголовком, поясняющий заголовок.</p>
        </div>
      </div>
    </div>
  </div>
HTML;


echo Html::toMDS($html);
