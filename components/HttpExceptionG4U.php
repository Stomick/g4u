<?php
/**
 * Created by PhpStorm.
 * User: Stomick
 * Date: 18.07.2018
 * Time: 20:59
 */

namespace app\components;

use yii\base\UserException;
use yii\web\HttpException;
use yii\web\Response;

class HttpExceptionG4U extends UserException
{
	public function __construct($status, $message = null, $code = 0, \Exception $previous = null)
	{
		$this->statusCode = $status;
		parent::__construct($message, $code, $previous);
	}

	/**
	 * @return string the user-friendly name of this exception
	 */
	public function getName()
	{
		if (isset(Response::$httpStatuses[$this->statusCode])) {
			return Response::$httpStatuses[$this->statusCode];
		}

		return 'Error';
	}
}