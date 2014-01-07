<?php

# Create by LYK on 2012-01-09 @ 12:12PM
# Addon by LYK on 2013-01-29 @ 18:00PM

class Array2XML
{

    private static $xml = null;
    private static $encoding = 'UTF-8';

    public static function init($version = '1.0', $encoding = 'UTF-8', $format_output = true)
    {
        self::$xml = new DomDocument($version, $encoding);
        self::$xml->formatOutput = $format_output;
        self::$encoding = $encoding;
    }

    public static function &createXML($node_name, $arr = array())
    {
        $xml = self::getXMLRoot();
        $xml->appendChild(self::convert($node_name, $arr));

        self::$xml = null; // clear the xml node in the class for 2nd time use.
        return $xml;
    }

    private static function &convert($node_name, $arr = array())
    {
        $xml = self::getXMLRoot();
        $node = $xml->createElement($node_name);
        if (is_array($arr)) {
            if (array_key_exists('@attributes', $arr)) {
                foreach ($arr['@attributes'] as $key => $value) {
                    if (!self::isValidTagName($key))
                        throw new Exception('[Array2XML] Illegal character in attribute name. attribute: ' .
                            $key . ' in node: ' . $node_name);
                    $node->setAttribute($key, self::bool2str($value));
                }
                unset($arr['@attributes']);
            }
            if (array_key_exists('@value', $arr)) {
                $node->appendChild($xml->createTextNode(self::bool2str($arr['@value'])));
                unset($arr['@value']);
                return $node;
            } elseif (array_key_exists('@cdata', $arr)) {
                $node->appendChild($xml->createCDATASection(self::bool2str($arr['@cdata'])));
                unset($arr['@cdata']);
                return $node;
            }
            foreach ($arr as $key => $value) {
                if (!self::isValidTagName($key)) {
                    throw new Exception('[Array2XML] Illegal character in tag name. tag: ' . $key .
                        ' in node: ' . $node_name);
                }
                if (is_array($value) && is_numeric(key($value)))
                    foreach ($value as $k => $v)
                        $node->appendChild(self::convert($key, $v));
                    else
                        $node->appendChild(self::convert($key, $value));
                unset($arr[$key]);
            }
        }
        if (!is_array($arr))
            $node->appendChild($xml->createTextNode(self::bool2str($arr)));
        return $node;
    }

    private static function getXMLRoot()
    {
        if (empty(self::$xml))
            self::init();
        return self::$xml;
    }

    private static function bool2str($v)
    {
        $v = $v === true ? 'true' : $v;
        $v = $v === false ? 'false' : $v;
        return $v;
    }

    private static function isValidTagName($tag)
    {
        $pattern = '/^[a-z_]+[a-z0-9\:\-\.\_]*[^:]*$/i';
        return preg_match($pattern, $tag, $matches) && $matches[0] == $tag;
    }
}

class XML2Array
{
    private $XML_Resource;
    private $Values;
    private $Index;
    private $Error;
    private $IsParser = false;

    public function __construct()
    {
        $this->XML_Resource = xml_parser_create('UTF-8');
        xml_parser_set_option($this->XML_Resource, XML_OPTION_CASE_FOLDING, true);
        xml_parser_set_option($this->XML_Resource, XML_OPTION_SKIP_WHITE, true);
        $this->IsParser = (boolean)false;
    }

    public function __destruct()
    {
        if (is_resource($this->XML_Resource))
            xml_parser_free($this->XML_Resource);
    }

    public function XML_Reset()
    {
        $this->__destruct();
        usleep(1000);
        $this->Values = array();
        $this->Index = array();
        $this->Error = '';
        $this->IsParser = (boolean)false;
        $this->__construct();
    }

    public function XML_Parser($Data)
    {
        if (!xml_parse_into_struct($this->XML_Resource, $Data, $this->Values, $this->
            Index)) {
            $this->Error = xml_error_string(xml_get_error_code($this->XML_Resource)) .
                ' at line ' . xml_get_current_line_number($this->XML_Resource);
            $this->IsParser = (boolean)false;
        } else {
            $this->IsParser = (boolean)true;
        }
        return $this->IsParser;
    }

    public function XML_Error()
    {
        return $this->Error;
    }

    public function XML_Tag_Count($Tag = '')
    {
        if ($this->IsParser === (boolean)true) {
            $Tag = str_replace(" ", "", strtoupper($Tag));
            if (!empty($Tag) && array_key_exists($Tag, $this->Index)) {
                return count($this->Index[$Tag]);
            } elseif (empty($Tag)) {
                return count($this->Values);
            } else {
                return false;
            }
        } else {
            return $this->IsParser;
        }
    }

