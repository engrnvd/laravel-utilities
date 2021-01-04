<?php
/**
 * Created by PhpStorm.
 * User: Shahid
 * Date: 12/4/2020
 * Time: 11:22 AM
 */

namespace Naveed\Utils\Helpers\Reporting;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;

trait ReportGeneratorControllerTrait
{
    public function loadReports()
    {
        return ReportGenerator::reports();
    }

    protected function modifyRequest()
    {

    }

    public function generate()
    {
        $this->modifyRequest();
        $report = ReportGenerator::find((request('report')));
        if (!$report) {
            abort(400, 'Unknown report.');
        }
        $this->validate(request(), $this->rules($report));
        return (new ReportGenerator(request()->all()))->getReport();
    }

    public function export()
    {
        $this->modifyRequest();
        $report = ReportGenerator::find((request('report')));
        if (!$report) {
            abort(400, 'Unknown report.');
        }
        $this->validate(request(), $this->rules($report));
        return (new ReportGenerator(request()->all()))->getCsv();
    }

    public function sendReport(Request $request)
    {
        $this->modifyRequest();
        $report = ReportGenerator::find((request('report')));
        if (!$report) {
            abort(400, 'Unknown report.');
        }
        $rules = $this->rules($report);
        $rules['recipients'] = 'required|array';
        $this->validate($request, $rules);
        return (new ReportGenerator(request()->all()))->sendReport();
    }

    private function rules($report)
    {
        $rules = [
            'report' => 'required',
            'startDate' => 'required|date',
            'endDate' => 'required|date',
        ];
        if (Arr::get($report->config, 'time_picker')) {
            $rules['startTime'] = 'required|regex:/^\d{2}\:\d{2}\:\d{2}$/';
            $rules['endTime'] = 'required|regex:/^\d{2}\:\d{2}\:\d{2}$/|after:startTime';
        }
        foreach ($report->filters as $filter => $params) {
            if (!$params['enable']) continue;
            $rules["filters.{$filter}"] = 'required|array';
        }
        return $rules;
    }
}