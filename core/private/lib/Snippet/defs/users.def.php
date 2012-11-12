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
				'id_profile'	=> array('FK' => '_profiles.id_profile', 'hidden' => true),
				'name'			=> 'Nombre',
				'lastName'		=> 'Apellido',
				'fullName'		=> 'Nombre',
				'address'		=> 'Dirección',
				'phone'			=> 'Teléfono',
				'department'	=> array('name' => 'Departamento', 'FK' => '_departments.id_department', 'hidden' => true),
				'email'			=> 'Email',
				'employeeNum'	=> 'Número',
				'position'		=> 'Cargo',
				'last_access'	=> array('name' => 'Último Login', 'frozen' => true),
				'blocked'		=> array('name' => 'Bloqueado', 'frozen' => true),
			),

			'_profiles' => array(
				'id_profile'    => array('FK' => '_users.profile', 'hidden' => true),
				'profile'		=> array('name' => 'Perfil', 'frozen' => true),
			),

			'_departments' => array(
				'id_department'	=> array('FK' => '_users.department', 'hidden' => true),
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
		SnippetLayer_access::addCustomPermit('newCustomerTech');
		SnippetLayer_access::addCustomPermit('newAgendaEvent');

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



	// TEMP : All these methods below should be automatically created based on the definition

	private function globalFilters(&$filters)
	{
		$srch = $filters['*'];
		$filters = array();

		$fields = array_diff($this->getFieldsFor('view'), (array)'>');
		foreach ($fields as $field)
		{
			$filters["`{$field}`"] = $srch;
		}

		$filters["`p`.`profile`"] = $srch;
		$filters["`d`.`department`"] = $srch;
	}

	protected function getListData($filters=array(), $join='AND')
	{
		if (isset($filters['*']))
		{
			$this->globalFilters($filters);
			$join = 'OR';
		}

		$this->fixFilters($filters, array(
			'profile'       => '`p`.`profile`',
			'department'    => '`d`.`department`',
		));

		$sql = "SELECT	`u`.*,
						`p`.`profile`,
						`d`.`department`,
						CONCAT(`u`.`name`, ' ', `u`.`lastName`) AS 'fullName'
				FROM `_users` `u`
				JOIN `_profiles` `p` USING (`id_profile`)
				JOIN `_departments` `d` USING (`id_department`)
				WHERE ({$this->array2filter($filters, $join)})
				AND NOT `blocked`";
		return $sql;
	}

	protected function getItemData($id)
	{
		return $this->getListData(array('user' => array($id, '=')));
	}

}