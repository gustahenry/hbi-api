<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Cache\CacheInterface;
use CodeIgniter\Validation\ValidationInterface;

class Contacts extends BaseController
{
    protected $db;
    protected $cache;
    protected $validation;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->cache = \Config\Services::cache();
        $this->validation = \Config\Services::validation();
    }

    public function index()
    {
        $cacheKey = 'contacts_list';

        if ($this->cache->get($cacheKey)) {
            $contacts = $this->cache->get($cacheKey);
        } else {
            try {
                $builder = $this->db->table('contacts');
                $builder->select('contacts.id as contact_id, contacts.name, contacts.description, 
                                  address.zip_code, address.country, address.state, address.street_address, address.address_number, address.city, address.address_line, address.neighborhood,
                                  phone.phone as phone_number,
                                  email.email as email_address');
                $builder->join('address', 'contacts.id = address.id_contact', 'left');
                $builder->join('phone', 'contacts.id = phone.id_contact', 'left');
                $builder->join('email', 'contacts.id = email.id_contact', 'left');
                $query = $builder->get();
        
                $results = $query->getResultArray();
        
                $contacts = [];
        
                foreach ($results as $row) {
                    $contactId = $row['contact_id'];
        
                    if (!isset($contacts[$contactId])) {
                        $contacts[$contactId] = [
                            'name' => esc($row['name']),
                            'description' => esc($row['description']),
                            'address' => null,
                            'phone' => null,
                            'email' => null,
                        ];
                    }
        
                    if ($row['zip_code']) {
                        $contacts[$contactId]['address'] = [
                            'zip_code' => esc($row['zip_code']),
                            'country' => esc($row['country']),
                            'state' => esc($row['state']),
                            'street_address' => esc($row['street_address']),
                            'address_number' => esc($row['address_number']),
                            'city' => esc($row['city']),
                            'address_line' => esc($row['address_line']),
                            'neighborhood' => esc($row['neighborhood']),
                        ];
                    }
        
                    if ($row['phone_number']) {
                        $contacts[$contactId]['phone'] = [
                            'phone' => esc($row['phone_number']),
                        ];
                    }
        
                    if ($row['email_address']) {
                        $contacts[$contactId]['email'] = [
                            'email' => esc($row['email_address']),
                        ];
                    }
                }
                
                $this->cache->save($cacheKey, $contacts, 300);
                
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
            'data' => array_values($contacts)
        ]);
    }

    public function create()
    {
        $request = $this->request->getJSON(true);

        $request['address']['zip_code'] = $this->sanitizeZipCode($request['address']['zip_code']);
        $request['phone']['phone'] = $this->sanitizePhoneNumber($request['phone']['phone']);

        log_message('debug', 'Comprimento do zip_code: ' . strlen($request['address']['zip_code']));
        log_message('debug', 'Comprimento do phone: ' . strlen($request['phone']['phone']));

        $validationRules = [
            'name' => 'required|string|max_length[255]',
            'description' => 'required|string|max_length[255]',
            'address.zip_code' => 'required|string|max_length[9]',
            'address.country' => 'required|string|max_length[100]',
            'address.state' => 'required|string|max_length[100]',
            'address.street_address' => 'required|string|max_length[255]',
            'address.address_number' => 'required|string|max_length[10]',
            'address.city' => 'required|string|max_length[100]',
            'address.address_line' => 'required|string|max_length[255]',
            'address.neighborhood' => 'required|string|max_length[100]',
            'phone.phone' => 'required|string|max_length[14]',
            'email.email' => 'required|string|valid_email|max_length[255]',
        ];

        if (!$this->validate($validationRules)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => $this->validator->getErrors()
            ]);
        }

        try {
            $contactData = [
                'name' => esc($request['name']),
                'description' => esc($request['description']),
            ];

            $this->db->transBegin();

            try {
                $this->db->table('contacts')->insert($contactData);
                $contactId = $this->db->insertID();

                $currentDateTime = date('Y-m-d H:i:s');

                if (isset($request['address'])) {
                    $addressData = array_merge(
                        $request['address'], 
                        ['id_contact' => $contactId, 'created_at' => $currentDateTime]
                    );
                    $this->db->table('address')->insert($addressData);
                }

                if (isset($request['phone'])) {
                    $phoneData = array_merge(
                        $request['phone'], 
                        ['id_contact' => $contactId, 'created_at' => $currentDateTime]
                    );
                    $this->db->table('phone')->insert($phoneData);
                }

                if (isset($request['email'])) {
                    $emailData = array_merge(
                        $request['email'], 
                        ['id_contact' => $contactId, 'created_at' => $currentDateTime]
                    );
                    $this->db->table('email')->insert($emailData);
                }

                $this->db->transCommit();
                
                $this->cache->delete('contacts_list');
                
                return $this->response->setStatusCode(201)->setJSON([
                    'status' => 'success',
                    'message' => 'Contato criado com sucesso.',
                    'contact_id' => $contactId
                ]);
            } catch (\Exception $e) {
                $this->db->transRollback();
                return $this->response->setStatusCode(500)->setJSON([
                    'status' => 'error',
                    'message' => 'Erro ao criar contato: ' . $e->getMessage()
                ]);
            }
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

        $request['address']['zip_code'] = $this->sanitizeZipCode($request['address']['zip_code']);
        $request['phone']['phone'] = $this->sanitizePhoneNumber($request['phone']['phone']);

        $validationRules = [
            'name' => 'required|string|max_length[255]',
            'description' => 'required|string|max_length[255]',
            'address.zip_code' => 'required|string|max_length[9]',
            'address.country' => 'required|string|max_length[100]',
            'address.state' => 'required|string|max_length[100]',
            'address.street_address' => 'required|string|max_length[255]',
            'address.address_number' => 'required|string|max_length[10]',
            'address.city' => 'required|string|max_length[100]',
            'address.address_line' => 'required|string|max_length[255]',
            'address.neighborhood' => 'required|string|max_length[100]',
            'phone.phone' => 'required|string|max_length[14]',
            'email.email' => 'required|string|valid_email|max_length[255]',
        ];

        if (!$this->validate($validationRules)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => $this->validator->getErrors()
            ]);
        }

        try {
            $this->db->transBegin();

            try {
                $contact = $this->db->table('contacts')->where('id', $id)->get()->getRow();
                if (!$contact) {
                    return $this->response->setStatusCode(404)
                                        ->setJSON(['status' => 'error', 'message' => 'Contato não encontrado']);
                }

                $contactData = [
                    'name' => esc($request['name']),
                    'description' => esc($request['description']),
                ];
                $this->db->table('contacts')->where('id', $id)->update($contactData);

                if (isset($request['address'])) {
                    $addressData = array_merge(
                        $request['address'], 
                        ['updated_at' => date('Y-m-d H:i:s')]
                    );
                    $this->db->table('address')->where('id_contact', $id)->update($addressData);
                }

                if (isset($request['phone'])) {
                    $phoneData = array_merge(
                        $request['phone'], 
                        ['updated_at' => date('Y-m-d H:i:s')]
                    );
                    $this->db->table('phone')->where('id_contact', $id)->update($phoneData);
                }

                if (isset($request['email'])) {
                    $emailData = array_merge(
                        $request['email'], 
                        ['updated_at' => date('Y-m-d H:i:s')]
                    );
                    $this->db->table('email')->where('id_contact', $id)->update($emailData);
                }

                $this->db->transCommit();
                
                $this->cache->delete('contacts_list');

                return $this->response->setStatusCode(200)->setJSON([
                    'status' => 'success',
                    'message' => 'Contato atualizado com sucesso.'
                ]);
            } catch (\Exception $e) {
                $this->db->transRollback();
                return $this->response->setStatusCode(500)->setJSON([
                    'status' => 'error',
                    'message' => 'Erro ao atualizar contato: ' . $e->getMessage()
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Houve um erro ao processar sua solicitação: ' . $e->getMessage()
            ]);
        }
    }

    public function delete($id)
    {
        try {
            $this->db->transBegin();

            try {
                $contact = $this->db->table('contacts')->where('id', $id)->get()->getRow();
                if (!$contact) {
                    return $this->response->setStatusCode(404)
                                        ->setJSON(['status' => 'error', 'message' => 'Contato não encontrado']);
                }

                $this->db->table('contacts')->where('id', $id)->delete();
                $this->db->table('address')->where('id_contact', $id)->delete();
                $this->db->table('phone')->where('id_contact', $id)->delete();
                $this->db->table('email')->where('id_contact', $id)->delete();

                $this->db->transCommit();
                
                $this->cache->delete('contacts_list');

                return $this->response->setStatusCode(200)->setJSON([
                    'status' => 'success',
                    'message' => 'Contato excluído com sucesso.'
                ]);
            } catch (\Exception $e) {
                $this->db->transRollback();
                return $this->response->setStatusCode(500)->setJSON([
                    'status' => 'error',
                    'message' => 'Erro ao excluir contato: ' . $e->getMessage()
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Houve um erro ao processar sua solicitação: ' . $e->getMessage()
            ]);
        }
    }

    private function sanitizeZipCode($zipCode)
    {
        return substr(preg_replace('/[^0-9]/', '', $zipCode), 0, 8);
    }

    private function sanitizePhoneNumber($phoneNumber)
    {
        return substr(preg_replace('/[^0-9]/', '', $phoneNumber), 0, 11);
    }
}
