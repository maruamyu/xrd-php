<?php

namespace Maruamyu\Core\Xrd\Data;

/**
 * XRD entity (RFC6415)
 */
class General
{
    /**
     * XRD Content-Type
     */
    const XRD_CONTENT_TYPE = 'application/xrd+xml';

    /**
     * JRD Content-Type
     */
    const JRD_CONTENT_TYPE = 'application/jrd+json';

    /**
     * XRD template
     */
    const XRD_TEMPLATE = '<?xml version="1.0" encoding="UTF-8"?><XRD xmlns="http://docs.oasis-open.org/ns/xri/xrd-1.0"/>';

    /**
     * XML original namespace
     */
    const XML_NAMESPACE = 'http://www.w3.org/XML/1998/namespace';

    /**
     * XML Schema instance namespace
     */
    const XSI_NAMESPACE = 'http://www.w3.org/2001/XMLSchema-instance';

    /**
     * Subject
     * @var string
     */
    protected $subject;

    /**
     * Expires
     * @var \DateTime
     */
    protected $expires;

    /**
     * list of Alias
     * @var string[]
     */
    protected $aliases;

    /**
     * hash of Property
     * @var array
     */
    protected $properties;

    /**
     * list of Link element
     * @var LinkElement[]
     */
    protected $links;

    /**
     * @param string $xmlString XRD String
     * @return static
     */
    public static function fromXml($xmlString)
    {
        $rd = new static();
        $rd->setFromXml($xmlString);
        return $rd;
    }

    /**
     * @param string $jsonString JRD String
     * @return static
     */
    public static function fromJson($jsonString)
    {
        $rd = new static();
        $rd->setFromJson($jsonString);
        return $rd;
    }

    /**
     * initialize object
     *
     * @param string $subject Subject
     */
    public function __construct($subject = '')
    {
        $this->subject = strval($subject);
        $this->expires = null;
        $this->aliases = [];
        $this->properties = [];
        $this->links = [];
    }

    /**
     * @return string XRD
     */
    public function __toString()
    {
        return $this->toXml();
    }

    /**
     * @return string Subject
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $subject Subject
     */
    public function setSubject($subject)
    {
        $this->subject = strval($subject);
    }

    /**
     * @return \DateTime expires
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * @param \DateTime $expires expires
     */
    public function setExpires(\DateTime $expires)
    {
        $this->expires = $expires;
    }

    /**
     * @return string[] list of Alias
     */
    public function getAliases()
    {
        return $this->aliases;
    }

