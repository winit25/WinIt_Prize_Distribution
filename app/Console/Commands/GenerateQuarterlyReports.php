<?php

namespace App\Console\Commands;

use App\Services\QuarterlyReportService;
use Illuminate\Console\Command;

class GenerateQuarterlyReports extends Command
{
    protected $signature = 'buypower:generate-quarterly-reports 
                            {--quarter= : Quarter number (1-4)}
                            {--year= : Year}';

    protected $description = 'Generate and send quarterly summary reports to all recipients';

    protected $quarterlyReportService;

    public function __construct(QuarterlyReportService $quarterlyReportService)
    {
        parent::__construct();
        $this->quarterlyReportService = $quarterlyReportService;
    }

    public function handle()
    {
        $quarter = $this->option('quarter') ? (int) $this->option('quarter') : null;
        $year = $this->option('year') ? (int) $this->option('year') : null;

        if ($quarter && ($quarter < 1 || $quarter > 4)) {
            $this->error('Quarter must be between 1 and 4');
            return 1;
        }

        $this->info('Generating quarterly reports...');
        
        $result = $this->quarterlyReportService->generateQuarterlyReports($quarter, $year);

        if ($result['success']) {
            $this->info("Quarterly reports generated successfully!");
            $this->info("Quarter: Q{$result['quarter']} {$result['year']}");
            $this->info("Reports Generated: {$result['generated']}");
            $this->info("Reports Failed: {$result['failed']}");
            return 0;
        }

        $this->error('Failed to generate quarterly reports');
        return 1;
    }
}
