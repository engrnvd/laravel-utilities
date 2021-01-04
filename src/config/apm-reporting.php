<?php
/**
 * Created by PhpStorm.
 * User: Shahid
 * Date: 12/4/2020
 * Time: 11:44 AM
 */

return [
    'default' => [
        'config' => [
            'single_date' => false,
            'time_picker' => true,
        ],
        'filters' => [
            /*'filter/relation name' => [
                'name' => 'string: filter/relation name',
                'enable' => 'boolean default enable or disable',
                'title' => 'Filter Queues',
                'labelField' => 'filter/relation value to display for example label, name or title',
                'identifier' => 'primary or unique key of filter/relation'
            ],*/
        ],
        'emailTemplate' => [
            'default' => 'default',
            'defaultGroupBy' => 'default-groupBy',
        ],
    ],
    'emailTemplatePath' => 'vendor.apm.emails.reports',
    'reports' => [
        /*"Report name" => [
            'class' => 'Path to report class for example \App\Helpers\Reports\ReportClass',
            'toggle_filters' => 'Array of filter(s) to toggle default setting',
        ],*/
    ],
];