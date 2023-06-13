<?php

namespace App\Console\Commands;

use App\Jobs\ProcessXMLJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ProcessXMLFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process:xml-files';

    protected $description = 'Procesa los archivos XML guarda en la carpeta public/xml y los guarda en la bd.';

    public function handle()
    {
        $directory = "public/xml";

        $files = collect(Storage::allFiles($directory));

        $files->chunk(2500)->each(function ($chunk) {
            ProcessXMLJob::dispatch($chunk)->onQueue('xml');
        });

        $this->info('Se han procesado los archivos XML.');
    }
}
