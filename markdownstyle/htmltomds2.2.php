<?php
// htmltomds2.2.php статический класс 
/////Для вывода ошибок на экран  ini_set('display_errors','on'); on || of
#print_r(function_exists('mb_internal_encoding')); //проверка 1-подключено, 0 - не подключено
/*error_reporting(E_ALL);
ini_set('display_errors','on');
mb_internal_encoding('UTF-8');
*/


class Html {
    private static $indent = 1; // Количество пробелов для отступа
    
    public static function toMDS(string $html): string {
        // Добавляем заголовок с указанием кодировки UTF-8
        $html = '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body>' . $html . '</body></html>';
        
        $dom = new DOMDocument();
        @$dom->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING);
        
        $output = '';
        self::processNode($dom->documentElement, 0, $output);
        return trim($output);
    }

    private static function processNode(DOMNode $node, int $level, string &$output) {
        // Пропускаем служебные теги и текстовые узлы без содержимого
        if ($node instanceof DOMText || $node->nodeName === '#comment') {
            $text = trim($node->nodeValue);
            if ($text !== '') {
                self::addLine(0, "<".$text.">", $output);
            }
            return;
        }

        // Пропускаем добавленные нами теги html, head, body
        if (in_array($node->nodeName, ['html', 'head', 'body', '!DOCTYPE'])) {
            if ($node->hasChildNodes()) {
                foreach ($node->childNodes as $child) {
                    self::processNode($child, $level, $output);
                }
            }
            return;
        }

        // Формируем строку тега
        $selector = self::buildSelector($node);
        
        // Добавляем строку с тегом
        self::addLine($level, $selector, $output);

        // Рекурсивная обработка дочерних элементов
        if ($node->hasChildNodes()) {
            foreach ($node->childNodes as $child) {
                self::processNode($child, $level + 1, $output);
            }
        }
    }

    private static function buildSelector(DOMElement $element): string {
        $parts = [$element->nodeName];
        
        // ID
        if ($element->hasAttribute('id')) {
            $parts[] = ' #' . $element->getAttribute('id');
        }

        // Классы
        if ($element->hasAttribute('class')) {
            $classes = explode(' ', $element->getAttribute('class'));
            $parts[] = ' .' . implode(' ', $classes);
        }

        // Атрибуты (кроме id и class)
        $attrs = [];
        foreach ($element->attributes as $attr) {
            if (!in_array($attr->name, ['id', 'class'])) {
                $attrs[] = $attr->name . "='" . $attr->value . "'";
            }
        }
        if (!empty($attrs)) {
            $parts[] = ' | ' . implode(' ', $attrs);
        }

        return implode('', $parts);
    }

    private static function addLine(int $level, string $content, string &$output) {
        $indent = str_repeat(' ', $level * self::$indent);
        #$output .= $indent.'<'.$level.' '.$content.">\n";
		$output .= str_replace(['<0 <', '>>'], "\n", $indent.'<'.$level.' '.$content . ">\n");
    }
}

