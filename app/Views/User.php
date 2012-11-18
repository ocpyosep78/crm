<?php


class View_User extends View
{

	// Basic properties (for the view, usually)
	protected $__name   = 'Usuario';
	protected $__plural = 'Usuarios';
	protected $__gender = 'm';

	// A field often used to describe an instance of this model
	protected $__descr_field = 'Nombre';

	// Screen name of each field (real or aliased)
	protected $__screen_names = array(
		'user'        => 'Usuario',
		'pass'        => 'Contraseña',
		'name'        => 'Nombre',
		'lastName'    => 'Apellido',
		'address'     => 'Dirección',
		'phone'       => 'Teléfono',
		'email'       => 'Email',
		'employeeNum' => 'Número',
		'position'    => 'Cargo',
		'last_access' => 'Último Login',
		'blocked'     => 'Bloqueado',
		'profile'     => 'Perfil',
		'department'  => 'Departamento');


	/**
	 * array getTabularData([mixed $limit = 30])
	 *      Generate relevant information to build a tabular list.
	 *
	 * @param mixed $limit      A valid LIMIT value (e.g. 4, '0, 30', etc.).
	 * @return array
	 */
	public function getTabularData($limit=30)
	{
		$fieldlist = array("CONCAT(`name`, ' ', `lastName`)",
		                   'user', 'phone', 'email', 'profile');

		$fields = $this->mapnames($fieldlist);
		$fields["CONCAT(`name`, ' ', `lastName`)"] = 'Nombre';

		$data = $this->Model->find('NOT blocked', $fields, $limit)->get();

		$hidden = array(); // No retrieved field is to be hidden

		return compact('fields', 'data', 'hidden');
	}

	/**
	 * array getItemData(mixed $id)
	 *      Generate relevant information to build a single item's page.
	 *
	 * @param mixed $id         The id of this element (primary key value)
	 * @return array
	 */
	public function getItemData($id)
	{
		$fields = $this->__screen_names;
		unset($fields['pass']);

		$this->Model->setId($id)->select($fields);
		$data = $this->Model->find()->convert('row')->get();

		return compact('fields', 'data');
	}

}