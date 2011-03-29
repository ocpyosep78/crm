<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */


	
/**
 *	L I S T S
 */
	
	function page_products(){ 	return oLists()->printList('products', 'products');		}
	function page_materials(){	return oLists()->printList('products', 'materials');	}
	function page_services(){	return oLists()->printList('products', 'services');		}
	function page_others(){		return oLists()->printList('products', 'others');		}
	
	
/**
 *	I N F O
 */
 
	function page_productsInfo( $id ){
	
		# Make sure we got an existing ID
		if( !$id || !($prod=oSQL()->getProduct($id)) ) return oNav()->goBack('No se encontr� el producto pedido.');
		
		oFormTable()->clear();
		oFormTable()->setFrameTitle( 'Detalles del art�culo' );
		
		if( $prod['type'] == 'products' ){
			oFormTable()->addRow('', "<div class='productInfoPreview'><img src='app/images/products/{$id}.jpg' /></div>");
		}
		
		# Block 'Cuenta'
		oFormTable()->addTitle( 'Informaci�n' );
		oFormTable()->addRow('Categor�a', $prod['category']);
		oFormTable()->addRow('Nombre', $prod['name']);
		if( oPermits()->can('seeProductsPrice') ){
			oFormTable()->addRow('Precio', "U\$S {$prod['price']}");
		}
		oFormTable()->addRow('Descripci�n', "<div class='productDescription'>{$prod['description']}</div>");
		if( $prod['type'] == 'products' ){
			oFormTable()->addRow('C�digo', $prod['code']);
			oFormTable()->addRow('Marca', $prod['trademark']);
			oFormTable()->addRow('Modelo', $prod['model']);
			oFormTable()->addRow('Garant�a', ($prod['warranty'] == 12) ? "1 a�o" : "{$prod['warranty']} meses");
			oFormTable()->addRow('Sistema', $prod['system']);
		}
		
		oSmarty()->assign('type', $prod['type']);
		
		# Options for this article
		if( oPermits()->can('editProducts') ){
			$link = "<a href='javascript:void(0);' ".
				"onclick=\"getPage('editProducts', ['{$id}', '{$prod['type']}']);\">Editar Art�culo</a>";
			oFormTable()->addPreText( $link );
		}
		
		# Combo to jump to another product
		oLists()->includeComboList('products', $prod['type'], $id);
		
		oSmarty()->assign('productInfoTbl', oFormTable()->getTemplate());
		
		# Add commands and actions to Xajax response object
		return oNav()->updateContent('products/productsInfo.tpl');
		
	}
	
	
/**
 *	C R E A T E   /   E D I T
 */

	function page_createProducts(){		return page_editProducts(NULL, 'products');		}
	function page_createMaterials(){	return page_editProducts(NULL, 'materials');	}
	function page_createServices(){		return page_editProducts(NULL, 'services');		}
	function page_createOthers(){		return page_editProducts(NULL, 'others');		}
	
	function page_editProducts($id=NULL, $type=NULL){
	
		if( $id ){
			$prod = oSQL()->getProduct( $id );
			if( empty($prod) ) return oNav()->loadContent('products', array(), 'Art�culo no encontrado.');
		}
		
		$type = !empty($prod['type']) ? $prod['type'] : (!empty($type) ? $type : NULL);
		
		oFormTable()->clear();
		oFormTable()->setPrefix( 'np_' );
		oFormTable()->setFrameTitle( $id ? 'Editar Art�culo' : 'Nuevo Art�culo' );
		
		if( $id && $type == 'products' ){
			oFormTable()->addRow('', "<div class='productInfoPreview'><img src='app/images/products/{$id}.jpg' /></div>");
		}
		
		oFormTable()->addTitle( '' );
		if( count($categories=oLists()->product_categories($type)) == 1 ){
			oFormTable()->hiddenRow();
			$atts = array('id' => 'id_category', 'value' => array_shift(array_keys($categories)));
			oFormTable()->addInput('', $atts, 'hidden');
			oFormTable()->addRow('Categor�a', array_shift($categories));
		}
		else{
			oFormTable()->addCombo('Categor�a',
				array('' => 'seleccionar') + $categories,
				array('id' => 'id_category', 'selected' => $id ? $prod['id_category'] : ''));
		}
		oFormTable()->addInput('Nombre', array('id' => 'name'));
		if( oPermits()->can('seeProductsPrice') ){
			oFormTable()->addInput('Precio', array('id' => 'price'));
		}
		oFormTable()->addArea('Descripci�n', array(
			'id'	=> 'description',
			'style'	=> 'height:50px; width:300px;'
		) );
		
		if( $type == 'products' ){
			oFormTable()->addInput('C�digo', array('id' => 'code'));
			oFormTable()->addInput('Marca', array('id' => 'trademark'));
			oFormTable()->addInput('Modelo', array('id' => 'model'));
			oFormTable()->addCombo('Garant�a',
				array(0 => 'Sin garant�a', 3 => '3 meses', 6 => '6 meses', 12 => '1 a�o'),
				array('id' => 'warranty', 'selected' => $id ? (int)$prod['warranty'] : 12 ));
			oFormTable()->addTitle( '' );
			oFormTable()->addFile('Imagen', array('id' => 'img'), '');
			oFormTable()->addTitle( '' );
			oFormTable()->addCombo('Sistema',
				array('' => '(seleccionar)') + oLists()->systems(),
				array('id' => 'id_system', 'selected' => $id ? (int)$prod['id_system'] : '' ));
		}
		
		# Fill form values if editting
		if( $id ){
			oFormTable()->hiddenRow();
			oFormTable()->addInput('', array('id' => 'id_product'), 'hidden');
			oFormTable()->fillValues( $prod );
		}
		
		# Make it ready for Pajax
		oFormTable()->pajaxSubmit('createProducts');
		
		# Submit line
		oFormTable()->addSubmit($id ? 'Guardar Cambios' : 'Ingresar Art�culo');
		
		oSmarty()->assign('editProductTbl', oFormTable()->getTemplate());
		
		# Combo to jump to another product
		oLists()->includeComboList('products', $type, $id);
		
		# Add commands and actions to Xajax response object
		oNav()->updateContent('products/editProducts.tpl');
		
		return addScript("\$('np_id_category').focus();");
		
	}

?>