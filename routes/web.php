<?php

use App\Jobs\ProcessXMLJob;
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
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Spatie\ArrayToXml\ArrayToXml;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
