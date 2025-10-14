<?php

namespace App\Http\Controllers;

use App\Services\FacturaLOPlus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class CfdiController extends Controller
{
    public function timbrarDemo(FacturaLOPlus $api)
    {
        // 1) Arma el JSON del CFDI 4.0 (mínimo de prueba, 1 concepto)
        //    Ajusta con tus catálogos: UsoCFDI, Regímenes, c_ClaveProdServ, c_ClaveUnidad, ObjetoImp, etc.
        $cfdi = [
            "Comprobante" => [
                "Version" => "4.0",
                "Serie" => "A",
                "Folio" => "1",
                "Fecha" => now()->format('Y-m-d\TH:i:s'),
                "SubTotal" => "100.00",
                "Moneda" => "MXN",
                "Total" => "116.00",
                "TipoDeComprobante" => "I",
                "Exportacion" => "01",
                "LugarExpedicion" => "83000",
                "Emisor" => [
                    "Rfc" => "EKU9003173C9",
                    "Nombre" => "ESCUELA KEMPER URGATE",
                    "RegimenFiscal" => "601"
                ],
                "Receptor" => [
                    "Rfc" => "XAXX010101000",
                    "Nombre" => "PUBLICO EN GENERAL",
                    "DomicilioFiscalReceptor" => "99999",
                    "RegimenFiscalReceptor" => "616",
                    "UsoCFDI" => "S01"
                ],
                "Conceptos" => [
                    [
                        "ClaveProdServ" => "01010101",
                        "Cantidad" => "1",
                        "ClaveUnidad" => "H87",
                        "Descripcion" => "PRODUCTO DEMO",
                        "ValorUnitario" => "100.00",
                        "Importe" => "100.00",
                        "ObjetoImp" => "02",
                        "Impuestos" => [
                            "Traslados" => [
                                [
                                    "Base" => "100.00",
                                    "Impuesto" => "002",
                                    "TipoFactor" => "Tasa",
                                    "TasaOCuota" => "0.160000",
                                    "Importe" => "16.00"
                                ]
                            ]
                        ]
                    ]
                ],
                "Impuestos" => [
                    "Traslados" => [
                        [
                            "Base" => "100.00",
                            "Impuesto" => "002",
                            "TipoFactor" => "Tasa",
                            "TasaOCuota" => "0.160000",
                            "Importe" => "16.00"
                        ]
                    ]
                ],
            ]
        ];

        $json = json_encode($cfdi, JSON_UNESCAPED_UNICODE);

        // 2) Carga los PEM del CSD (contenido en texto, NO archivos binarios)
        $cerPem = File::get(env('CSD_CER_PEM'));
        $keyPem = File::get(env('CSD_KEY_PEM'));

        // 3) Timbrar y devolver respuesta
        $res = $api->timbrarJSON3($json, $cerPem, $keyPem);

        if (($res['json']['code'] ?? 0) !== 200) {
            return response()->json(['error' => $res['json']], 400);
        }

        // data trae: XML, UUID, CadenaOriginal, CodigoQR, etc.
        // (timbrar3/timbrarJSON3 devuelven datos listos para la representación impresa)
        return response()->json($res['json']['data']);
    }

    public function cancelarDemo(FacturaLOPlus $api, Request $r)
    {
        // Debes mandar el TOTAL EXACTO del CFDI emitido y los RFCs tal cual
        $args = [
            'keyCSD'         => base64_encode(file_get_contents(storage_path('csd/csd.key'))),
            'cerCSD'         => base64_encode(file_get_contents(storage_path('csd/csd.cer'))),
            'passCSD'        => 'PASSWORD_DEL_KEY',
            'uuid'           => $r->input('uuid'),
            'rfcEmisor'      => 'EKU9003173C9',
            'rfcReceptor'    => 'XAXX010101000',
            'total'          => '116.00', // EXACTO como en el CFDI
            'motivo'         => '02',     // 01-04
            'folioSustitucion' => '',     // si aplica cuando motivo=01
        ];
        $res = $api->cancelar2($args);
        return response()->json($res);
    }

    public function estadoSat(FacturaLOPlus $api, Request $r)
    {
        $res = $api->consultarEstadoSAT(
            $r->input('uuid'),
            'EKU9003173C9',
            'XAXX010101000',
            '116.00'
        );
        return response()->json($res);
    }
}
