<?php

namespace App\Jobs;

use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessXMLJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $directory = "public/xml/ACS210207S95";
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
            'cfdiComplemento' => '/cfdi:Comprobante/cfdi:Complemento/*',
        ];

        // 'cfdiConceptos' => '/cfdi:Comprobante/cfdi:Conceptos/*',
        // 'cfdiConcepto' => '/cfdi:Comprobante/cfdi:Concepto/*',

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

                $cfdiConceptos = $xml->xpath('/cfdi:Comprobante/cfdi:Conceptos/*');

                if( $cfdiConceptos) {
                    foreach ($cfdiConceptos as $concepto => $value) {
                        $attributes = $value->attributes();
                        $newAttributes = [];

                        foreach ($attributes as $key => $value) {
                            if(in_array($key, $disabledKeys)) $value = '**********';
                            $newAttributes[$key] = (string) $value;
                        }

                        $data[$rfc][$type][$year][$month]['cfdiConceptos'][$concepto] = $newAttributes;
                    }
                }

                $cfdiImp = $xml->xpath('/cfdi:Comprobante/cfdi:Impuestos/cfdi:Traslados/*');
                if( $cfdiImp ) {
                    foreach ($cfdiImp as $imp => $value) {
                        $attributes = $value->attributes();
                        $newAttributes = [];

                        foreach ($attributes as $key => $value) {
                            if(in_array($key, $disabledKeys)) $value = '**********';
                            $newAttributes[$key] = (string) $value;
                        }

                        $data[$rfc][$type][$year][$month]['cfdiImpuestos']['cfdiTraslados'][$imp] = $newAttributes;
                    }
                }
            }

        });

        $jsonFilePath = 'public/json/data.json';
        Storage::put($jsonFilePath, json_encode($data , JSON_PRETTY_PRINT));
    }


}
