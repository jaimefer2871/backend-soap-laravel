<?php

namespace App\Services;

use App\Models\Client;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ClientService
{
    /**
     * Register Client
     *
     * @param string $document
     * @param string $name
     * @param string $email
     * @param string $phone
     * @return array
     */
    public function register(string $document, string $name, string $email, string $phone)
    {
        $output = [
            'success' => true,
            'code' => 200,
            'messager' => 'OK',
            'errors_details' => [],
            'data' => []
        ];

        $input = [
            'document'  => $document,
            'name'      => $name,
            'email'     => $email,
            'phone'     => $phone
        ];

        try {
            Validator::make($input, [
                'document'  => 'required|alpha_num|unique:clients',
                'name'      => 'required|string',
                'email'     => 'required|email|unique:clients',
                'phone'     => 'required|string|unique:clients'
            ],[
                'required' => 'The :attribute field is required'
            ])->validate();

            $ClientInstance = new Client($input);

            if ($ClientInstance->save()) {
                $output['data'] = $ClientInstance->toArray();
                $output['code'] = 201;
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

        return $output;
    }
}
