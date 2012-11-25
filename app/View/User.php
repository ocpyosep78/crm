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
		'address'     => 'Direcci�n',
		'phone'       => 'Tel�fono',
		'email'       => 'Email',
		'department'  => 'Departamento',
		'position'    => 'Cargo',
		'employeeNum' => 'N� de Empleado',
		'profile'     => 'Perfil',
		'last_access' => '�ltimo Login');

	// List of fields to present when listing all Model items
	protected $__tabular_fields = array(
		"CONCAT(`name`, ' ', `lastName`)",
		'user', 'phone', 'email', 'profile');

	protected function img_path()
	{
		return IMG_PATH . '/users/%id%.png';
	}

}