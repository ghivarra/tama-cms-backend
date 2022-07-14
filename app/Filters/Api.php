<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class Api implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $allowedSite = str_replace(' ', '', $_ENV['ALLOWED_CORS']);
        $allowedSite = explode(',', $allowedSite);

        // if not exist
        if (!$request->hasHeader('origin'))
        {
            die('Anda tidak memiliki izin untuk mengakses URL ini');
        }

        // get request origin
        $origin = $request->header('origin')->getValue();

        // if
        if (in_array($origin, $allowedSite))
        {
            $key  = array_search($origin, $allowedSite);
            $site = $allowedSite[$key];

            header("Access-Control-Allow-Origin: {$origin}");

        } else {

            die('Anda tidak memiliki izin untuk mengakses URL ini');
        }

        // set acccess control allow origin
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Authorization");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

        $method = $_SERVER['REQUEST_METHOD'];

        if ($method == 'OPTIONS')
        {
            header("Access-Control-Max-Age: 864000");
            http_response_code(204);
            die('');
        }
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}
