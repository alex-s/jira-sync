<?php
namespace Sync\JiraApi;

use chobie\Jira\Api\Authentication\AuthenticationInterface;
use chobie\Jira\Api\Client\CurlClient;
use chobie\Jira\Api\Exception;
use chobie\Jira\Api\UnauthorizedException;
use Sync\EverhourApi\ApiKeyAuth;

class AdvancedCurlClient extends CurlClient
{
    public function sendRequest(
        $method,
        $url,
        $data = array(),
        $endpoint,
        AuthenticationInterface $credential,
        $is_file = false,
        $debug = false
    ) {
        $curl = curl_init();
        if ( $method == 'GET' ) {
            if ( !is_array($data) ) {
                throw new \InvalidArgumentException('Data must be an array.');
            }

            $url .= '?' . http_build_query($data);
        }
        curl_setopt($curl, CURLOPT_URL, $endpoint . $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_USERPWD, sprintf('%s:%s', $credential->getId(), $credential->getPassword()));

        $headers = ['Content-Type: application/json;charset=UTF-8'];
        if ($credential instanceof ApiKeyAuth) {
            $headers[] = 'x-api-key:' . $credential->getApiKey();
        }

        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_VERBOSE, $debug);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        if ( $method == 'POST' ) {
            curl_setopt($curl, CURLOPT_POST, 1);

            if ( $is_file ) {
                $data['file'] = $this->getCurlValue($data['file'], $data['name']);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            }
            else {
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }
        elseif ( $method == 'PUT' || $method == 'DELETE' ) {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($curl);

        $error_number = curl_errno($curl);

        if ( $error_number > 0 ) {
            throw new Exception(
                sprintf('Jira request failed: code = %s, "%s"', $error_number, curl_error($curl))
            );
        }

        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        // If empty result and status != "204 No Content".
        if ( $http_code == 401 ) {
            throw new UnauthorizedException('Unauthorized');
        }

        if ( $response === '' && !in_array($http_code, array(201, 204)) ) {
            throw new Exception('JIRA Rest server returns unexpected result.');
        }

        if ($http_code == 404 || $http_code == 500) {
            var_dump($data);
            throw new Exception(sprintf('Error request to "%s"', $endpoint.$url));
        }

        // @codeCoverageIgnoreStart
        if ( is_null($response) ) {
            throw new Exception('JIRA Rest server returns unexpected result.');
        }
        // @codeCoverageIgnoreEnd

        return $response;
    }
}