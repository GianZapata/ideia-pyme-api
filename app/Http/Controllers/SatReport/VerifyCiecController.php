<?php

namespace App\Http\Controllers\SatReport;

use App\Http\Controllers\Controller;
use App\Jobs\SatScraperJob;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use PhpCfdi\CfdiSatScraper\Exceptions\LoginException;
use PhpCfdi\CfdiSatScraper\SatHttpGateway;
use PhpCfdi\CfdiSatScraper\SatScraper;
use PhpCfdi\CfdiSatScraper\Sessions\Ciec\CiecSessionManager;
use PhpCfdi\ImageCaptchaResolver\Resolvers\AntiCaptchaResolver;


class VerifyCiecController extends Controller
{

    public $message;
    public $response;

    public function verify($rfc, $password, $reportId)
    {
        set_time_limit(300);
        $captchaResolver = AntiCaptchaResolver::create('fef83c227ebc3e09750579460a64768e');
        $insecureClient = new Client([
            RequestOptions::VERIFY => false,
            'curl' => [CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1']
        ]);
        $gateway = new SatHttpGateway($insecureClient);
        $satScraper = new SatScraper(CiecSessionManager::create($rfc, $password, $captchaResolver), $gateway);
        try {
            $satScraper->confirmSessionIsAlive();
            $this->message = 'Credenciales correctas';
            $this->response = 'LOGUEADO';

            $this->dispatchJobsByPeriods($rfc, $password, $reportId);

        } catch (LoginException $th) {
            $this->message = 'Error al iniciar sesión con las credenciales';
            $this->response = $th->getMessage();
        }

        return $this;

    }


    public static function dispatchJobsByPeriods($rfc, $password, $reportId)
    {
        // for ($i=2020; $i < 2024; $i++) { 
        //     SatScraperJob::dispatch($rfc, $password, $i, 'emitidos', $reportId)->onConnection('database')->onQueue('sat-scraper');
        //     SatScraperJob::dispatch($rfc, $password, $i, 'recibidos', $reportId)->onConnection('database')->onQueue('sat-scraper');
        // }
        for ($i=2020; $i < 2024; $i++) { 
            SatScraperJob::dispatch($rfc, $password, $i, 'emitidos', $reportId)->onConnection('database')->onQueue('sat-scraper');
            // SatScraperJob::dispatch($rfc, $password, $i, 'recibidos', $reportId)->onConnection('database')->onQueue('sat-scraper');
        }

        return;
    }

}
