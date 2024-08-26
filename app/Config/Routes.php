<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->get('contacts', 'ContactController::index');
$routes->post('contacts', 'ContactController::create');
$routes->put('contacts/(:num)', 'ContactController::update/$1');
$routes->delete('contacts/(:num)', 'ContactController::delete/$1');
