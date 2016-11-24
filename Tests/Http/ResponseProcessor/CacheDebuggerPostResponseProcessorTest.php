<?php

namespace Tests\ResponseProcessor;

use Http\ResponseProcessor\CacheDebuggerPostResponseProcessor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 * CacheDebugggerPostResponseProcessorTest
 *
 * @author Jean Pasqualini <jpasqualini75@gmail.com>
 * @package Tests\ResponseProcessor;
 */
class CacheDebuggerPostResponseProcessorTest extends \PHPUnit_Framework_TestCase
{
    /** @var CacheDebuggerPostResponseProcessor */
    protected $cacheDebugger;
    protected $request;

    public function setUp()
    {
        $this->cacheDebugger = new CacheDebuggerPostResponseProcessor();
        $this->request = $this->getMock(Request::class);
    }

    public function testProcessUncachedResponse()
    {
        $request = new Request();
        $response = new Response('une plante verte');

        $this->assertEquals(
            '<style type="text/css">body { background: rgb(255, 0, 0) !important; }</style>une plante verte',
            $this->cacheDebugger->process($request, $response)->getContent(),
            'debug not work'
        );
    }

    public function testProcessCachedResponse()
    {
        $request = new Request();
        $response = new Response('une plante verte');
        $response->setPublic();
        $response->setSharedMaxAge(86400);

        $this->assertEquals(
            '<style type="text/css">body { background: rgb(0, 255, 0) !important; }</style>une plante verte',
            $this->cacheDebugger->process($request, $response)->getContent(),
            'debug not work'
        );
    }

    public function testProcessFragmentCachedResponse()
    {
        $this->request
            ->expects($this->once())
            ->method('getUri')
            ->will($this->returnValue('/?_fragment=uri'));

        $request  = $this->request;
        $response = new Response('une plante verte');
        $response->setPublic();
        $response->setSharedMaxAge(86400);

        $this->assertEquals(
            '<style type="text/css">body { background: rgb(255, 140, 0) !important; }</style>une plante verte',
            $this->cacheDebugger->process($request, $response)->getContent(),
            'debug not work'
        );
    }
}