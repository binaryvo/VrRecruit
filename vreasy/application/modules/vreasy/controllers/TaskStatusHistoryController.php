<?php

use Vreasy\Models\Task;
use Vreasy\Models\TaskStatusHistory;

class Vreasy_TaskStatusHistoryController extends Vreasy_Rest_Controller
{
    protected $status, $statuses;

    public function preDispatch()
    {
        parent::preDispatch();
        $req = $this->getRequest();
        $action = $req->getActionName();
        $contentType = $req->getHeader('Content-Type');
        $rawBody     = $req->getRawBody();
        if ($rawBody) {
            if (stristr($contentType, 'application/json')) {
                $req->setParams(['status' => Zend_Json::decode($rawBody)]);
            }
        }

        if($req->getParam('format') == 'json') {
            switch ($action) {
                case 'index':
                    $this->statuses = TaskStatusHistory::Where(['task_id' => $req->getParam('task_id')]);
                    break;
                case 'show':
                    $this->statuses = TaskStatusHistory::Where(['task_id' => $req->getParam('task_id')]);
                    break;
            }
        }

        if( !in_array($action, [
                'index'
            ]) && !$this->statuses) {
            throw new Zend_Controller_Action_Exception('Resource not found', 404);
        }

    }

    public function indexAction()
    {
        $this->view->statuses = $this->statuses;
        $this->_helper->conditionalGet()->sendFreshWhen(['etag' => $this->statuses]);
    }

    public function newAction()
    {
        $this->view->status = $this->status;
        $this->_helper->conditionalGet()->sendFreshWhen(['etag' => $this->status]);
    }

    public function createAction()
    {
        if ($this->status->isValid() && $this->status->save()) {
            $this->view->status = $this->status;
        } else {
            $this->view->errors = $this->status->errors();
            $this->getResponse()->setHttpResponseCode(422);
        }
    }

    public function showAction()
    {
        $this->view->statuses = $this->statuses;
        $this->_helper->conditionalGet()->sendFreshWhen(
            ['etag' => [$this->statuses]]
        );
    }

    public function updateAction()
    {
        Task::hydrate($this->status, $this->_getParam('status'));
        if ($this->status->isValid() && $this->status->save()) {
            $this->view->status = $this->status;
        } else {
            $this->view->errors = $this->status->errors();
            $this->getResponse()->setHttpResponseCode(422);
        }
    }

    public function destroyAction()
    {
        if($this->status->destroy()) {
            $this->view->status = $this->status;
        } else {
            $this->view->errors = ['delete' => 'Unable to delete resource'];
        }
    }
}
