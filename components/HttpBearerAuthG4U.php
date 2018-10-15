<?php
/**
 * Created by PhpStorm.
 * User: Stomick
 * Date: 18.07.2018
 * Time: 20:16
 */

namespace app\components;

use yii\filters\auth\HttpBearerAuth;
use yii\web\UnauthorizedHttpException;



class HttpBearerAuthG4U extends HttpBearerAuth {

	public function handleFailure($response)
	{
		throw new UnauthorizedHttpException(Yii::t('app', 'Your request was made with invalid token.'));
	}
}