<?php

namespace Naveed\Utils\Helpers\Reporting;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

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
    protected $attachmentMimeType = '';
    protected $fileHandler;
    protected $sumColumns = [];
    protected $additionalData = [];

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
        $this->title = Arr::get($config, 'title');
    }

    protected function getFilter($filter)
    {
        return Arr::get($this->filters, $filter);
    }

    /**
     * @param $key string
     * @param $default | null
     * @return mixed
     */
    public static function config($key, $default = null)
    {
        return config("apm-reporting.{$key}", $default);
    }

    public function load()
    {
        $this->data = $this->getData();
        if ($this->groupBy) $this->data = $this->data->groupBy($this->groupBy);
        return $this;
    }

    public function get()
    {
        $data = [
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'startTime' => $this->startTime,
            'endTime' => $this->endTime,
            'groupBy' => $this->groupBy,
            'data' => $this->data,
            'columns' => $this->columns(),
            'sum_columns' => $this->sumColumns,
            'title' => $this->getTitle(),
            'report' => $this->report,
            'emailTemplatePath' => self::config('emailTemplatePath')
        ];
        return array_merge($data, $this->additionalData);
    }

    /**
     * @return string|array
     */
    public function getEmailView()
    {
        return static::config("default.emailSetting.append_into_body", true) ? $this->getEmailTemplate() : [];
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

    /**
     * @return string
     */
    public function getMimeType()
    {
        if ($this->attachmentMimeType) {
            return $this->attachmentMimeType;
        }
        return static::config("default.emailSetting.attachment_type", "text/csv");
    }

    /**
     * @return string
     */
    public function getAttachmentName()
    {
        return $this->getTitle() . "." . Str::afterLast($this->getMimeType(), '/');
    }

    /**
     * @return boolean
     */
    public function attachFile()
    {
        return static::config("default.emailSetting.include_attachment", true);
    }

    public function attachmentData()
    {
        $this->initiateFileHandler();
        $columns = $this->columns();
        if ($this->groupBy) {
            foreach ($this->data as $groupTitle => $groupData) {
                $this->toCsvDataIteration($this->getWithSumRow($groupData), $columns, $groupTitle);
            }
        } else {
            $this->toCsvDataIteration($this->getWithSumRow($this->data), $columns);
        }
        return $this->toCsvGetStream();
    }

    /**
     * @deprecated use "attachmentData" instead
     */
    public function toCsv()
    {
        return $this->attachmentData();
    }

    protected function toCsvDataIteration($dataSet, $columns, $heading = false, $separatorRow = false)
    {
        fprintf($this->fileHandler, chr(0xEF) . chr(0xBB) . chr(0xBF));
        if ($heading) fputcsv($this->fileHandler, [$heading]);
        fprintf($this->fileHandler, chr(0xEF) . chr(0xBB) . chr(0xBF));
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

    private function getWithSumRow($data)
    {
        if (count($this->sumColumns)) {
            $sumRow = [];
            foreach ($this->columns() as $variable => $column) {
                $sumRow[$variable] = in_array($variable, $this->sumColumns) ? round($data->sum($variable), 2) : '';
            }
            $data[] = $sumRow;
        }
        return $data;
    }
}