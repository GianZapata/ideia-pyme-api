<?php

use App\Jobs\ProcessXMLJob;
use App\Models\Comprobante;
use App\Models\Concepto;
use App\Models\Emisor;
use App\Models\Factura;
use App\Models\Receptor;
use Carbon\Carbon;
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

Route::get('/test', function (){
    $directory = "public/xml/ACO091214PD0/emitidos/2021";

    // Obtén una lista de todos los archivos en el directorio y sus subdirectorios.
    $files = collect(Storage::allFiles($directory));

    $files->each(function ($file) {
        // Comprueba si el archivo es un archivo XML.
        if (pathinfo($file, PATHINFO_EXTENSION) === 'xml') {
            // Obtiene el contenido del archivo.
            $contents = Storage::get($file);
            $contents = str_replace('xmlns:schemaLocation', 'x-schemaLocation', $contents);

            // Extrae el RFC y el tipo (emitido o recibido) del nombre del archivo
            $pathParts = explode('/', $file);
            $rfc = $pathParts[2];
            $type = $pathParts[3];
            $uuid = basename($file, '.xml');

            // Procesa el contenido como XML.
            $xml = simplexml_load_string($contents);

            // Convierte el XML a JSON
            $json = json_encode($xml);
            $cfdiComprobante = $xml->xpath('/cfdi:Comprobante');
            $attributesComprobante = $cfdiComprobante[0]->attributes();

            $cfdiEmisor = $xml->xpath('/cfdi:Comprobante/cfdi:Emisor');
            $attributesEmisor = $cfdiEmisor[0]->attributes();

            $rfcEmisor = (string) $attributesEmisor->Rfc;
            $nombreEmisor = (string) $attributesEmisor->Nombre;
            $regimenFiscalEmisor = (string) $attributesEmisor->RegimenFiscal;

            $cfdiReceptor = $xml->xpath('/cfdi:Comprobante/cfdi:Receptor');
            $attributesReceptor = $cfdiReceptor[0]->attributes();

            $rfcReceptor = (string) $attributesReceptor->Rfc;
            $nombreReceptor = (string) $attributesReceptor->Nombre;
            $usoCFDI = (string) $attributesReceptor->UsoCFDI;

            $cfdiConceptos = $xml->xpath('/cfdi:Comprobante/cfdi:Conceptos/cfdi:Concepto');
            $attributesConceptos = $cfdiConceptos[0]->attributes();

            try {
                $fecha = isset($attributesComprobante['Fecha']) ? Carbon::parse($attributesComprobante['Fecha']) : null;
            } catch (Exception $e) {
                $fecha = null;
            }

            // Si no se pudo obtener la fecha del atributo Fecha, la extrae de la ruta del archivo
            if (!$fecha) {
                $year = $pathParts[4];
                $month = $pathParts[5];
            } else {
                // Extrae el año y el mes de la fecha
                $year = $fecha->format('Y');
                $month = $fecha->format('m');
            }

            // Crear o buscar el emisor y receptor
            $emisor = Emisor::firstOrCreate(
                [ 'rfc' => $rfcEmisor ],
                [
                    'nombre' => $nombreEmisor,
                    'regimen_fiscal' => $regimenFiscalEmisor
                ],
            );

            $receptor = Receptor::firstOrCreate([
                'rfc' => $rfcReceptor
            ], [
                'nombre' => $nombreReceptor,
                'uso_cfdi' => $usoCFDI
            ]);

            $factura = Factura::create([
                'uuid' => $uuid,
                'year' => $year,
                'month' => $month,
                'emisor_id' => $emisor->id,
                'receptor_id' => $receptor->id
            ]);

            $comprobante = Comprobante::create([
                'certificado' => (string) $attributesComprobante->Certificado ?? null,
                'fecha' => (string) $attributesComprobante->Fecha ?? null,
                'folio' => (string) $attributesComprobante->Folio ?? null,
                'forma_pago' => (string) $attributesComprobante->FormaPago ?? null,
                'lugar_expedicion' => (string) $attributesComprobante->LugarExpedicion ?? null,
                'metodo_pago' => (string) $attributesComprobante->MetodoPago ?? null,
                'moneda' => (string) $attributesComprobante->Moneda ?? null,
                'no_certificado' => (string) $attributesComprobante->NoCertificado ?? null,
                'sello' => (string) $attributesComprobante->Sello ?? null,
                'sub_total' => (string) $attributesComprobante->SubTotal ?? null,
                'tipo_cambio' => (string) $attributesComprobante->TipoCambio ?? null,
                'tipo_comprobante' => (string) $attributesComprobante->TipoDeComprobante ?? null,
                'total' => (string) $attributesComprobante->Total ?? null,
                'version' => (string) $attributesComprobante->Version ?? null,
                'condiciones_de_pago' => (string) $attributesComprobante->condiciones_de_pago ?? null,
                'exportacion' => (string) $attributesComprobante->exportacion ?? null,
                'serie' => (string) $attributesComprobante->serie ?? null,
                'factura_id' => $factura->id,
            ]);

            foreach ($cfdiConceptos as $concepto => $conceptoValue) {
                $conceptoAttributes = $conceptoValue->attributes();

                $concepto = $comprobante->conceptos()->create([
                    'cantidad' => (string) $conceptoAttributes->Cantidad ?? null,
                    'clave_prod_serv' => (string) $conceptoAttributes->ClaveProdServ ?? null,
                    'clave_unidad' => (string) $conceptoAttributes->ClaveUnidad ?? null,
                    'descripcion' => (string) $conceptoAttributes->Descripcion ?? null,
                    'importe' => (string) $conceptoAttributes->Importe ?? null,
                    'no_identificacion' => (string) $conceptoAttributes->NoIdentificacion ?? null,
                    'unidad' => (string) $conceptoAttributes->Unidad ?? null,
                    'valor_unitario' => (string) $conceptoAttributes->ValorUnitario ?? null,
                    'comprobante_id' => $comprobante->id,
                ]);


                $cfdiConceptoImpuestosTraslados = $conceptoValue->xpath('cfdi:Impuestos/cfdi:Traslados/cfdi:Traslado');
                foreach ($cfdiConceptoImpuestosTraslados as $cfdiTraslado) {
                    $attributesTraslado = $cfdiTraslado->attributes();
                    $concepto->impuestos()->create([
                        'base' => $attributesTraslado->Base,
                        'impuesto' => $attributesTraslado->Impuesto,
                        'tipo_factor' => $attributesTraslado->TipoFactor,
                        'tasa_o_cuota' => $attributesTraslado->TasaOCuota,
                        'importe' => $attributesTraslado->Importe
                    ]);
                }
            }
        }
    });
});

