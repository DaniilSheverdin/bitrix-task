<?
$data=json_decode(file_get_contents('php://input'),1);
$arResult = [];
if ($oCurl = curl_init("http://suggestions.dadata.ru/suggestions/api/4_1/rs" . $_REQUEST['act'])) {
            curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($oCurl, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: ' . $_SERVER['HTTP_AUTHORIZATION']
            ]);
            curl_setopt($oCurl, CURLOPT_POST, 1);
            curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($data));
            $sResult = curl_exec($oCurl);
             echo $sResult;
            curl_close($oCurl);
        }
