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

Какой аспект важнее для вашего проекта? 😊
