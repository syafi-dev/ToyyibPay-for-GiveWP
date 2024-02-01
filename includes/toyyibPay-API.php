<?php

if (!class_exists('ToyyibPayGiveAPI')) {
	
	class ToyyibPayGiveAPI
	{
		private $api_key;
		private $category_code;

		private $process;
		private $curldata;
		public $is_production;
		public $url;

		public $header;

		const TIMEOUT = 10; //10 Seconds
		const PRODUCTION_URL = 'https://toyyibpay.com/';
		const STAGING_URL = 'https://dev.toyyibpay.com/';

		public function __construct($api_key)
		{
			$this->api_key = $api_key;
			$this->header = $api_key . ':';
		}

		public function setMode()
		{
			if (give_is_test_mode()) {
				$this->url = self::STAGING_URL;
			} else {
				$this->url = self::PRODUCTION_URL;
			}
			return $this;
		}
		
		public function createBill($parameter)
		{
			/* Create Bills */
			$this->setActionURL('CREATEBILL');	
			$bill = $this->toArray($this->submitAction($parameter)); 
			$billdata = $this->setPaymentURL($bill); 
			
			return $billdata;
		}
		
		public function setPaymentURL($bill)
		{
			if( isset($bill['body'][0]['BillCode']) ) {
				$this->setActionURL('PAYMENT', $bill['body'][0]['BillCode'] ); 
				$bill['body'][0]['BillURL'] = $this->url;
			}
			$return = array('header'=>$bill['header'], 'body'=>$bill['body'][0]);
			
			return $return;	
		}
		
		public function checkBill($parameter)
		{
			$this->setActionURL('CHECKBILL');
			$checkData = $this->toArray($this->submitAction($parameter)); 
			$checkData['body'] = $checkData['body'][0];
			
			return $checkData;	
		}

		public function setUrlQuery($url,$data)
		{
			if (!empty($url)) {
				if( count( explode("?",$url) ) > 1 )  
					$url = $url .'&'. http_build_query($data);
				else  
					$url = $url .'?'. http_build_query($data);
			}
			return $url;
		}

		public function getTransactionData()
		{
			if (isset($_GET['billcode']) && isset($_GET['transaction_id']) && isset($_GET['order_id']) && isset($_GET['status_id'])) {

				$data = array(
					'status_id' => $_GET['status_id'],
					'billcode' => $_GET['billcode'],
					'order_id' => $_GET['order_id'],
					'msg' => $_GET['msg'],
					'transaction_id' => $_GET['transaction_id']
				);
				$type = 'redirect';
				
			} elseif ( isset($_POST['refno']) && isset($_POST['status']) && isset($_POST['amount']) ) {

				$data = array(
					'status_id' => $_POST['status'],
					'billcode' => $_POST['billcode'],
					'order_id' => $_POST['order_id'],
					'amount' => $_POST['amount'],
					'reason' => $_POST['reason'],
					'transaction_id' => $_POST['refno']
				);
				$type = 'callback';
				
			} else {
				return false;
			}
			
			
			if( $type === 'redirect' ) {
				//check bill
				$parameter = array(
					'billCode' => $data['billcode'],
					'billExternalReferenceNo' => $data['order_id']
				);
				$checkbill = $this->checkBill($parameter);
				if ($checkbill['header'] == 200) {
					//if($checkbill['body']['billpaymentStatus'] != $data['status_id']) {
					if( $data['status_id'] != '1' ) {
						$data['status_id'] = $checkbill['body']['billpaymentStatus'];
					}
					$data['amount'] = $checkbill['body']['billpaymentAmount'];
				}
			}
			
			$data['paid'] = $data['status_id'] === '1' ? true : false; /* Convert paid status to boolean */
			
			if( $data['status_id']=='1' ) $data['status_name'] = 'Success';
			else if( $data['status_id']=='2' ) $data['status_name'] = 'Pending';
			else $data['status_name'] = 'Unsuccessful';
			
			$data['type'] = $type;
			
			return $data;

		}

		public function setActionURL($action, $id = '')
		{
			$this->setMode();
			$this->action = $action;
			
			if ($this->action == 'PAYMENT') {
				$this->url .= $id;
			}

			else if ($this->action == 'CREATEBILL') {
				$this->url .= 'index.php/api/createBill';
			}
			else if ($this->action == 'CHECKBILL') {
				$this->url .= 'index.php/api/getBillTransactions';
			}
			else {
				throw new Exception('URL Action not exist');
			}
			
			return $this;
		}
		
		public function submitAction($data='')
		{		
			$this->process = curl_init();
			curl_setopt($this->process, CURLOPT_HEADER, 0);
			curl_setopt($this->process, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($this->process, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($this->process, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($this->process, CURLOPT_TIMEOUT, self::TIMEOUT);
			curl_setopt($this->process, CURLOPT_USERPWD, $this->header);
			
			curl_setopt($this->process, CURLOPT_URL, $this->url);
			curl_setopt($this->process, CURLOPT_POSTFIELDS, http_build_query($data));

			$body = curl_exec($this->process);
			$header = curl_getinfo($this->process, CURLINFO_HTTP_CODE);
			curl_close($this->process);
			
			//$return = json_decode($body, true);
			$return = array('header'=>$header, 'body'=>$body);
			
			
			return $return;
		}
		
		public function toArray($json)
		{
			return array('header'=>$json['header'], 'body'=>json_decode($json['body'], true));
		}
	
	
	}//close class ToyyibPayGiveAPI

}