    /**
     * @param string $alias Alias
     */
    public function addAlias($alias)
    {
        # TODO validation
        $this->aliases[] = strval($alias);
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
     * @return LinkElement[] list of Link element
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * @param string $rel rel
     * @return array[] found Link elements
     */
    public function findLinkElements($rel)
    {
        $foundLinkElements = [];
        foreach ($this->links as $link) {
            if (strcasecmp($link->getRel(), $rel) == 0) {
                $foundLinkElements[] = $link;
            }
        }
        return $foundLinkElements;
    }

    /**
     * @param string $rel rel
     * @return LinkElement|null first found Link element
     */
    public function findFirstLinkElement($rel)
    {
        foreach ($this->links as $link) {
            if (strcasecmp($link->getRel(), $rel) == 0) {
                return $link;
            }
        }
        return null;
    }

    /**
     * @param LinkElement|array $link link element
     * @throws \UnexpectedValueException if invalid arg
     */
    public function addLink($link)
    {
        if ($link instanceof LinkElement) {
            $this->links[] = $link;
        } elseif (is_array($link)) {
            $linkElement = LinkElement::fromArray($link);
            $this->links[] = $linkElement;
        }
    }

    /**
     * @return string XRD
     */
    public function toXml()
    {
        $xml = simplexml_load_string(static::XRD_TEMPLATE);
        if ($xml === false || $xml->getName() !== 'XRD') {
            throw new \RuntimeException('SimpleXMLElement initialize failed.');
        }
        $isUseXsiNamespace = false;

        if ($this->subject !== '') {
            $xml->addChild('Subject', $this->subject);
        }

        if ($this->expires) {
            $xml->addChild('Expires', $this->expires->format(\DateTime::ATOM));
        }

        foreach ($this->aliases as $alias) {
            $xml->addChild('Alias', $alias);
        }

        foreach ($this->properties as $type => $value) {
            $propertyMode = $xml->addChild('Property', $value);
            $propertyMode->addAttribute('type', $type);
            if (is_null($value)) {
                $propertyMode->addAttribute('xmlns:xsi:nil', 'true');
                $isUseXsiNamespace = true;
            }
        }

        foreach ($this->links as $link) {
            $linkNode = $xml->addChild('Link');
            if (!is_null($link->getRel())) {
                $linkNode->addAttribute('rel', $link->getRel());
            }
            if (!is_null($link->getType())) {
                $linkNode->addAttribute('type', $link->getType());
            }
            if (!is_null($link->getHref())) {
                $linkNode->addAttribute('href', $link->getHref());
            }
            if (!is_null($link->getTemplate())) {
                $linkNode->addAttribute('template', $link->getTemplate());
            }
            foreach ($link->getTitles() as $lang => $title) {
                $titleNode = $linkNode->addChild('Title', $title);
                if ($lang !== LinkElement::TITLE_LANG_DEFAULT) {
                    $titleNode->addAttribute('xmlns:xml:lang', $lang);
                }
            }
            foreach ($link->getProperties() as $type => $property) {
                $propertyMode = $linkNode->addChild('Property', $property);
                $propertyMode->addAttribute('type', $type);
                if (is_null($property)) {
                    $propertyMode->addAttribute('xmlns:xsi:nil', 'true');
                    $isUseXsiNamespace = true;
                }
            }
        }

        if ($isUseXsiNamespace) {
            $xml->addAttribute('xmlns:xmlns:xsi', static::XSI_NAMESPACE);
        }
        return $xml->asXML();
    }

    /**
     * @return string JRD
     */
    public function toJson()
    {
        $data = [];

        if ($this->subject !== '') {
            $data['subject'] = $this->subject;
        }

        if ($this->expires) {
            $data['expires'] = $this->expires->format(\DateTime::ATOM);
        }

        if (!empty($this->aliases)) {
            $data['aliases'] = $this->aliases;
        }

        if (!empty($this->properties)) {
            $data['properties'] = $this->properties;
        }

        if (!empty($this->links)) {
            $links = [];
            foreach ($this->links as $link) {
                $links[] = $link->toArray();
            }
            $data['links'] = $links;
        }

        if (empty($data)) {
            return '{}';
        } else {
            return json_encode($data);
        }
    }

    /**
     * @param string $xmlString XRD String
     * @throws \UnexpectedValueException if invalid XML
     */
    protected function setFromXml($xmlString)
    {
        $xml = simplexml_load_string($xmlString);
        if ($xml === false) {
            throw new \UnexpectedValueException('invalid XML.');
        }

        if ($xml->getName() !== 'XRD') {
            throw new \UnexpectedValueException('invalid XRD.');
        }

        $this->subject = strval($xml->Subject);

        if (isset($xml->Expires)) {
            $this->expires = new \DateTime(strval($xml->Expires));
        } else {
            $this->expires = null;
        }

        $this->aliases = [];
        if (isset($xml->Alias)) {
            foreach ($xml->Alias as $alias) {
                $this->aliases[] = strval($alias);
            }
        }

        if (isset($xml->Property)) {
            $this->properties = static::parsePropertyNode($xml->Property);
        } else {
            $this->properties = [];
        }

        $this->links = [];
        if (isset($xml->Link)) {
            foreach ($xml->Link as $linkNode) {
                $attributes = $linkNode->attributes();
                $relAttr = (isset($attributes['rel'])) ? strval($attributes['rel']) : null;
                $typeAttr = (isset($attributes['type'])) ? strval($attributes['type']) : null;
                if (isset($attributes['template'])) {
                    $linkElement = new LinkElement($relAttr, strval($attributes['template']), $typeAttr, true);
                } else {
                    $hrefAttr = (isset($attributes['href'])) ? strval($attributes['href']) : null;
                    $linkElement = new LinkElement($relAttr, $hrefAttr, $typeAttr);
                }
                if (isset($linkNode->Title)) {
                    foreach ($linkNode->Title as $titleNode) {
                        $lang = null;
                        $attributes = $titleNode->attributes();
                        if (isset($attributes['lang'])) {
                            $lang = strval($attributes['lang']);
                        }
                        $xmlAttributes = $titleNode->attributes(static::XML_NAMESPACE);
                        if (isset($xmlAttributes['lang'])) {
                            $lang = strval($xmlAttributes['lang']);
                        }
                        $linkElement->setTitle(strval($titleNode), $lang);
                    }
                }
                if (isset($linkNode->Property)) {
                    $linkElement->setProperties(static::parsePropertyNode($linkNode->Property));
                }
                $this->links[] = $linkElement;
            }
        }
    }

    /**
     * @param string $jsonString JRD String
     * @throws \UnexpectedValueException if invalid JSON
     */
    protected function setFromJson($jsonString)
    {
        $data = json_decode($jsonString, true);
        if (is_null($data)) {
            throw new \UnexpectedValueException('invalid JSON.');
        }
        $this->setFromArray($data);
    }

    /**
     * @param array $jrd decoded JRD JSON data
     */
    protected function setFromArray(array $jrd)
    {
        $this->subject = strval($jrd['subject']);

        if (isset($jrd['expires'])) {
            $this->expires = new \DateTime(strval($jrd['expires']));
        } else {
            $this->expires = null;
        }

        if (isset($jrd['aliases']) && !empty($jrd['aliases'])) {
            $this->aliases = $jrd['aliases'];
        } else {
            $this->aliases = [];
        }

        if (isset($jrd['properties']) && !empty($jrd['properties'])) {
            $this->properties = $jrd['properties'];
        } else {
            $this->properties = [];
        }

        $this->links = [];
        if (isset($jrd['links'])) {
            foreach ($jrd['links'] as $link) {
                $relAttr = (isset($link['rel'])) ? $link['rel'] : null;
                $typeAttr = (isset($link['type'])) ? $link['type'] : null;
                if (isset($link['template'])) {
                    $linkElement = new LinkElement($relAttr, $link['template'], $typeAttr, true);
                } else {
                    $hrefAttr = (isset($link['href'])) ? $link['href'] : null;
                    $linkElement = new LinkElement($relAttr, $hrefAttr, $typeAttr);
                }
                if (isset($link['titles'])) {
                    foreach ($link['titles'] as $lang => $title) {
                        $linkElement->setTitle($title, $lang);
                    }
                }
                if (isset($link['properties'])) {
                    $linkElement->setProperties($link['properties']);
                }
                $this->links[] = $linkElement;
            }
        }
    }

    /**
     * @param \SimpleXMLElement[] $propertyNodes
     * @return array parsed hash
     */
    protected static function parsePropertyNode(\SimpleXMLElement $propertyNodes)
    {
        $properties = [];
        foreach ($propertyNodes as $propertyNode) {
            $attributes = $propertyNode->attributes();
            $xsiAttributes = $propertyNode->attributes(static::XSI_NAMESPACE);

            $key = strval($attributes['type']);
            if ($key === '') {
                throw new \UnexpectedValueException('invalid XRD. (type is empty in Property)');
            }

            $value = strval($propertyNode);
            if ((isset($xsiAttributes['nil']) && strval($xsiAttributes['nil']) === 'true')) {
                $value = null;
            }

            $properties[$key] = $value;
        }
        return $properties;
    }
}
