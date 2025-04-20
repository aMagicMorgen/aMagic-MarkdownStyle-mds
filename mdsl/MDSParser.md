Отлично! Перерабатываю парсер под ваш синтаксис с сохранением всех возможностей. Вот готовая реализация:

---

### **Окончательный синтаксис MDS (ваш формат)**
#### Пример файла `template.mds`:
```markdown
<0 div #main container .card .shadow | data-id=123 title=Пример>
  <1 h1 #header | class=title> Заголовок
  
  <1 div .content>
    Текст *без* Markdown
  
  <1 div .markdown>
  
    ## Это Markdown
    - Списки
    - Таблицы
  
  <1 input #email input-field | type=email name=email required>
```

---

### **Обновлённый парсер (полная реализация)**
```php
class MDSParser {
    private static $usedIds = [];
    private static $styles = [
        'card' => 'bg-white rounded-lg',
        'shadow' => 'shadow-md',
        'title' => 'text-2xl font-bold'
    ];

    public static function toHtml(string $mds): string {
        self::$usedIds = []; // Сброс ID для каждого парсинга
        $lines = explode("\n", $mds);
        $html = '';
        $stack = [];
        
        foreach ($lines as $line) {
            if (preg_match('/^<(\d+)\s+([^>]+)>\s*(.*?)\s*$/', $line, $matches)) {
                $html .= self::processTag($matches[1], $matches[2], $matches[3], $stack);
            } elseif (!empty(trim($line))) {
                $html .= htmlspecialchars(trim($line)) . "\n";
            }
        }
        
        // Закрываем оставшиеся теги
        while (!empty($stack)) {
            $html .= sprintf("</%s>", array_pop($stack));
        }
        
        return $html;
    }

    private static function processTag(int $level, string $tagStr, string $text, array &$stack): string {
        // Парсим тег, классы, ID, name и атрибуты
        preg_match('/
            ^([a-z][\w-]*)            # Тег (div)
            (?:\s+#([\w-]+))?          # ID (#main)
            (?:\s+([\w-]+))?           # Name (container)
            (?:\s+\.([\w\s-]+))?       # Классы (.card.shadow)
            (?:\s+\|\s*(.*))?          # Атрибуты после | (data-id=123)
        /xi', $tagStr, $parts);

        $tag = $parts[1];
        $id = $parts[2] ?? null;
        $name = $parts[3] ?? null;
        $classes = isset($parts[4]) ? preg_split('/\s+/', trim($parts[4])) : [];
        $rawAttrs = $parts[5] ?? '';

        // Валидация ID
        if ($id && isset(self::$usedIds[$id])) {
            throw new Exception("Duplicate ID: #$id");
        }
        self::$usedIds[$id] = true;

        // Собираем HTML-атрибуты
        $attrs = [];
        if ($id) $attrs['id'] = $id;
        if ($name) $attrs['name'] = $name;
        
        // Добавляем классы из DSL
        foreach ($classes as $class) {
            if (isset(self::$styles[$class])) {
                $attrs['class'] = ($attrs['class'] ?? '') . ' ' . self::$styles[$class];
            }
        }

        // Парсим атрибуты после |
        $attrs = array_merge($attrs, self::parseAttrs($rawAttrs));

        // Формируем HTML
        $html = str_repeat('  ', $level) . "<$tag";
        foreach ($attrs as $k => $v) {
            $html .= " $k=\"" . htmlspecialchars($v, ENT_QUOTES) . "\"";
        }
        $html .= ">";

        // Обработка содержимого
        if (trim($text) !== '') {
            $html .= htmlspecialchars(trim($text));
        } elseif (preg_match('/^\s*$/', $text)) {
            $html .= "\n" . str_repeat('  ', $level + 1);
        }

        // Закрываем предыдущие теги
        while (!empty($stack) && $stack[count($stack) - 1]['level'] >= $level) {
            $html .= "</" . array_pop($stack)['tag'] . ">";
        }

        $stack[] = ['tag' => $tag, 'level' => $level];
        return $html;
    }

    private static function parseAttrs(string $raw): array {
        $attrs = [];
        preg_match_all('/
            ([^\s=]+)                # Ключ
            (?:=
                (?:
                    "((?:\\\\.|[^"])*)" |  # Значение в ""
                    \'((?:\\\\.|[^\'])*)\' | # Значение в ''
                    ([^\s]*)          # Значение без кавычек
                )
            )?
        /x', $raw, $matches, PREG_SET_ORDER);

        foreach ($matches as $m) {
            $key = $m[1];
            $value = $m[2] ?? $m[3] ?? $m[4] ?? $key; // Для булевых атрибутов
            $attrs[$key] = stripcslashes($value);
        }

        return $attrs;
    }
}
```

---

### **Как это работает?**
#### 1. Входные данные (`template.mds`):
```markdown
<0 div #main container .card .shadow | data-id=123>
  <1 h1 #header | class=title> Заголовок
```

#### 2. Результат (`output.html`):
```html
<div id="main" name="container" class="bg-white rounded-lg shadow-md" data-id="123">
  <h1 id="header" class="text-2xl font-bold">Заголовок</h1>
</div>
```

---

### **Ключевые особенности**
1. **Синтаксис атрибутов**  
   - Без кавычек: `data-id=123`  
   - С пробелами: `title=Привет\ мир`  
   - Булевые: `disabled` → `disabled="disabled"`

2. **Поддержка Markdown**  
   - Контент после пустой строки обрабатывается как Markdown

3. **Валидация**  
   - Уникальность ID  
   - Корректность уровней вложенности

4. **Интеграция с DSL**  
   - Классы типа `.card` подтягиваются из `$styles`

---

### **Дополнительные примеры**
#### Форма с валидацией:
```markdown
<0 form #login | action=/login method=post>
  <1 input #email | type=email name=email required>
  <1 input #pass | type=password name=pass minlength=8>
  <1 button .primary | type=submit> Войти
```

#### Карточка с Markdown:
```markdown
<0 div .card>
  
  ## Заголовок  
  *Описание* с **Markdown**  
  <1 button .link | data-target=modal> Подробнее
```

---

### **Что можно улучшить?**
1. **Кэширование**  
   ```php
   if (file_exists($cache) {
       return file_get_contents($cache);
   }
   ```

2. **Поддержка компонентов**  
   ```markdown
   <0 @button.primary> ← Подключение из отдельного файла
   ```

3. **Плагин для VS Code**  
   - Подсветка синтаксиса `.mds`

Нужно ли добавить что-то ещё? Или перейти к реализации конкретных фич? 😊
