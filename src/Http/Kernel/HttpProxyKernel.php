<?php
namespace Http\Kernel;

use Event\HttpCacheEsiPreProcessEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

use Symfony\Component\HttpFoundation\Request;

/**
 * HttpProxyKernelInterface
 *
 * @author Jean Pasqualini <jpasqualini75@gmail.com>
 */
class HttpProxyKernel implements HttpKernelInterface
{
    protected $config;
    protected $eventDispatcher;

    public function __construct(array $config = array())
    {
        $this->config = $config;
    }

    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        $uri = str_replace($request->getScheme().'://'.$request->getHost().':'.$request->getPort(), $this->config['baseUrl'], $request->getUri());

        $content = file_get_contents($uri, false, $this->config['context']);

        if (false === $content) {
            $content = print_r($http_response_header, true);
        } else {
            $beforeContent = "<base href='".$this->config['baseUrl']."/'/>";

            $content = $beforeContent.$content;
        }

        $response = new Response($content);

        $headerAlloweds = array(
            'Age',
            'Cache-Control'
        );

        foreach($http_response_header as $header)
        {
            $headerSplit = explode(':', $header);

            if(false !== strpos($header, 'HTTP/'))
            {
                preg_match('/HTTP\/1\.[0-9] (?P<statusCode>[0-9]{3})/i', $header, $http);

                $statusCode = intval($http['statusCode']);

                $response->setStatusCode($statusCode);
            }

            if(count($headerSplit) === 2)
            {
                list($key, $value) = $headerSplit;

                if(in_array(trim($key), $headerAlloweds)) {
                    $response->headers->set(trim($key), trim($value));
                };
            }
        }

        $response->headers->set('Surrogate-Control', 'no-store, content="ESI/1.0"');

        return $response;
    }
}