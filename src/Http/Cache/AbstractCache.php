<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

namespace Http\Cache;

use Event\HttpCacheEsiPreProcessEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpCache\AbstractSurrogate;

/**
 * Iframe
 *
 * @author Jean Pasqualini <jpasqualini75@gmail.com>
 * @package Http\Cache;
 */
abstract class AbstractCache extends AbstractSurrogate
{
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    protected $proxyUrl;

    public function getName()
    {
        return 'esi';
    }

    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function setProxyUrl($proxyUrl)
    {
        $this->proxyUrl = $proxyUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function addSurrogateControl(Response $response)
    {
        throw new \Exception('not implemented method');
    }

    /**
     * {@inheritdoc}
     */
    public function renderIncludeTag($uri, $alt = null, $ignoreErrors = true, $comment = '')
    {
        throw new \Exception('not implemented method');
    }

    abstract public function getReplace($proxyUrl);

    protected function handleEsiIncludeTag($attributes, $proxyUrl)
    {
        $options = array();
        preg_match_all('/(src|onerror|alt)="([^"]*?)"/', $attributes[1], $matches, PREG_SET_ORDER);
        foreach ($matches as $set) {
            $options[$set[1]] = $set[2];
        }

        if (!isset($options['src'])) {
            throw new \RuntimeException('Unable to process an ESI tag without a "src" attribute.');
        }

        $this->eventDispatcher->dispatch(HttpCacheEsiPreProcessEvent::EVENT, new HttpCacheEsiPreProcessEvent($proxyUrl.$options['src']));

        return sprintf($this->getReplace($proxyUrl),
            $options['src'],
            isset($options['alt']) ? $options['alt'] : null,
            isset($options['onerror']) && 'continue' == $options['onerror'] ? 'true' : 'false'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function process(Request $request, Response $response)
    {
        $content = $response->getContent();

        $proxyUrl = 'http://'.$request->server->get('HTTP_HOST');

        $content = preg_replace_callback(
            '#<esi\:include\s+(.*?)\s*(?:/|</esi\:include)>#',
            function ($attributes) use ($proxyUrl) {
                return $this->handleEsiIncludeTag($attributes, $proxyUrl);
            },
            $content
        );

        $response->setContent($content);
    }
}