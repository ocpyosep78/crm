<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */



	class Tips{
		
		public $format = array(
			'open'			=> '',
			'text'			=> 'El valor ingresado contiene caracteres inválidos.',
			'alpha'			=> 'Este campo acepta solamente letras, números y guiones bajos.',
			'alphaMixed'	=> 'Este campo acepta letras, números y guiones bajos, y debe tener al menos una letra y un número.',
			'email'			=> 'Debe escribir una dirección de email válida.',
			'num'			=> 'Este campo acepta solamente números.',
			'cost'			=> 'Este campo acepta solamente números enteros o decimales.',
			'docNum'		=> 'Debe ingresar un documento válido (ej: 1234567-8).',
			'phone'			=> 'El teléfono ingresado no es válido.',
			'rut'			=> 'Un RUT válido debe ser un número de 12 dígitos.',
			'ranged'		=> 'Este campo acepta solamente números.',
			'time'			=> 'Debe seleccionar una hora válida.',
			'date'			=> 'Debe seleccionar una fecha válida.',
			'selection'		=> 'Debe seleccionar un elemento de la lista',
		);
		
		public $length = array(	/* Key means, in order: ranged, min is defined, max is defined */
			'101'	=> '%sEl campo debe contener un máximo de %s caracteres.',
			'110'	=> 'El campo debe contener al menos %s caracteres.%s',
			'111'	=> 'La longitud del campo debe estar entre %s y %s.',
			'001'	=> '%sLa longitud del campo no puede ser mayor que %s.',
			'010'	=> 'La longitud del campo no puede ser menor que %s.%s',
			'011'	=> 'Debe escribir al menos %s caracteres y no más de %s.',
			'000'	=> 'Este campo debe contener %s caracteres',
		);
		
	}