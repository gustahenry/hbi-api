<?php

namespace App\Services;

use Exception;

class AddressService
{
    public function fetchAddressDetails(string $zipCode)
    {
        $url = "https://viacep.com.br/ws/{$zipCode}/json/";

        try {
            $response = file_get_contents($url);
            if ($response === false) {
                throw new Exception("Erro ao fazer a requisição para a API ViaCEP.");
            }

            $data = json_decode($response, true);
            if (isset($data['erro']) && $data['erro']) {
                return null;
            }

            return $data;
        } catch (Exception $e) {
            log_message('error', 'Erro ao buscar endereço ViaCep: ' . $e->getMessage());
            return null;
        }
    }
}

