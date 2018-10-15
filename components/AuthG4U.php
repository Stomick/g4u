<?php
/**
 * Created by PhpStorm.
 * User: Stomick
 * Date: 18.07.2018
 * Time: 20:29
 */

namespace app\components;

use Yii;
use yii\filters\auth\AuthMethod;
use yii\filters\auth\QueryParamAuth;

use yii\web\IdentityInterface;
use yii\web\Request;
use yii\web\Response;
use yii\web\UnauthorizedHttpException;
use yii\web\User;


class AuthG4U extends AuthMethod {

	public function handleFailure($response)
	{
	    $lang = 'ru';
	    $mess = '';
        if($accept = \Yii::$app->request->getHeaders()->get('Accept-Language')) {
            $lang = explode(',',explode(';' , $accept)[0])[0];
        }
        if(strpos($lang , 'ru') == 0){
            $mess = 'Авторизация не удачна';
        }elseif (strpos($lang , 'en') == 0){
            $mess = 'Authorization filed';
        }
        die(json_encode(['answer' => [], 'error' => true, 'message' => $mess ]));
	}

	/**
	 * Authenticates the current user.
	 *
	 * @param User $user
	 * @param Request $request
	 * @param Response $response
	 *
	 * @return IdentityInterface the authenticated user identity. If authentication information is not provided, null will be returned.
	 * @throws UnauthorizedHttpException if authentication information is provided but is invalid.
	 */
	public function authenticate( $user, $request, $response ) {
		// TODO: Implement authenticate() method.
}}