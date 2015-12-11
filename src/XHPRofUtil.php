<?php

namespace LastCall\Crawler;

class XHProfUtil
{

    public static function start()
    {
        xhprof_enable(XHPROF_FLAGS_CPU | XHPROF_FLAGS_MEMORY);
    }

    public static function stop()
    {
        $xhprof_data = xhprof_disable();
        $xhprof_runs = new \XHProfRuns_Default();
        $runId = $xhprof_runs->save_run($xhprof_data, null);

        return $runId;
    }
}