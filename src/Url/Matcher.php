<?php

namespace LastCall\Crawler\Url;

/**
 * Handles path matching for discovered URLs.
 */
class Matcher
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
        array $includePatterns = null,
        array $excludePatterns = null,
        array $htmlPatterns = null,
        array $filePatterns = null
    ) {
        if (isset($includePatterns)) {
            $this->patterns['include'] = $includePatterns;
        }
        if (isset($excludePatterns)) {
            $this->patterns['exclude'] = $excludePatterns;
        }
        if (isset($htmlPatterns)) {
            $this->patterns['html'] = $htmlPatterns;
        }
        if (isset($filePatterns)) {
            $this->patterns['file'] = $filePatterns;
        }
    }

    public function addInclusionPattern($pattern)
    {
        $this->addPattern('include', $pattern);
    }

    private function addPattern($type, $pattern)
    {
        $this->patterns[$type][] = $pattern;
        $this->compiled[$type] = null;
    }

    public function addExclusionPattern($pattern)
    {
        $this->addPattern('exclude', $pattern);
    }

    public function matchesInclude($url)
    {
        return $this->matchesPattern('include', $url, true);
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

    public function matchesExclude($url)
    {
        return $this->matchesPattern('exclude', $url, false);
    }

    public function matchesFile($url)
    {
        return $this->extensionMatchesPattern('file', $url, false);
    }

    private function extensionMatchesPattern($type, $url, $default)
    {
        if ($path = parse_url($url, PHP_URL_PATH)) {
            if ($ext = pathinfo($path, PATHINFO_EXTENSION)) {
                if ($pattern = $this->compilePattern($type)) {
                    return (bool)preg_match($pattern, $ext);
                }
            }
        }

        return $default;
    }

    public function matchesHTML($url)
    {
        return $this->extensionMatchesPattern('html', $url, true);
    }

}