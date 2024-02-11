<?php

namespace Byjuno\ByjunoCore\Helper\CembraApi;


class CembraPayAzure
{
    /**
     * @param $endpoint
     * @param $timeout
     * @param $username
     * @param $password
     * @return string[]
        curl --request POST \
        --url 'https://login.microsoftonline.com/4c6a6b34-0bcf-4dff-b3a7-949dcb43a07e/oauth2/v2.0/token' \
        --header 'content-type: application/x-www-form-urlencoded' \
        --data grant_type=client_credentials \
        --data client_id=<> \
        --data client_secret=<> \
        --data scope=<>
     */
    public function getToken(CembraPayLoginDto $accessData) {
        if (intval($accessData->timeout) < 0) {
            $timeout = 30;
        } else {
            $timeout = intval($accessData->timeout);
        }
        $url = 'https://login.microsoftonline.com/4c6a6b34-0bcf-4dff-b3a7-949dcb43a07e/oauth2/v2.0/token';
        $request_data = [
            "grant_type" => "client_credentials",
            "client_id" => $accessData->username,
            "client_secret" => $accessData->password,
            "scope" => $accessData->audience
        ];

        $headers = [
            "Content-type: application/x-www-form-urlencoded",
            "accept: text/plain"
        ];

        $postfields = (is_array($request_data)) ? http_build_query($request_data) : $request_data;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postfields);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $response = @curl_exec($curl);
        @curl_close($curl);

        $response = $this->decodeJson(trim($response ?? ""));
        return $response;
    }

    public static function validToken($token, $timeoutSec = 120)
    {
        $tokenExp = explode('.', $token ?? "");
        if (!empty($tokenExp[1])) {
            $jsonToken = base64_decode($tokenExp[1]);
            $arrayToken = json_decode($jsonToken, true);
            if ($arrayToken["exp"] - time() >= $timeoutSec) {
                return true;
            }
        }
        return false;
    }

    private function decodeJson($json)
    {
        $result = "";
        $reponse = json_decode($json);
        if (!empty($reponse->token_type)
            && $reponse->token_type == "Bearer"
            && !empty($reponse->access_token)
            && self::validToken($reponse->access_token)) {
                return $reponse->access_token;
        }
        return $result;
    }
}
