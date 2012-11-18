<?php


class View_Customer extends View
{

	// Basic properties (for the view, usually)
	protected $__name   = 'Cliente';
	protected $__plural = 'Clientes';
	protected $__gender = 'm';

	// A field often used to describe an instance of this model
	protected $__descr_field = 'Empresa';

	// Screen name of each field (real or aliased)
	protected $__screen_names = array(
		'number'         => 'N�mero',
		'customer'       => 'Empresa',
		'legal_name'     => 'Raz�n Social',
		'rut'            => 'RUT',
		'address'        => 'Direcci�n',
		'billingaddress' => 'Dir. de Facturaci�n',
		'phone'          => 'Tel�fono',
		'email'          => 'Email',
		'since'          => 'Fecha Ingreso',
		'subscribed'     => 'Subscripci�n',
		'location'       => 'Ciudad/Localidad');


	/**
	 * array getTabularData([mixed $limit = 30])
	 *      Generate relevant information to build a tabular list.
	 *
	 * @param mixed $limit      A valid LIMIT value (e.g. 4, '0, 30', etc.).
	 * @return array
	 */
	public function getTabularData($limit=30)
	{
		$fieldlist = array('id_customer', 'number', 'customer', 'legal_name', 'address', 'phone');

		$fields = $this->mapnames($fieldlist);
		$fields["CONCAT(`_users`.`name`, ' ', `_users`.`lastName`)"] = 'Vendedor';

		$data = $this->Model->find(NULL, $fields, $limit)->get();

		$hidden = array('id_customer'); // No retrieved field is to be hidden

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

		$this->Model->setId($id)->select($fields);
		$data = $this->Model->find()->convert('row')->get();

		return compact('fields', 'data');
	}

}