<?php


class Controller
{


	use Object;


	public static function process()
	{
		if (self::ajax())
		{
			// For guests, block all ajax calls except 'login'
			if (!self::logged() && !self::ajax('login'))
			{
				Response::reload();
			}
			elseif(self::ajax('content'))
			{
				PageController::content();
			}
			else
			{
				ActionController::doAction();
			}

			$response = Template::one()->retrieve('js');

			if (!$response)
			{
				$response = ["say('Error: no response returned by server')"];
			}

			echo json_encode($response);
		}
		else
		{
			PageController::page();
		}
	}

	/**
	 * protected static array readAjaxArgs()
	 *      Read input arguments and decode them (from json).
	 *
	 * @return array
	 */
	protected static function readAjaxArgs()
	{
		$convert = function(&$args) use (&$convert)
		{
			if (!is_scalar($args))
			{
				is_array($args) || ($args = (array)$args);

				foreach ($args as &$arg)
				{
					$convert($arg);
				}
			}
		};

		$args = empty($_POST['args']) ? [] : json_decode($_POST['args']);
		$convert($args);

		return $args;
	}

	protected function requestType()
	{
		return self::$request_type;
	}

}