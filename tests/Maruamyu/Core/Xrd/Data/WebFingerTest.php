<?php

namespace Maruamyu\Core\Xrd\Data;

class WebFingerTest extends \PHPUnit_Framework_TestCase
{
    public function test_getProfilePageHref()
    {
        $webFinger = $this->getFixture();
        $this->assertEquals('https://mastodon.example.jp/@example', $webFinger->getProfilePageHref());
    }

    public function test_getLinkHref()
    {
        $webFinger = $this->getFixture();
        $this->assertEquals('https://mastodon.example.jp/api/salmon/8765', $webFinger->getLinkHref('salmon'));
    }

    public function test_getLinkTemplate()
    {
        $webFinger = $this->getFixture();
        $this->assertEquals('https://mastodon.example.jp/authorize_follow?acct={uri}', $webFinger->getLinkTemplate('http://ostatus.org/schema/1.0/subscribe'));
    }

    /**
     * @return WebFinger fixture
     */
    private function getFixture()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<XRD xmlns="http://docs.oasis-open.org/ns/xri/xrd-1.0">';
        $xml .= '<Subject>acct:example@mastodon.example.jp</Subject>';
        $xml .= '<Alias>https://mastodon.example.jp/@example</Alias>';
        $xml .= '<Alias>https://mastodon.example.jp/users/example</Alias>';
        $xml .= '<Link rel="http://webfinger.net/rel/profile-page" type="text/html" href="https://mastodon.example.jp/@example"/>';
        $xml .= '<Link rel="http://schemas.google.com/g/2010#updates-from" type="application/atom+xml" href="https://mastodon.example.jp/users/example.atom"/>';
        $xml .= '<Link rel="salmon" href="https://mastodon.example.jp/api/salmon/8765"/>';
        $xml .= '<Link rel="magic-public-key" href="data:application/magic-public-key,RSA.====.===="/>';
        $xml .= '<Link rel="http://ostatus.org/schema/1.0/subscribe" template="https://mastodon.example.jp/authorize_follow?acct={uri}"/>';
        $xml .= '</XRD>';
        return WebFinger::fromXml($xml);
    }
}
