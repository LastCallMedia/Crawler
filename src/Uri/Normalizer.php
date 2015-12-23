<?php


namespace LastCall\Crawler\Uri;


use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\UriInterface;

class Normalizer implements NormalizerInterface
{
    private $handlers = [];

    public function __construct(array $handlers = array())
    {
        $this->handlers = $handlers;
    }

    public function normalize($url)
    {
        $uri = $this->createUri($url);

        foreach ($this->handlers as $handler) {
            $uri = $handler($uri);
        }

        return $uri;
    }

    protected function createUri($uri)
    {
        if (!$uri instanceof UriInterface) {
            $uri = new Uri($uri);
        }
        if (!$uri instanceof TraceableUri) {
            $uri = new TraceableUri($uri);
        }

        return $uri;
    }


    /**
     * Give a list of preferred domains that will be used.  An example of this
     * would be preferring the www. form of the domain.
     *
     * @param array $map
     *
     * @return \Closure
     */
    public static function preferredDomainMap(array $map)
    {
        return function (UriInterface $uri) use ($map) {
            return isset($map[$uri->getHost()]) ? $uri->withHost($map[$uri->getHost()]) : $uri;
        };
    }

    /**
     * Strip off the URL fragment (#fragment)
     *
     * @return \Closure
     */
    public static function stripFragment()
    {
        return function (UriInterface $uri) {
            return $uri->getFragment() ? $uri->withFragment(false) : $uri;
        };
    }

    /**
     * Use http instead of https.
     *
     * @return \Closure
     */
    public static function stripSSL()
    {
        return function (UriInterface $uri) {
            return $uri->getScheme() == 'https' ? $uri->withScheme('http') : $uri;
        };
    }

    /**
     * Strip a trailing slash off of the url path.
     *
     * @return \Closure
     */
    public static function stripTrailingSlash()
    {
        return function (UriInterface $uri) {
            return substr($uri->getPath(),
                -1) === '/' ? $uri->withPath(substr($uri->getPath(), 0,
                strlen($uri->getPath()) - 1)) : $uri;
        };
    }

    /**
     * Strip off an index page (index.html, index.php, etc)
     *
     * @param string $indexRegex
     *
     * @return \Closure
     */
    public static function stripIndex(
        $indexRegex = '@/index.(html|htm|php|asp|aspx|cfm)$@'
    ) {
        return function (UriInterface $uri) use ($indexRegex) {
            return preg_match($indexRegex,
                $uri->getPath()) ? $uri->withPath(preg_replace($indexRegex, '/',
                $uri->getPath())) : $uri;
        };
    }

    /**
     * Convert the casing of the URL to all upper or lower case.
     *
     * @param string $case
     *
     * @return \Closure
     */
    public static function normalizeCase($case = 'lower')
    {
        if (!in_array($case, array('upper', 'lower'))) {
            throw new \InvalidArgumentException(sprintf('Invalid case \'%s\'',
                (string)$case));
        }

        return function (UriInterface $uri) use ($case) {

            switch ($case) {
                case 'lower':
                    $ret = $uri;
                    $ret = (empty($ret->getHost()) || ctype_lower($ret->getHost())) ? $ret : $ret->withHost(mb_strtolower($ret->getHost()));
                    $ret = (empty($ret->getPath()) || ctype_lower($ret->getPath())) ? $ret : $ret->withPath(mb_strtolower($ret->getPath()));
                    $ret = (empty($ret->getFragment()) || ctype_lower($ret->getFragment())) ? $ret : $ret->withFragment(mb_strtolower($ret->getFragment()));

                    return $ret;
                case 'upper':
                    $ret = $uri;
                    $ret = (empty($ret->getHost()) || ctype_upper($ret->getHost())) ? $ret : $ret->withHost(mb_strtoupper($ret->getHost()));
                    $ret = (empty($ret->getPath()) || ctype_upper($ret->getPath())) ? $ret : $ret->withPath(mb_strtoupper($ret->getPath()));
                    $ret = (empty($ret->getFragment()) || ctype_upper($ret->getFragment())) ? $ret : $ret->withFragment(mb_strtouppers($ret->getFragment()));

                    return $ret;
            }
        };
    }

}