<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class FacturaLOPlus
{
    private Client $http;
    private string $apikey;

    public function __construct()
    {
        $this->http = new Client([
            'base_uri' => rtrim(config('services.facturaloplus.base_url'), '/') . '/',
            'timeout'  => 30,
        ]);
        $this->apikey = config('services.facturaloplus.apikey');
    }

    private function post(string $endpoint, array $json): array
    {
        try {
            $resp = $this->http->post($endpoint, ['json' => $json]);
            return [
                'status' => $resp->getStatusCode(),
                'json'   => json_decode((string) $resp->getBody(), true),
            ];
        } catch (RequestException $e) {
            $code = $e->getResponse()?->getStatusCode() ?? 0;
            $body = (string) ($e->getResponse()?->getBody() ?? '');
            return ['status' => $code, 'json' => $body ? json_decode($body, true) : ['message' => $e->getMessage()]];
        }
    }

    /** TIMBRADO: JSON -> XML timbrado */
    public function timbrarJSON(string $jsonCfdi, string $cerPem, string $keyPem): array
    {
        $payload = [
            'apikey' => $this->apikey,
            // jsonB64 debe ir en BASE64 (ver guía de "Utilerías")
            'jsonB64' => base64_encode($jsonCfdi),
            'cerPEM' => $cerPem,
            'keyPEM' => $keyPem,
        ];
        return $this->post('timbrarJSON', $payload);
    }

    /** TIMBRADO: JSON -> XML + datos impresos (UUID, QR, sellos, etc.) */
    public function timbrarJSON3(string $jsonCfdi, string $cerPem, string $keyPem): array
    {
        $payload = [
            'apikey' => $this->apikey,
            'jsonB64' => base64_encode($jsonCfdi),
            'cerPEM' => $cerPem,
            'keyPEM' => $keyPem,
        ];
        return $this->post('timbrarJSON3', $payload);
    }

    /** CANCELAR (vía CSD suelto en Base64) */
    public function cancelar2(array $args): array
    {
        // $args = ['keyCSD','cerCSD','passCSD','uuid','rfcEmisor','rfcReceptor','total','motivo','folioSustitucion']
        $payload = array_merge(['apikey' => $this->apikey], $args);
        return $this->post('cancelar2', $payload);
    }

    /** CONSULTAR ESTADO EN SAT */
    public function consultarEstadoSAT(string $uuid, string $rfcEmisor, string $rfcReceptor, string $total): array
    {
        $payload = [
            'apikey'      => $this->apikey,
            'uuid'        => $uuid,
            'rfcEmisor'   => $rfcEmisor,
            'rfcReceptor' => $rfcReceptor,
            'total'       => $total,
        ];
        return $this->post('consultarEstadoSAT', $payload);
    }

    /** VALIDAR XML CFDI */
    public function validar(string $xmlCfdi): array
    {
        $payload = [
            'apikey'  => $this->apikey,
            'xmlCFDI' => $xmlCfdi,
        ];
        return $this->post('validar', $payload);
    }
}
