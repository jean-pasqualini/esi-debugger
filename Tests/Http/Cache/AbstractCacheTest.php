<?php

namespace Tests\Http\Cache;

use Http\Cache\Iframe;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * AbstractCacheTest
 *
 * @author Jean Pasqualini <jpasqualini75@gmail.com>
 * @package Tests\Http\Cache;
 */
class AbstractCacheTest extends \PHPUnit_Framework_TestCase
{
    /** @var Iframe */
    protected $cacheTest;

    protected $response;

    public function setUp()
    {
        $this->cacheTest = new Iframe();
        $this->cacheTest->setEventDispatcher($this->getMock(EventDispatcher::class));
    }

    public function testContructor()
    {
        $this->assertInstanceOf('Http\\Cache\\Iframe', $this->cacheTest);
    }

    public function testProcess()
    {
        $request = new Request();
        $request->server->set('HTTP_HOST', 'localhost');
        $response = new Response('une <esi:include src="/paris" /> verte');

        $this->cacheTest->process($request, $response);

        $this->assertEquals(
            'une <iframe src="http://localhost/paris" style="border: outset lightgrey 5px; margin: 5px;"></iframe> verte',
            $response->getContent(),
            'the process dot not transform esi to iframe'
        );
    }
}