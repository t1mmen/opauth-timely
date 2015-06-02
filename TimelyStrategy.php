<?php
/**
 * Timely strategy for Opauth
 *
 * Based on work by U-Zyn Chua (http://uzyn.com)
 *
 * More information on Opauth: http://opauth.org
 *
 * @copyright    Copyright © 2015 Timm Stokke (http://timm.stokke.me)
 * @link         http://opauth.org
 * @package      Opauth.BasecampStrategy
 * @license      MIT License
 */


/**
 * Timely strategy for Opauth
 *
 * @package			Opauth.Timely
 */
class TimelyStrategy extends OpauthStrategy {

	/**
	 * Compulsory config keys, listed as unassociative arrays
	 */
	public $expects = array('client_id', 'client_secret');

	/**
	 * Optional config keys, without predefining any default values.
	 */
	public $optionals = array('redirect_uri');

	/**
	 * Optional config keys with respective default values, listed as associative arrays
	 * eg. array('scope' => 'post');
	 */
	public $defaults = array(
		'redirect_uri' => '{complete_url_to_strategy}oauth2callback'
	);

	/**
	 * Auth request
	 */
	public function request() {
		$url = 'https://api.timelyapp.com/1.0/oauth/authorize';
		$params = array(
			'response_type' => 'code',
			'client_id' => $this->strategy['client_id'],
			'redirect_uri' => $this->strategy['redirect_uri']
		);

		foreach ($this->optionals as $key) {
			if (!empty($this->strategy[$key])) $params[$key] = $this->strategy[$key];
		}

		$this->clientGet($url, $params);
	}

	/**
	 * Internal callback, after OAuth
	 */
	public function oauth2callback() {
		if (array_key_exists('code', $_GET) && !empty($_GET['code'])) {
			$code = $_GET['code'];
			$url = 'https://api.timelyapp.com/1.0/oauth/token';

			$params = array(
				'code' => $code,
				'client_id' => $this->strategy['client_id'],
				'client_secret' => $this->strategy['client_secret'],
				'redirect_uri' => $this->strategy['redirect_uri'],
				'grant_type' => 'authorization_code',
			);


			if (!empty($this->strategy['state'])) $params['state'] = $this->strategy['state'];

			$response = $this->serverPost($url, $params, null, $headers);
			$results = json_decode($response,true);

			if (!empty($results) && !empty($results['access_token'])) {

				$user = $this->user($results['access_token']);

				$this->auth = array(
					'uid' => $user['id'],
					'info' => array(
						'urls' => array(
							'api_url' => 'https://api.timelyapp.com/1.0/'.$user['account_id']
						)),
					'credentials' => array(
						'token' => $results['access_token'],
						'refresh_token' => $results['refresh_token'],
					),
					'raw' => $user
				);

				$this->mapProfile($user, 'name', 'info.name');
				$this->mapProfile($user, 'email', 'info.email');
				$this->mapProfile($user, 'avatar', 'info.image');

				$this->callback();
			}
			else {
				$error = array(
					'code' => 'access_token_error',
					'message' => 'Failed when attempting to obtain access token',
					'raw' => array(
						'response' => $response,
						'headers' => $headers
					)
				);

				$this->errorCallback($error);
			}
		}
		else {
			$error = array(
				'code' => 'oauth2callback_error',
				'raw' => $_GET
			);

			$this->errorCallback($error);
		}
	}

	/**
	 * Queries Timely API for user info
	 *
	 * @param string $access_token
	 * @return array Parsed JSON results
	 */
	private function user($access_token) {

		$options['http']['header'] = "Content-Type: application/json";
		$options['http']['header'] .= "\r\nAccept: application/json";
		$options['http']['header'] .= "\r\nAuthorization: Bearer ".$access_token;

		$accountDetails = $this->serverGet('https://api.timelyapp.com/1.0/accounts', array(), $options, $headers);

		if (!empty($accountDetails)) {

			// Assume first account is the active one.
			$account = $this->recursiveGetObjectVars(json_decode($accountDetails,true))[0];

			$url = 'https://api.timelyapp.com/1.0/'.$account['id'].'/users/current';
			$userDetails = $this->serverGet($url, array(), $options, $headers);

			$user = $this->recursiveGetObjectVars(json_decode($userDetails,true));

			return $user;
		}
		else {
			$error = array(
				'code' => 'userinfo_error',
				'message' => 'Failed when attempting to query Timely API for user information',
				'raw' => array(
					'response' => $user,
					'headers' => $headers
				)
			);

			$this->errorCallback($error);
		}
	}
}
