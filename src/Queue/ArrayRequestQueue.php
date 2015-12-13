<?php


namespace LastCall\Crawler\Queue;


use Psr\Http\Message\RequestInterface;

class ArrayRequestQueue implements RequestQueueInterface
{

    private $jobs = [];

    public function push(RequestInterface $request)
    {
        $key = $request->getMethod() . $request->getUri();
        if (!isset($this->jobs[$key])) {
            $job = new Job($request, $key);
            $this->jobs[$key] = $job;

            return true;
        }

        return false;
    }

    public function pop()
    {
        $now = time();
        foreach ($this->jobs as $job) {
            if ($job->getStatus() === Job::FREE && $job->getExpire() < $now) {
                $job->setExpire(time() + 30);

                return $job;
            }
        }
    }

    public function complete(Job $job)
    {
        $managedJob = $this->getJob($job->getIdentifier());
        $managedJob->setStatus(Job::COMPLETE);
        $managedJob->setExpire(0);
    }

    private function getJob($identifier)
    {
        if (!isset($this->jobs[$identifier])) {
            throw new \RuntimeException('This job is not managed by this queue');
        }

        return $this->jobs[$identifier];
    }

    public function release(Job $job)
    {
        $managedJob = $this->getJob($job->getIdentifier());
        $managedJob->setStatus(Job::FREE);
        $managedJob->setExpire(0);
    }

    public function count($status = Job::FREE)
    {
        return array_reduce($this->jobs,
            function ($count, Job $job) use ($status) {
                switch ($status) {
                    case Job::FREE:
                        return $job->getStatus() === Job::FREE && $job->getExpire() <= time() ? $count + 1 : $count;
                    case Job::CLAIMED:
                        return $job->getStatus() === Job::FREE && $job->getExpire() > time() ? $count + 1 : $count;
                    case Job::COMPLETE:
                        return $job->getStatus() === Job::COMPLETE ? $count + 1 : $count;
                }
            }, 0);
    }

}