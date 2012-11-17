<?php


class Snippet_def_users extends Snippets_Handler_Source{

	protected function getBasicAttributes()
	{
		return array(
			'name'		=> 'Usuario',
			'plural'	=> 'Usuarios',
		);
	}


	/**
	 * @overview: List of tables involving this module, and each relevant field in them,
	 *            plus attributes for each field that could be used by Modules.
	 *            Attributes might have the following fields:
	 *                name     Screen name for this field
	 *                type     text, image, area, combo (defaults to text)
	 *                isKey    whether this field is a key
	 *                hidden   whether to hide this field in infoPage
	 *                FK       i.e. ['sales']['seller']['FK'] = 'users.user'
	 *            Field will be ignored for output when name is empty or hidden is true
	 *            Fields flagged as keys (isKey => true) will be hidden by default. Set
	 *            hidden => false explicitly to override.
	 */
	protected function getDatabaseDefinition()
	{
		return array(
			'_users' => array(
				'user'			=> array('name' => 'Usuario', 'isKey' => true, 'hidden' => false),
				'pass'			=> array('name' => 'Contraseña', 'hidden' => true),
				'name'			=> 'Nombre',
				'lastName'		=> 'Apellido',
				'fullName'		=> 'Nombre',
				'address'		=> 'Dirección',
				'phone'			=> 'Teléfono',
				'email'			=> 'Email',
				'employeeNum'	=> 'Número',
				'position'		=> 'Cargo',
				'last_access'	=> array('name' => 'Último Login', 'frozen' => true),
				'blocked'		=> array('name' => 'Bloqueado', 'frozen' => true),
				'profile'		=> array('name' => 'Perfil', 'frozen' => true),
				'department'	=> array('name' => 'Departamento'),
			),
		);

	}

	protected function getFieldsFor($type)
	{
		switch ($type)
		{
			case 'list':
				return array('user', 'fullName', 'phone', 'email', 'profile');

			case 'view':
				return array('user', 'name', 'lastName', 'address', 'phone', 'department', '>',
				             'email', 'employeeNum', 'position', 'profile', 'last_access');

			case 'create':
			case 'edit':
				return array('user', 'name', 'lastName', 'address', 'phone', 'department', 'email', '>',
				             'employeeNum', 'position', 'profile', 'last_access', 'blocked');
		}
	}

	protected function getTools()
	{
		snp_Layer_access::addCustomPermit('newCustomerTech');
		snp_Layer_access::addCustomPermit('newAgendaEvent');

		return array('view', 'create', 'edit', 'delete');
	}

	public function delete($user)
	{
		return $this->update($user, array('blocked' => 1));
	}

	protected function getValidationRuleSet()
	{
		return array(
			'user'			=> array('alpha', 2, 20),
			'pass'			=> array('alphaMixed', 4, 12),
			'id_profile'	=> array('selection'),
			'name'			=> array('text', 2, 40 ),
			'lastName'		=> array('text', 2, 40 ),
			'phone'			=> array('phone', 3, 20 ),
			'address'		=> array('text', 2, 40 ),
			'email'			=> array('email', 2, 40 ),
			'id_department'	=> array('selection'),
			'position'		=> array('text', 0, 40 ),
			'employeeNum'	=> array('alpha', 0, 20 ),
		);
	}

	protected function getListData($filters=array(), $type='list')
	{
		switch ($type)
		{
			default:
			case 'list':
				$filters['blocked'] = array(0, '!=');
				$fullName = "CONCAT(name, ' ', lastName) AS fullName";
				$fields = array('user', $fullName, 'phone', 'email', 'profile');
				$data = $this->find($filters, $fields, '0, 10');
				break;

			case 'hash':
				$filters = array('blocked' => array(0, '!='));
				$fields = array('user', "CONCAT(`name`, ' ', `lastName`)" => 'fullName');
				$data = $this->find($filters, $fields, 0)->convert('col');
				break;

			case 'view':
				$filters['blocked'] = array(0, '!=');
				$fields = array('user', 'name', 'lastName', 'address', 'phone', 'department',
				                'email', 'employeeNum', 'position', 'profile', 'last_access');
				$data = $this->find($filters, $fields, 1)->convert('row');

		}

		return $data->get();
	}

	protected function getItemData($id)
	{
		return $this->getListData(array('user' => array($id, '=')), 'view');
	}

}