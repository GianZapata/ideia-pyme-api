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

class ProcessXMLJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $files;

    /**
     * Create a new job instance.
     */
    public function __construct( $files )
    {
        $this->files = $files;
    }

    public function handle(){
        foreach ($this->files as $file) {
            if (Storage::exists($file) && pathinfo($file, PATHINFO_EXTENSION) === 'xml') {
                try {
                    DB::transaction(function () use ($file) {
                        $this->procesarArchivoXML($file);
                    });
                } catch (Exception $e) {
                    Log::error($e->getMessage());
                }
            }
        }
    }

    function procesarArchivoXML( $file ){
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

        $cfdiComplementos = $xml->xpath('/cfdi:Comprobante/cfdi:Complemento/*');

        $attributesComplemento = null;

        foreach($cfdiComplementos as $cfdiComplemento) {
            // Convertimos los atributos a un array
            $attributes = ((array) $cfdiComplemento->attributes())['@attributes'] ?? [];

            // Verificamos si este nodo es de tipo TimbreFiscalDigital
            if($cfdiComplemento->getName() === 'TimbreFiscalDigital') {
                $attributesComplemento = $attributes;
                break;
            }

        }

        $attributesComplemento = (object) $attributesComplemento;

        $cfdiReceptor = $xml->xpath('/cfdi:Comprobante/cfdi:Receptor');
        $attributesReceptor = $cfdiReceptor[0]->attributes();

        $fecha = isset($attributesComprobante['Fecha']) ? Carbon::parse($attributesComprobante['Fecha']) : null;

        $facturaFind = Factura::where('uuid', $uuid)->first();

        if($facturaFind) return;

        /** CFDI Emisor */
        $emisor = Emisor::firstOrCreate([
            'rfc'              => (string) $attributesEmisor->Rfc
        ], [
                'nombre'         => (string) $attributesEmisor->Nombre,
                'regimen_fiscal' => (string) $attributesEmisor->RegimenFiscal
        ]);

        /** CFDI Receptor */
        $receptor = Receptor::firstOrCreate([
            'rfc'               => (string) $attributesReceptor->Rfc
        ], [
            'nombre'            => (string) $attributesReceptor->Nombre,
            'uso_cfdi'          => (string) $attributesReceptor->UsoCFDI
        ]);

        $factura = Factura::create([
            'uuid'          => $uuid,
            'fecha'         => $fecha,
            'tipo'          => $type, // 'emitido' o 'recibido
            'emisor_id'     => $emisor->id,
            'receptor_id'   => $receptor->id
        ]);

        /** CFDI Comprobante */
        $comprobante = Comprobante::create([
            'certificado'           => $attributesComprobante->Certificado ?? null,
            'condiciones_de_pago'   => (string) $attributesComprobante->CondicionesDePago ?? null,
            'exportacion'           => (string) $attributesComprobante->Exportacion ?? null,
            'fecha'                 => Carbon::parse((string) $attributesComprobante->Fecha) ?? null,
            'folio'                 => (string) $attributesComprobante->Folio ?? null,
            'forma_pago'            => (string) $attributesComprobante->FormaPago ?? null,
            'lugar_expedicion'      => (string) $attributesComprobante->LugarExpedicion ?? null,
            'metodo_pago'           => (string) $attributesComprobante->MetodoPago ?? null,
            'moneda'                => (string) $attributesComprobante->Moneda ?? null,
            'no_certificado'        => (string) $attributesComprobante->NoCertificado ?? null,
            'sello'                 => (string) $attributesComprobante->Sello ?? null,
            'serie'                 => (string) $attributesComprobante->Serie ?? null,
            'sub_total'             => (string) $attributesComprobante->SubTotal ?? null,
            'tipo_cambio'           => (string) $attributesComprobante->TipoCambio ?? null,
            'tipo_comprobante'      => (string) $attributesComprobante->TipoDeComprobante ?? null,
            'total'                 => (string) $attributesComprobante->Total ?? null,
            'version'               => (string) $attributesComprobante->Version ?? null,
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

            $concepto = Concepto::create([
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
        $nominasNamespace = '';
        foreach ($namespaces as $prefix => $namespace) {
            if (strpos($prefix, 'pago') !== false) {
                $pagoNamespace = $prefix;
                break;
            }

            if (strpos($prefix, 'nomina') !== false) {
                $nominasNamespace = $prefix;
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

        if( $nominasNamespace ) {
            $xml->registerXPathNamespace($nominasNamespace, $namespaces[$nominasNamespace]);

            $nomina = $xml->xpath("/cfdi:Comprobante/cfdi:Complemento/{$nominasNamespace}:Nomina");

            $nominaAttributes = (!empty($nomina)) ? $nomina[0]->attributes() : null;

            $nominaData  = [
                'fecha_final_pago'   =>  (string) $nominaAttributes->FechaFinalPago ?? null,
                'fecha_inicial_pago' =>  (string) $nominaAttributes->FechaInicialPago ?? null,
                'fecha_pago'         =>  (string) $nominaAttributes->FechaPago ?? null,
                'num_dias_pagados'   =>  (string) $nominaAttributes->NumDiasPagados ?? null,
                'tipo_nomina'        =>  (string) $nominaAttributes->TipoNomina ?? null,
                'total_deducciones'  =>  (string) $nominaAttributes->TotalDeducciones ?? null,
                'total_otros_pagos'  =>  (string) $nominaAttributes->TotalOtrosPagos ?? null,
                'total_percepciones' =>  (string) $nominaAttributes->TotalPercepciones ?? null,
                'version'            =>  (string) $nominaAttributes->Version ?? null,
                'complemento_id'     =>  $complemento->id,
            ];

            $nomina = Nomina::create($nominaData);

            // Emisor
            $nominaEmisor = $xml->xpath("/cfdi:Comprobante/cfdi:Complemento/{$nominasNamespace}:Nomina/{$nominasNamespace}:Emisor");
            if($nominaEmisor) {
                $nominaEmisorAttributes = $nominaEmisor[0]->attributes();

                $nominaEmisorData = [
                    'registro_patronal' => (string) $nominaEmisorAttributes->RegistroPatronal ?? null,
                    'nomina_id'         => $nomina->id,
                ];

                NominaEmisor::create($nominaEmisorData);
            }

            $nominaReceptor = $xml->xpath("/cfdi:Comprobante/cfdi:Complemento/{$nominasNamespace}:Nomina/{$nominasNamespace}:Receptor");
            if($nominaReceptor) {
                $nominaReceptorAttributes = $nominaReceptor[0]->attributes();

                $nominaReceptorData = [
                    'antiguedad'               => (string) $nominaReceptorAttributes->Antigüedad ?? null,
                    'clave_ent_fed'            => (string) $nominaReceptorAttributes->ClaveEntFed ?? null,
                    'curp'                     => (string) $nominaReceptorAttributes->Curp ?? null,
                    'departamento'             => (string) $nominaReceptorAttributes->Departamento ?? null,
                    'fecha_inicio_rel_laboral' => isset($nominaReceptorAttributes->FechaInicioRelLaboral) ? Carbon::parse((string) $nominaReceptorAttributes->FechaInicioRelLaboral) : null,
                    'num_empleado'             => (string) $nominaReceptorAttributes->NumEmpleado ?? null,
                    'num_seguridad_social'     => (string) $nominaReceptorAttributes->NumSeguridadSocial ?? null,
                    'periodicidad_pago'        => (string) $nominaReceptorAttributes->PeriodicidadPago ?? null,
                    'puesto'                   => (string) $nominaReceptorAttributes->Puesto ?? null,
                    'riesgo_puesto'            => (string) $nominaReceptorAttributes->RiesgoPuesto ?? null,
                    'salario_base_cot_apor'    => (string) $nominaReceptorAttributes->SalarioBaseCotApor ?? null,
                    'salario_diario_integrado' => (string) $nominaReceptorAttributes->SalarioDiarioIntegrado ?? null,
                    'sindicalizado'            => (string) $nominaReceptorAttributes->Sindicalizado ?? null,
                    'tipo_contrato'            => (string) $nominaReceptorAttributes->TipoContrato ?? null,
                    'tipo_jornada'             => (string) $nominaReceptorAttributes->TipoJornada ?? null,
                    'tipo_regimen'             => (string) $nominaReceptorAttributes->TipoRegimen ?? null,
                    'nomina_id'                => $nomina->id,
                ];

                NominaReceptor::create($nominaReceptorData);
            }

            // Percepciones
            $nominaPercepciones = $xml->xpath("/cfdi:Comprobante/cfdi:Complemento/{$nominasNamespace}:Nomina/{$nominasNamespace}:Percepciones");
            if($nominaPercepciones) {
                $nominasPercepcionesAttributes = $nominaPercepciones[0]->attributes();

                $nominaPercepcionesPaths = [];

                $nominaPercepcionesData = [
                    'total_exento'  => (string) $nominasPercepcionesAttributes->TotalExento ?? null,
                    'total_gravado' => (string) $nominasPercepcionesAttributes->TotalGravado ?? null,
                    'total_sueldos' => (string) $nominasPercepcionesAttributes->TotalSueldos ?? null,
                    'nomina_id'     => $nomina->id,
                ];

                $nominaPercepcionesModel = NominaPercepciones::create($nominaPercepcionesData);

                foreach ($nominaPercepciones as $nominaPercepcion) {
                    $nominaPercepcionesPaths[] = $nominaPercepcion->xpath("{$nominasNamespace}:Percepcion");
                }

                foreach ($nominaPercepcionesPaths as $percepcion) {
                    $percepcionAttributes = $percepcion[0]->attributes();

                    $nominaPercepcionData = [
                        'clave'                  => (string) $percepcionAttributes->Clave ?? null,
                        'concepto'               => (string) $percepcionAttributes->Concepto ?? null,
                        'importe_exento'         => (string) $percepcionAttributes->ImporteExento ?? null,
                        'importe_gravado'        => (string) $percepcionAttributes->ImporteGravado ?? null,
                        'tipo_percepcion'        => (string) $percepcionAttributes->TipoPercepcion ?? null,
                        'nomina_percepciones_id' => $nominaPercepcionesModel->id,
                    ];

                    NominaPercepcion::create($nominaPercepcionData);
                }

            }

            // Deducciones
            $nominaDeducciones = $xml->xpath("/cfdi:Comprobante/cfdi:Complemento/{$nominasNamespace}:Nomina/{$nominasNamespace}:Deducciones");
            if($nominaDeducciones) {
                $nominaDeduccionesAttributes = $nominaDeducciones[0]->attributes();

                $nominaDeduccionesPaths = [];

                $nominaDeduccionesData = [
                    'total_impuestos_retenidos' => (string) $nominaDeduccionesAttributes->TotalImpuestosRetenidos ?? null,
                    'total_otras_deducciones'   => (string) $nominaDeduccionesAttributes->TotalOtrasDeducciones ?? null,
                    'nomina_id'                 => $nomina->id,
                ];

                $nominaDeduccionesModel = NominaDeducciones::create($nominaDeduccionesData);

                foreach ($nominaDeducciones as $nominaDeduccion) {
                    $nominaDeduccionesPaths[] = $nominaDeduccion->xpath("{$nominasNamespace}:Deduccion");
                }

                foreach ($nominaDeduccionesPaths as $deduccion) {
                    $deduccionAttributes = $deduccion[0]->attributes();

                    $nominaDeduccionData = [
                        'clave'                 => (string) $deduccionAttributes->Clave ?? null,
                        'concepto'              => (string) $deduccionAttributes->Concepto ?? null,
                        'importe'               => (string) $deduccionAttributes->Importe ?? null,
                        'tipo_deduccion'        => (string) $deduccionAttributes->TipoDeduccion ?? null,
                        'nomina_deducciones_id' => $nominaDeduccionesModel->id,
                    ];

                    NominaDeduccion::create($nominaDeduccionData);
                }

            }

            $nominaOtrosPagosPaths = $xml->xpath("/cfdi:Comprobante/cfdi:Complemento/{$nominasNamespace}:Nomina/*");
            $nominaOtrosPagos = null;


            foreach ($nominaOtrosPagosPaths as $nominaOtrosPago) {
                // OtrosPagos
                if( $nominaOtrosPago->getName() === 'OtrosPagos' ) {
                    $nominaOtrosPagos = $nominaOtrosPago->xpath("{$nominasNamespace}:OtroPago");

                    foreach ($nominaOtrosPagos as $otroPago) {
                        $otrosPagosAttributes = $otroPago->attributes();

                        $nominaOtrosPagosData = [
                            'clave'         => (string) $otrosPagosAttributes->Clave ?? null,
                            'concepto'      => (string) $otrosPagosAttributes->Concepto ?? null,
                            'importe'       => (string) $otrosPagosAttributes->Importe ?? null,
                            'tipo_otro_pago' => (string) $otrosPagosAttributes->TipoOtroPago ?? null,
                            'nomina_id'     => $nomina->id,
                        ];

                        $nominaOtroPago = NominaOtroPago::create($nominaOtrosPagosData);
                        $subsidioAlEmpleoNode = $otroPago->xpath("{$nominasNamespace}:SubsidioAlEmpleo");

                        if($subsidioAlEmpleoNode){
                            $subsidioAlEmpleoAttributes = $subsidioAlEmpleoNode[0]->attributes();

                            $subsidioEmpleoData = [
                                'subsidio_causado' => (string) $subsidioAlEmpleoAttributes->SubsidioCausado ?? null,
                                'nomina_otro_pago_id'    => $nominaOtroPago->id,
                            ];

                            SubsidioEmpleo::create($subsidioEmpleoData);
                        }
                    }

                    break;
                }
            }
        }

    }

    // private function procesarArchivoXML( $file ){
    //     // Obtiene el contenido del archivo.
    //     $contents = Storage::get($file);
    //     $contents = str_replace('xmlns:schemaLocation', 'x-schemaLocation', $contents);

    //     // Extrae el RFC y el tipo (emitido o recibido) del nombre del archivo
    //     $pathParts = explode('/', $file);
    //     $rfc = $pathParts[2];
    //     $type = $pathParts[3];
    //     $uuid = basename($file, '.xml');

    //     $xml = simplexml_load_string($contents);

    //     $cfdiComprobante = $xml->xpath('/cfdi:Comprobante');
    //     $attributesComprobante = $cfdiComprobante[0]->attributes();

    //     $cfdiEmisor = $xml->xpath('/cfdi:Comprobante/cfdi:Emisor');
    //     $attributesEmisor = $cfdiEmisor[0]->attributes();

    //     $cfdiConceptos = $xml->xpath('/cfdi:Comprobante/cfdi:Conceptos/cfdi:Concepto');

    //     $cfdiComplemento = $xml->xpath('/cfdi:Comprobante/cfdi:Complemento/*');

    //     // Comprobamos si hay más de un elemento
    //     if (count($cfdiComplemento) > 1) {
    //         // Obtenemos los atributos de los dos primeros elementos
    //         $attributesComplemento1 = ((array) $cfdiComplemento[0]->attributes())['@attributes'] ?? [];
    //         $attributesComplemento2 = ((array) $cfdiComplemento[1]->attributes())['@attributes'] ?? [];

    //         if (count($attributesComplemento2) > count($attributesComplemento1)) {
    //             // Si el segundo elemento es más largo, lo usamos
    //             $attributesComplemento = $attributesComplemento2;
    //         } else {
    //             // Si no, usamos el primer elemento
    //             $attributesComplemento = $attributesComplemento1;
    //         }

    //     } else {
    //         // Si solo hay un elemento, usamos ese
    //         $attributesComplemento = $cfdiComplemento[0]->attributes();
    //     }

    //     $attributesComplemento = (object) $attributesComplemento;

    //     $cfdiReceptor = $xml->xpath('/cfdi:Comprobante/cfdi:Receptor');
    //     $attributesReceptor = $cfdiReceptor[0]->attributes();

    //     $fecha = isset($attributesComprobante['Fecha']) ? Carbon::parse($attributesComprobante['Fecha']) : null;

    //     $year = $fecha->format('Y');
    //     $month = $fecha->format('m');


    //     /** CFDI Emisor */
    //     $emisor = Emisor::firstOrCreate(
    //         [ 'rfc'              => (string) $attributesEmisor->Rfc ],
    //         [
    //             'nombre'         => (string) $attributesEmisor->Nombre,
    //             'regimen_fiscal' => (string) $attributesEmisor->RegimenFiscal
    //         ],
    //     );

    //     /** CFDI Receptor */
    //     $receptor = Receptor::firstOrCreate([
    //         'rfc'               => (string) $attributesReceptor->Rfc
    //     ], [
    //         'nombre'            => (string) $attributesReceptor->Nombre,
    //         'uso_cfdi'          => (string) $attributesReceptor->UsoCFDI
    //     ]);

    //     $factura = Factura::updateOrCreate([
    //         'uuid'          => $uuid,
    //     ],[
    //         'year'          => $year,
    //         'month'         => $month,
    //         'tipo'          => $type, // 'emitido' o 'recibido
    //         'emisor_id'     => $emisor->id,
    //         'receptor_id'   => $receptor->id
    //     ]);

    //     /** CFDI Comprobante */
    //     $comprobante = Comprobante::create([
    //         'certificado'           => (string) $attributesComprobante->Certificado ?? null,
    //         'fecha'                 => Carbon::parse((string) $attributesComprobante->Fecha) ?? null,
    //         'folio'                 => (string) $attributesComprobante->Folio ?? null,
    //         'forma_pago'            => (string) $attributesComprobante->FormaPago ?? null,
    //         'lugar_expedicion'      => (string) $attributesComprobante->LugarExpedicion ?? null,
    //         'metodo_pago'           => (string) $attributesComprobante->MetodoPago ?? null,
    //         'moneda'                => (string) $attributesComprobante->Moneda ?? null,
    //         'no_certificado'        => (string) $attributesComprobante->NoCertificado ?? null,
    //         'sello'                 => (string) $attributesComprobante->Sello ?? null,
    //         'sub_total'             => (string) $attributesComprobante->SubTotal ?? null,
    //         'tipo_cambio'           => (string) $attributesComprobante->TipoCambio ?? null,
    //         'tipo_comprobante'      => (string) $attributesComprobante->TipoDeComprobante ?? null,
    //         'total'                 => (string) $attributesComprobante->Total ?? null,
    //         'version'               => (string) $attributesComprobante->Version ?? null,
    //         'condiciones_de_pago'   => (string) $attributesComprobante->condiciones_de_pago ?? null,
    //         'exportacion'           => (string) $attributesComprobante->exportacion ?? null,
    //         'serie'                 => (string) $attributesComprobante->serie ?? null,
    //         'factura_id'            => $factura->id,
    //     ]);

    //     /** CFDI Complemento */
    //     $complemento = Complemento::create([
    //         'uuid'                  => (string) $attributesComplemento->UUID ?? null,
    //         'fecha_timbrado'        => Carbon::parse((string) $attributesComplemento->FechaTimbrado) ?? null,
    //         'no_certificado_sat'    => (string) $attributesComplemento->NoCertificadoSAT ?? null,
    //         'sello_cfd'             => (string) $attributesComplemento->SelloCFD ?? null,
    //         'sello_sat'             => (string) $attributesComplemento->SelloSAT ?? null,
    //         'version'               => (string) $attributesComplemento->Version ?? null,
    //         'rfc_prov_certif'       => (string) $attributesComplemento->RfcProvCertif ?? null,
    //         'factura_id'            => $factura->id,
    //     ]);

    //     /** CFDI Conceptos */
    //     foreach ($cfdiConceptos as $concepto => $conceptoValue) {
    //         $conceptoAttributes = $conceptoValue->attributes();

    //         $concepto = $comprobante->conceptos()->create([
    //             'cantidad'          => (string) $conceptoAttributes->Cantidad ?? null,
    //             'clave_prod_serv'   => (string) $conceptoAttributes->ClaveProdServ ?? null,
    //             'clave_unidad'      => (string) $conceptoAttributes->ClaveUnidad ?? null,
    //             'descripcion'       => (string) $conceptoAttributes->Descripcion ?? null,
    //             'importe'           => (string) $conceptoAttributes->Importe ?? null,
    //             'no_identificacion' => (string) $conceptoAttributes->NoIdentificacion ?? null,
    //             'unidad'            => (string) $conceptoAttributes->Unidad ?? null,
    //             'valor_unitario'    => (string) $conceptoAttributes->ValorUnitario ?? null,
    //             'comprobante_id'    => $comprobante->id,
    //         ]);

    //         /** CFDI Conceptos Impuestos */
    //         $cfdiConceptoImpuestosTraslados = $conceptoValue->xpath('cfdi:Impuestos/cfdi:Traslados/cfdi:Traslado');

    //         foreach ($cfdiConceptoImpuestosTraslados as $cfdiTraslado) {
    //             $attributesTraslado = $cfdiTraslado->attributes();

    //             ConceptoTraslado::create([
    //                 'base'          => (string) $attributesTraslado->Base,
    //                 'impuesto'      => (string) $attributesTraslado->Impuesto,
    //                 'tipo_factor'   => (string) $attributesTraslado->TipoFactor,
    //                 'tasa_o_cuota'  => (string) $attributesTraslado->TasaOCuota,
    //                 'importe'       => (string) $attributesTraslado->Importe,
    //                 'concepto_id'   => $concepto->id,
    //             ]);
    //         }

    //     }

    //     /** CFDI Impuesto Traslados */
    //     $cfdiImpuestos = $xml->xpath('/cfdi:Comprobante/cfdi:Impuestos/cfdi:Traslados/cfdi:Traslado');
    //     if($cfdiImpuestos) {
    //         $attributesImpuestos = $cfdiImpuestos[0]->attributes();

    //         ComprobanteTraslado::create([
    //             'base'         => (string) $attributesImpuestos->Base ?? null,
    //             'impuesto'    => (string) $attributesImpuestos->Impuesto ?? null,
    //             'tipo_factor' => (string) $attributesImpuestos->TipoFactor ?? null,
    //             'tasa_o_cuota' => (string) $attributesImpuestos->TasaOCuota ?? null,
    //             'importe' => (string) $attributesImpuestos->Importe ?? null,
    //             'comprobante_id' => $comprobante->id,
    //         ]);
    //     }

    //     $namespaces = $xml->getNamespaces(true);
    //     $pagoNamespace = '';
    //     foreach ($namespaces as $prefix => $namespace) {
    //         if (strpos($prefix, 'pago') !== false) {
    //             $pagoNamespace = $prefix;
    //             break;
    //         }
    //     }

    //     if ($pagoNamespace) {
    //         $xml->registerXPathNamespace($pagoNamespace, $namespaces[$pagoNamespace]);

    //         $pago = $xml->xpath("/cfdi:Comprobante/cfdi:Complemento/{$pagoNamespace}:Pagos");
    //         $pagos = $xml->xpath("/cfdi:Comprobante/cfdi:Complemento/{$pagoNamespace}:Pagos/{$pagoNamespace}:Pago");
    //         $pagoTotal = $xml->xpath("/cfdi:Comprobante/cfdi:Complemento/{$pagoNamespace}:Pagos/{$pagoNamespace}:Totales ");

    //         $pagoAttributes = (!empty($pago)) ? $pago[0]->attributes() : null;
    //         $pagosAttributes = (!empty($pagos)) ? $pagos[0]->attributes() : null;
    //         $pagoTotalAttributes = (!empty($pagoTotal)) ? $pagoTotal[0]->attributes() : null;

    //         $doctosRelacionados = [];

    //         foreach($pagos as $pago) {
    //             $doctosRelacionados[] = $pago->xpath("{$pagoNamespace}:DoctoRelacionado");
    //         }

    //         $pago = Pago::create([
    //             'version'           => (string) $pagoAttributes->Version ?? null,
    //             'monto_total_pagos' => isset($pagoTotalAttributes->MontoTotalPagos) ? (string) $pagoTotalAttributes->MontoTotalPagos : null,
    //             'fecha_pago'        => isset($pagosAttributes->FechaPago) ? Carbon::parse((string) $pagosAttributes->FechaPago) : null,
    //             'forma_de_pago_p'   => (string) $pagosAttributes->FormaDePagoP ?? null,
    //             'moneda_p'          => (string) $pagosAttributes->MonedaP ?? null,
    //             'tipo_cambio_p'     => (string) $pagosAttributes->TipoCambioP ?? null,
    //             'monto'             => (string) $pagosAttributes->Monto ?? null,
    //             'complemento_id'    => $complemento->id,
    //         ]);

    //         foreach ($doctosRelacionados as $doctosRelacionado) {
    //             $doctoRelacionadoAttributes = $doctosRelacionado[0]->attributes();
    //             DoctoRelacionado::create([
    //                 'pago_id'               => $pago->id,
    //                 'id_documento'          => $doctoRelacionadoAttributes->IdDocumento ?? null,
    //                 'serie'                 => $doctoRelacionadoAttributes->Serie ?? null,
    //                 'folio'                 => $doctoRelacionadoAttributes->Folio ?? null,
    //                 'moneda_dr'             => $doctoRelacionadoAttributes->MonedaDR ?? null,
    //                 'equivalencia_dr'       => $doctoRelacionadoAttributes->EquivalenciaDR ?? null,
    //                 'num_parcialidad'       => $doctoRelacionadoAttributes->NumParcialidad ?? null,
    //                 'imp_saldo_ant'         => $doctoRelacionadoAttributes->ImpSaldoAnt ?? null,
    //                 'imp_pagado'            => $doctoRelacionadoAttributes->ImpPagado ?? null,
    //                 'imp_saldo_insoluto'    => $doctoRelacionadoAttributes->ImpSaldoInsoluto ?? null,
    //                 'objeto_imp_dr'         => $doctoRelacionadoAttributes->ObjetoImpDR ?? null,
    //             ]);
    //         }


    //     }
    // }


    /**
     * Execute the job.
     */
    public function handleToJson()
    {
        ini_set('memory_limit', '256M');
        $directory = "public/xml";
        $data  = [];

        $disabledKeys = [
            'Certificado',
            'NoCertificado',
            'NoCertificadoSAT',
            'Sello',
            'SelloCFD',
            'SelloSAT',
            'TipoDeComprobante',
        ];

        $enabledNodes = [
            'cfdiComprobante' => '/cfdi:Comprobante',
            'cfdiEmisor' => '/cfdi:Comprobante/cfdi:Emisor',
            'cfdiReceptor' => '/cfdi:Comprobante/cfdi:Receptor',
            'cfdiComplemento' => '/cfdi:Comprobante/cfdi:Complemento/*',
        ];

        // Obtén una lista de todos los archivos en el directorio y sus subdirectorios.
        $files = collect(Storage::allFiles($directory));

        // Itera a través de la lista de archivos.
        $files->each(function ($file) use (&$data, $disabledKeys, $enabledNodes) {
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

                // Get last part of the path
                $uuid = basename($file, '.xml');

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

                        $data[$rfc][$type][$year][$month][$uuid][$nodeName] = $newAttributes;
                    }
                }

                $cfdiConceptos = $xml->xpath('/cfdi:Comprobante/cfdi:Conceptos/cfdi:Concepto');

                if($cfdiConceptos) {
                    foreach ($cfdiConceptos as $concepto => $value) {
                        $attributes = $value->attributes();
                        $newAttributesConcepto = [];

                        foreach ($attributes as $key => $attrValue) {
                            if(in_array($key, $disabledKeys)) $attrValue = '**********';
                            $newAttributesConcepto[$key] = (string) $attrValue;
                        }

                        $cfdiConceptoImpuestosTraslados = $value->xpath('cfdi:Impuestos/cfdi:Traslados/cfdi:Traslado');
                        if($cfdiConceptoImpuestosTraslados) {
                            $newAttributesConcepto['cfdiConceptoImpuestos']['cfdiTraslados'] = [];
                            foreach ($cfdiConceptoImpuestosTraslados as $traslado => $cfdiConceptoImpuestosTraslado) {
                                $attributesConceptoImpuestosTraslado = $cfdiConceptoImpuestosTraslado->attributes();
                                $newAttributesTraslado = [];

                                foreach ($attributesConceptoImpuestosTraslado as $key => $attrValue) {
                                    if(in_array($key, $disabledKeys)) $attrValue = '**********';
                                    $newAttributesTraslado[$key] = (string) $attrValue;
                                }

                                $newAttributesConcepto['cfdiConceptoImpuestos']['cfdiTraslados'][$traslado] = $newAttributesTraslado;
                            }
                        }

                        $data[$rfc][$type][$year][$month][$uuid]['cfdiConceptos'][$concepto] = $newAttributesConcepto;

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

                        $data[$rfc][$type][$year][$month][$uuid]['cfdiImpuestos']['cfdiTraslados'][$imp] = $newAttributes;
                    }
                }
            }

        });

        $jsonFilePath = 'public/json/data.json';
        Storage::put($jsonFilePath, json_encode($data , JSON_PRETTY_PRINT));
    }


}
