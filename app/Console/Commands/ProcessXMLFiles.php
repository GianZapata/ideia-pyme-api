<?php

namespace App\Console\Commands;

use App\Jobs\ProcessXMLJob;
use App\Services\XmlProcessingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessXMLFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process:xml-files {rfc?}';


    protected $description = 'Procesa los archivos XML guarda en la carpeta public/xml y los guarda en la bd.';


    public function handle( XmlProcessingService $xmlProcessingService )
    {
        $rfc = $this->argument('rfc');
        $directory = "public/xml/" . ($rfc ?? '');

        $files = collect(Storage::allFiles($directory));

        $files->chunk(2500)->each(function ($chunk) use ($xmlProcessingService, $rfc) {
            ProcessXMLJob::dispatch($chunk, $xmlProcessingService, $rfc)->onQueue('xml');
        });

        $this->info('Se han procesado los archivos XML.');
    }
}
