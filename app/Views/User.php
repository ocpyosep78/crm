<?php


class View_User extends View
{

	// Basic properties (for the view, usually)
	protected $__name   = 'Usuario';
	protected $__plural = 'Usuarios';
	protected $__gender = 'm';

	// A field often used to describe an instance of this model
	protected $__descr_field = "CONCAT(`name`, ' ', `lastName`, ' (', `user`, ')')";

	// Screen name of each field (real or aliased)
	protected $__screen_names = array(
		'user'        => 'Usuario',
		'pass'        => 'Contraseña',
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

	protected $__tabular_fields = array(
		"CONCAT(`name`, ' ', `lastName`)",
		'user', 'phone', 'email', 'profile');


	/**
	 * protected array fullinfo_fields()
	 *      Dynamically override @__fullinfo_fields.
	 *
	 * @return array
	 */
	protected function fullinfo_fields()
	{
		$all = $this->__screen_names;
		unset($all["CONCAT(`name`, ' ', `lastName`)"], $all['pass']);

		return array_keys($all);
	}

}