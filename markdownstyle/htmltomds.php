<?php
//
class Html {
    private $indent = 1; // Количество пробелов для отступа
    private $output = '';

    public function toMDS(string $html): string {
        // Добавляем заголовок с указанием кодировки UTF-8
        $html = '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body>' . $html . '</body></html>';
        
        $dom = new DOMDocument();
        $dom->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING);
        
        $this->processNode($dom->documentElement, 0);
        return trim($this->output);
    }

    private function processNode(DOMNode $node, int $level) {
        // Пропускаем служебные теги и текстовые узлы без содержимого
        if ($node instanceof DOMText || $node->nodeName === '#comment') {
            $text = trim($node->nodeValue);
            if ($text !== '') {
                // текст
                $this->addLine(0, "<".$text . ">");
            }
            return;
        }

        // Пропускаем добавленные нами теги html, head, body
        if (in_array($node->nodeName, ['html', 'head', 'body', '!DOCTYPE'])) {
            if ($node->hasChildNodes()) {
                foreach ($node->childNodes as $child) {
                    $this->processNode($child, $level);
                }
            }
            return;
        }

        // Формируем строку тега
        $selector = $this->buildSelector($node);
        
        // Добавляем строку с тегом
        $this->addLine($level, $selector);

        // Рекурсивная обработка дочерних элементов
        if ($node->hasChildNodes()) {
            foreach ($node->childNodes as $child) {
                $this->processNode($child, $level + 1);
            }
        }
    }

    private function buildSelector(DOMElement $element): string {
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

    private function addLine(int $level, string $content) {
        $indent = str_repeat(' ', $level * $this->indent);
        $this->output .= str_replace(['<0 <', '>>'], "\n", $indent.'<'.$level.' '.$content . ">\n");
    }
}


#header('Content-Type: text/html; charset=utf-8');
// Пример использования
$html = <<<HTML
<div id="header" class="main banner" data-role="primary">
    <ul class="nav">
        <li class="item active">Home</li>
        <li>About</li>
    </ul>
    <img src="logo.png" alt="Logo">
</div>
<p>Hello <strong>World</strong></p>
<input class="class1 class2" id="id" name="name" type="text" required>
<div id="menu-container" class="class1 class2 class3" name="name">МЕНЮ
Большое
     Длинное<ul class="nav-menu">
		<li class="folder">
			<span class="folder-name">.</span>
			<ul class="file-list">
				<li class="file">
					<a href="#" data-md=".\README.md" title=".\README.md">README</a>
				</li>
				<li class="file">
					<a href="#" data-md=".\class-HtmlToMDS.md" title=".\class-HtmlToMDS.md">class-HtmlToMDS</a>
				</li>
			</ul>
		</li>
	</ul>
</div>
HTML;


$parser = new Html();
echo $parser->toMDS($html);