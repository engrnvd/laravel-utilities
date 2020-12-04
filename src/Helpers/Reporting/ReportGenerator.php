<?php

namespace Naveed\Utils\Helpers\Reporting;

use Illuminate\Support\Arr;

class ReportGenerator
{
    private $params;
    /** @var $report AbstractReport */
    private $report;

    /**
     * @param $params array
     */
    public function __construct($params = [])
    {
        $this->params = $params;
    }

    public function generate()
    {
        $class = config("apm-reporting.reports.{$this->params['report']}.class");
        if (!$class) abort(400, "Unknown Report: {$this->params['report']}");
        $this->report = new $class($this->params);
        $this->report->load();
        return $this->report;
    }

    public function getReport()
    {
        return $this->generate()->get();
    }

    public function getCsv()
    {
        return $this->generate()->toCsv();
    }

    public function sendReport()
    {
        $recipients = Arr::get($this->params, 'recipients', []);
        if (!count($recipients)) abort(400, "Recipients not found");
        $report = $this->generate();
        $data = $report->get();
        try {
            app('mailer')->send($report->getEmailTemplate(), $data, function ($m) use ($report) {
                /** @var $m \Illuminate\Mail\Mailable */
                $m->to(Arr::get($this->params, 'recipients', []))
                    ->subject($report->getTitle())
                    ->attachData($report->toCsv(), $report->getTitle() . '.csv');
            });
            return "Sent to:" . join(',', $recipients);
        } catch (\Exception $e) {
            abort($e->getCode(), $e->getMessage());
        }
    }

    private static function config($key)
    {
        return config("apm-reporting.{$key}");
    }

    public static function find($reportName)
    {
        $report = self::config("reports.{$reportName}");
        if (!$report) return false;
        return self::getReportObject($reportName, $report);
    }

    /**
     * @param $report string
     * @param $params array
     * @return \stdClass
     */
    private static function getReportObject($report, $params)
    {
        $instance = new \stdClass();
        $instance->name = $report;
        $instance->config = self::config('default.config');
        $instance->filters = self::config('default.filters');
        foreach (Arr::get($params, 'toggle_filters', []) as $filter) {
            $instance->filters[$filter]['enable'] = !$instance->filters[$filter]['enable'];
        }
        foreach (Arr::get($params, 'toggle_config', []) as $config) {
            $instance->config[$config] = !$instance->config[$config];
        }
        return $instance;
    }

    public static function reports()
    {
        $reports = [];
        foreach (self::config('reports') as $report => $reportParams) {
            $reports[] = self::getReportObject($report, $reportParams);
        }
        return $reports;
    }

    public static function reportNames()
    {
        return array_keys(self::config('reports'));
    }
}