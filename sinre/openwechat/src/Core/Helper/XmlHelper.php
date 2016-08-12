<?php
/**
 * Created by PhpStorm.
 * User: songzhen
 * Date: 2016/7/22
 * Time: 16:09
 */
namespace OpenWechat\Core\Helper;

class XmlHelper
{
    private $_xml;
    private $_body;
    public function __construct()
    {
        $this->_xml = new \XMLReader();
    }
    public function setXml($xml)
    {
        $this->_body = $xml;
        $this->_xml->XML($xml);
    }
    public function getValue($tagname)
    {
        $value = '';
        if(!$tagname) return $value;

        $this->setXml($this->_body);

        while ($this->_xml->read()){
            if($this->_xml->nodeType == \XMLReader::END_ELEMENT && $this->_xml->name == $tagname)
            {
                return $value;
            }
            if($this->_xml->nodeType == \XMLReader::TEXT || $this->_xml->nodeType == \XMLReader::CDATA){
                $value = $this->_xml->value;
            }
        }
        return '';
    }
    /**
     * 获取指定循环tag的数组，递归实现
     * @param string $tagname 标签名
     * @return array $frame 数组
     */
    public function getFrame($tagname)
    {
        $frame = array();
        if(!$tagname) return $frame;

        while ($this->_xml->read()){
            if($this->_xml->nodeType == \XMLReader::ELEMENT){
                if(!$this->_xml->isEmptyElement && $this->_xml->name == $tagname){
                    $frame[$tagname][] = $this->getFrame($this->_xml->name);
                    $this->_xml->next();
                }
            }
            if($this->_xml->nodeType == \XMLReader::TEXT || $this->_xml->nodeType == \XMLReader::CDATA){
                $value = $this->_xml->value;
            }
            if($this->_xml->nodeType == \XMLReader::END_ELEMENT){
                if($this->_xml->name == $tagname) return $frame;
                $frame[$this->_xml->name] = $value;
                $value = '';
            }
        }
        return $frame[$tagname];
    }
    /**
     * 获取指定循环tag的数组，非递归实现
     * @param string $tagname 标签名
     * @param array $field 返回字段名
     * @return array $frame 数组
     */
    public function getFrameUnRecursive($tagname, $field = array())
    {
        $frame = array();
        if(!$tagname) return $frame;

        while ($this->_xml->read()){
            if($this->_xml->nodeType == \XMLReader::ELEMENT && $this->_xml->name == $tagname){
                $dom = $this->_xml->expand();
                if($dom->hasChildNodes()){
                    $items = array();
                    foreach ($dom->childNodes as $c){
                        if($c instanceof \DOMElement){
                            if($field && is_array($field) && !in_array($c->tagName, $field)){
                                continue;
                            }
                            $items[$c->tagName] = $c->nodeValue;
                        }
                    }
                    $frame[] = $items;
                }
            }
        }
        return count($frame) > 1 ? $frame : $frame['0'];
    }
    public function __destruct()
    {
        $this->_xml->close();
    }

    /**
     * XML encode.
     *
     * @param mixed  $data
     * @param string $root
     * @param string $item
     * @param string $attr
     * @param string $id
     *
     * @return string
     */
    public static function build(
        $data,
        $root = 'xml',
        $item = 'item',
        $attr = '',
        $id = 'id'
    ) {
        if (is_array($attr)) {
            $_attr = [];

            foreach ($attr as $key => $value) {
                $_attr[] = "{$key}=\"{$value}\"";
            }

            $attr = implode(' ', $_attr);
        }

        $attr = trim($attr);
        $attr = empty($attr) ? '' : " {$attr}";
        $xml = "<{$root}{$attr}>";
        $xml  .= self::data2Xml($data, $item, $id);
        $xml  .= "</{$root}>";

        return $xml;
    }
    /**
     * Array to XML.
     *
     * @param array  $data
     * @param string $item
     * @param string $id
     *
     * @return string
     */
    protected static function data2Xml($data, $item = 'item', $id = 'id')
    {
        $xml = $attr = '';

        foreach ($data as $key => $val) {
            if (is_numeric($key)) {
                $id && $attr = " {$id}=\"{$key}\"";
                $key = $item;
            }

            $xml .= "<{$key}{$attr}>";

            if ((is_array($val) || is_object($val))) {
                $xml .= self::data2Xml((array) $val, $item, $id);
            } else {
                $xml .= is_numeric($val) ? $val : self::cdata($val);
            }

            $xml .= "</{$key}>";
        }

        return $xml;
    }
    /**
     * Build CDATA.
     *
     * @param string $string
     *
     * @return string
     */
    public static function cdata($string)
    {
        return sprintf('<![CDATA[%s]]>', $string);
    }
}