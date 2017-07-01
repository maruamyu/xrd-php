<?php

namespace Maruamyu\Core\Xrd\Data;

class GeneralTest extends \PHPUnit_Framework_TestCase
{
    protected $classname = 'Maruamyu\Core\\Xrd\\Data\\General';

    public function test_empty_xrd()
    {
        $general = new $this->classname();

        $expected = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $expected .= '<XRD xmlns="http://docs.oasis-open.org/ns/xri/xrd-1.0"/>' . "\n";
        $this->assertEquals($expected, $general->toXml());
    }

    public function test_empty_jrd()
    {
        $general = new $this->classname();
        $this->assertEquals('{}', $general->toJson());
    }

    public function test_setget_subject()
    {
        $general = new $this->classname();
        $general->setSubject('acct:mirai-iro@example.jp');
        $this->assertEquals('acct:mirai-iro@example.jp', $general->getSubject());
    }

    public function test_setget_expires()
    {
        $general = new $this->classname();
        $nowTime = new \DateTime();
        $general->setExpires($nowTime);
        $this->assertEquals($nowTime, $general->getExpires());
    }

    public function test_addget_aliases()
    {
        $general = new $this->classname();
        $general->addAlias('acct:mirai-iro@example.jp');
        $general->addAlias('https://example.jp/users/mirai-iro');
        $this->assertEquals(['acct:mirai-iro@example.jp', 'https://example.jp/users/mirai-iro'], $general->getAliases());
    }

    public function test_setget_property()
    {
        $general = new $this->classname();
        $general->setProperty('http://spec.example.net/color', 'red');
        $this->assertEquals('red', $general->getProperty('http://spec.example.net/color'));
    }

    public function test_setget_properties()
    {
        $general = new $this->classname();
        $general->setProperties([
            'http://blgx.example.net/ns/version' => '1.3',
            'http://blgx.example.net/ns/ext' => null,
        ]);
        $this->assertEquals([
            'http://blgx.example.net/ns/version' => '1.3',
            'http://blgx.example.net/ns/ext' => null,
        ], $general->getProperties());
    }

    public function test_addget_links()
    {
        $general = new $this->classname();

        $linkNode1 = new LinkElement(
            'http://webfinger.net/rel/profile-page',
            'text/html',
            'https://example.jp/users/mirai-iro',
            true
        );
        $linkNode2 = new LinkElement(
            'http://schemas.google.com/g/2010#updates-from',
            'https://example.jp/users/mirai-iro.atom',
            'application/atom+xml'
        );
        $general->addLink($linkNode1);
        $general->addLink($linkNode2);
        $this->assertEquals([$linkNode1, $linkNode2], $general->getLinks());
    }

    public function test_findLinkElements()
    {
        $general = new $this->classname();
        $linkNode1 = new LinkElement('author', 'http://example.jp/users/user1');
        $linkNode2 = new LinkElement('author', 'http://example.jp/users/user2');
        $linkNode3 = new LinkElement('copyright', 'http://example.jp/copyright');
        $general->addLink($linkNode1);
        $general->addLink($linkNode2);
        $general->addLink($linkNode3);
        $this->assertEquals([$linkNode1, $linkNode2], $general->findLinkElements('author'));
        $this->assertEquals([$linkNode3], $general->findLinkElements('copyright'));
        $this->assertEmpty($general->findLinkElements('notfound'));
    }

    public function test_findFirstLinkElement()
    {
        $general = new $this->classname();
        $linkNode1 = new LinkElement('author', 'http://example.jp/users/user1');
        $linkNode2 = new LinkElement('author', 'http://example.jp/users/user2');
        $linkNode3 = new LinkElement('copyright', 'http://example.jp/copyright');
        $general->addLink($linkNode1->toArray());
        $general->addLink($linkNode2->toArray());
        $general->addLink($linkNode3->toArray());

        $this->assertEquals($linkNode1, $general->findFirstLinkElement('author'));
        $this->assertEquals($linkNode3, $general->findFirstLinkElement('copyright'));
        $this->assertNull($general->findFirstLinkElement('notfound'));
    }

    public function test_fromXml()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<XRD xmlns="http://docs.oasis-open.org/ns/xri/xrd-1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">';
        $xml .= '<Subject>http://blog.example.com/article/id/314</Subject>';
        $xml .= '<Expires>2010-01-30T09:30:00Z</Expires>';
        $xml .= '<Alias>http://blog.example.com/cool_new_thing</Alias>';
        $xml .= '<Alias>http://blog.example.com/steve/article/7</Alias>';
        $xml .= '<Property type="http://blgx.example.net/ns/version">1.2</Property>';
        $xml .= '<Property type="http://blgx.example.net/ns/version">1.3</Property>';
        $xml .= '<Property type="http://blgx.example.net/ns/ext" xsi:nil="true" />';
        $xml .= '<Link rel="author" type="text/html" href="http://blog.example.com/author/steve">';
        $xml .= '    <Title>About the Author</Title>';
        $xml .= '    <Title xml:lang="en-us">Author Information</Title>';
        $xml .= '    <Property type="http://example.com/role">editor</Property>';
        $xml .= '</Link>';
        $xml .= '<Link rel="author" href="http://example.com/author/john">';
        $xml .= '    <Title>The other guy</Title>';
        $xml .= '    <Title>The other author</Title>';
        $xml .= '</Link>';
        $xml .= '<Link rel="copyright" template="http://example.com/copyright?id={uri}" />';
        $xml .= '</XRD>';

