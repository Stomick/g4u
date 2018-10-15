<?php
return [
	'format' => yii\web\Response::FORMAT_JSON, # I think this option caused the issue.
	'charset' => 'UTF-8',
	'on beforeSend' => function ($event) {
		$response = $event->sender;
		if ($response->data !== null) {
			if(!$response->isSuccessful) {

				$response->data = [
					'message'   => ! $response->isSuccessful ? $response->data['message'] : $response->data,
					'error' => ! $response->isSuccessful ? true:false,
					'answer' => []
				];
			}
			$response->statusCode = 200;
		}
	},
];