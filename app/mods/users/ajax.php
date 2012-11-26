<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */



	return array(
		'createUsers',
		'blockUsers',
		'deleteUsers',
	);

	function createUsers($atts)
	{
		# Get rid of prefixes in $atts keys (remaining keys match field names in DB)
		oValidate()->preProcessInput($atts, 'createUsers_');

		if (($valid=oValidate()->test($atts, 'users')) === true)
		{
            $img = $atts['img'];
            unset($atts['img']);

            # Check image type and other attributes, if a new image was submitted
            if ($img['size'])
			{
                if (($imgAtts=getimagesize($img['tmp_name'])) === false || $imgAtts[2] != IMAGETYPE_PNG )
				{
                    $msg = "El archivo subido debe ser una imagen con formato/extensión \'png\'.";
                    return FileForm::addResponse("say('{$msg}');");
                }
            }

			# Set messages to return to the user after processing the request
			oSQL()->setErrMsg("El usuario {$atts['user']} fue creado con éxito");
			oSQL()->setErrMsg("Ocurrió un error al intentar crear el usuario {$atts['user']}");
			oSQL()->setDuplMsg("El usuario {$atts['user']} ya existe. Debe elegir otro nombre de usuario");

			# Request query and catch answer, then return it to the user
			$ans = oSQL()->createUsers( $atts );

			if ($ans->error)
			{
				return FileForm::addResponse("say('{$ans->msg}');");
			}
			else
			{
                # Save picture if one was chosen and data was stored
                if ($img['size'])
				{
					$imgpath = View::get('User')->image($atts['user']);

                    if (!move_uploaded_file($img['tmp_name'], $imgpath))
					{
                        $msg = "No se pudo guardar la imagen. Inténtelo nuevamente.";
                        return FileForm::addResponse("say('{$msg}');");
                    }
                }

				return FileForm::addResponse("getPage('usersInfo', ['{$atts['user']}'], '{$ans->msg}', 1);");
			}
		}
		else return FileForm::addResponse("showTip('createUsers_{$valid['field']}', '{$valid['tip']}');");
	}

	function blockUsers($user, $unblock=false)
	{
		$unblock = !!$unblock; # Cast to boolean values received through xajax

		// Verify that the user can take this action
		Access::enforce('blockUsers');

		$blockStatus = $unblock ? 'desbloqueado' : 'bloqueado';
		oSQL()->setOkMsg("El usuario {$user} fue {$blockStatus} correctamente.");
		oSQL()->setErrMsg("El usuario {$user} no pudo ser bloqueado. ".
			"Verifique sus permisos e inténtelo nuevamente.");

		$ans = oSQL()->blockUsers($user, $unblock);
		if( !$ans->error ){
			if( $user == loggedIn() ){
				$msg = 'Su cuenta fue bloqueada. No podrá iniciar sesión hasta que un administrador la habilite.';
				return logout( $msg );
			}
			else return oNav()->reloadPage($ans->msg, 1);
		}
		else return say( $ans->msg );

	}

	function deleteUsers( $user ){

		if( $user == loggedIn() ){		# Double check
			return say('No es posible eliminar su propio usuario.');
		}

		# Handle security issues to the right function (security.php)
		Access::enforce('deleteUsers');

		oSQL()->setOkMsg("El usuario {$user} fue eliminado correctamente.");
		oSQL()->setErrMsg("No se pudo eliminar el usuario {$user}. ".
			"Verifique sus permisos e inténtelo nuevamente.");

		$ans = oSQL()->deleteUsers( $user );
		if( !$ans->error ) return oNav()->reloadPage($ans->msg, 1);
		else return say( $ans->msg );

	}