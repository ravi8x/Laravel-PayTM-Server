<?php
header("Pragma: no-cache");
header("Cache-Control: no-cache");
header("Expires: 0");

// following files need to be included
require_once("./lib/config_paytm.php");
require_once("./lib/encdec_paytm.php");

$paytmChecksum = "";
$paramList = array();
$isValidChecksum = FALSE;

$paramList = $_POST;
$return_array = $_POST;
$paytmChecksum = isset($_POST["CHECKSUMHASH"]) ? $_POST["CHECKSUMHASH"] : ""; //Sent by Paytm pg

//Verify all parameters received from Paytm pg to your application. Like MID received from paytm pg is same as your application’s MID, TXN_AMOUNT and ORDER_ID are same as what was sent by you to Paytm PG for initiating transaction etc.
$isValidChecksum = verifychecksum_e($paramList, PAYTM_MERCHANT_KEY, $paytmChecksum); //will return TRUE or FALSE string.

// if ($isValidChecksum===TRUE)
// 	$return_array["IS_CHECKSUM_VALID"] = "Y";
// else
// 	$return_array["IS_CHECKSUM_VALID"] = "N";

$return_array["IS_CHECKSUM_VALID"] = $isValidChecksum ? "Y" : "N";
//$return_array["TXNTYPE"] = "";
//$return_array["REFUNDAMT"] = "";
unset($return_array["CHECKSUMHASH"]);

$encoded_json = htmlentities(json_encode($return_array));

?>

<html>
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=ISO-8859-I">
	<title>Paytm</title>
	<script type="text/javascript">
		function response(){
			return document.getElementById('response').value;
		}
	</script>
</head>
<body>
  Redirect back to the app<br>

  <form name="frm" method="post">
    <input type="hidden" id="response" name="responseField" value='<?php echo $encoded_json?>'>
  </form>
</body>
</html>
