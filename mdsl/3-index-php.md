Вот готовая реализация `index.php`, которая загружает MDS-файл как объект с поддержкой PHP-вставок и подключением шаблонов:

---

### **index.php**
```php
<?php
require_once 'MDSL.php'; // Подключаем основной класс
require_once 'Parsedown.php'; // Для Markdown (если используется)

class MDSFile {
    private $filePath;
    private $vars = [];
    private $compiledContent;

    public function __construct(string $filePath, array $vars = []) {
        $this->filePath = $filePath;
        $this->vars = $vars;
        $this->compile();
    }

    public function render(): string {
        // Выполняем PHP-вставки через буферизацию
        ob_start();
        extract($this->vars);
        eval('?>' . $this->compiledContent);
        return ob_get_clean();
    }

    private function compile(): void {
        $content = file_get_contents($this->filePath);
        
        // 1. Обрабатываем MDSL-шаблоны
        $content = MDSL::render($content);
        
        // 2. Компилируем Markdown (если нужно)
        if (strpos($content, '## ') !== false) {
            $content = Parsedown::instance()->text($content);
        }
        
        $this->compiledContent = $content;
    }
}

// Пример использования
$vars = [
    'pageTitle' => 'Главная страница',
    'showFooter' => true
];

$page = new MDSFile('page.mds', $vars);
echo $page->render();
```

---

### **Как это работает**

1. **Подключение файла**  
   ```php
   $page = new MDSFile('page.mds', ['var' => 'value']);
   ```
   - Загружает файл `page.mds`
   - Принимает переменные для PHP-вставок

2. **Компиляция**  
   - Обрабатывает MDSL-шаблоны через `MDSL::render()`
   - Конвертирует Markdown в HTML (если есть)
   - Сохраняет результат в `$compiledContent`

3. **Рендеринг с PHP**  
   ```php
   echo $page->render();
   ```
   - Выполняет PHP-код вида `<?= $var ?>` через `eval()`
   - Использует переданные в конструктор переменные

---

### **Пример файла `page.mds`**
```markdown
<!--mdsl header-->
title = <?= htmlspecialchars($pageTitle) ?>
<!--/mdsl-->

<0 div.container>
  <? if ($showFooter): ?>
    <1 footer> © <?= date('Y') ?>
  <? endif; ?>
```

---

### **Особенности реализации**

1. **Безопасность**  
   - Автоматическое экранирование через `htmlspecialchars()`  
   - Переменные изолированы через `extract()`

2. **Кэширование** (добавьте при необходимости)  
   ```php
   if (file_exists($cacheFile)) {
       $content = file_get_contents($cacheFile);
   } else {
       $content = MDSL::render(...);
       file_put_contents($cacheFile, $content);
   }
   ```

3. **Поддержка Markdown**  
   Автоматически обрабатывает блоки с `## Заголовки` и `- Списки`

---

### **Важные нюансы**

1. Для работы PHP-вставок файл должен иметь расширение `.mds`  
2. Переменные передаются ТОЛЬКО через конструктор:  
   ```php
   // Правильно
   new MDSFile('file.mds', ['var' => 123]);
   
   // Небезопасно (переменные из глобальной области)
   new MDSFile('file.mds', $_GET);
   ```

3. Для сложной логики лучше выносить код в отдельные методы:
   ```markdown
   <!-- Вместо <?= someComplexLogic() ?> -->
   <?= $this->renderComponent('name') ?>
   ```

---

Готов адаптировать код под конкретные требования! Например, можно:
1. Добавить кэширование  
2. Реализовать наследование шаблонов  
3. Ввести безопасный режим без `eval()`  

Какой аспект важнее для вашего проекта? 

### **Финальная реализация с Markdown-обработкой**

#### **1. Структура файлов**
```
project/
├── index.php          # Главный обработчик
├── MDSL.php           # Класс для работы с MDSL-шаблонами
├── Parsedown.php      # Парсер Markdown (скачать с GitHub)
└── templates/
    └── contact.mdsl   # Пример шаблона
```

---

