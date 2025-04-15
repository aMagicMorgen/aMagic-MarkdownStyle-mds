<?php
//class-MDS-2.2
/*
/////Для вывода ошибок на экран  ini_set('display_errors','on'); on || of
#print_r(function_exists('mb_internal_encoding')); //проверка 1-подключено, 0 - не подключено
error_reporting(E_ALL);
ini_set('display_errors','on');
mb_internal_encoding('UTF-8');
*/
#require_once 'Michelf/MarkdownExtra.inc.php';

/**
 * Класс MarkdownStyle - расширение MarkdownExtra с поддержкой MDS
 *///extends \Michelf\MarkdownExtra
class MarkdownStyle  {
/*    public function __construct() {
        parent::__construct();
        $this->block_gamut['parseMdsBlocks'] = 5;
    }
*/
    /**
     * Преобразует MDS-блоки в HTML
     */
    protected function parseMdsBlocks(string $text): string {
        return preg_replace_callback(
            #'/<!--\s*mds\s*(.*?)\s*-->/s',
			#'/<!--\s*mds\s*-->\s*\n?(.*?)\n?\s*\/mds\s*-->/s',
			'/<!--\s*mds\s*(.*?)\s*\/mds\s*-->/s',
            function ($matches) {
                return MDS::toHtml(trim($matches[1]));
            },
            $text
        );
    }

    /**
     * Статический метод для удобного преобразования
     */
    public static function parse(string $text): string {
        $parser = new self();
        // Вызываем родительский метод parse, а не transform
        return $parser->parseMdsBlocks($text);
    }
}

class MDS {
    const VOID_TAGS = [
        'area', 'base', 'br', 'col', 'embed', 'hr', 'img',
        'input', 'link', 'meta', 'param', 'source', 'track', 'wbr'
    ];

    public static function toHtml(string $mds): string {
        $cleaned_mds = self::removeComments($mds);
        return self::buildHtml(self::parse($cleaned_mds));
    }

    private static function removeComments(string $content): string {
        return preg_replace('/<!-.*?->/s', '', $content);
    }

    private static function parse(string $mds): array {
        $tags = explode('<', $mds);
        $parsed_tags = [];
        $prev_level = 0;
        
        foreach ($tags as $tag) {
            if (trim($tag) === '') continue;
            
            // Определяем уровень вложенности
            $spaces_before = strlen($tag) - strlen(ltrim($tag));
            $level = $spaces_before;
            
            // Проверяем наличие цифры уровня
            if (preg_match('/^\s*(\d+)\s/', $tag, $matches)) {
                $level = (int)$matches[1];
                // Удаляем уровень из строки
                $tag = preg_replace('/^\s*\d+\s*/', '', $tag);
            }
            
            $tag = ltrim($tag);
            
            // Разделяем атрибуты и текст
            $parts = explode('>', $tag, 2);
            $text = isset($parts[1]) ? $parts[1] : '';
            $left_part = $parts[0];
            
            // Инициализация
            $attributes = '';
            $class = '';
            $id = '';
            $name = '';
            
            // 1. Обработка атрибутов после |
            if (strpos($left_part, '|') !== false) {
                $parts = explode('|', $left_part, 2);
                $left_part = trim($parts[0]);
                $attributes = ' ' . trim($parts[1]);
            }
            
            // 2. Обработка классов после .
            if (strpos($left_part, '.') !== false) {
                $parts = explode('.', $left_part, 2);
                $left_part = trim($parts[0]);
                $class = ' class="' . htmlspecialchars(trim($parts[1])) . '"';
            }
            
            // 3. Обработка id и name после #
            if (strpos($left_part, '#') !== false) {
                $parts = explode('#', $left_part, 2);
                $left_part = trim($parts[0]);
                $id_content = trim($parts[1]);
                
                $id_parts = explode(' ', $id_content, 2);
                $id = ' id="' . htmlspecialchars($id_parts[0]) . '"';
                
                if (isset($id_parts[1])) {
                    $name = ' name="' . htmlspecialchars(trim($id_parts[1])) . '"';
                }
            }
            
            // 4. Обработка тега
            $elements = preg_split('/\s+/', trim($left_part), 2);
            if (count($elements) < 1) continue;
            
            $tag_name = $elements[0];
            
            $parsed_tags[] = [
                'level' => $level,
                'tag' => $tag_name,
                'attrs' => $id . $name . $class . $attributes,
                'text' => $text
            ];
        }
        
        // Нормализуем уровни
        $min_level = PHP_INT_MAX;
        foreach ($parsed_tags as $tag) {
            if ($tag['level'] < $min_level) {
                $min_level = $tag['level'];
            }
        }
        
        foreach ($parsed_tags as &$tag) {
            $tag['level'] -= $min_level;
        }
        
        return $parsed_tags;
    }

    private static function buildHtml(array $tags): string {
        $stack = [];
        $html = '';
        
        foreach ($tags as $tag) {
            // Закрываем теги с уровнем >= текущего
            while (!empty($stack) && end($stack)['level'] >= $tag['level']) {
                $last_tag = array_pop($stack);
                if (!in_array($last_tag['tag'], self::VOID_TAGS)) {
                    $html .= "\n" . str_repeat('  ', $last_tag['level']) . '</' . $last_tag['tag'] . '>';
                }
            }
            
            $is_void = in_array($tag['tag'], self::VOID_TAGS);
            $html .= "\n" . str_repeat('  ', $tag['level']) . '<' . $tag['tag'] . $tag['attrs'];
            $html .= $is_void ? ' />' : '>' . $tag['text'];
            
            if (!$is_void) {
                array_push($stack, $tag);
            }
        }
        
        // Закрываем оставшиеся теги
        while (!empty($stack)) {
            $last_tag = array_pop($stack);
            if (!in_array($last_tag['tag'], self::VOID_TAGS)) {
                $html .= "\n" . str_repeat('  ', $last_tag['level']) . '</' . $last_tag['tag'] . '>';
            }
        }
        $html = preg_replace(['/<-.*?->/s','/<\/-.*?->/s', '/<!-.*?->/s','/<\/!-.*?->/s'], '', $html);
        return ltrim($html, "\n");
    }
}