        $general = General::fromXml($xml);

        $this->assertEquals('http://blog.example.com/article/id/314', $general->getSubject());
        $this->assertEquals(new \DateTime('2010-01-30T09:30:00Z'), $general->getExpires());
        $this->assertEquals('1.3', $general->getProperty('http://blgx.example.net/ns/version'));
        $this->assertNull($general->getProperty('http://blgx.example.net/ns/ext'));

        $linkElements = $general->getLinks();
        $this->assertEquals(3, count($linkElements));

        $linkElement1 = $linkElements[0];
        $this->assertEquals('author', $linkElement1->getRel());
        $this->assertEquals('text/html', $linkElement1->getType());
        $this->assertEquals('http://blog.example.com/author/steve', $linkElement1->getHref());
        $this->assertNull($linkElement1->getTemplate());
        $this->assertEquals('About the Author', $linkElement1->getTitle());
        $this->assertEquals('Author Information', $linkElement1->getTitle('en-us'));
        $this->assertEquals(['http://example.com/role' => 'editor'], $linkElement1->getProperties());

        $linkElement2 = $linkElements[1];
        $this->assertEquals('author', $linkElement2->getRel());
        $this->assertNull($linkElement2->getType());
        $this->assertEquals('http://example.com/author/john', $linkElement2->getHref());
        $this->assertNull($linkElement2->getTemplate());
        $this->assertEquals(['default' => 'The other author'], $linkElement2->getTitles());
        $this->assertEmpty($linkElement2->getProperties());

