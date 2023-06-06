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
use App\Models\Pago;
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
    $directory = "public/xml/ACS210207S95";

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

            $xml = simplexml_load_string($contents);

            $cfdiComprobante = $xml->xpath('/cfdi:Comprobante');
            $attributesComprobante = $cfdiComprobante[0]->attributes();

            $cfdiEmisor = $xml->xpath('/cfdi:Comprobante/cfdi:Emisor');
            $attributesEmisor = $cfdiEmisor[0]->attributes();

            $cfdiConceptos = $xml->xpath('/cfdi:Comprobante/cfdi:Conceptos/cfdi:Concepto');

            $cfdiComplemento = $xml->xpath('/cfdi:Comprobante/cfdi:Complemento/*');

            // Comprobamos si hay más de un elemento
            if (count($cfdiComplemento) > 1) {
                // Obtenemos los atributos de los dos primeros elementos
                $attributesComplemento1 = ((array) $cfdiComplemento[0]->attributes())['@attributes'] ?? [];
                $attributesComplemento2 = ((array) $cfdiComplemento[1]->attributes())['@attributes'] ?? [];

                if (count($attributesComplemento2) > count($attributesComplemento1)) {
                    // Si el segundo elemento es más largo, lo usamos
                    $attributesComplemento = $attributesComplemento2;
                } else {
                    // Si no, usamos el primer elemento
                    $attributesComplemento = $attributesComplemento1;
                }

            } else {
                // Si solo hay un elemento, usamos ese
                $attributesComplemento = $cfdiComplemento[0]->attributes();
            }

            $attributesComplemento = (object) $attributesComplemento;

            $cfdiReceptor = $xml->xpath('/cfdi:Comprobante/cfdi:Receptor');
            $attributesReceptor = $cfdiReceptor[0]->attributes();


            $fecha = isset($attributesComprobante['Fecha']) ? Carbon::parse($attributesComprobante['Fecha']) : null;

            $year = $fecha->format('Y');
            $month = $fecha->format('m');

            /** CFDI Emisor */
            $emisor = Emisor::firstOrCreate(
                [ 'rfc'              => (string) $attributesEmisor->Rfc ],
                [
                    'nombre'         => (string) $attributesEmisor->Nombre,
                    'regimen_fiscal' => (string) $attributesEmisor->RegimenFiscal
                ],
            );

            /** CFDI Receptor */
            $receptor = Receptor::firstOrCreate([
                'rfc'               => (string) $attributesReceptor->Rfc
            ], [
                'nombre'            => (string) $attributesReceptor->Nombre,
                'uso_cfdi'          => (string) $attributesReceptor->UsoCFDI
            ]);

            $factura = Factura::updateOrCreate([
                'uuid'          => $uuid,
            ],[
                'year'          => $year,
                'month'         => $month,
                'tipo'          => $type, // 'emitido' o 'recibido
                'emisor_id'     => $emisor->id,
                'receptor_id'   => $receptor->id
            ]);

            /** CFDI Comprobante */
            $comprobante = Comprobante::create([
                'certificado'           => (string) $attributesComprobante->Certificado ?? null,
                'fecha'                 => Carbon::parse((string) $attributesComprobante->Fecha) ?? null,
                'folio'                 => (string) $attributesComprobante->Folio ?? null,
                'forma_pago'            => (string) $attributesComprobante->FormaPago ?? null,
                'lugar_expedicion'      => (string) $attributesComprobante->LugarExpedicion ?? null,
                'metodo_pago'           => (string) $attributesComprobante->MetodoPago ?? null,
                'moneda'                => (string) $attributesComprobante->Moneda ?? null,
                'no_certificado'        => (string) $attributesComprobante->NoCertificado ?? null,
                'sello'                 => (string) $attributesComprobante->Sello ?? null,
                'sub_total'             => (string) $attributesComprobante->SubTotal ?? null,
                'tipo_cambio'           => (string) $attributesComprobante->TipoCambio ?? null,
                'tipo_comprobante'      => (string) $attributesComprobante->TipoDeComprobante ?? null,
                'total'                 => (string) $attributesComprobante->Total ?? null,
                'version'               => (string) $attributesComprobante->Version ?? null,
                'condiciones_de_pago'   => (string) $attributesComprobante->condiciones_de_pago ?? null,
                'exportacion'           => (string) $attributesComprobante->exportacion ?? null,
                'serie'                 => (string) $attributesComprobante->serie ?? null,
                'factura_id'            => $factura->id,
            ]);

            /** CFDI Complemento */
            $complemento = Complemento::create([
                'uuid'                  => (string) $attributesComplemento->UUID ?? null,
                'fecha_timbrado'        => Carbon::parse((string) $attributesComplemento->FechaTimbrado) ?? null,
                'no_certificado_sat'    => (string) $attributesComplemento->NoCertificadoSAT ?? null,
                'sello_cfd'             => (string) $attributesComplemento->SelloCFD ?? null,
                'sello_sat'             => (string) $attributesComplemento->SelloSAT ?? null,
                'version'               => (string) $attributesComplemento->Version ?? null,
                'rfc_prov_certif'       => (string) $attributesComplemento->RfcProvCertif ?? null,
                'factura_id'            => $factura->id,
            ]);

            /** CFDI Conceptos */
            foreach ($cfdiConceptos as $concepto => $conceptoValue) {
                $conceptoAttributes = $conceptoValue->attributes();

                $concepto = $comprobante->conceptos()->create([
                    'cantidad'          => (string) $conceptoAttributes->Cantidad ?? null,
                    'clave_prod_serv'   => (string) $conceptoAttributes->ClaveProdServ ?? null,
                    'clave_unidad'      => (string) $conceptoAttributes->ClaveUnidad ?? null,
                    'descripcion'       => (string) $conceptoAttributes->Descripcion ?? null,
                    'importe'           => (string) $conceptoAttributes->Importe ?? null,
                    'no_identificacion' => (string) $conceptoAttributes->NoIdentificacion ?? null,
                    'unidad'            => (string) $conceptoAttributes->Unidad ?? null,
                    'valor_unitario'    => (string) $conceptoAttributes->ValorUnitario ?? null,
                    'comprobante_id'    => $comprobante->id,
                ]);

                /** CFDI Conceptos Impuestos */
                $cfdiConceptoImpuestosTraslados = $conceptoValue->xpath('cfdi:Impuestos/cfdi:Traslados/cfdi:Traslado');

                foreach ($cfdiConceptoImpuestosTraslados as $cfdiTraslado) {
                    $attributesTraslado = $cfdiTraslado->attributes();

                    ConceptoTraslado::create([
                        'base'          => (string) $attributesTraslado->Base,
                        'impuesto'      => (string) $attributesTraslado->Impuesto,
                        'tipo_factor'   => (string) $attributesTraslado->TipoFactor,
                        'tasa_o_cuota'  => (string) $attributesTraslado->TasaOCuota,
                        'importe'       => (string) $attributesTraslado->Importe,
                        'concepto_id'   => $concepto->id,
                    ]);
                }

            }

            /** CFDI Impuesto Traslados */
            $cfdiImpuestos = $xml->xpath('/cfdi:Comprobante/cfdi:Impuestos/cfdi:Traslados/cfdi:Traslado');
            if($cfdiImpuestos) {
                $attributesImpuestos = $cfdiImpuestos[0]->attributes();

                ComprobanteTraslado::create([
                    'base'         => (string) $attributesImpuestos->Base ?? null,
                    'impuesto'    => (string) $attributesImpuestos->Impuesto ?? null,
                    'tipo_factor' => (string) $attributesImpuestos->TipoFactor ?? null,
                    'tasa_o_cuota' => (string) $attributesImpuestos->TasaOCuota ?? null,
                    'importe' => (string) $attributesImpuestos->Importe ?? null,
                    'comprobante_id' => $comprobante->id,
                ]);
            }

            $namespaces = $xml->getNamespaces(true);
            $pagoNamespace = '';
            foreach ($namespaces as $prefix => $namespace) {
                if (strpos($prefix, 'pago') !== false) {
                    $pagoNamespace = $prefix;
                    break;
                }
            }

            if ($pagoNamespace) {
                $xml->registerXPathNamespace($pagoNamespace, $namespaces[$pagoNamespace]);

                $pago = $xml->xpath("/cfdi:Comprobante/cfdi:Complemento/{$pagoNamespace}:Pagos");
                $pagos = $xml->xpath("/cfdi:Comprobante/cfdi:Complemento/{$pagoNamespace}:Pagos/{$pagoNamespace}:Pago");
                $pagoTotal = $xml->xpath("/cfdi:Comprobante/cfdi:Complemento/{$pagoNamespace}:Pagos/{$pagoNamespace}:Totales ");

                $pagoAttributes = (!empty($pago)) ? $pago[0]->attributes() : null;
                $pagosAttributes = (!empty($pagos)) ? $pagos[0]->attributes() : null;
                $pagoTotalAttributes = (!empty($pagoTotal)) ? $pagoTotal[0]->attributes() : null;

                $doctosRelacionados = [];

                foreach($pagos as $pago) {
                    $doctosRelacionados[] = $pago->xpath("{$pagoNamespace}:DoctoRelacionado");
                }

                $pago = Pago::create([
                    'version'           => (string) $pagoAttributes->Version ?? null,
                    'monto_total_pagos' => isset($pagoTotalAttributes->MontoTotalPagos) ? (string) $pagoTotalAttributes->MontoTotalPagos : null,
                    'fecha_pago'        => isset($pagosAttributes->FechaPago) ? Carbon::parse((string) $pagosAttributes->FechaPago) : null,
                    'forma_de_pago_p'   => (string) $pagosAttributes->FormaDePagoP ?? null,
                    'moneda_p'          => (string) $pagosAttributes->MonedaP ?? null,
                    'tipo_cambio_p'     => (string) $pagosAttributes->TipoCambioP ?? null,
                    'monto'             => (string) $pagosAttributes->Monto ?? null,
                    'complemento_id'    => $complemento->id,
                ]);

                foreach ($doctosRelacionados as $doctosRelacionado) {
                    $doctoRelacionadoAttributes = $doctosRelacionado[0]->attributes();
                    DoctoRelacionado::create([
                        'pago_id'               => $pago->id,
                        'id_documento'          => $doctoRelacionadoAttributes->IdDocumento ?? null,
                        'serie'                 => $doctoRelacionadoAttributes->Serie ?? null,
                        'folio'                 => $doctoRelacionadoAttributes->Folio ?? null,
                        'moneda_dr'             => $doctoRelacionadoAttributes->MonedaDR ?? null,
                        'equivalencia_dr'       => $doctoRelacionadoAttributes->EquivalenciaDR ?? null,
                        'num_parcialidad'       => $doctoRelacionadoAttributes->NumParcialidad ?? null,
                        'imp_saldo_ant'         => $doctoRelacionadoAttributes->ImpSaldoAnt ?? null,
                        'imp_pagado'            => $doctoRelacionadoAttributes->ImpPagado ?? null,
                        'imp_saldo_insoluto'    => $doctoRelacionadoAttributes->ImpSaldoInsoluto ?? null,
                        'objeto_imp_dr'         => $doctoRelacionadoAttributes->ObjetoImpDR ?? null,
                    ]);
                }


            }

        }
    });
});
