<?php

namespace Maruamyu\Core\Xrd\Data;

/**
 * XRD Link Element
 */
class LinkElement
{
    /**
     * default lang
     */
    const TITLE_LANG_DEFAULT = 'default';

    /**
     * @var string
     */
    protected $rel;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $href;

    /**
     * @var string
     */
    protected $template;

    /**
     * @var array
     */
    protected $titles;

    /**
     * @var array
     */
    protected $properties;

    /**
     * initialize
     *
     * @param string $rel rel value
     * @param string $href href value
     * @param string $type type value
     * @param boolean $isTemplate if true, then href is template
     */
    public function __construct($rel = null, $href = null, $type = null, $isTemplate = false)
    {
        $this->rel = $rel;
        if ($isTemplate) {
            $this->href = null;
            $this->template = $href;
        } else {
            $this->href = $href;
            $this->template = null;
        }
        $this->type = $type;
        $this->titles = [];
        $this->properties = [];
    }

    /**
     * @return string|null rel value
     */
    public function getRel()
    {
        return $this->rel;
    }

    /**
     * @return string|null type value
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string|null href value
     */
    public function getHref()
    {
        return $this->href;
    }

    /**
     * @return string|null href value
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param string $key key
     * @return string|null value
     */
    public function getProperty($key)
    {
        if (isset($this->properties[$key])) {
            return $this->properties[$key];
        } else {
            return null;
        }
    }

    /**
     * @param string $key key
     * @param string|null $value value
     */
    public function setProperty($key, $value)
    {
        # TODO validation
        $this->properties[$key] = $value;
    }

    /**
     * @return array hash of Properties
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @param array $properties hash of Properties
     */
    public function setProperties(array $properties)
    {
        foreach ($properties as $key => $value) {
            $this->setProperty($key, $value);
        }
    }

    /**
     * @param string $lang lang
     * @return string|null value
     */
    public function getTitle($lang = null)
    {
        if (is_null($lang)) {
            $lang = static::TITLE_LANG_DEFAULT;
        }
        if (isset($this->titles[$lang])) {
            return $this->titles[$lang];
        } else {
            return null;
        }
    }

    /**
     * @param string $title title
     * @param string $lang lang of title
     */
    public function setTitle($title, $lang = null)
    {
        if (empty($lang)) {  # '0' is invalid lang
            $lang = static::TITLE_LANG_DEFAULT;
        }
        $this->titles[$lang] = $title;
    }

    /**
     * @return array titles
     */
    public function getTitles()
    {
        return $this->titles;
    }

    /**
     * @return string[] langs
     */
    public function getTitleLangs()
    {
        return array_keys($this->titles);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $linkNode = [];

        if (!is_null($this->rel)) {
            $linkNode['rel'] = $this->rel;
        }

        if (!is_null($this->type)) {
            $linkNode['type'] = $this->type;
        }

        if (!is_null($this->href)) {
            $linkNode['href'] = $this->href;
        }

        if (!is_null($this->template)) {
            $linkNode['template'] = $this->template;
        }

        if (!empty($this->titles)) {
            $linkNode['titles'] = $this->titles;
        }

        if (!empty($this->properties)) {
            $linkNode['properties'] = $this->properties;
        }

        return $linkNode;
    }

    /**
     * @return string XML
     */
    public function toXml()
    {
        $linkNode = simplexml_load_string('<Link />');
        if ($linkNode === false || $linkNode->getName() !== 'Link') {
            throw new \RuntimeException('SimpleXMLElement initialize failed.');
        }

        if (!is_null($this->rel)) {
            $linkNode->addAttribute('rel', $this->rel);
        }

        if (!is_null($this->type)) {
            $linkNode->addAttribute('type', $this->type);
        }

        if (!is_null($this->href)) {
            $linkNode->addAttribute('href', $this->href);
        }

        if (!is_null($this->template)) {
            $linkNode->addAttribute('template', $this->template);
        }

        foreach ($this->titles as $lang => $title) {
            $titleNode = $linkNode->addChild('Title', $title);
            if ($lang !== static::TITLE_LANG_DEFAULT) {
                $titleNode->addAttribute('xmlns:xml:lang', $lang);
            }
        }

        foreach ($this->properties as $type => $property) {
            $propertyMode = $linkNode->addChild('Property', $property);
            $propertyMode->addAttribute('type', $type);
            if (is_null($property)) {
                $propertyMode->addAttribute('xmlns:xsi:nil', 'true');
            }
        }

        list(, $linkXmlString) = explode("\n", $linkNode->asXML());
        return $linkXmlString;
    }

    /**
     * @return string XML
     * @see toXml()
     */
    public function __toString()
    {
        return $this->toXml();
    }

    /**
     * @param array $link
     * @return static
     */
    public static function fromArray(array $link)
    {
        $instance = new static();
        $instance->setFromArray($link);
        return $instance;
    }

    /**
     * @param array $link
     */
    protected function setFromArray(array $link)
    {
        if (isset($link['rel'])) {
            $this->rel = $link['rel'];
        }

        if (isset($link['type'])) {
            $this->type = $link['type'];
        }

        if (isset($link['href'])) {
            $this->href = $link['href'];
        }

        if (isset($link['template'])) {
            $this->template = $link['template'];
        }

        if (isset($link['titles'])) {
            $this->titles = [];
            foreach ($link['titles'] as $lang => $title) {
                $this->setTitle($title, $lang);
            }
        }
        if (isset($link['properties'])) {
            $this->properties = [];
            $this->setProperties($link['properties']);
        }
    }
}
