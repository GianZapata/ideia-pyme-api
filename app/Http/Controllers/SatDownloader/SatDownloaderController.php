<?php

namespace App\Http\Controllers\SatDownloader;

use App\Models\SatReport;
use DateTimeImmutable;
use Illuminate\Support\Facades\Log;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use PhpCfdi\CfdiSatScraper\Exceptions\LoginException;
use PhpCfdi\CfdiSatScraper\SatHttpGateway;
use PhpCfdi\CfdiSatScraper\SatScraper;
use PhpCfdi\CfdiSatScraper\Sessions\Ciec\CiecSessionManager;
use PhpCfdi\ImageCaptchaResolver\Resolvers\AntiCaptchaResolver;
use PhpCfdi\CfdiSatScraper\QueryByFilters;
use PhpCfdi\CfdiSatScraper\Filters\DownloadType;
use PhpCfdi\CfdiSatScraper\Filters\Options\StatesVoucherOption;
use PhpCfdi\CfdiSatScraper\ResourceType;

class SatDownloaderController
{

    public static function executeQuery($rfc, $password, $year, $downloadType, $reportId)
    {
        $initial = $year . '-01-01';
        $final = $year . '-12-31';

        try {
            $captchaResolver = AntiCaptchaResolver::create('fef83c227ebc3e09750579460a64768e');
            $insecureClient = new Client([
                RequestOptions::VERIFY => false,
                'curl' => [CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1']
            ]);
            $gateway = new SatHttpGateway($insecureClient);
            $satScraper = new SatScraper(CiecSessionManager::create($rfc, $password, $captchaResolver), $gateway);

            if ($downloadType == 'emitidos') {
                $query = new QueryByFilters(new DateTimeImmutable($initial), new DateTimeImmutable($final), DownloadType::emitidos());
            } else {
                $query = new QueryByFilters(new DateTimeImmutable($initial), new DateTimeImmutable($final), DownloadType::recibidos());
            }

            $query->setStateVoucher(StatesVoucherOption::vigentes());
            $list = $satScraper->listByPeriod($query);

            $satScraper->resourceDownloader(ResourceType::xml(), $list, 50)
                ->saveTo("storage/app/public/xml/{$rfc}/{$downloadType}/{$year}", true, 0777);

            Log::info('Se descargaron los archivos correctamente');
            Log::info('Se descargaron ' . count($list) . ' archivos');

            // Sumar 1 a total_tasks_completed en report
            // $report = SatReport::where('id', $reportId)->first();
            // $report->total_tasks_completed = intval($report->total_tasks_completed) + 1;
            // $report->save();
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
            return false;
        }

        return true;
    }

    public static function downloadYear($rfc, $password, $year)
    {
        $initial = $year . '-01-01';
        $final = $year . '-12-31';

        $a = 1;
        do {
            Log::info("-------------{$year}-----------------");
            Log::info('Intento de inicio de sesión: ' . $a);
            Log::info('------------------------------');
            echo("-------------{$year}-----------------");

            $captchaResolver = AntiCaptchaResolver::create('fef83c227ebc3e09750579460a64768e');
            $insecureClient = new Client([
                RequestOptions::VERIFY => false,
                'curl' => [CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1']
            ]);
            $gateway = new SatHttpGateway($insecureClient);
            $satScraper = new SatScraper(CiecSessionManager::create($rfc, $password, $captchaResolver), $gateway);
            try {
                $checkLogin = $satScraper->confirmSessionIsAlive();
                if ($checkLogin) {
                    $a = 6;
                }
            } catch (LoginException $th) {
                Log::info('------------------------------');
                Log::info('Error al iniciar sesión...');
                Log::info($th->getMessage());
                $a++;
                Log::info('------------------------------');
            }
        } while ($a <= 5);

        // Emitidos vigentes y cancelados
        try {
            Log::info('------------------------------');
            Log::info('Solicitando Emitidos vigentes...');
            echo("-------------{$year}----Emitidos-------------");
            Log::info('------------------------------');
            $emitidos = new QueryByFilters(new DateTimeImmutable($initial), new DateTimeImmutable($final), DownloadType::emitidos());
            $emitidosVigentes = $emitidos->setStateVoucher(StatesVoucherOption::vigentes());
            $listEmitidosVigentes = $satScraper->listByPeriod($emitidosVigentes);
            $satScraper->resourceDownloader(ResourceType::xml(), $listEmitidosVigentes, 50)->saveTo("storage/app/public/xml/{$rfc}/emitidos/{$year}", true, 0777);
            Log::info('Se descargaron los emitidosVigentes correctamente');
            Log::info('Se descargaron ' . count($listEmitidosVigentes) . ' archivos emitidosVigentes');
            Log::info('------------------------------');
        } catch (\Throwable $th) {
            Log::info("Error en emitidos vigentes {$th->getMessage()}");
            Log::info('------------------------------');
        }

        try {
            Log::info('------------------------------');
            Log::info('Solicitando Emitidos Cancelados...');
            Log::info('------------------------------');
            $emitidos = new QueryByFilters(new DateTimeImmutable($initial), new DateTimeImmutable($final), DownloadType::emitidos());
            $emitidosCancelados = $emitidos->setStateVoucher(StatesVoucherOption::cancelados());
            $listEmitidosCancelados = $satScraper->listByPeriod($emitidosCancelados);
            $satScraper->resourceDownloader(ResourceType::xml(), $listEmitidosCancelados, 50)->saveTo("storage/app/public/xml/{$rfc}/emitidos/{$year}/cancelados", true, 0777);
            Log::info('Se descargaron los emitidosCancelados correctamente');
            Log::info('Se descargaron ' . count($listEmitidosCancelados) . ' archivos emitidosCancelados');
            Log::info('------------------------------');
        } catch (\Throwable $th) {
            Log::info("Error en emitidos Cancelados {$th->getMessage()}");
            Log::info('------------------------------');
        }

        // Recibidos vigentes y cancelados
        try {
            Log::info('------------------------------');
            Log::info('Solicitando Recibidos vigentes...');
            echo("-------------{$year}----Recibidos-------------");

            Log::info('------------------------------');
            $recibidos = new QueryByFilters(new DateTimeImmutable($initial), new DateTimeImmutable($final), DownloadType::recibidos());
            $recibidosVigentes = $recibidos->setStateVoucher(StatesVoucherOption::vigentes());
            $listRecibidosVigentes = $satScraper->listByPeriod($recibidosVigentes);
            $satScraper->resourceDownloader(ResourceType::xml(), $listRecibidosVigentes, 50)->saveTo("storage/app/public/xml/{$rfc}/recibidos/{$year}", true, 0777);
            Log::info('Se descargaron los recibidosVigentes correctamente');
            Log::info('Se descargaron ' . count($listRecibidosVigentes) . ' archivos recibidosVigentes');
            Log::info('------------------------------');
        } catch (\Throwable $th) {
            Log::info("Error en recibidos vigentes {$th->getMessage()}");
            Log::info('------------------------------');
        }

        try {
            Log::info('------------------------------');
            Log::info('Solicitando Recibidos Cancelados...');
            Log::info('------------------------------');
            $recibidos = new QueryByFilters(new DateTimeImmutable($initial), new DateTimeImmutable($final), DownloadType::recibidos());
            $recibidosCancelados = $recibidos->setStateVoucher(StatesVoucherOption::cancelados());
            $listRecibidosCancelados = $satScraper->listByPeriod($recibidosCancelados);
            $satScraper->resourceDownloader(ResourceType::xml(), $listRecibidosCancelados, 50)->saveTo("storage/app/public/xml/{$rfc}/recibidos/{$year}/cancelados", true, 0777);
            Log::info('Se descargaron los recibidosCancelados correctamente');
            Log::info('Se descargaron ' . count($listRecibidosCancelados) . ' archivos recibidosCancelados');
            Log::info('------------------------------');
        } catch (\Throwable $th) {
            Log::info("Error en recibidos Cancelados {$th->getMessage()}");
            Log::info('------------------------------');
        }

        echo("-------------{$year}----Finalizado-------------");
        return true;
    }
}
