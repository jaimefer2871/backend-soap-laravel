<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Wallet;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class WalletService
{
    /**
     * Reload funds
     *
     * @param string $document
     * @param string $phone
     * @param float $amount
     * @return array
     */
    public function reload(string $document, string $phone, float $amount)
    {
        $wasReload = false;
        $output = [
            'success' => true,
            'code' => 200,
            'message' => 'OK',
            'data' => []
        ];

        $input = [
            'document'  => $document,
            'funds'     => $amount,
            'phone'     => $phone
        ];

        try {
            Validator::make($input, [
                'document'  => 'required|alpha_num',
                'funds'      => 'required|numeric',
                'phone'     => 'required|string'
            ], [
                'required' => 'The :attribute field is required'
            ])->validate();

            $client = Client::where('phone', $phone)->where('document', $document)->first();

            if (!empty($client)) {
                $WalletInstance = Wallet::where('client_id', $client->id)->first();

                if (!empty($WalletInstance)) {
                    $WalletInstance->funds += floatval($amount);

                    if ($WalletInstance->save()) {
                        $wasReload = true;
                    }
                } else {
                    $input['client_id'] = $client->id;
                    $WalletInstance = new Wallet($input);
                    $wasReload = (bool)$WalletInstance->save();
                }
            }

            $output['data'] = [
                'wasReload' => $wasReload,
                'funds' => $amount
            ];
        } catch (ValidationException $validation) {
            $output['success'] = false;
            $output['code'] = $validation->status;
            $output['message'] = $validation->getMessage();
            $output['errors_details'] = $validation->errors();
        } catch (\Throwable $th) {
            $output['success'] = false;
            $output['code'] = 500;
            $output['message'] = $th->getMessage();
        }

        return json_decode(json_encode($output), FALSE);
    }

    /**
     * get funds client
     *
     * @param string $document
     * @param string $phone
     * @return array
     */
    public function getFunds(string $document, string $phone)
    {
        $output = [
            'success' => true,
            'code' => 200,
            'message' => 'OK',
            'data' => [
                'funds_available' => 0
            ]
        ];

        $input = [
            'document'  => $document,
            'phone'     => $phone
        ];

        try {
            Validator::make($input, [
                'document'  => 'required|alpha_num',
                'phone'     => 'required|string'
            ], [
                'required' => 'The :attribute field is required'
            ])->validate();

            $client = Client::where('phone', $phone)->where('document', $document)->first();

            if (!empty($client)) {
                $WalletInstance = Wallet::where('client_id', $client->id)->first();

                if (!empty($WalletInstance)) {
                    $output['data'] = [
                        'funds_available' => $WalletInstance->funds
                    ];
                }
            }
        } catch (ValidationException $validation) {
            $output['success'] = false;
            $output['code'] = $validation->status;
            $output['message'] = $validation->getMessage();
            $output['errors_details'] = $validation->errors();
        } catch (\Throwable $th) {
            $output['success'] = false;
            $output['code'] = 500;
            $output['message'] = $th->getMessage();
        }

        return json_decode(json_encode($output), FALSE);
    }
}