        $linkElement3 = $linkElements[2];
        $this->assertEquals('copyright', $linkElement3->getRel());
        $this->assertNull($linkElement3->getType());
        $this->assertNull($linkElement3->getHref());
        $this->assertEquals('http://example.com/copyright?id={uri}', $linkElement3->getTemplate());
        $this->assertEmpty($linkElement3->getTitles());
        $this->assertEmpty($linkElement3->getProperties());
    }

    public function test_fromJson()
    {
        $json = <<<'__EOS__'
{
  "subject":"http://blog.example.com/article/id/314",
  "expires":"2010-01-30T09:30:00Z",

  "aliases":[
    "http://blog.example.com/cool_new_thing",
    "http://blog.example.com/steve/article/7"],

  "properties":{
    "http://blgx.example.net/ns/version":"1.3",
    "http://blgx.example.net/ns/ext":null
  },

  "links":[
    {
      "rel":"author",
      "type":"text/html",
      "href":"http://blog.example.com/author/steve",
      "titles":{
        "default":"About the Author",
        "en-us":"Author Information"
      },
      "properties":{
        "http://example.com/role":"editor"
      }
    },
    {
      "rel":"author",
      "href":"http://example.com/author/john",
      "titles":{
        "default":"The other author"
      }
    },
    {
      "rel":"copyright",
      "template":"http://example.com/copyright?id={uri}"
    }
  ]
}
__EOS__;

        $general = General::fromJson($json);

        $this->assertEquals('http://blog.example.com/article/id/314', $general->getSubject());
        $this->assertEquals(new \DateTime('2010-01-30T09:30:00Z'), $general->getExpires());
        $this->assertEquals('1.3', $general->getProperty('http://blgx.example.net/ns/version'));
        $this->assertNull($general->getProperty('http://blgx.example.net/ns/ext'));

        $linkElements = $general->getLinks();
        $this->assertEquals(3, count($linkElements));

        $linkElement1 = $linkElements[0];
        $this->assertEquals('author', $linkElement1->getRel());
        $this->assertEquals('text/html', $linkElement1->getType());
        $this->assertEquals('http://blog.example.com/author/steve', $linkElement1->getHref());
        $this->assertNull($linkElement1->getTemplate());
        $this->assertEquals('About the Author', $linkElement1->getTitle());
        $this->assertEquals('Author Information', $linkElement1->getTitle('en-us'));
        $this->assertEquals(['http://example.com/role' => 'editor'], $linkElement1->getProperties());

        $linkElement2 = $linkElements[1];
        $this->assertEquals('author', $linkElement2->getRel());
        $this->assertNull($linkElement2->getType());
        $this->assertEquals('http://example.com/author/john', $linkElement2->getHref());
        $this->assertNull($linkElement2->getTemplate());
        $this->assertEquals(['default' => 'The other author'], $linkElement2->getTitles());
        $this->assertEmpty($linkElement2->getProperties());

        $linkElement3 = $linkElements[2];
        $this->assertEquals('copyright', $linkElement3->getRel());
        $this->assertNull($linkElement3->getType());
        $this->assertNull($linkElement3->getHref());
        $this->assertEquals('http://example.com/copyright?id={uri}', $linkElement3->getTemplate());
        $this->assertEmpty($linkElement3->getTitles());
        $this->assertEmpty($linkElement3->getProperties());
    }

    public function test_toXml()
    {
        $json = <<<'__EOS__'
{
  "subject":"http://blog.example.com/article/id/314",
  "expires":"2010-01-30T09:30:00Z",

  "aliases":[
    "http://blog.example.com/cool_new_thing",
    "http://blog.example.com/steve/article/7"],

  "properties":{
    "http://blgx.example.net/ns/version":"1.3",
    "http://blgx.example.net/ns/ext":null
  },

  "links":[
    {
      "rel":"author",
      "type":"text/html",
      "href":"http://blog.example.com/author/steve",
      "titles":{
        "default":"About the Author",
        "en-us":"Author Information"
      },
      "properties":{
        "http://example.com/role":"editor"
      }
    },
    {
      "rel":"author",
      "href":"http://example.com/author/john",
      "titles":{
        "default":"The other author"
      }
    },
    {
      "rel":"copyright",
      "template":"http://example.com/copyright?id={uri}"
    }
  ]
}
__EOS__;
        $general = General::fromJson($json);

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<XRD xmlns="http://docs.oasis-open.org/ns/xri/xrd-1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">';
        $xml .= '<Subject>http://blog.example.com/article/id/314</Subject>';
        $xml .= '<Expires>2010-01-30T09:30:00+00:00</Expires>';
        $xml .= '<Alias>http://blog.example.com/cool_new_thing</Alias>';
        $xml .= '<Alias>http://blog.example.com/steve/article/7</Alias>';
        $xml .= '<Property type="http://blgx.example.net/ns/version">1.3</Property>';
        $xml .= '<Property type="http://blgx.example.net/ns/ext" xsi:nil="true"/>';
        $xml .= '<Link rel="author" type="text/html" href="http://blog.example.com/author/steve">';
        $xml .= '<Title>About the Author</Title>';
        $xml .= '<Title xml:lang="en-us">Author Information</Title>';
        $xml .= '<Property type="http://example.com/role">editor</Property>';
        $xml .= '</Link>';
        $xml .= '<Link rel="author" href="http://example.com/author/john">';
        $xml .= '<Title>The other author</Title>';
        $xml .= '</Link>';
        $xml .= '<Link rel="copyright" template="http://example.com/copyright?id={uri}"/>';
        $xml .= '</XRD>' . "\n";

        $this->assertEquals($xml, $general->toXml());
    }

    public function test_toJson()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<XRD xmlns="http://docs.oasis-open.org/ns/xri/xrd-1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">';
        $xml .= '<Subject>http://blog.example.com/article/id/314</Subject>';
        $xml .= '<Expires>2010-01-30T09:30:00Z</Expires>';
        $xml .= '<Alias>http://blog.example.com/cool_new_thing</Alias>';
        $xml .= '<Alias>http://blog.example.com/steve/article/7</Alias>';
        $xml .= '<Property type="http://blgx.example.net/ns/version">1.2</Property>';
        $xml .= '<Property type="http://blgx.example.net/ns/version">1.3</Property>';
        $xml .= '<Property type="http://blgx.example.net/ns/ext" xsi:nil="true" />';
        $xml .= '<Link rel="author" type="text/html" href="http://blog.example.com/author/steve">';
        $xml .= '    <Title>About the Author</Title>';
        $xml .= '    <Title xml:lang="en-us">Author Information</Title>';
        $xml .= '    <Property type="http://example.com/role">editor</Property>';
        $xml .= '</Link>';
        $xml .= '<Link rel="author" href="http://example.com/author/john">';
        $xml .= '    <Title>The other guy</Title>';
        $xml .= '    <Title>The other author</Title>';
        $xml .= '</Link>';
        $xml .= '<Link rel="copyright" template="http://example.com/copyright?id={uri}" />';
        $xml .= '</XRD>';

        $general = General::fromXml($xml);
        $json = '{"subject":"http:\\/\\/blog.example.com\\/article\\/id\\/314","expires":"2010-01-30T09:30:00+00:00","aliases":["http:\\/\\/blog.example.com\\/cool_new_thing","http:\\/\\/blog.example.com\\/steve\\/article\\/7"],"properties":{"http:\\/\\/blgx.example.net\\/ns\\/version":"1.3","http:\\/\\/blgx.example.net\\/ns\\/ext":null},"links":[{"rel":"author","type":"text\\/html","href":"http:\\/\\/blog.example.com\\/author\\/steve","titles":{"default":"About the Author","en-us":"Author Information"},"properties":{"http:\\/\\/example.com\\/role":"editor"}},{"rel":"author","href":"http:\\/\\/example.com\\/author\\/john","titles":{"default":"The other author"}},{"rel":"copyright","template":"http:\\/\\/example.com\\/copyright?id={uri}"}]}';
        $this->assertEquals($json, $general->toJson());
    }

    public function test_toString()
    {
        $general = new $this->classname();
        $this->assertEquals($general->toXml(), strval($general));
    }
}
