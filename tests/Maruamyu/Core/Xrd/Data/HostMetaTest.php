<?php

namespace Maruamyu\Core\Xrd\Data;

class HostMetaTest extends GeneralTest
{
    protected $classname = 'Maruamyu\Core\\Xrd\\Data\\HostMeta';

    public function test_addget_lrdd()
    {
        $hostMeta = new HostMeta();
        $hostMeta->addLrdd('https://example.jp/.well-known/webfinger?resource={uri}');
        $hostMeta->addLrdd('https://example.jp/.well-known/webfinger.json?resource={uri}', 'application/jrd+json');

        $linkElements = $hostMeta->getLinks();
        $this->assertEquals(2, count($linkElements));

        $linkElement1 = $linkElements[0];
        $this->assertEquals('lrdd', $linkElement1->getRel());
        $this->assertEquals('application/xrd+xml', $linkElement1->getType());
        $this->assertEquals('https://example.jp/.well-known/webfinger?resource={uri}', $linkElement1->getTemplate());

        $linkElement2 = $linkElements[1];
        $this->assertEquals('lrdd', $linkElement2->getRel());
        $this->assertEquals('application/jrd+json', $linkElement2->getType());
        $this->assertEquals('https://example.jp/.well-known/webfinger.json?resource={uri}', $linkElement2->getTemplate());

        $this->assertEquals('https://example.jp/.well-known/webfinger?resource={uri}', $hostMeta->getLrdd());
        $this->assertEquals('https://example.jp/.well-known/webfinger.json?resource={uri}', $hostMeta->getLrdd('application/jrd+json'));
    }

    public function test_toXml()
    {
        $hostMeta = new HostMeta();
        $hostMeta->addLrdd('https://example.jp/.well-known/webfinger?resource={uri}');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<XRD xmlns="http://docs.oasis-open.org/ns/xri/xrd-1.0">';
        $xml .= '<Link rel="lrdd" type="application/xrd+xml" template="https://example.jp/.well-known/webfinger?resource={uri}"/>';
        $xml .= '</XRD>' . "\n";

        $this->assertEquals($xml, $hostMeta->toXml());
    }

    public function test_toJson()
    {
        $hostMeta = new HostMeta();
        $hostMeta->addLrdd('https://example.jp/.well-known/webfinger.json?resource={uri}', 'application/jrd+json');
        $json = '{"links":[{"rel":"lrdd","type":"application\/jrd+json","template":"https:\/\/example.jp\/.well-known\/webfinger.json?resource={uri}"}]}';
        $this->assertEquals($json, $hostMeta->toJson());
    }
}
