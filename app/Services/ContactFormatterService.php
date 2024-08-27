<?php

namespace App\Services;

class ContactFormatterService
{
    public function formatContacts(array $contacts)
    {
        $formattedContacts = [];

        foreach ($contacts as $contact) {
            $contactId = $contact['contact_id'];
            
            if (!isset($formattedContacts[$contactId])) {
                $formattedContacts[$contactId] = [
                    'name' => $contact['name'],
                    'description' => $contact['description'],
                    'address' => [
                        'zip_code' => $contact['zip_code'],
                        'country' => $contact['country'] ?? 'Brasil',
                        'state' => $contact['state'],
                        'street_address' => $contact['street_address'],
                        'address_number' => $contact['address_number'],
                        'city' => $contact['city'],
                        'address_line' => $contact['address_line'],
                        'neighborhood' => $contact['neighborhood'],
                    ],
                    'phone' => [
                        'phone' => $contact['phone_number'] ?? ''
                    ],
                    'email' => [
                        'email' => $contact['email_address'] ?? ''
                    ],
                ];
            } else {

                if (!empty($contact['zip_code'])) {
                    $formattedContacts[$contactId]['address'] = [
                        'zip_code' => $contact['zip_code'],
                        'country' => $contact['country'] ?? 'Brasil',
                        'state' => $contact['state'],
                        'street_address' => $contact['street_address'],
                        'address_number' => $contact['address_number'],
                        'city' => $contact['city'],
                        'address_line' => $contact['address_line'],
                        'neighborhood' => $contact['neighborhood'],
                    ];
                }
                
                if (!empty($contact['phone_number'])) {
                    $formattedContacts[$contactId]['phone'] = [
                        'phone' => $contact['phone_number']
                    ];
                }
                
                if (!empty($contact['email_address'])) {
                    $formattedContacts[$contactId]['email'] = [
                        'email' => $contact['email_address']
                    ];
                }
            }
        }

        return array_values($formattedContacts);
    }
}

