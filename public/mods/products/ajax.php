<?php

return array(
	'deleteProducts',
);

function deleteProducts($id)
{
	// Handle security issues to the right function (security.php)
	Access::enforce('deleteProducts');

	$inUse = oSQL()->isProductUsedInEstimates($id);

	if (!empty($inUse))
	{
		$msg = 'No es posible eliminar este producto porque está en uso. '.
			'El producto es utilizado en uno o más presupuestos ('.join(', ', $inUse).')';
		return say($msg);
	}

	oSQL()->setOkMsg("El artículo seleccionado fue eliminado correctamente.");
	oSQL()->setErrMsg("No se pudo eliminar el artículo. Verifique sus permisos e inténtelo nuevamente.");

	$ans = oSQL()->deleteProducts($id);

	if (!$ans->error)
	{
		if (is_file($path=IMAGES_URL . "/products/{$id}.jpg"))
		{
			@unlink($path);
		}

		return oNav()->reloadPage($ans->msg, 1);
	}
	else
	{
		return say($ans->msg);
	}
}