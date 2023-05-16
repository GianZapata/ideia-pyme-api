<?php

namespace App\Services;

use App\Models\Client;

class FinancialHealthService
{
    protected $PONDERACION = [
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

    public function calculate( Client $client ) {
        $factores = [
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
        $prestamosActuales = (isset($client->prestamosActuales)) ? $client->prestamosActuales : 600;

        return [
            'ventasAnioAnterior' => $ventasAnioAnterior,
            'resultadoDeExplotacion' => $resultadoDeExplotacion,
            'resultadoFinanciero' => $resultadoFinanciero,
            'resultadoAntesImpuestos' => $resultadoAntesImpuestos,
            'deudoresComerciales' => $deudoresComerciales,
            'inversionesFinancierasCortoPlazo' => $inversionesFinancierasCortoPlazo,
            'efectivoLiquidez' => $efectivoLiquidez,
            'activoTotal' => $activoTotal,
            'pasivoNoCirculante' => $pasivoNoCirculante,
            'provisionesLargoPlazo' => $provisionesLargoPlazo,
            'pasivoCirculante' => $pasivoCirculante,
            'prestamosActuales' => $prestamosActuales,
        ];
    }


}

?>

