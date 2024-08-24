<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->get('contacts', 'Contacts::index');
$routes->post('contacts', 'Contacts::create');
$routes->put('contacts/(:num)', 'Contacts::update/$1');
$routes->delete('contacts/(:num)', 'Contacts::delete/$1');