    public function XML_Index($Tag = '', $Index = 0)
    {
        if ($this->IsParser === (boolean)true) {
            $Tag = str_replace(" ", "", strtoupper($Tag));
            if (!empty($Tag) && array_key_exists($Tag, $this->Index) && array_key_exists($Index,
                $this->Index[$Tag])) {
                return $this->Index[$Tag][$Index];
            } elseif (empty($Tag)) {
                return $this->Index;
            } else {
                return false;
            }
        } else {
            return $this->IsParser;
        }
    }

    public function XML_Value($Index = 0)
    {
        if ($this->IsParser === (boolean)true) {
            if (!empty($Index) && array_key_exists($Index, $this->Values)) {
                return $this->Values[$Index]['value'];
            } elseif ($Index == 0) {
                return $this->Values;
            } else {
                return false;
            }
        } else {
            return $this->IsParser;
        }
    }

    public function XML_Attribute($Index = null, $Attribute = null)
    {
        if ($this->IsParser === (boolean)true) {
            if (!empty($Index) && array_key_exists($Index, $this->Values)) {
                if (!empty($Attribute) && array_key_exists('attributes', $this->Values[$Index])) {
                    return $this->Values[$Index]['attributes'][$Attribute];
                } elseif (array_key_exists('attributes', $this->Values[$Index])) {
                    return $this->Values[$Index]['attributes'];
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return $this->IsParser;
        }
    }

    public function XML_Type($Index = 0)
    {
        if ($this->IsParser === (boolean)true) {
            if (!empty($Index) && array_key_exists($Index, $this->Values)) {
                return $this->Values[$Index]['type'];
            } else {
                return false;
            }
        } else {
            return $this->IsParser;
        }
    }

    public function XML_Tag($Index = 0)
    {
        if ($this->IsParser === (boolean)true) {
            if (!empty($Index) && array_key_exists($Index, $this->Values)) {
                return $this->Values[$Index]['tag'];
            } else {
                return false;
            }
        } else {
            return $this->IsParser;
        }
    }

    public function XML_Level($Index = 0)
    {
        if ($this->IsParser === (boolean)true) {
            if (!empty($Index) && array_key_exists($Index, $this->Values)) {
                return $this->Values[$Index]['level'];
            } else {
                return false;
            }
        } else {
            return $this->IsParser;
        }
    }

    public function XML_Cell($Tag, $Index = 0)
    {
        if ($this->IsParser !== (boolean)true)
            return $this->IsParser;
        $Tag = str_replace(" ", "", strtoupper($Tag));
        if (!array_key_exists($Tag, $this->Index)) {
            return false;
        } elseif (!array_key_exists($Index, $this->Index[$Tag])) {
            return false;
        } elseif (!array_key_exists($this->Index[$Tag][$Index], $this->Values)) {
            return false;
        }
        $Cell = $this->Values[$this->Index[$Tag][$Index]]['value'];
        if (is_double($Cell)) {
            $Cell = (double)$Cell;
        } elseif (is_int($Cell)) {
            $Cell = (int)$Cell;
        } elseif (is_string($Cell)) {
            $Cell = (string )$Cell;
        } elseif (empty($Cell)) {
            $Cell = null;
        }
        return $Cell;
    }

    public function XML_Cell_Attribute($Tag, $Attribute, $Index = 0)
    {
        if ($this->IsParser !== (boolean)true)
            return $this->IsParser;
        $Tag = str_replace(" ", "", strtoupper($Tag));
        $Attribute = str_replace(" ", "", strtoupper($Attribute));
        if (!array_key_exists($Tag, $this->Index)) {
            return false;
        } elseif (!array_key_exists($Index, $this->Index[$Tag])) {
            return false;
        } elseif (!array_key_exists($this->Index[$Tag][$Index], $this->Values)) {
            return false;
        } elseif (empty($Attribute) && array_key_exists('attributes', $this->Values[$this->
        Index[$Tag][$Index]])) {
            return $this->Values[$this->Index[$Tag][$Index]]['attributes'];
        } elseif (!array_key_exists('attributes', $this->Values[$this->Index[$Tag][$Index]])) {
            return false;
        }
        $Cell_Attb = $this->Values[$this->Index[$Tag][$Index]]['attributes'][$Attribute];
        if (is_double($Cell_Attb)) {
            $Cell_Attb = (double)$Cell_Attb;
        } elseif (is_int($Cell_Attb)) {
            $Cell_Attb = (int)$Cell_Attb;
        } elseif (is_string($Cell_Attb)) {
            $Cell_Attb = (string )$Cell_Attb;
        } elseif (empty($Cell_Attb)) {
            $Cell_Attb = null;
        }
        return $Cell_Attb;
    }
}

?>