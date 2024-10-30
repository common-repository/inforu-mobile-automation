<?php
/**
 * @category Betanet
 * @copyright Betanet (https://www.betanet.co.il/)
 */

namespace WC_Inforu;

class Api
{
    protected $data = [];

    public function doRequest()
    {
        $username = $this->getUsername();
        $apitoken = $this->getToken();
        $url = $this->getUrl();

        if (!$username || !$apitoken || !$url) {
            return false;
        }

        $request = ['Data' => $this->data];
        $body = wp_json_encode($request);

        $debugMode = \WC_Inforu::app()->getOption('debug');
        if ($debugMode === 'yes') {
            $file = 'inforu-' . date('Y_m_d') . '.log';
            $message = implode("\t", array(
                "Inforu/Automation/Request",
                "url=" . $url,
                "username=" . $username,
                "apitoken=" . str_repeat("*", strlen($apitoken)),
                $body
            ));
            wplogger()->info($message, $file);
        }

        $options = [
            'timeout' => 30,
            'redirection' => 5,
            'httpversion' => '1.1',
            'sslverify' => false,
            'headers' => [
                'Content-type' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($username . ":" . $apitoken)
            ],
            'body' => $body,
            'data_format' => 'body'
        ];
        $response = wp_remote_post($url, $options);
        $responseBody = wp_remote_retrieve_body($response);

        if ($debugMode === 'yes') {
            $file = 'inforu-' . date('Y_m_d') . '.log';
            $message = implode("\t", array(
                "Inforu/Automation/Response",
                "url=" . $url,
                "username=" . $username,
                "apitoken=" . str_repeat("*", strlen($apitoken)),
                $body,
                $responseBody,
            ));
            wplogger()->info($message, $file);
        }

        return $response;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return string
     */
    protected function getUsername()
    {
        return \WC_Inforu::app()->getOption('api_username');
    }

    /**
     * @return string
     */
    protected function getToken()
    {
        return \WC_Inforu::app()->getOption('api_token');
    }

    /**
     * @return string
     */
    protected function getUrl()
    {
        return \WC_Inforu::app()->getOption('api_url');
    }
}