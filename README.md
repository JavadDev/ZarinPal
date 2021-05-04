# Create ZarinPal GateWay
```PHP
require 'ZarinPal.php';

$merchant_id = 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'; // MerchantID
$sandbox = false; // For use test version, pass true

$ZarinPal = new ZarinPal($merchant_id, $sandbox);
```

# Create New Payment
```PHP
$data = array(
	'amount' => 10000, // Required, amount based on rials
	'description' => 'Desc', // Required, Payment Description
	'callback_url' => 'https://yoursite.ir/index.php', // Required, user will redirect this page after transaction
);

$metadata = array(
	'mobile' => '09123456789', // Optional
	'email' => 'hawkintherain@gmail.com', // Optional
	'card_pan' => '6104337720001000', // Optional, Description: https://docs.zarinpal.com/paymentGateway/other/#card-pan
);

$ZarinPal->setData($data);
$ZarinPal->setMetaData($metadata);

$create = $ZarinPal->createPayment();

if ($create !== false) {
	$auth = $create->authority;
	// payment url:
	$url = $create->startPay(true);
	// redirect to payment:
	$create->startPay();
} else {
	$errCode = $ZarinPal->error_code;
	echo 'Error Code: '.$errCode;
}
```

# Verify Payment

```PHP
if (isset($_GET['Status']) and $_GET['Status'] == 'OK') {
	$data = array(
		'amount' => 10000, // Required, Based on rials
		'authority' => $_GET['Authority'] // Required, Authority
	);
	
	$ZarinPal->setData($data);
	$verify = $ZarinPal->verifyPayment();
	
	if ($verify !== false) {
		$response = $verify->response();
		echo 'Success!';
	} else {
		$errCode = $ZarinPal->error_code;
		echo 'Error on payment : '.$errCode;
	}
} else {
	echo 'Payment cancelled by user';
}
```

# Refund Payment

```PHP

$data = array(
	'authorization' => 'ACCESS_TOKEN', // Required
	'authority' => 'Authority', // Required
);

$ZarinPal->setData($data);
$refund = $ZarinPal->refund();

if ($refund !== false) {
	// Refund data saved in $refund
	echo 'Successfully refunded!';
} else {
	$errCode = $ZarinPal->error_code;
	echo 'Error: '.$errCode;
}
```

# Get UnVerified Payment List

```PHP
$unVerified = $ZarinPal->unVerified();

if ($unVerified !== false) {
	foreach ($unVerified as $data) {
		$authority = $data['authority'];
		$amount = $data['amount'];
		$callback_url = $data['callback_url'];
		$date = $data['date'];
		$timestamp = strtotime($date);
		$passed_time = time() - $timestamp;
		// do something ...
	}
} else {
	$errCode = $ZarinPal->error_code;
	echo 'Error: '.$errCode;
}
``` 