// Route::get('/test', function (){
//     ProcessXMLJob::dispatch();
// });

// Route::get('/test' , function (){
//     $directory = "public/xml/KFM131016RJ1/";
//     $data  = [];
//     $allKeys = [];
//     $someKeys = [];

//     $disabledKeys = [
//         'Certificado',
//         'NoCertificado',
//         'NoCertificadoSAT',
//         'Sello',
//         'SelloCFD',
//         'SelloSAT',
//         'TipoDeComprobante',
//         'UUID',
//     ];

//     $enabledNodes = [
//         'cfdiComprobante' => '/cfdi:Comprobante',
//         'cfdiEmisor' => '/cfdi:Comprobante/cfdi:Emisor',
//         'cfdiReceptor' => '/cfdi:Comprobante/cfdi:Receptor',
//         'cfdiConceptos' => '/cfdi:Comprobante/cfdi:Conceptos/*',
//         'cfdiComplemento' => '/cfdi:Comprobante/cfdi:Complemento/*',
//     ];

//     // Obtén una lista de todos los archivos en el directorio y sus subdirectorios.
//     $files = collect(Storage::allFiles($directory));

//     // Itera a través de la lista de archivos.
//     $files->each(function ($file) use (&$data, &$allKeys, &$someKeys, $disabledKeys, $enabledNodes) {
//         // Comprueba si el archivo es un archivo XML.
//         if (pathinfo($file, PATHINFO_EXTENSION) === 'xml') {

//             // Obtiene el contenido del archivo.
//             $contents = Storage::get($file);
//             $contents = str_replace('xmlns:schemaLocation', 'x-schemaLocation', $contents);

//             // Procesa el contenido como XML.
//             $xml = simplexml_load_string($contents);

//             // Convierte el XML a JSON
//             $json = json_encode($xml);
//             $arrayComprobante = json_decode($json, true);
//             $attributesComprobante = $arrayComprobante['@attributes'];

//             // Extrae el RFC y el tipo (emitido o recibido) del nombre del archivo
//             $pathParts = explode('/', $file);
//             $rfc = $pathParts[2];
//             $type = $pathParts[3];

//             // Intenta obtener la fecha del atributo Fecha
//             try {
//                 $fecha = isset($attributesComprobante['Fecha']) ? Carbon::parse($attributesComprobante['Fecha']) : null;
//             } catch (Exception $e) {
//                 $fecha = null;
//             }

//             // Si no se pudo obtener la fecha del atributo Fecha, la extrae de la ruta del archivo
//             if (!$fecha) {
//                 $year = $pathParts[4];
//                 $month = $pathParts[5];
//             } else {
//                 // Extrae el año y el mes de la fecha
//                 $year = $fecha->format('Y');
//                 $month = $fecha->format('m');
//             }


//             Log::info("{$rfc} - {$type} - {$year} - {$month} - {$file}");
//             // Nodos que se van a extraer
//             foreach ($enabledNodes as $nodeName => $nodePath) {
//                 $xmlNode = $xml->xpath($nodePath);

//                 if($xmlNode) {
//                     $attributes = $xmlNode[0]->attributes();
//                     $newAttributes = [];

//                     foreach ($attributes as $key => $value) {
//                         if(in_array($key, $disabledKeys)) $value = '**********';
//                         $newAttributes[$key] = (string) $value;
//                     }

//                     $data[$rfc][$type][$year][$month][$nodeName] = $newAttributes;
//                 }
//             }
//         }

//     });

//     $jsonFilePath = 'public/json/data.json';
//     Storage::put($jsonFilePath, json_encode($data , JSON_PRETTY_PRINT));
// });
