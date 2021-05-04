<?php

class ZarinPal {
	
	public $merchant;
	public $sandbox;
	private $data;
	private $metadata;
	public $authority;
	public $error_code;
	public $error_message;
	public $confirm;
	
	public function __construct(string $merchant, bool $sandbox = false)
	{
		$this->merchant = $merchant;
		$this->sandbox = $sandbox;
	}
	
	public function send(string $url, $data = [], $metadata = [])
	{
		$curl = curl_init();
		
		$data['merchant_id'] = $this->merchant;
		if (! empty($metadata)) {
			$data['metadata'] = $metadata;
		}
		
		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POSTFIELDS => json_encode($data),
			CURLOPT_HTTPHEADER => [
				'accept: application/json',
				'content-type: application/json'
			]
		));
		
		$exec = curl_exec($curl);
		curl_close($curl);
		return json_decode($exec, true);
	}
	
	private function getUrl(string $query)
	{
		switch ($query) {
			case 'payment':
			$url = 'zarinpal.com/pg/v4/payment/request.json';
			break;
			
			case 'verify':
			$url = 'zarinpal.com/pg/v4/payment/verify.json';
			break;
			
			case 'pg':
			$url = 'zarinpal.com/pg/StartPay/';
			break;
		}
		
		return 'https://'.($this->sandbox ? 'sandbox.': ($query == 'pg' ? 'www.': 'api.')).$url;
	}
	
	public function setMetaData(array $metadata)
	{
		$this->metadata = $metadata;
		return $this;
	}
	
	public function setData(array $data)
	{
		$this->data = $data;
		return $this;
	}
	
	public function getMetaData()
	{
		return $this->metadata;
	}
	
	public function getData()
	{
		return $this->data;
	}
	
	public function response()
	{
		return $this->confirm;
	}
	
	public function createPayment()
	{
		$request = $this->send(
			$uri = $this->getUrl('payment'),
			$this->data,
			$this->metadata
		);
		
		if (@$request['data']['code'] == 100) {
			$this->authority = $request['data']['authority'];
			return $this;
		} else {
			$this->setErrors($request);
			return false;
		}
	}
	
	public function startPay(bool $string = false)
	{
		$url = $this->getUrl('pg').$this->authority;
		if (! $string) {
			header('location: '.$url);
		} else {
			return $url;
		}
	}
	
	public function verifyPayment()
	{
		$request = $this->send(
			$this->getUrl('verify'),
			$this->data,
			$this->metadata
		);
		
		if (@$request['data']['code'] == 100) {
			$this->confirm = $request['data'];
			return $this;
		} else {
			$this->setErrors($request);
			return false;
		}
	}
	
	public function unVerified()
	{
		$request = $this->send(
			'https://api.zarinpal.com/pg/v4/payment/unVerified.json'
		);
		
		if (@$request['data']['code'] == 100) {
			return $request['data'];
		} else {
			$this->setErrors($request);
			return false;
		}
	}
	
	public function refund()
	{
		$request = $this->send(
			'https://api.zarinpal.com/pg/v4/payment/refund.json',
			$this->data
		);
		
		if (@$request['data']['code'] == 100) {
			return $request['data'];
		} else {
			$this->setErrors($request);
			return false;
		}
	}
	
	private function setErrors(array $result)
	{
		if (isset($result['errors'])) {
			$this->error_code = $result['errors']['code'];
			$this->error_message = $result['errors']['message'];
		} else {
			$this->error_code = $result['data']['code'];
		}
	}
}
