<?php
namespace App\Controllers;

use CodeIgniter\HTTP\Message;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class ApiController extends BaseController
{
	/**
	 * Constructor.
	 *
	 * @param RequestInterface  $request
	 * @param ResponseInterface $response
	 * @param LoggerInterface   $logger
	 */
	public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
	{
		// Do Not Edit This Line
		parent::initController($request, $response, $logger);
        $this->method = strtoupper($this->request->getMethod());
	}

    public function checkValidation(array $validations)
    {
        $this->validation->setRules($validations);

        if(in_array($this->method, [METHOD_GET, METHOD_POST])) {
            $isValid = $this->validation->run($this->request->getPost());
        } else {
            $isValid = $this->validation->withRequest($this->request)->run();
        }

        if(!$isValid) {
            $this->json(HTTP_BAD_REQUEST, [ 'msg' => 'Validation failed' ]);
        }

        return true;
    }

	public function json($httpCode, $data = null)
	{
        $response = [];
		if($data) $response = $data;

		$this->response->setStatusCode($httpCode)
			->setJSON($response)
			->send();
		exit;
	}
}
