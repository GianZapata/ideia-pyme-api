<?php

namespace App\Jobs;

use App\Http\Controllers\SatDownloader\SatDownloaderController;
use App\Models\SatReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class SatScraperJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $rfc;
    protected $password;
    protected $year;
    protected $downloadType;
    protected $reportId;


    /**
     * Create a new job instance.
     */
    public function __construct($rfc, $password, $year, $downloadType, $reportId)
    {
        $this->rfc = $rfc;
        $this->password = $password;
        $this->year = $year;
        $this->downloadType = $downloadType;
        $this->reportId = $reportId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $rfc = $this->rfc;
        $password = $this->password;
        $year = $this->year;
        $downloadType = $this->downloadType;
        $reportId = $this->reportId;

        try {
            // SatDownloaderController::executeQuery($rfc, $password, $year, $downloadType, $reportId);
            SatDownloaderController::downloadYear($rfc, $password, $year);
            Log::info('Se ejecutó el job correctamente');

            Artisan::call('process:xml-files', [
                'rfc' => $rfc,
            ]);

            $satReport = SatReport::where('id', $reportId)->first();
            $satReport->total_tasks_completed = intval($satReport->total_tasks_completed) + 1;
            $satReport->save();

            Log::info('Se ejecutó el job de procesamiento de archivos correctamente');

            return;
        } catch (\Throwable $th) {
            Log::info('Error al iniciar sesión con las credenciales en Job');
            Log::info($th->getMessage());
            return;
        }
    }
}
