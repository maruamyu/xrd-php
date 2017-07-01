<?php

namespace Maruamyu\Core\Xrd\Data;

/**
 * host-meta entity (RFC6415)
 */
class HostMeta extends General
{
    /**
     * get LRDD template URI
     *
     * @param string $type Content-Type
     * @return string LRDD template URI
     */
    public function getLrdd($type = General::XRD_CONTENT_TYPE)
    {
        foreach ($this->links as $link) {
            if (
                (strcasecmp($link->getRel(), 'lrdd') == 0)
                && (strcasecmp($link->getType(), $type) == 0)
                && !is_null($link->getTemplate())
            ) {
                return $link->getTemplate();
            }
        }
        return '';
    }

    /**
     * add LRDD Link Elements
     *
     * @param string $template template URL
     * @param string $type Content-Type
     */
    public function addLrdd($template, $type = General::XRD_CONTENT_TYPE)
    {
        $link = new LinkElement('lrdd', $template, $type, true);
        $this->addLink($link);
    }
}
