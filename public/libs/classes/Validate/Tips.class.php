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
			'text'			=> 'El valor ingresado contiene caracteres inv�lidos.',
			'alpha'			=> 'Este campo acepta solamente letras, n�meros y guiones bajos.',
			'alphaMixed'	=> 'Este campo acepta letras, n�meros y guiones bajos, y debe tener al menos una letra y un n�mero.',
			'email'			=> 'Debe escribir una direcci�n de email v�lida.',
			'num'			=> 'Este campo acepta solamente n�meros.',
			'cost'			=> 'Este campo acepta solamente n�meros enteros o decimales.',
			'docNum'		=> 'Debe ingresar un documento v�lido (ej: 1234567-8).',
			'phone'			=> 'El tel�fono ingresado no es v�lido.',
			'rut'			=> 'Un RUT v�lido debe ser un n�mero de 12 d�gitos.',
			'ranged'		=> 'Este campo acepta solamente n�meros.',
			'time'			=> 'Debe seleccionar una hora v�lida.',
			'date'			=> 'Debe seleccionar una fecha v�lida.',
			'selection'		=> 'Debe seleccionar un elemento de la lista',
		);
		
		public $length = array(	/* Key means, in order: ranged, min is defined, max is defined */
			'101'	=> '%sEl campo debe contener un m�ximo de %s caracteres.',
			'110'	=> 'El campo debe contener al menos %s caracteres.%s',
			'111'	=> 'La longitud del campo debe estar entre %s y %s.',
			'001'	=> '%sLa longitud del campo no puede ser mayor que %s.',
			'010'	=> 'La longitud del campo no puede ser menor que %s.%s',
			'011'	=> 'Debe escribir al menos %s caracteres y no m�s de %s.',
			'000'	=> 'Este campo debe contener %s caracteres',
		);
		
	}