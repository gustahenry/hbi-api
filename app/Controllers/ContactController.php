<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\CacheService;
use App\Services\ContactService;
use App\Services\ContactFormatterService;
use App\Services\ValidationService;
use App\Services\AddressService;
use App\Helpers\SanitizationHelper;

class ContactController extends BaseController
{
    protected $cacheService;
    protected $contactService;
    protected $contactFormatterService;
    protected $validationService;
    protected $addressService;
    protected $sanitizationHelper;

    public function __construct()
    {
        $this->cacheService = new CacheService();
        $this->contactService = new ContactService();
        $this->contactFormatterService = new ContactFormatterService();
        $this->validationService = new ValidationService();
        $this->addressService = new AddressService();
        $this->sanitizationHelper = new SanitizationHelper();
    }

    public function index()
    {
        $cacheKey = 'contacts_list';

        $contacts = $this->cacheService->getCache($cacheKey);

        if (!$contacts) {
            try {
                $results = $this->contactService->getContacts();
                $contacts = $this->contactFormatterService->formatContacts($results);
                $this->cacheService->saveCache($cacheKey, $contacts, 300);
            } catch (\Exception $e) {
                log_message('error', $e->getMessage());
                return $this->response->setStatusCode(500)->setJSON([
                    'status' => 'error',
                    'message' => 'Houve um erro ao processar sua solicitação.'
                ]);
            }
        }

        return $this->response->setStatusCode(200)->setJSON([
            'status' => 'success',
            'data' => $contacts
        ]);
    }

    public function create()
    {
        $request = $this->request->getJSON(true);

        $request['address']['zip_code'] = $this->sanitizationHelper->sanitizeZipCode($request['address']['zip_code']);
        $request['phone']['phone'] = $this->sanitizationHelper->sanitizePhoneNumber($request['phone']['phone']);

        if (!$this->validationService->validateContact($request)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => $this->validationService->getErrors()
            ]);
        }

        $addressDetails = $this->addressService->fetchAddressDetails($request['address']['zip_code']); // Use AddressService

        if ($addressDetails) {
            $request['address'] = array_merge($request['address'], $addressDetails);
        }

        try {
            $contactId = $this->contactService->createContact($request);
            
            $this->cacheService->invalidateCache('contacts_list');
            
            return $this->response->setStatusCode(201)->setJSON([
                'status' => 'success',
                'message' => 'Contato criado com sucesso.',
                'contact_id' => $contactId
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Houve um erro ao processar sua solicitação: ' . $e->getMessage()
            ]);
        }
    }

    public function update($id)
    {
        $request = $this->request->getJSON(true);

        if (isset($request['address']['zip_code'])) {
            $request['address']['zip_code'] = $this->sanitizationHelper->sanitizeZipCode($request['address']['zip_code']);
        }
        
        if (isset($request['phone']['phone'])) {
            $request['phone']['phone'] = $this->sanitizationHelper->sanitizePhoneNumber($request['phone']['phone']);
        }

        if (!$this->validationService->validateContact($request)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => $this->validationService->getErrors()
            ]);
        }

        $addressDetails = $this->addressService->fetchAddressDetails($request['address']['zip_code']); // Use AddressService

        if ($addressDetails) {
            $request['address'] = array_merge($request['address'], $addressDetails);
        }

        try {
            $this->contactService->updateContact($id, $request);

            $this->cacheService->invalidateCache('contacts_list');

            return $this->response->setStatusCode(200)->setJSON([
                'status' => 'success',
                'message' => 'Contato atualizado com sucesso.'
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Erro ao atualizar contato: ' . $e->getMessage()
            ]);
        }
    }

    public function delete($id)
    {
        try {
            $this->contactService->deleteContact($id);

            return $this->response->setStatusCode(200)->setJSON([
                'status' => 'success',
                'message' => 'Contato excluído com sucesso.'
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Erro ao excluir o contato: ' . $e->getMessage()
            ]);
        }
    }
}


