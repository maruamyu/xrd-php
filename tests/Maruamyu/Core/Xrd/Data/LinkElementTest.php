<?php

namespace Maruamyu\Core\Xrd\Data;

class LinkElementTest extends \PHPUnit_Framework_TestCase
{
    public function test_init_and_basic_attributes()
    {
        $link1 = new LinkElement(
            'http://webfinger.net/rel/profile-page',
            'https://example.jp/users/mirai-iro',
            'text/html'
        );
        $this->assertEquals('http://webfinger.net/rel/profile-page', $link1->getRel());
        $this->assertEquals('text/html', $link1->getType());
        $this->assertEquals('https://example.jp/users/mirai-iro', $link1->getHref());
        $this->assertNull($link1->getTemplate());

        $link2 = new LinkElement(
            'lrdd',
            'https://example.jp/.well-known/webfinger?resource={uri}',
            'application/xrd+xml',
            true
        );
        $this->assertEquals('lrdd', $link2->getRel());
        $this->assertEquals('application/xrd+xml', $link2->getType());
        $this->assertNull($link2->getHref());
        $this->assertEquals('https://example.jp/.well-known/webfinger?resource={uri}', $link2->getTemplate());
    }

    public function test_setget_title()
    {
        $link = new LinkElement();

        $link->setTitle('English');
        $link->setTitle('日本語', 'ja-jp');

        $this->assertEquals('English', $link->getTitle());
        $this->assertEquals('日本語', $link->getTitle('ja-jp'));
        $this->assertNull($link->getTitle('en-us'));
    }

    public function test_setget_property()
    {
        $link = new LinkElement();
        $link->setProperty('http://spec.example.net/color', 'red');
        $this->assertEquals('red', $link->getProperty('http://spec.example.net/color'));
    }

    public function test_setget_properties()
    {
        $link = new LinkElement();
        $link->setProperties([
            'http://blgx.example.net/ns/version' => '1.3',
            'http://blgx.example.net/ns/ext' => null,
        ]);
        $this->assertEquals([
            'http://blgx.example.net/ns/version' => '1.3',
            'http://blgx.example.net/ns/ext' => null,
        ], $link->getProperties());
    }

    public function test_toXml()
    {
        $link = new LinkElement('author', 'http://blog.example.com/author/steve', 'text/html');
        $link->setTitle('About the Author');
        $link->setTitle('Author Information', 'en-us');
        $link->setProperty('http://example.com/role', 'editor');

        $expects = '<Link rel="author" type="text/html" href="http://blog.example.com/author/steve">';
        $expects .= '<Title>About the Author</Title>';
        $expects .= '<Title xml:lang="en-us">Author Information</Title>';
        $expects .= '<Property type="http://example.com/role">editor</Property>';
        $expects .= '</Link>';

        $this->assertEquals($expects, $link->toXml());
    }

    public function test_toString()
    {
        $link = new LinkElement('author', 'http://blog.example.com/author/steve', 'text/html');
        $link->setTitle('About the Author');
        $link->setTitle('Author Information', 'en-us');
        $link->setProperty('http://example.com/role', 'editor');

        $expects = '<Link rel="author" type="text/html" href="http://blog.example.com/author/steve">';
        $expects .= '<Title>About the Author</Title>';
        $expects .= '<Title xml:lang="en-us">Author Information</Title>';
        $expects .= '<Property type="http://example.com/role">editor</Property>';
        $expects .= '</Link>';

        $this->assertEquals($expects, strval($link));
    }

    public function test_toArray()
    {
        $link = new LinkElement('author', 'http://blog.example.com/author/steve', 'text/html');
        $link->setTitle('About the Author');
        $link->setTitle('Author Information', 'en-us');
        $link->setProperty('http://example.com/role', 'editor');

        $expected = [
            'rel' => 'author',
            'type' => 'text/html',
            'href' => 'http://blog.example.com/author/steve',
            'titles' => [
                'default' => 'About the Author',
                'en-us' => 'Author Information',
            ],
            'properties' => [
                'http://example.com/role' => 'editor',
            ],
        ];
        $this->assertEquals($expected, $link->toArray());
    }

    public function test_fromArray()
    {
        $link = LinkElement::fromArray([
            'rel' => 'author',
            'type' => 'text/html',
            'href' => 'http://blog.example.com/author/steve',
            'titles' => [
                'default' => 'About the Author',
                'en-us' => 'Author Information',
            ],
            'properties' => [
                'http://example.com/role' => 'editor',
            ],
        ]);
        $this->assertEquals('author', $link->getRel());
        $this->assertEquals('text/html', $link->getType());
        $this->assertNull($link->getTemplate());
        $this->assertEquals([
            'default' => 'About the Author',
            'en-us' => 'Author Information',
        ], $link->getTitles());
        $this->assertEquals([
            'http://example.com/role' => 'editor',
        ], $link->getProperties());
    }
}
