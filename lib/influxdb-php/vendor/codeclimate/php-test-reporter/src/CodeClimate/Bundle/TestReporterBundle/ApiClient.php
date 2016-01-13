<?php
namespace CodeClimate\Bundle\TestReporterBundle;

class ApiClient
{
    protected $apiHost;

    /**
     * Init the API client and set the hostname
     */
    public function __construct()
    {
        $this->apiHost = "https://codeclimate.com";

        if (isset($_SERVER["CODECLIMATE_API_HOST"])) {
            $this->apiHost = $_SERVER["CODECLIMATE_API_HOST"];
        }

    }

    /**
     * Send the given JSON as a request to the CodeClimate Server
     *
     * @param object $json JSON data
     * @return \stdClass Response object with (code, message, headers & body properties)
     */
    public function send($json)
    {
        $response = new \stdClass;
        $payload = (string)$json;
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => array(
                    'Host: codeclimate.com',
                    'Content-Type: application/json',
                    'User-Agent: Code Climate (PHP Test Reporter v'.Version::VERSION.')',
                    'Content-Length: '.strlen($payload)
                ),
                'content' => $payload,
                "timeout" => 10
            )
        );
        $context = stream_context_create($options);
        $url = $this->apiHost.'/test_reports';

        if ($stream = @fopen($url, 'r', false, $context)) {
            $meta = stream_get_meta_data($stream);
            $raw_response = implode("\r\n", $meta['wrapper_data'])."\r\n\r\n".stream_get_contents($stream);
            fclose($stream);

            if (!empty($raw_response)) {
                $response = $this->buildResponse($response, $raw_response);
            }
        } else {
            $error = error_get_last();
            preg_match('/([0-9]{3})/', $error['message'], $match);
            $errorCode = (isset($match[1])) ? $match[1] : 500;

            $response->code = $errorCode;
            $response->message = $error['message'];
            $response->headers = array();
            $response->body = NULL;
        }

        return $response;
    }

    /**
     * Build the response object from the HTTP results
     *
     * @param \stdClass $response Standard object
     * @param string $body HTTP response contents
     * @return \stcClass Populated class object
     */
    private function buildResponse($response, $body)
    {
        list($response->headers, $response->body) = explode("\r\n\r\n", $body, 2);
        $response->headers = explode("\r\n", $response->headers);
        list(, $response->code, $response->message) = explode(' ', $response->headers[0], 3);

        return $response;
    }
}
