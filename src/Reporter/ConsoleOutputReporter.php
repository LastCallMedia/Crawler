<?php


namespace LastCall\Crawler\Reporter;


use Symfony\Component\Console\Helper\ProgressIndicator;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleOutputReporter implements ReporterInterface
{
    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    private $output;

    private $indicator;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function report(array $stats)
    {
        if (!$this->indicator) {
            $this->indicator = new ProgressIndicator($this->output);
            $this->indicator->start('Starting');
        }
        $this->indicator->advance();
        $message = strtr("Crawling {{sent}} sent - {{remaining}} left", [
            '{{sent}}' => $stats['sent'],
            '{{remaining}}' => $stats['remaining']
        ]);
        $this->indicator->setMessage($message);
    }

}