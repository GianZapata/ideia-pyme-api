<?php

use App\Jobs\ProcessXMLJob;
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

// Route::get('/test', function (){
//     ProcessXMLJob::dispatch();
// });

Route::get('/test' , function (){
    $directory = "public/xml/KFM131016RJ1/";
    $data  = [];
    $allKeys = [];
    $someKeys = [];

    $disabledKeys = [
        'Certificado',
        'NoCertificado',
        'NoCertificadoSAT',
        'Sello',
        'SelloCFD',
        'SelloSAT',
        'TipoDeComprobante',
        'UUID',
    ];

    $enabledNodes = [
        'cfdiComprobante' => '/cfdi:Comprobante',
        'cfdiEmisor' => '/cfdi:Comprobante/cfdi:Emisor',
        'cfdiReceptor' => '/cfdi:Comprobante/cfdi:Receptor',
        'cfdiConceptos' => '/cfdi:Comprobante/cfdi:Conceptos/*',
        'cfdiComplemento' => '/cfdi:Comprobante/cfdi:Complemento/*',
    ];

    // Obtén una lista de todos los archivos en el directorio y sus subdirectorios.
    $files = collect(Storage::allFiles($directory));

    // Itera a través de la lista de archivos.
    $files->each(function ($file) use (&$data, &$allKeys, &$someKeys, $disabledKeys, $enabledNodes) {
        // Comprueba si el archivo es un archivo XML.
        if (pathinfo($file, PATHINFO_EXTENSION) === 'xml') {

            // Obtiene el contenido del archivo.
            $contents = Storage::get($file);
            $contents = str_replace('xmlns:schemaLocation', 'x-schemaLocation', $contents);

            // Procesa el contenido como XML.
            $xml = simplexml_load_string($contents);

            // Convierte el XML a JSON
            $json = json_encode($xml);
            $arrayComprobante = json_decode($json, true);
            $attributesComprobante = $arrayComprobante['@attributes'];

            // Extrae el RFC y el tipo (emitido o recibido) del nombre del archivo
            $pathParts = explode('/', $file);
            $rfc = $pathParts[2];
            $type = $pathParts[3];

            // Intenta obtener la fecha del atributo Fecha
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


            Log::info("{$rfc} - {$type} - {$year} - {$month} - {$file}");
            // Nodos que se van a extraer
            foreach ($enabledNodes as $nodeName => $nodePath) {
                $xmlNode = $xml->xpath($nodePath);

                if($xmlNode) {
                    $attributes = $xmlNode[0]->attributes();
                    $newAttributes = [];

                    foreach ($attributes as $key => $value) {
                        if(in_array($key, $disabledKeys)) $value = '**********';
                        $newAttributes[$key] = (string) $value;
                    }

                    $data[$rfc][$type][$year][$month][$nodeName] = $newAttributes;
                }
            }
        }

    });

    $jsonFilePath = 'public/json/data.json';
    Storage::put($jsonFilePath, json_encode($data , JSON_PRETTY_PRINT));
});
