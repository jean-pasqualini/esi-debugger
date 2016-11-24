<?php
namespace Http\ResponseProcessor;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * CacheDebuggerPostResponseProcessor
 *
 * @author Jean Pasqualini <jpasqualini75@gmail.com>
 */
class CacheDebuggerPostResponseProcessor
{
    protected function getRgb($inCache, $requestUri = 'fragment', $countHorsCache = 0)
    {
        if($inCache) {
            if ($countHorsCache > 0 || false !== strpos($requestUri, 'fragment')) {
                return 'rgb(255, 140, 0)';
            } else {
                return 'rgb(0, 255, 0)';
            }
        } else {
            return 'rgb(255, 0, 0)';
        }
    }

    protected function convertResponseHeaderToArray(array $responseHeaders)
    {
        $headers = array();

        foreach ($responseHeaders as $header) {

            $headerSplit = explode(':', $header);

            if(count($headerSplit) === 2) {
                list($key, $value) = $headerSplit;

                $headers[trim($key)] = trim($value);
            }
        }
    }

    protected function isInCache(array $headers)
    {
        $inCache = false;

        foreach ($headers as $key => $value) {
            if ('cache-control' === strtolower(trim($key))) {
                if(is_array($value)) $value = $value[0];
                $cacheControlHeader = trim($value);
                $cacheControlConfigs = explode(', ', $cacheControlHeader);

                foreach ($cacheControlConfigs as $cacheControlConfig) {
                    $cacheControlConfigSplit = explode('=', $cacheControlConfig);

                    if(2 === count($cacheControlConfigSplit)) {
                        list($keyCacheControlConfig, $valueCacheControlConfig) = $cacheControlConfigSplit;

                        if ('s-maxage' === strtolower(trim($keyCacheControlConfig))) {
                            $sMaxAge = intval(trim($valueCacheControlConfig));

                            if ($sMaxAge > 0) {
                                $inCache = true;
                            }
                        }
                    }
                }
            }
        }

        return $inCache;
    }

    public function process(Request $request, Response $response)
    {
        $content = $response->getContent();

        $inCache = $this->isInCache($response->headers->all());
        $content = '<style type="text/css">body { background: '.$this->getRgb($inCache, $request->getUri(), 0).' !important; }</style>' . $content;

        $response->setContent($content);

        return $response;
    }
}