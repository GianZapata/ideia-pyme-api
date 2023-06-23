<?php

namespace App\Jobs;

use App\Models\Complemento;
use App\Models\Comprobante;
use App\Models\ComprobanteTraslado;
use App\Models\Concepto;
use App\Models\ConceptoTraslado;
use App\Models\DoctoRelacionado;
use App\Models\Emisor;
use App\Models\Factura;
use App\Models\Nomina;
use App\Models\NominaDeduccion;
use App\Models\NominaDeducciones;
use App\Models\NominaEmisor;
use App\Models\NominaOtroPago;
use App\Models\NominaPercepcion;
use App\Models\NominaPercepciones;
use App\Models\NominaReceptor;
use App\Models\Pago;
use App\Models\Receptor;
use App\Models\SubsidioEmpleo;
use App\Services\XmlProcessingService;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProcessXMLJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $files;
    protected $xmlProcessingService;

    /**
     * Create a new job instance.
     */
    public function __construct( $files, XmlProcessingService $xmlProcessingService )
    {
        $this->files = $files;
        $this->xmlProcessingService = $xmlProcessingService;
    }

    public function handle(){
        $this->xmlProcessingService->process($this->files);
    }
}
