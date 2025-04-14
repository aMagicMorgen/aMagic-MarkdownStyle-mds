# mds-MarkdownStyle
Рсширение MarkdownExtra вводит новые возможности в md текст предлагаю писать в *.mds - MarkdownStyle или *.mdh - MarkdownHibrid

# Документация класса `MarkdownStyle`

## Обзор

`MarkdownStyle` - это гибридный процессор разметки, расширяющий `MarkdownExtra` с поддержкой MDS-разметки через специальные комментарии. Позволяет комбинировать обычный Markdown с MDS-разметкой в одном документе.

## Синтаксис файлов

### Вариант 1: Файлы `.mds` (MarkdownStyle)
```
# Обычный Markdown

<!-- mds
<1 div .container>
<2 p >Это MDS-разметка
-->
```

### Вариант 2: Файлы `.mdh` (Markdown Hybrid)
```
<!-- mds
<1 div .header>
<2 h1 >Гибридный документ
-->

Основной *Markdown* контент

<!-- mds
<1 div .footer>
<2 p >Футер в MDS
-->
```

## Подключение и использование

```php
require_once 'MarkdownStyle.php';

// Преобразование текста
$html = MarkdownStyle::transform($markdownText);

// Или через экземпляр
$parser = new MarkdownStyle();
$html = $parser->parse($markdownText);
```

## Наследование от MarkdownExtra

`MarkdownStyle` наследует все возможности `MarkdownExtra`:
- Таблицы
- Сноски
- Определения
- Атрибуты HTML

## Магические комментарии

### Основной синтаксис MDS:
```markdown
<!-- mds
<1 div .panel>
<2 h2 >Заголовок
<2 p >Текст панели
-->
```

### Расширяемость системы (пример с Pug):
```markdown
<!-- phug
div.panel
  h2 Заголовок
  p Текст панели
-->
```

### Кастомные стили:
```markdown
<!-- styled
Этот текст будет стилизован | color:red;font-weight:bold
-->
```

## Полный пример класса

```php
class MarkdownStyle extends \Michelf\MarkdownExtra {
    protected $block_gamut = [
        'parseMdsBlocks' => 5,
        'doHeaders' => 10,
        // ... остальные обработчики
    ];

    protected function parseMdsBlocks(string $text): string {
        return preg_replace_callback_array([
            '/<!--\s*mds\s*(.*?)\s*-->/s' => fn($m) => MDS::toHtml($m[1]),
            '/<!--\s*phug\s*(.*?)\s*-->/s' => fn($m) => Pug::compile($m[1]),
            '/<!--\s*styled\s*(.*?)\s*-->/s' => fn($m) => $this->parseStyled($m[1])
        ], $text);
    }
    
    protected function parseStyled(string $text): string {
        [$content, $style] = explode('|', $text);
        return '<span style="'.htmlspecialchars($style).'">'.trim($content).'</span>';
    }
}
```

## Рекомендации по использованию

1. **Именование файлов**:
   - `.mds` - для документов с преимущественно MDS-разметкой
   - `.mdh` - для гибридных документов

2. **Поддержка IDE**:
   - Создайте сниппеты для быстрой вставки MDS-блоков
   - Настройте подсветку синтаксиса для магических комментариев

3. **Оптимизация**:
   - Кэшируйте результат преобразования
   - Используйте препроцессинг для сложных документов

## Пример полного документа

```markdown
<!-- mds
<1 div .article>
<2 h1 >Гибридный документ
-->

Это *обычный* Markdown текст.

<!-- phug
div.alert.alert-warning
  | Предупреждение
-->

<!-- styled
Важный текст | color:red;font-size:1.2em
-->
```

Такой подход позволяет:
- Сочетать преимущества Markdown для текста
- Использовать MDS для сложных структур
- Встраивать другие шаблонизаторы через комментарии
- Добавлять кастомные стили к фрагментам

  
