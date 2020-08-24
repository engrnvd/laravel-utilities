<?php

namespace Naveed\Utils\Commands;

use Illuminate\Console\Command;
use Naveed\Utils\Traits\HasLogs;

class LogsClearCommand extends Command
{
    use HasLogs;
    private $logDir = 'logs-clear';
    private $threshold;
    private $removeEmptyDir;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'The command used to remove old log files';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->threshold = config('apm-laravel-utilities.logs.threshold',10);
        $this->removeEmptyDir = config('apm-laravel-utilities.logs.remove_empty_dir',true);
        $this->clearDir(storage_path("/logs"));
    }

    private function clearDir($dir)
    {
        foreach (new \DirectoryIterator($dir) as $fileInfo) {
            if ($fileInfo->isDot() || $fileInfo->getFilename() == '.gitignore') continue;
            $path = $fileInfo->getRealPath();
            if ($fileInfo->isFile()) {
                if ($fileInfo->getMTime() < (time() - $this->threshold * 24 * 3600)) {
                    $this->log("Removing file: {$path}");
                    @unlink($path);
                }
            } else {
                $this->clearDir($path);
                if ($this->removeEmptyDir && count(\File::allFiles($path)) === 0) {
                    $this->log("Removing directory: {$path}");
                    rmdir($path);
                }
            }
        }
    }
}