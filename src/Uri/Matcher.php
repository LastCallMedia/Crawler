<?php


namespace LastCall\Crawler\Uri;


class Matcher implements MatcherInterface
{
    private $patterns = [
        'include' => [],
        'exclude' => [],
        'html' => ['html', 'htm', 'php', 'asp', 'aspx', 'cfm'],
        'file' => [
            'gif',
            'png',
            'jpg',
            'jpeg',
            'svg',
            'psd',
            'pdf',
            'xml',
            'doc',
            'docx',
            'zip',
            'txt'
        ]
    ];

    private $compiled = [];

    public function __construct(
        array $includePatterns = [],
        array $excludePatterns = [],
        array $htmlPatterns = null,
        array $filePatterns = null
    ) {
        $this->patterns['include'] = $includePatterns;
        $this->patterns['exclude'] = $excludePatterns;
        if (isset($filePatterns)) {
            $this->patterns['file'] = $filePatterns;
        }
        if (isset($htmlPatterns)) {
            $this->patterns['html'] = $htmlPatterns;
        }
    }

    public function matches($uri)
    {
        // Cast to a string here so we don't have to do it 2x.
        $uri = (string)$uri;

        return $this->matchesPattern('include', $uri,
            true) && !$this->matchesPattern('exclude', $uri, false);
    }

    private function matchesPattern($type, $value, $default)
    {
        if ($pattern = $this->compilePattern($type)) {
            return (bool)preg_match($pattern, $value);
        }

        return $default;
    }

    private function compilePattern($type)
    {
        if (!isset($this->compiled[$type])) {
            switch ($type) {
                case 'file':
                case 'html':
                    $this->compiled[$type] = $this->patterns[$type] ? '@(^' . implode('$|^',
                            $this->patterns[$type]) . '$)@S' : false;
                    break;
                default:
                    $this->compiled[$type] = $this->patterns[$type] ? '@(' . implode('|',
                            $this->patterns[$type]) . ')@S' : false;
            }
        }

        return $this->compiled[$type];
    }

    public function matchesFile($uri)
    {
        return $this->extensionMatchesPattern('file', $uri, false);
    }

    private function extensionMatchesPattern($type, $url, $default)
    {
        if ($path = parse_url($url, PHP_URL_PATH)) {
            if ($ext = pathinfo($path, PATHINFO_EXTENSION)) {
                return $this->matchesPattern($type, $ext, $default);
            }
        }

        return $default;
    }

    public function matchesHtml($uri)
    {
        return $this->extensionMatchesPattern('html', $uri, true);
    }

}