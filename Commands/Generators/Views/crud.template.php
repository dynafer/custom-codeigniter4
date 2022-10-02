<@php

namespace {namespace};

use {useStatement};

class {class} extends BaseController
{
    private $model;
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
	{
		// Do Not Edit This Line
        parent::initController($request, $response, $logger);
        $this->model = model('{class}');
    }

    public function index(...$params)
    {
        $paramCnt = count($params);

        switch($this->method) {
            case METHOD_GET:
                if($paramCnt === 1 && is_numeric($params[0])) {
                    $this->get(intval($params[0]));
                } else {
                    $this->getAll();
                }
            case METHOD_POST:
                $this->create();
            case METHOD_PUT:
                if($paramCnt !== 1 || is_numeric($params[0])) {
                    $this->json(HTTP_NOT_FOUND);
                }
                $this->update(intval($params[0]));
            case METHOD_DELETE:
                if($paramCnt === 1 && is_numeric($params[0])) {
                    $this->delete(intval($params[0]));
                } else {
                    $this->delete();
                }
            default:
                $this->json(HTTP_NOT_FOUND);
        }
    }

    private function getAll()
    {
        $rows = $this->model->findAll();

        $this->json(HTTP_OK, [ 'rows' => $rows ]);
    }

    /**
     * Return an array of resource objects, themselves in array format
     *
     * @param int $id {varName} id
     */
    private function get($id)
    {
        ${varName} = $this->model->find($id);

        $this->json(HTTP_OK, [ 'row' => ${varName} ]);
    }

    private function create()
    {
        $this->checkValidation([
            // Any validation rules...
        ]);

        // Any form data here...

<?php if($modelType === 'entity') { ?>
        ${varName} = new \App\Entities\{class}();

        // Anything to set the data to {varName}

        $this->model->save(${varName});
<?php } else { ?>
        ${varName} = (object) [];

        // Anything to set the data to {varName}

        $this->model->insert(${varName});
<?php } ?>

        $this->json(HTTP_CREATED);
    }

    /**
     * Update a model resource, from "posted" properties
     *
     * @param int $id {varName} id
     */
    private function update($id)
    {
        $this->checkValidation([
            // Any validation rules...
        ]);

        ${varName} = $this->model->find($id);
        if(!${varName}) {
            $this->json(HTTP_BAD_REQUEST, [ 'msg' => 'No {varName} found' ]);
        }

        // Any form data here...

<?php if($modelType === 'entity') { ?>
        // Anything to set the data to {varName}

        $this->model->save(${varName});
<?php } else { ?>
        $data = [
            // Anything to set the data here...
        ];

        $this->model->where('id', ${varName}->id);
        $this->model->update($data);
<?php } ?>

        $this->json(HTTP_OK);
    }

    private function delete($id = null)
    {
        $this->checkValidation([
            // Any validation rules...
        ]);

        $ids = !is_null($id) ? [ $id ] : $this->request->getPost('ids');

		$db = db_connect();
		$db->transStart();

        $rows = 0;
        if(!empty($ids)) {
            $this->model->delete($ids);
            $rows = $this->model->affectedRows() ?? 0;
        }

        $db->transComplete();
        if($db->transStatus() === false) {
            $this->json(HTTP_INTERNAL_SERVER_ERROR, [ 'msg' => 'database error' ]);
        }

        $this->json(HTTP_OK, [
            'affected' => $rows
        ]);
    }
}
