<?php

namespace Naveed\Utils\Helpers\Reporting;

use Illuminate\Support\Arr;

abstract class AbstractReport
{
    protected $startDate;
    protected $endDate;
    protected $startTime;
    protected $endTime;

    protected $title = '';
    protected $report = '';
    protected $data;
    protected $groupBy = '';
    protected $filters = '';

    protected $emailTemplate = '';
    protected $fileHandler;

    abstract protected function getData();

    abstract protected function columns();

    public function __construct($config = [])
    {
        $this->startDate = Arr::get($config, 'startDate', date('Y-m-d'));
        $this->endDate = Arr::get($config, 'endDate', $this->startDate);
        $this->startTime = Arr::get($config, 'startTime', "00:00:00");
        $this->endTime = Arr::get($config, 'endTime', "23:59:59");
        $this->report = Arr::get($config, 'report');
        $this->filters = Arr::get($config, 'filters');
    }

    protected function getFilter($filter)
    {
        return Arr::get($this->filters, $filter);
    }

    public static function config($key)
    {
        return config("apm-reporting.{$key}");
    }

    public function load()
    {
        $this->data = $this->getData();
        if ($this->groupBy) $this->data = $this->data->groupBy($this->groupBy);
        return $this;
    }

    public function get()
    {
        return [
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'startTime' => $this->startTime,
            'endTime' => $this->endTime,
            'groupBy' => $this->groupBy,
            'data' => $this->data,
            'columns' => $this->columns(),
            'title' => $this->getTitle(),
            'report' => $this->report,
            'emailTemplatePath' => self::config('emailTemplatePath')
        ];
    }

    public function getEmailTemplate()
    {
        if (!$this->emailTemplate) {
            $this->emailTemplate = self::config("default.emailTemplate." . ($this->groupBy ? 'defaultGroupBy' : 'default'));
        }
        return self::config('emailTemplatePath') . ".{$this->emailTemplate}";
    }

    public function getTitle()
    {
        if ($this->title)
            return $this->title;
        return "{$this->report} between {$this->startDate} and {$this->endDate}";
    }

    protected function getDateRangeWithTime()
    {
        return [
            "{$this->startDate} {$this->startTime}",
            "{$this->endDate} {$this->endTime}",
        ];
    }

    protected function startDateTime()
    {
        return "{$this->startDate} {$this->startTime}";
    }

    protected function endDateTime()
    {
        return "{$this->endDate} {$this->endTime}";
    }

    protected function initiateFileHandler()
    {
        $this->fileHandler = fopen('php://temp', 'rw');
    }

    public function toCsv()
    {
        $this->initiateFileHandler();
        $columns = $this->columns();
        if ($this->groupBy) {
            foreach ($this->data as $groupTitle => $groupData) {
                $this->toCsvDataIteration($groupData, $columns, $groupTitle, true);
            }
        } else {
            $this->toCsvDataIteration($this->data, $columns);
        }
        return $this->toCsvGetStream();
    }

    protected function toCsvDataIteration($dataSet, $columns, $heading = false, $separatorRow = false)
    {
        if ($heading) fputcsv($this->fileHandler, [$heading]);
        fputcsv($this->fileHandler, array_values($columns));
        foreach ($dataSet as $data) {
            fputcsv($this->fileHandler, $this->getSelectedKeys($columns, $data));
        }
        if ($separatorRow) fputcsv($this->fileHandler, []);
    }

    protected function toCsvGetStream()
    {
        rewind($this->fileHandler);
        $csv = stream_get_contents($this->fileHandler);
        fclose($this->fileHandler);
        return $csv;
    }

    protected function getSelectedKeys($columns, $data)
    {
        $newData = [];
        foreach ($columns as $columnKey => $column) {
            $newData[$columnKey] = Arr::get($data, $columnKey, '');
        }
        return $newData;
    }
}