<?php

namespace App\Services;

use App\Models\Client;

class FinancialHealthService
{
    protected const PONDERACION = [
        "cuantitativas" => [
            "1" => 60,
            "2" => 48,
            "3" => 36,
            "4" => 24,
            "5" => 12
        ],
        "cualitativas" => [
            "1" => 40,
            "2" => 32,
            "3" => 24,
            "4" => 16,
            "5" => 8
        ]
    ];

    protected const FACTORES = [
        "industriaManufacturera" => [
            "capitalTrabajo"  => 1.5,
            "liquidez"        => 2.02,
            "tesoreria"       => 1.49,
            "disponible"      => 0.75,
            "endeudamiento"   => 0.43,
            "calidadDeuda"    => 0.72,
            "costeDeuda"      => 0.12,
            "rotacionActivo"  => 0.93,
            "rotacionStock"   => 2.61,
            "existencia"      => 139.95,
            "cuentasPorCobrar"=> 90.69,
            "cuentasPorPagar" => 87.08,
            "roi"             => 0.06,
            "roe"             => 0.08,
            "margenNeto"      => 0.08
        ]
    ];

    /**
     * Calcula los porcentajes en base a los factores seleccionados.
     *
     * @param array $factores Factores seleccionados para el cálculo.
     * @param array $valores Valores utilizados en los cálculos.
     * @return array Porcentajes calculados.
     */
    public function calculatePercentages(array $factores, array $valores): array
    {
        $porcentajes = [];

        foreach ($factores as $clave => $valor) {
            if (isset($valores[$clave])) {
                $porcentaje = $valores[$clave] / $valor * 10;
                $porcentajes[$clave] = ($porcentaje > 10) ? 10 : $porcentaje;
            }
        }

        return $porcentajes;
    }

    /**
     * Asigna un rating y una descripción en base a una puntuación.
     *
     * @param int $puntuación La puntuación obtenida.
     * @return array Un array con el rating y la descripción asignados.
     */
    public function asignarRating($puntuacion): array
    {
        $ratingDescripcion = [
            [50, 'E - Muy Bajo', 'Situación crítica.'],
            [55, 'D - Bajo', 'Elevada debilidad financiera.'],
            [60, 'C - Medio', 'Muestras de debilidad financiera.'],
            [65, 'B - Bueno', 'Elevada fortaleza financiera.']
        ];

        $rating = '';
        $descripcion = '';

        foreach ($ratingDescripcion as $item) {
            if ($puntuacion < $item[0]) {
                $rating = $item[1];
                $descripcion = $item[2];
                break;
            }
        }

        // Si la puntuación es mayor o igual a 65, se asume el máximo rating y descripción.

        if (empty($rating)) {
            $rating = 'A - Muy Bueno';
            $descripcion = 'Máxima fortaleza financiera.';
        }

        return [
            'rating' => $rating,
            'descripcion' => $descripcion
        ];
    }


    /**
     * Obtiene el puntaje mínimo de riesgo para una métrica dada.
     *
     * @param string $metricName Nombre de la métrica.
     * @param float $metricValue Valor de la métrica.
     * @param array $riskValues Valores de riesgo para comparar con la métrica.
     * @param string $type (Opcional) Tipo de métricas ('cuantitativas' por defecto).
     * @return array Array que contiene el valor de la métrica, los valores de riesgo,
     *               la diferencia mínima, la clave correspondiente a la diferencia mínima
     *               y la ponderación de riesgo asociada.
    */
    public static function getMinRiskScore($metricName, $metricValue, $riskValues, $type = 'cuantitativas')
    {
        $riskPonderation = self::PONDERACION;

        $minDifference = PHP_FLOAT_MAX;
        $minDifferenceKey = null;

        foreach ($riskValues as $key => $riskValue) {
            $difference = abs($riskValue - $metricValue);
            if ($difference < $minDifference) {
                $minDifference = $difference;
                $minDifferenceKey = $key;
            }
        }

        return [
            "valor" => $metricValue,
            "riesgo" => $riskValues,
            "minDiferencia" => $minDifference,
            "claveMinima" => $minDifferenceKey,
            "ponderacion" => $riskPonderation[$type][$minDifferenceKey]
        ];
    }

