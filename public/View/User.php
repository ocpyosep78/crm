<?php


class View_User extends View
{

	// Basic properties (for the view, usually)
	protected $__name   = 'Usuario';
	protected $__plural = 'Usuarios';
	protected $__gender = 'm';

	// Descriptive text and item's image (path with %id% wildcard)
	protected $__descr_field = "CONCAT(`name`, ' ', `lastName`, ' (', `user`, ')')";

	// Screen name of each field (real or aliased)
	protected $__screen_names = array(
		'user'        => 'Usuario',
		'name'        => 'Nombre',
		'lastName'    => 'Apellido',
		"CONCAT(`name`, ' ', `lastName`)" => 'Nombre',
		'address'     => 'Dirección',
		'phone'       => 'Teléfono',
		'email'       => 'Email',
		'department'  => 'Departamento',
		'position'    => 'Cargo',
		'employeeNum' => 'Nº de Empleado',
		'profile'     => 'Perfil',
		'last_access' => 'Último Login');

	// List of fields to present when listing all Model items
	protected $__tabular_fields = array(
		"CONCAT(`name`, ' ', `lastName`)",
		'user', 'phone', 'email', 'profile');

	protected function img_path()
	{
		return IMAGES_URL . '/User/%id%.png';
	}

}