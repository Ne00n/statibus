<?php
//Add your IP here
$whitelist = array('');

function createRequest($url,$timeout = 1,$connect = 1) {
  $result = array();
  $request = curl_init();
  curl_setopt($request, CURLOPT_URL,$url);
  curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($request, CURLOPT_SSL_VERIFYPEER, true);
  curl_setopt($request, CURLOPT_SSL_VERIFYHOST, 2);
  curl_setopt($request, CURLOPT_CONNECTTIMEOUT ,$connect);
  curl_setopt($request, CURLOPT_TIMEOUT, $timeout);
  $result['content'] = curl_exec($request);
  $result['http'] = curl_getinfo($request, CURLINFO_HTTP_CODE);
  curl_close($request);
  return $result;
}

$method = $_SERVER['REQUEST_METHOD'];
$payload = json_decode(file_get_contents('php://input'),true);
$requestIP = $_SERVER['REMOTE_ADDR'];

if ($method == 'POST' && json_last_error() === 0 && in_array($requestIP, $whitelist)) {
  if ((filter_var($payload['target'], FILTER_VALIDATE_IP) || filter_var($payload['target'], FILTER_VALIDATE_DOMAIN)) && is_numeric($payload['port']) && ($payload['type'] == 'ping' || $payload['type'] == 'tcp' || $payload['type'] == 'http')) {
    $ipv6 = filter_var($payload['target'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
    if ($payload['type'] == "ping") {
      if ($ipv6) {
        exec("ping6 -c 3 " . $data['target'], $output, $result);
      } else {
        exec("ping -c 3 " . $data['target'], $output, $result);
      }
      if ($result == 0) { $status = 1; } else { $status = 0; }
      echo json_encode(array('result' => $status));

    } elseif ($payload['type'] == 'tcp') {
      if ($ipv6) {
      	$socket = @fsockopen("[".$payload['target']."]", $payload['port'], $errorNo, $errorStr, $payload['timeout']);
      } else {
      	$socket = @fsockopen($payload['target'], $payload['port'], $errorNo, $errorStr, $payload['timeout']);
      }
      if ($errorNo == 0) { echo json_encode(array('result' => 1,'info' => '')); } else { echo json_encode(array('result' => 0,'info' => $errorStr)); }

    } elseif ($payload['type'] == 'http') {
      $response = createRequest($payload['target'],$payload['timeout'],$payload['connect']);
      echo json_encode(array('http' => $response['http'],'content' => $response['content']));

    }
  }
}

?>
