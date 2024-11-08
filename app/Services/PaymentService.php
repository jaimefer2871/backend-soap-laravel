<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Payment;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    /**
     * pay purchase
     *
     * @param string $document
     * @param string $phone
     * @param float $amount
     * @return array
     */
    public function pay(string $document, string $phone, float $amount)
    {
        $output = [
            'success' => true,
            'code' => 200,
            'message' => 'OK',
            'data' => []
        ];

        $input = [
            'document'  => $document,
            'amount'    => $amount,
            'phone'     => $phone
        ];

        $generateToken = function () {
            $token = random_int(100000, 999999);
            $tokenStr = str_pad($token, 6, '0', STR_PAD_LEFT);

            return $tokenStr;
        };

        try {
            Validator::make($input, [
                'document'  => 'required|alpha_num',
                'amount'    => 'required|numeric',
                'phone'     => 'required|string'
            ], [
                'required' => 'The :attribute field is required'
            ])->validate();

            $client = Client::where('phone', $phone)->where('document', $document)->first();

            if (!empty($client)) {
                $sessionID = Crypt::encrypt($input['document'] . '-' . date('Y-m-d'));

                $input['client_id'] = $client->id;

                $paymentAttributes = [
                    'amount'        => $input['amount'],
                    'client_id'     => $client->id,
                    'token'         => $generateToken(),
                    'session_id'    => $sessionID,
                    'confirmed'     => false
                ];

                $PaymentInstance = new Payment($paymentAttributes);

                $dataMail = [
                    'amount'        => $paymentAttributes['amount'],
                    'token'         => $paymentAttributes['token'],
                    'session_id'    => $paymentAttributes['session_id'],
                    'name'          => $client->name
                ];

                if ($PaymentInstance->save()) {
                    Mail::send('mail', $dataMail, function ($message) use ($client) {
                        $message->to($client->email, $client->name)->subject('Confirmacion de Pago');
                        $message->from('info@prueba.com', 'Backend');
                    });

                    $output['code'] = 201;
                    $output['message'] = 'Se ha registrado la solicitud de pago. Se ha enviado un correo con los datos para ser usado en la confirmacion de compra';
                    $output['data'] = [
                        'send' => true,
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
        } catch (\Exception $e) {
            $output['success'] = false;
            $output['code'] = 500;
            $output['message'] = $e->getMessage();
        }

        return json_decode(json_encode($output), FALSE);
    }

    /**
     * confirm purchase
     *
     * @param string $document
     * @param string $phone
     * @return array
     */
    public function confirm(string $sessionId, string $token)
    {
        $output = [
            'success' => true,
            'code' => 200,
            'message' => 'OK',
            'data' => []
        ];

        $input = [
            'session_id'  => $sessionId,
            'token'     => $token
        ];

        try {
            Validator::make($input, [
                'session_id'  => 'required|string',
                'token'     => 'required|string'
            ])->validate();

            $payment = Payment::where('session_id', $sessionId)
                ->where('token', $token)
                ->where('confirmed', false)
                ->first();

            if (!empty($payment)) {
                $payment->confirmed = true;
                $payment->date_confirmed = Carbon::now();

                if ($payment->save()) {
                    $wallet = Wallet::where('client_id', $payment->client_id)->first();

                    if (!empty($wallet)) {
                        $wallet->funds -= floatval($payment->amount);
                        $wallet->save();
                    }
                }
            } else {
                $output['success'] = false;
                $output['code'] = 422;
                $output['message'] = 'Payment could not be confirmed. Please verify the data provided';
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