    /**
     * Calcula la salud financiera de un cliente.
     *
     * @param Client $client Cliente para el cual se calculará la salud financiera.
     * @return array Array que contiene la información sobre la salud financiera del cliente,
     *               incluyendo puntajes, calificaciones y descripciones.
    */
    public function calculateFinancialHealth( Client $client )
    {
        $ventas = $client->ventas;
        $ventasAnioAnterior = $client->ventasAnterior;
        $resultadoDeExplotacion = $client->resExplotacion;
        $resultadoFinanciero = $client->resFinanciero;
        $resultadoAntesImpuestos = $client->resAntesImp;
        $deudoresComerciales = $client->deudoresComerciales;
        $inversionesFinancierasCortoPlazo = $client->inversionesFin;
        $efectivoLiquidez = $client->efectivoLiquidez;
        $activoTotal = $client->activoTotal;
        $pasivoNoCirculante = $client->pasivoNoCirculante;
        $provisionesLargoPlazo = $client->provisionesLargoPlazo;
        $pasivoCirculante = $client->pasivoCirculante;
        $prestamosActuales = $client->prestamosActuales ?? 600;

        if(
            !$ventas  ||
            !$ventasAnioAnterior  ||
            !$resultadoDeExplotacion  ||
            !$resultadoFinanciero  ||
            !$resultadoAntesImpuestos  ||
            !$deudoresComerciales  ||
            !$inversionesFinancierasCortoPlazo  ||
            !$efectivoLiquidez  ||
            !$activoTotal  ||
            !$pasivoNoCirculante  ||
            !$provisionesLargoPlazo  ||
            !$pasivoCirculante  ||
            !$prestamosActuales
        ) return [];

        $inventario = 0;

        $capitalTrabajo = (
            ( $deudoresComerciales + $inversionesFinancierasCortoPlazo + $efectivoLiquidez + $inventario ) - $pasivoCirculante
        ) / $pasivoCirculante;

        $liquidez = ( $deudoresComerciales + $inversionesFinancierasCortoPlazo + $efectivoLiquidez ) / $pasivoCirculante;

        $liquidezOrigen = $liquidez;

        $tesoreria = $deudoresComerciales / $pasivoCirculante;

        $disponible = $efectivoLiquidez / $pasivoCirculante;

        $endeudamiento = $activoTotal / (
            $pasivoCirculante +
            $provisionesLargoPlazo +
            $pasivoCirculante
        );

        $calidadDeuda = $pasivoCirculante / (
            $pasivoNoCirculante +
            $provisionesLargoPlazo +
            $pasivoCirculante
        );

        $costeDeuda = 0;
        $rotacionStock = 0;
        $rotacionActivo = $ventas / $activoTotal;
        $existencia = 0;

        $cuentasPorCobrar = 365 / ( $ventas / ( $deudoresComerciales / 2 ) );

        $cuentasPorPagar = 365 / ( $ventas / ( $ventas - $resultadoDeExplotacion ) / 2);

        $roi = 0;

        $roe = $resultadoDeExplotacion / $activoTotal;
        $margenNeto = $resultadoAntesImpuestos / $ventas;

        $factoresSelected = self::FACTORES['industriaManufacturera'];

        $porcentajes = self::calculatePercentages($factoresSelected, [
            'capitalTrabajo' => $capitalTrabajo,
            'liquidez' => $liquidez,
            'tesoreria' => $tesoreria,
            'disponible' => $disponible,
            'endeudamiento' => $endeudamiento,
            'calidadDeuda' => $calidadDeuda,
            'costeDeuda' => $costeDeuda,
            'rotacionActivo' => $rotacionActivo,
            'rotacionStock' => $rotacionStock,
            'existencia' => $existencia,
            'cuentasPorCobrar' => $cuentasPorCobrar,
            'cuentasPorPagar' => $cuentasPorPagar,
            'roi' => $roi,
            'roe' => $roe,
            'margenNeto' => $margenNeto
        ]);

        $sumatorias = [
            "capitalTrabajo"   => min($porcentajes['capitalTrabajo'], 10),
            "liquidez"         => min($porcentajes['liquidez'], 10),
            "tesoreria"        => min($porcentajes['tesoreria'], 10),
            "disponible"       => min($porcentajes['disponible'], 10),
            "endeudamiento"    => min($porcentajes['endeudamiento'], 10),
            "calidadDeuda"     => min($porcentajes['calidadDeuda'], 10),
            "costeDeuda"       => min($porcentajes['costeDeuda'], 10),
            "rotacionActivo"   => min($porcentajes['rotacionActivo'], 10),
            "rotacionStock"    => min($porcentajes['rotacionStock'], 10),
            "existencia"       => min($porcentajes['existencia'], 10),
            "cuentasPorCobrar" => min($porcentajes['cuentasPorCobrar'], 10),
            "cuentasPorPagar"  => min($porcentajes['cuentasPorPagar'], 10),
            "roi"              => min($porcentajes['roi'], 10),
            "roe"              => min($porcentajes['roe'], 10),
            "margenNeto"       => min($porcentajes['margenNeto'], 10)
        ];

        $puntuacion = array_sum($sumatorias) - (
            $sumatorias['calidadDeuda'] -
            $sumatorias['costeDeuda'] -
            $sumatorias['rotacionStock'] -
            $sumatorias['existencia'] -
            $sumatorias['roi']
        );

        $activoCirculante = $activoTotal;
        $rentabilidad = ( $activoCirculante - $pasivoCirculante ) / $pasivoCirculante;
        $rentabilidad = $rentabilidad / 1.4;
        $rentabilidad = 10 * $rentabilidad;

        $liquidez = $activoCirculante / $pasivoCirculante;
        $liquidez = $liquidez / 1.94;
        $liquidez = 10 * $liquidez;

        $solvencia = $deudoresComerciales / $pasivoCirculante;
        $solvencia = $solvencia / 1.33;
        $solvencia = 10 * $solvencia;

        $actividad = $ventas / $activoTotal;
        $actividad = $actividad / 0.94;
        $actividad = 10 * $actividad;

        $evolucion = $ventas / $ventasAnioAnterior;
        $evolucion = 10 * $evolucion;

        if ( $prestamosActuales < 1 ) {
            $costoFinanciero = 0;
        } else {
            $costoFinanciero = $resultadoFinanciero / (1 - .32);
            $costoFinanciero = $costoFinanciero / $prestamosActuales;
            $costoFinanciero = 10 * $costoFinanciero;
        }

        $conversiones = [
            'capitalTrabajo' => $capitalTrabajo,
            'liquidez' => $liquidez,
            'solvencia' => $solvencia,
            'actividad' => $actividad,
            'evolucion' => $evolucion,
            'rentabilidad' => $rentabilidad,
            'costoFinanciero' => $costoFinanciero
        ];

        $resultadosRating = self::asignarRating($puntuacion);
        $rating = $resultadosRating['rating'];
        $descripcion = $resultadosRating['descripcion'];

        $conceptosRiesgos = [
            "capitalTrabajo" => [
                'promedio' => 0.86,
                'desviacion' => 0.78,
            ],
            "razonCirculante" => [
                'promedio' => 1.86,
                'desviacion' => 0.78,
            ],
            "pruebaAcida" => [
                'promedio' => 1.6,
                'desviacion' => 0.81,
            ],
            "tesoreria" => [
                'promedio' => 1.06,
                'desviacion' => 0.44,
            ],
            "rotacionActivo" => [
                'promedio' => 2.06,
                'desviacion' => 0.71,
            ],
            "margenNeto" => [
                'promedio' => 0.06,
                'desviacion' => 0.04,
            ],
            "roa" => [
                'promedio' => 0.06,
                'desviacion' => 0.04,
            ],
            "periodoPromedioCobro" => [
                'promedio' => 74.33,
                'desviacion' => 42.85,
            ],
            "periodoPromedioPago" => [
                'promedio' => 66.97,
                'desviacion' => 54.58,
            ],
            "deuda" => [
                'promedio' => 0.47,
                'desviacion' => 0.12,
            ]
        ];

        foreach ($conceptosRiesgos as $key => $conceptoRiesgos) {
            if ($key == 'periodoPromedioCobro' || $key == 'periodoPromedioPago' || $key == 'deuda') {
                $conceptosRiesgos[$key]['riesgo'][1] = $conceptoRiesgos['promedio'] - $conceptoRiesgos['desviacion'];
                $conceptosRiesgos[$key]['riesgo'][2] = $conceptoRiesgos['promedio'] - ($conceptoRiesgos['desviacion'] / 2);
                $conceptosRiesgos[$key]['riesgo'][3] = $conceptoRiesgos['promedio'];
                $conceptosRiesgos[$key]['riesgo'][4] = $conceptoRiesgos['promedio'] + ($conceptoRiesgos['desviacion'] / 2);
                $conceptosRiesgos[$key]['riesgo'][5] = $conceptoRiesgos['promedio'] + $conceptoRiesgos['desviacion'];
            } else {
                $conceptosRiesgos[$key]['riesgo'][1] = $conceptoRiesgos['promedio'] + $conceptoRiesgos['desviacion'];
                $conceptosRiesgos[$key]['riesgo'][2] = $conceptoRiesgos['promedio'] + ($conceptoRiesgos['desviacion'] / 2);
                $conceptosRiesgos[$key]['riesgo'][3] = $conceptoRiesgos['promedio'];
                $conceptosRiesgos[$key]['riesgo'][4] = $conceptoRiesgos['promedio'] - ($conceptoRiesgos['desviacion'] / 2);
                $conceptosRiesgos[$key]['riesgo'][5] = $conceptoRiesgos['promedio'] - $conceptoRiesgos['desviacion'];
            }
        }

        $deudaForCuantitive = $activoTotal / ($pasivoNoCirculante + $provisionesLargoPlazo + $pasivoCirculante);

        $arrScoreCuantitativo = [
            'capitalTrabajo'       => self::getMinRiskScore('capitalTrabajo', $capitalTrabajo, $conceptosRiesgos['capitalTrabajo']['riesgo']),
            'razonCirculante'      => self::getMinRiskScore('razonCirculante', $liquidezOrigen, $conceptosRiesgos['razonCirculante']['riesgo']),
            'pruebaAcida'          => self::getMinRiskScore('pruebaAcida', $disponible, $conceptosRiesgos['pruebaAcida']['riesgo']),
            'tesoreria'            => self::getMinRiskScore('tesoreria', $tesoreria, $conceptosRiesgos['tesoreria']['riesgo']),
            'rotacionActivo'       => self::getMinRiskScore('rotacionActivo', $rotacionActivo, $conceptosRiesgos['rotacionActivo']['riesgo']),
            'periodoPromedioCobro' => self::getMinRiskScore('periodoPromedioCobro', $cuentasPorCobrar, $conceptosRiesgos['periodoPromedioCobro']['riesgo']),
            'periodoPromedioPago'  => self::getMinRiskScore('periodoPromedioPago', $cuentasPorPagar, $conceptosRiesgos['periodoPromedioPago']['riesgo']),
            'roa'                  => self::getMinRiskScore('roa', $roe, $conceptosRiesgos['roa']['riesgo']),
            'margenNeto'           => self::getMinRiskScore('margenNeto', $margenNeto, $conceptosRiesgos['margenNeto']['riesgo']),
            'deuda'                => self::getMinRiskScore('deuda', $deudaForCuantitive, $conceptosRiesgos['deuda']['riesgo']),
        ];

        $scoreCuantitativo = 0;
        foreach ($arrScoreCuantitativo as $key => $itemCuantitativo) {
            $scoreCuantitativo += $itemCuantitativo['ponderacion'];
        }

        return [
            'rentabilidad'      => min($rentabilidad, 10),
            'liquidez'          => min($liquidez, 10),
            'solvencia'         => min($solvencia, 10),
            'actividad'         => min($actividad, 10),
            'evolucion'         => min($evolucion, 10),
            'costoFinanciero'   => min($costoFinanciero, 10),
            'capitalTrabajo'    => $capitalTrabajo,
            'puntuacion'        => $puntuacion,
            'rating'            => $rating,
            'descripcion'       => $descripcion,
            'scoreCuantitativo' => $scoreCuantitativo
        ];
    }

    public static function calculateQualitativeScore( Client $client )
    {
        $ponderaciones = self::PONDERACION;

        $requisitos = [
            "antiguedadEmpresa",
            "reconocimientoMercado",
            "informeComercial",
            "infraestructura",
            "problemasLegales",
            "calidadCartera",
            "referenciasBancarias",
            "referenciasComerciales",
            "importanciaMop",
            "perteneceHolding"
        ];

        $scoreCualitativo = array_reduce($requisitos, function ($carry, $requisito) use ($client, $ponderaciones) {
            if (isset($client->$requisito)) {
                $valorRequisito = $client->$requisito;
                $puntaje = $ponderaciones['cualitativas'][$valorRequisito] ?? 0;
                $carry[$requisito] = $puntaje;
            }
            return $carry;
        }, []);

        $puntajeTotal = array_sum($scoreCualitativo);

        return $puntajeTotal;
    }


}

?>

