<?php

namespace Maruamyu\Core\Xrd\Data;

/**
 * WebFinger entity (RFC7033)
 */
class WebFinger extends General
{
    const REL_PROFILE_PAGE = 'http://webfinger.net/rel/profile-page';

    /**
     * @return string webfinger profile-page href
     */
    public function getProfilePageHref()
    {
        return $this->getLinkHref(static::REL_PROFILE_PAGE);
    }

    /**
     * @param string $rel rel
     * @return string value of href
     * @see findFirstLinkElement()
     */
    public function getLinkHref($rel)
    {
        $link = $this->findFirstLinkElement($rel);
        if ($link) {
            return strval($link->getHref());
        } else {
            return '';
        }
    }

    /**
     * @param string $rel rel
     * @return string value of template
     * @see findFirstLinkElement()
     */
    public function getLinkTemplate($rel)
    {
        $link = $this->findFirstLinkElement($rel);
        if ($link) {
            return strval($link->getTemplate());
        } else {
            return '';
        }
    }
}
