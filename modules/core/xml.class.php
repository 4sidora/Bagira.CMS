<?php

/**
 * XML-парсер, замена для SimpleXML

$rss = new XML($xml);

foreach ($rss->rss->channel->item as $it)
echo $it->url."\n";


 */
//
class XML implements ArrayAccess, IteratorAggregate {

    /**
     * Указатель на текущий элемент
     * @var XML
     */
    private $pointer;

    /**
     * Название элемента
     * @var string
     */
    private $tagName;

    /**
     * Ассоциативный массив атрибутов
     * @var array
     */
    private $attributes = array();

    /**
     * Содержимое элемента
     * @var string
     */
    private $cdata;

    /**
     * Указатель на родительский элемент
     * @var XML
     */
    private $parent;

    /**
     * Массив потомков, вида:
     * array('tag1' => array(0 =>, 1 => ...) ...)
     * @var array
     */
    private $childs = array();

    /**
     * Конструктор из строки с xml-текстом
     * или данных вида array('название', array('атрибуты'))
     * @var array|string $data
     */
    public function __construct($data) {

        if (is_array($data)) {
            list($this->tagName, $this->attributes) = $data;
        } else if (is_string($data))
            $this->parse($data);
    }

    /**
     * Метод для доступа к содержанию элемента
     * @return stirng
     */
    public function __toString() {
        return $this->cdata;
    }



    /**
     * Доступ к потомку или массиву потомков
     * @var string $name
     * @return XML|array
     */
    public function __get($name) {
        if (isset($this->childs[$name])) {
            if (count($this->childs[$name]) == 1)
                return $this->childs[$name][0];
            else
                return $this->childs[$name];
        }
        return false;
        //throw new Exception("UFO steals [$name]!");
    }



    /**
     * Доступ к атрибутам текущего элемента
     * @var string $offset
     * @return mixed
     */
    public function offsetGet($offset) {
        if (isset($this->attributes[$offset]))
            return $this->attributes[$offset];
        throw new Exception("Holy cow! There is'nt [$offset] attribute!");

    }



    /**
     * Проверка на существование атрибута
     * @var string $offset
     * @return bool
     */
    public function offsetExists($offset) {
        return isset($this->attributes[$offset]);
    }


    /**
     * Затычки
     */
    public function offsetSet($offset, $value) { return; }
    public function offsetUnset($offset) { return; }


    /**
     * Возвращает количество элементов с этим именем у родителя
     * @return integer
     */
    public function count() {
        if ($this->parent != null)
            return count($this->parent->childs[$this->tagName]);
        return 1;
    }


    /**
     * Возвращает итератор по массиву одноименных элементов
     * @return ArrayIterator
     */
    public function getIterator() {
        if ($this->parent != null)
            return new ArrayIterator($this->parent->childs[$this->tagName]);
        return new ArrayIterator(array($this));
    }



    /**
     * Получить массив атрибутов
     * @return array
     */
    public function getAttributes($name = '') {
        if (!empty($name))
            
            if (isset($this->attributes[$name]))
                return $this->attributes[$name];
            else
                return false;

        else
            return $this->attributes;

    }



    /**
     * Добавить потомка
     * @var string $tag
     * @var array $attributes
     * @return XML
     */
    public function appendChild($tag, $attributes) {
        $element = new XML(array($tag, $attributes));
        $element->setParent($this);
        $this->childs[$tag][] = $element;
        return $element;
    }



    /**
     * Установить родительский элемент
     * @var XML $parent
     */
    public function setParent(XML $parent) {
        $this->parent =& $parent;
    }

    /**
     * Поулчить родительский элемент
     * @return XML
     */
    public function getParent() {
        return $this->parent;
    }

    /**
     * Установить данные элемента
     * @var string $cdata
     */
    public function setCData($cdata) {
        $this->cdata = $cdata;
    }

    /**
     * Парсим xml-строку и делаем дерево элементов
     * @var string $data
     */
    private function parse($data) {
        $this->pointer =& $this;
        $parser = xml_parser_create();
        xml_set_object($parser, $this);
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, false);
        xml_set_element_handler($parser, "tag_open", "tag_close");
        xml_set_character_data_handler($parser, "cdata");
        xml_parse($parser, $data);
    }

    /**
     * При открытии тега, добавляем дите и устанавливаем указатель на него
     */
    private function tag_open($parser, $tag, $attributes) {
        $this->pointer =& $this->pointer->appendChild($tag, $attributes);
    }

    /**
     * При получении данных
     */
    private function cdata($parser, $cdata) {
        $this->pointer->setCData($cdata);
    }



    /**
     * При закрытии тега, возвращаем указатель на предка
     */
    private function tag_close($parser, $tag) {
        $this->pointer =& $this->pointer->getParent();
    }

}

?>
