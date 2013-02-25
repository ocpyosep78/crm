<?php


trait Object
{

	use Connect;

	use Singleton;


	public static function logged()
	{
		return loggedIn();	# TODO : move session handling to its own class...
	}

	/**
	 * static mixed ajax([string $id = NULL])
	 *      Returns false if this is not an ajax call.
	 *      If $id is provided, returns whether the current ajax's id is $id.
	 *      Else, returns the call's id (usually the function to be executed).
	 *
	 * @param string $id
	 * @return mixed
	 */
	public static function ajax($id=NULL)
	{
		if (($_GET !== ['ajax' => '']) || empty($_POST['id']))
		{
			return false;
		}
		else
		{
			return $id ? ($_POST['id'] == $id) : $_POST['id'];
		}
	}

	public static function fromCamelCase($str)
	{
		$search = '_(^|[a-z])([A-Z])_e';
		$replace = "'\\1' . ('\\1' ? '_' : '') . strtolower('\\2')";

		return preg_replace($search, $replace, $str);
	}

	public static function uri($str)
	{
		return strtolower(preg_replace('_[^\wáéíóúÁÉÍÓÚ]_', '_', $str));
	}

}