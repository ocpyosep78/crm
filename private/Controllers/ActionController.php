<?php

class ActionController extends Controller
{

	public function doAction()
	{
		if (!is_callable('Ajax::' . self::ajax()))
		{
			return say('Error: ' . self::ajax() . ' is not a valid Ajax ID');
		}

		call_user_func_array('Ajax::' . self::ajax(), self::readAjaxArgs());
	}

}