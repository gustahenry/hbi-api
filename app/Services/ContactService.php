<?php

namespace App\Services;

use CodeIgniter\Database\ConnectionInterface;
use Exception;

class ContactService
{
    protected $db;
    protected $addressService;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->addressService = new AddressService();
    }

    public function getContacts()
    {
        $builder = $this->db->table('contacts');
        $builder->select('contacts.id as contact_id, contacts.name, contacts.description, 
                          address.zip_code, address.country, address.state, address.street_address, address.address_number, address.city, address.address_line, address.neighborhood,
                          phone.phone as phone_number,
                          email.email as email_address');
        $builder->join('address', 'contacts.id = address.id_contact', 'left');
        $builder->join('phone', 'contacts.id = phone.id_contact', 'left');
        $builder->join('email', 'contacts.id = email.id_contact', 'left');
        $query = $builder->get();

        return $query->getResultArray();

        
    }

    public function createContact(array $data)
    {
        $this->db->transBegin();

        try {

            $contactData = [
                'name' => esc($data['name']),
                'description' => esc($data['description']),
            ];

            $this->db->table('contacts')->insert($contactData);
            $contactId = $this->db->insertID();
            $currentDateTime = date('Y-m-d H:i:s');

            if (isset($data['address']) && isset($data['address']['zip_code'])) {
                $addressDetails = $this->addressService->fetchAddressDetails($data['address']['zip_code']);
                if ($addressDetails) {
                    $addressData = [
                        'id_contact' => $contactId,
                        'zip_code' => $data['address']['zip_code'],
                        'country' => 'Brasil',
                        'state' => $addressDetails['uf'] ?? '',
                        'street_address' => $addressDetails['logradouro'] ?? '',
                        'address_number' => $addressDetails['numero'] ?? '',
                        'city' => $addressDetails['localidade'] ?? '',
                        'address_line' => $addressDetails['complemento'] ?? '',
                        'neighborhood' => $addressDetails['bairro'] ?? '',
                        'created_at' => $currentDateTime,
                    ];
                    $this->db->table('address')->insert($addressData);
                } else {
                    throw new Exception("Dados de endereço não encontrados para o CEP: {$data['address']['zip_code']}");
                }
            }

            if (isset($data['phone'])) {
                $phoneData = [
                    'id_contact' => $contactId,
                    'phone' => $data['phone']['phone'] ?? '',
                    'created_at' => $currentDateTime,
                ];
                $this->db->table('phone')->insert($phoneData);
            }

            if (isset($data['email'])) {
                $emailData = [
                    'id_contact' => $contactId,
                    'email' => $data['email']['email'] ?? '',
                    'created_at' => $currentDateTime,
                ];
                $this->db->table('email')->insert($emailData);
            }

            $this->db->transCommit();

            return $contactId;
        } catch (\Exception $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    public function updateContact($id, array $data)
    {
        $this->db->transBegin();

        try {

            $contactData = [
                'name' => esc($data['name']),
                'description' => esc($data['description']),
            ];
            
            $this->db->table('contacts')->update($contactData, ['id' => $id]);

            if (isset($data['address']) && isset($data['address']['zip_code'])) {
                $addressDetails = $this->addressService->fetchAddressDetails($data['address']['zip_code']);
                if ($addressDetails) {
                    $addressData = [
                        'zip_code' => $data['address']['zip_code'],
                        'country' => 'Brasil',
                        'state' => $addressDetails['uf'] ?? '',
                        'street_address' => $addressDetails['logradouro'] ?? '',
                        'address_number' => $data['address']['address_number'] ?? '',
                        'city' => $addressDetails['localidade'] ?? '',
                        'address_line' => $addressDetails['complemento'] ?? '',
                        'neighborhood' => $addressDetails['bairro'] ?? '',
                        'updated_at' => date('Y-m-d H:i:s'),
                    ];
                    $this->db->table('address')->update($addressData, ['id_contact' => $id]);
                } else {
                    throw new Exception("Dados de endereço não encontrados para o CEP: {$data['address']['zip_code']}");
                }
            }

            if (isset($data['phone'])) {
                $phoneData = [
                    'phone' => $data['phone']['phone'] ?? '',
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
                $this->db->table('phone')->update($phoneData, ['id_contact' => $id]);
            }

            if (isset($data['email'])) {
                $emailData = [
                    'email' => $data['email']['email'] ?? '',
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
                $this->db->table('email')->update($emailData, ['id_contact' => $id]);
            }

            $this->db->transCommit();
        } catch (\Exception $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    public function deleteContact($id)
    {
        $this->db->transBegin();

        try {
            $this->db->table('address')->delete(['id_contact' => $id]);
            $this->db->table('phone')->delete(['id_contact' => $id]);
            $this->db->table('email')->delete(['id_contact' => $id]);
            $this->db->table('contacts')->delete(['id' => $id]);

            $this->db->transCommit();
        } catch (\Exception $e) {
            $this->db->transRollback();
            throw $e;
        }
    }
}
