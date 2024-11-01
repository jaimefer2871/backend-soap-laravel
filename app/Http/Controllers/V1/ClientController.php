<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Services\ClientService;
use App\Services\WalletService;
use App\Http\Controllers\Controller;
use Laminas\Soap\Server as SoapServer;
use Laminas\Soap\AutoDiscover as WsdlAutoDiscover;


class ClientController extends Controller
{
    public function wsdlAction(Request $request)
    {
        if (!$request->isMethod('get')) {
            return $this->prepareClientErrorResponse('GET');
        }

        $wsdl = new WsdlAutoDiscover();

        $wsdl->setUri(route('clients-soap-server'))
            ->setServiceName('Client');


        $wsdl->setClass(ClientService::class);


        return response()->make($wsdl->toXml())
            ->header('Content-Type', 'application/xml');
    }

    public function serverAction(Request $request)
    {
        if (!$request->isMethod('post')) {
            return $this->prepareClientErrorResponse('POST');
        }

        $context = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        $server = new SoapServer(
            null,
            [
                'stream_context' => $context,
                'actor' => route('clients-soap-server'),
                'soap_version' => SOAP_1_2,
                'uri' => route('clients-soap-wsdl')
            ]
        );

        $server->setReturnResponse(true);
        $server->setClass(ClientService::class);
        $soapResponse = $server->handle();

        return response()->make($soapResponse)->header('Content-Type', 'application/xml');
    }

    private function prepareClientErrorResponse($allowed)
    {
        return response()->make('Method not allowed', 405)->header('Allow', $allowed);
    }
}
