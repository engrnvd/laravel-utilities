<?php

namespace Naveed\Utils\Traits;

/**
 * Trait HasLogFile
 * @package App\Traits
 * @property string $logEveryHour
 * Set to true if there are a lot of logs in a day
 * @property string $logDir
 * The directory name to store the log files in
 *
 */
trait HasLogs
{
    protected $logFileHandle = null;
    protected $currentLogFile = '';

    protected function log($content)
    {
        try {
            // create a separate file for every hour / day
            $logFileName = property_exists($this, 'logEveryHour') && $this->logEveryHour ? date('Y-m-d-H') . '-00.log' : date('Y-m-d') . '.log';
            if ($logFileName != $this->currentLogFile && $this->logFileHandle) {
                fclose($this->logFileHandle);
                $this->logFileHandle = null;
            }

            // open file if not already opened
            if (!$this->logFileHandle) {
                $dir = storage_path("logs/{$this->logDir}/");
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }

                $this->currentLogFile = $logFileName;
                $this->logFileHandle = fopen($dir . $this->currentLogFile, 'a');
            }

            // log
            $msg = date("Y-m-d H:i:s============================================\n");
            $msg .= is_string($content) ? $content : var_export($content, true);
            $msg .= "\n\n";
            fwrite($this->logFileHandle, $msg);
        } catch (\Exception $e) {
            \Log::error("Error occurred while logging content: " . $e->getTraceAsString());
        }
    }
}