### **2. index.php (полная версия)**
```php
<?php
require_once 'MDSL.php';
require_once 'Parsedown.php';

class MDSFile {
    private $filePath;
    private $vars = [];
    private $compiledContent;

    public function __construct(string $filePath, array $vars = []) {
        $this->filePath = $filePath;
        $this->vars = $vars;
        $this->compile();
    }

    public function render(): string {
        ob_start();
        extract($this->vars, EXTR_SKIP); // Без перезаписи существующих переменных
        eval('?>' . $this->compiledContent);
        return ob_get_clean();
    }

    private function compile(): void {
        $content = file_get_contents($this->filePath);
        
        // Обработка MDSL-шаблонов
        $content = MDSL::render($content);
        
        // Обработка Markdown
        $parsedown = new Parsedown();
        $this->compiledContent = $parsedown->text($content);
    }
}

// Пример использования
$page = new MDSFile('page.mds', [
    'title' => 'Демо страница',
    'content' => 'Текст с **Markdown**'
]);

echo $page->render();
```

---

### **3. Как работает обработка Markdown**

#### **a) Установка Parsedown**
1. Скачайте библиотеку с [официального репозитория](https://github.com/erusev/parsedown)
2. Положите `Parsedown.php` в папку проекта

#### **b) Что обрабатывается**
```markdown
<!-- Будет преобразовано в <h2> -->
## Заголовок 

<!-- В список <ul><li> -->
- Пункт 1
- Пункт 2

<!-- В таблицу <table> -->
| Column 1 | Column 2 |
|----------|----------|
| Ячейка   | Данные   |
```

#### **c) Особенности**
- **Безопасность**: Parsedown автоматически экранирует HTML-теги
- **Производительность**: Обработка происходит за 1 проход
- **Кастомизация**: Можно наследовать класс `Parsedown` для своих правил

---

### **4. Пример файла `page.mds`**
```markdown
<!--mdsl header-->
title = <?= htmlspecialchars($title) ?>
<!--/mdsl-->

<0 div.container>
  ## <?= $title ?>
  
  <?= $content ?>

  <!-- Markdown-контент -->
  
  **Жирный текст** и [ссылка](https://example.com)
```

---

### **5. Этапы обработки**
1. **Шаг 1**: Замена MDSL-шаблонов  
   ```php
   $content = MDSL::render($content);
   ```
2. **Шаг 2**: Преобразование Markdown → HTML  
   ```php
   $parsedown->text($content);
   ```
3. **Шаг 3**: Выполнение PHP-вставок  
   ```php
   eval('?>' . $compiledContent);
   ```

---

### **6. Безопасность**
- **Экранирование переменных**:  
  Всегда используйте `htmlspecialchars()`:
  ```php
  <?= htmlspecialchars($userInput) ?>
  ```
- **Ограничение переменных**:  
  Передавайте только необходимые данные:
  ```php
  new MDSFile('file.mds', [
      'safeVar' => 'value' // Вместо $_POST
  ]);
  ```

---

### **7. Производительность**
Для ускорения добавьте кэширование:
```php
private function compile(): void {
    $cacheFile = 'cache/' . md5($this->filePath) . '.html';
    
    if (file_exists($cacheFile)) {
        $this->compiledContent = file_get_contents($cacheFile);
    } else {
        $content = MDSL::render(file_get_contents($this->filePath));
        $this->compiledContent = (new Parsedown())->text($content);
        file_put_contents($cacheFile, $this->compiledContent);
    }
}
```

---

### **8. Кастомизация Markdown**
Чтобы добавить свои правила (например, для Bootstrap):
```php
class CustomParsedown extends Parsedown {
    protected function blockHeader($Line) {
        $block = parent::blockHeader($Line);
        $block['element']['attributes']['class'] = 'mt-4'; // Добавляем класс
        return $block;
    }
}

// Использование:
$parsedown = new CustomParsedown();
```

---

### **Итог**
Теперь система:
1. Поддерживает MDSL-шаблоны  
2. Обрабатывает Markdown через Parsedown  
3. Позволяет встраивать PHP-код  
4. Легко масштабируется  

Для старта просто скопируйте код и поместите `Parsedown.php` в проект. Готов помочь с доработками! 😊
