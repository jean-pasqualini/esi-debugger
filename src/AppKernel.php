<?php
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpCache\Store;
use Http\Kernel\HttpProxyKernel;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Event\HttpCacheEsiPreProcessEvent;
use Http\Cache\AbstractCache;
use Symfony\Component\HttpKernel\HttpCache\HttpCache;
use Symfony\Component\HttpFoundation\Response;
use Http\ResponseProcessor\CacheDebuggerPostResponseProcessor;

/**
 * App
 *
 * @author Jean Pasqualini <jpasqualini75@gmail.com>
 */
class AppKernel implements HttpKernelInterface
{
    protected $configuration;
    protected $patternFragment;
    /** @var HttpKernelInterface */
    protected $proxyKernel;
    protected $initialized = false;

    public function __construct()
    {
        $this->configuration = $this->loadConfiguration();
    }

    public function loadConfiguration()
    {
        $configurationLoader = new ConfigurationLoader($this->getRootDir());
        $configurationProcessor = new AppConfigurationProcessor();
        $configuration = $configurationProcessor->process(
            $configurationLoader->load('config.yml')
        );
        return $configuration;
    }

    public function getRootDir()
    {
        return __DIR__.DIRECTORY_SEPARATOR.'..';
    }

    public function init()
    {
        if($this->initialized) return;

        $eventDispatcher = new EventDispatcher();

        $baseUrl = $this->configuration['baseUrl'];

        $esiClass = $this->configuration['mode']['class'];
        $kernel = new HttpProxyKernel(
            array(
                'baseUrl' => $baseUrl,
                'context' => stream_context_create(
                    array(
                        'http' => array(
                            'method' => 'GET',
                            'header' => array(
                                'Surrogate-Capability: abc=ESI/1.0'.PHP_EOL
                            )
                        ),
                        'ssl' => array(
                            'verify_peer' => false,
                        )
                    )
                )
            )
        );

        $esi = new $esiClass();
        if ($esi instanceof AbstractCache) {
            $esi->setEventDispatcher($eventDispatcher);

            $kernel = new HttpCache(
                $kernel, new Store($this->getRootDir().'/cache'), $esi, array(
                    'debug' => true,
                )
            );

            $eventDispatcher->addListener(
                HttpCacheEsiPreProcessEvent::EVENT,
                function (HttpCacheEsiPreProcessEvent $event) use ($kernel) {
                    $kernel->handle(Request::create($event->getUri()), HttpKernelInterface::SUB_REQUEST);
                }
            );

            $this->proxyKernel = $kernel;
        }

        $this->initialized = true;
    }

    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        try {
            $this->init();
            $response = $this->proxyKernel->handle($request);
            $this->postProcessResponse($request, $response);

            return $response;
        } catch (Exception $e) {
            exit($e->getMessage());
        }
    }

    public function postProcessResponse(Request $request, Response $response)
    {
        $cacheDebugger = new CacheDebuggerPostResponseProcessor();

        $response = $cacheDebugger->process($request, $response);

        if (!$response->isCacheable()) {
            $purgeRequest = $request->duplicate();
            $purgeRequest->setMethod('PURGE');
            $this->proxyKernel->handle($purgeRequest, HttpKernelInterface::MASTER_REQUEST);
        }
    }
}