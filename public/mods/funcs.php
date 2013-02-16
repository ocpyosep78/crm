<?php

/******************************************************************************/
/******************************* _ C O M M O N ********************************/
/******************************************************************************/

function canEditEvent($user, $creator, $target=NULL)
{
	# 1. Edit own events (from or to), always granted
	if( $user == $creator || $user == $target ) return true;

	# 2. Developer and admin permission always granted
	if( getSes('id_profile') <= 2 ) return true;

	# 3. Groups of users who can edit eachother's events
	$groups[] = ['mantunez', 'rdelossantos', 'gperdomo', 'rferdinand', 'flaborde'];

	foreach ($groups as $group)
	{
		if (in_array($user, $group))
		{
			if (in_array($creator, $group) || ($target && in_array($target, $group)))
			{
				return true;
			}
		}
	}

	# 4. Pairs of users where first one can edit second one's events
	$allowed = [
//			array('thisOneCanEdit', 'thisOnesEvents'),
	];

	return in_array([$user, $creator], $allowed);
}


/******************************************************************************/
/********************************** H O M E ***********************************/
/******************************************************************************/

function getActivity($type)
{
	require_once CLASSES_PATH . '/Activity/Activity.class.php';
	$activity = new Activity;

	Template::one()->assign('events', $activity->events($type));
	Template::one()->assign('notes', $activity->notes($type));
}

function openLogs($file)
{
	$path = LOGS_PATH . "/{$file}.txt";

	if (is_file($path))
	{
		$fp = @fopen($path, 'rb');

		if (!$fp)
		{
			return 'Error al abrir el archivo';
		}

		$log = '';
		while ($data=fgets($fp))
		{
			$log .= nl2br($data);
		}
	}

	return empty($log) ? '(vacío)' : $log;
}


/******************************************************************************/
/****************************** P R O D U C T S *******************************/
/******************************************************************************/

function productBaseKeys()
{
	return array(
		'id_product',
		'id_category',
		'name',
		'cost',
		'price',
		'description',
		'id_product',
	);
}

function createProducts($atts)    // false = not new (editing)
{
	$atts = oValidate()->preProcessInput($atts, $pfx='np_');

	$isNew = empty($atts['id_product']);

	# Separate Base keys, common to all types of products, from Extended keys, and img
	foreach( $atts as $key => $att ){
		if( in_array($key, productBaseKeys()) ) $base[$key] = $att;
		elseif( $key != 'img' ) $extended[$key] = $att;
	}
	$img = $atts['img'];

	# Validate input
	if( ($ans=oValidate()->test($base, 'products')) !== true ){
		return FileForm::addResponse("showTip('{$pfx}{$ans['field']}', '{$ans['tip']}');");
	}
	if( !empty($extended) ){
		if( ($ans=oValidate()->test($extended, 'productsExt')) !== true ){
			return FileForm::addResponse("showTip('{$pfx}{$ans['field']}', '{$ans['tip']}');");
		}
		if( $isNew && ($err=uploadAnalylize($img)) !== true && $err !== NULL ){
			return FileForm::addResponse( "say('{$err}');" );
		}
	}

	# Check image type and other attributes, if a new image was submitted
	if( $img['size'] ){
		if( ($imgAtts=getimagesize($img['tmp_name'])) === false || $imgAtts[2] != IMAGETYPE_JPEG ){
			$msg = "El archivo subido debe ser una imagen con formato/extensión \'jpg\'.";
			return FileForm::addResponse("say('{$msg}');");
		}
	}
	elseif( $isNew && !empty($extended) ){
		$msg = "Debe seleccionar una imagen para este artículo.";
		return FileForm::addResponse("say('{$msg}');");
	}

	# Request query and catch answer, then return it to the user
	$okMsg = 'El artículo fue %s correctamente.';
	oSQL()->setOkMsg( sprintf($okMsg, $isNew ? 'registrado' : 'modificado') );

	oSQL()->BEGIN();

	$ans1 = oSQL()->{$isNew ? 'insert' : 'update'}($base, '_products', 'id_product');
	if( $ans1->error ) return oSQL()->ROLLBACK( FileForm::addResponse("say('{$ans1->msg}');") );

	$id = $isNew ? $ans1->ID : $atts['id_product'];

	if( !empty($extended) ){
		$extended['id_product'] = $id;
		$ans2 = oSQL()->{$isNew ? 'insert' : 'update'}($extended, '_product_extension', 'id_product');
		if( $ans2->error ) return oSQL()->ROLLBACK( FileForm::addResponse("say('{$ans2->msg}');") );
	}
	oSQL()->COMMIT();

	# Save picture if one was chosen and data was stored
	if( $img['size'] ){
		if( !move_uploaded_file($img['tmp_name'], "app/images/products/{$id}.jpg") ){
			$msg = "No se pudo guardar la imagen. Inténtelo nuevamente.";
			return FileForm::addResponse("say('{$msg}');");
		}
	}

	return FileForm::addResponse("getPage('productsInfo', ['{$id}'], '{$ans1->msg}', 1);");

}


/******************************************************************************/
/***************************** T E C H N I C A L ******************************/
/******************************************************************************/

function fixTechVisitsList($data)
{
	$list = array(
		'bad'		=> 'Mala',
		'regular'	=> 'Regular',
		'good'		=> 'Buena',
		'excellent'	=> 'Excelente',
	);

	foreach ($data as &$row)
	{
		$row['quality'] = ($qty=$row['quality']) ? $list[$qty] : '';
	}
}