<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Cache\CacheInterface;

class Contacts extends BaseController
{
    protected $db;
    protected $cache;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->cache = \Config\Services::cache();
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
                            'name' => $row['name'],
                            'description' => $row['description'],
                            'address' => null,
                            'phone' => null,
                            'email' => null,
                        ];
                    }
        
                    if ($row['zip_code']) {
                        $contacts[$contactId]['address'] = [
                            'zip_code' => $row['zip_code'],
                            'country' => $row['country'],
                            'state' => $row['state'],
                            'street_address' => $row['street_address'],
                            'address_number' => $row['address_number'],
                            'city' => $row['city'],
                            'address_line' => $row['address_line'],
                            'neighborhood' => $row['neighborhood'],
                        ];
                    }
        
                    if ($row['phone_number']) {
                        $contacts[$contactId]['phone'] = [
                            'phone' => $row['phone_number'],
                        ];
                    }
        
                    if ($row['email_address']) {
                        $contacts[$contactId]['email'] = [
                            'email' => $row['email_address'],
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
        try {
            $request = $this->request->getJSON(true);

            if (!$request) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => 'Falha ao analisar o JSON. Verifique a formatação da solicitação.'
                ]);
            }

            $contactData = [
                'name' => $request['name'] ?? null,
                'description' => $request['description'] ?? null,
            ];

            $this->db->transBegin();

            try {
                $this->db->table('contacts')->insert($contactData);
                $contactId = $this->db->insertID();

                $currentDateTime = date('Y-m-d H:i:s');

                if (isset($request['address'])) {
                    $addressData = array_merge($request['address'], ['id_contact' => $contactId, 'created_at' => $currentDateTime]);
                    $this->db->table('address')->insert($addressData);
                }

                if (isset($request['phone'])) {
                    $phoneData = array_merge($request['phone'], ['id_contact' => $contactId, 'created_at' => $currentDateTime]);
                    $this->db->table('phone')->insert($phoneData);
                }

                if (isset($request['email'])) {
                    $emailData = array_merge($request['email'], ['id_contact' => $contactId, 'created_at' => $currentDateTime]);
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
        try {

            $request = $this->request->getJSON(true);

            $this->db->transBegin();

            try {

                $contact = $this->db->table('contacts')->where('id', $id)->get()->getRow();
                if (!$contact) {
                    return $this->response->setStatusCode(404)
                                        ->setJSON(['status' => 'error', 'message' => 'Contato não encontrado']);
                }

                $contactData = [
                    'name' => $request['name'] ?? null,
                    'description' => $request['description'] ?? null,
                ];
                $this->db->table('contacts')->where('id', $id)->update($contactData);

                if (isset($request['address'])) {
                    $addressData = array_merge($request['address'], ['id_contact' => $id]);
                    $this->db->table('address')->where('id_contact', $id)->update($addressData);
                }

                if (isset($request['phone'])) {
                    $phoneData = $request['phone'];
                    $this->db->table('phone')->where('id_contact', $id)->update($phoneData);
                }

                if (isset($request['email'])) {
                    $emailData = $request['email'];
                    $this->db->table('email')->where('id_contact', $id)->update($emailData);
                }

                $this->db->transCommit();

                $this->cache->delete('contacts_list');

                return $this->response->setStatusCode(200)
                      ->setJSON(['status' => 'success', 'message' => 'Contato atualizado com sucesso']);

            } catch (DatabaseException $e) {
                $this->db->transRollback();
                return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
            }
        }catch (\Exception $e) {
            $this->db->transRollback();
            return $this->response->setStatusCode(500)
                                  ->setJSON(['status' => 'error', 'message' => 'Erro ao atualizar contato: ' . $e->getMessage()]);
        }
    }

    public function delete($id)
    {
        $this->db->transBegin();

        try {

            $contact = $this->db->table('contacts')->where('id', $id)->get()->getRow();
            if (!$contact) {
                return $this->response->setStatusCode(404)
                                    ->setJSON(['status' => 'error', 'message' => 'Contato não encontrado']);
            }

            $this->db->table('address')->where('id_contact', $id)->delete();

            $this->db->table('phone')->where('id_contact', $id)->delete();

            $this->db->table('email')->where('id_contact', $id)->delete();

            $this->db->table('contacts')->where('id', $id)->delete();

            $this->db->transCommit();

            $this->cache->delete('contacts_list');

            return $this->response->setJSON(['status' => 'success', 'message' => 'Contato excluído com sucesso']);
        } catch (DatabaseException $e) {
            $this->db->transRollback();
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
