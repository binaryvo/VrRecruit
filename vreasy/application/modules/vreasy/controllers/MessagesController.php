<?php

use Vreasy\Models\Task;
use Vreasy\Models\Message;
use Vreasy\Models\Sms;

class Vreasy_MessagesController extends Vreasy_Rest_Controller {

    protected $message, $messages;

    public function preDispatch()
    {
        parent::preDispatch();
        $req = $this->getRequest();
        $action = $req->getActionName();
        $contentType = $req->getHeader('Content-Type');
        $rawBody = $req->getRawBody();

        if ($rawBody) {
            if (stristr($contentType, 'application/json')) {
                $req->setParams(['message' => Zend_Json::decode($rawBody)]);
            }
        }

        if ($req->getParam('format') == 'json') {
            switch ($action) {
                case 'index':
                    $this->messages = Message::Where(['task_id' => $req->getParam('task_id')]);
                    break;
                case 'new':
                    $this->message = new Message();
                    break;
                case 'create':
                    $this->message = Message::instanceWith($req->getParam('message'));
                    break;
                case 'show':
                    $this->message = Message::Where(['id' => $req->getParam('id')]);
                    break;
            }
        }

        if (!in_array($action, [
                    'index',
                    'new',
                    'create',
                    'show'
                ])/* && !$this->messages */) {
            throw new Zend_Controller_Action_Exception('Resource not found', 404);
        }
    }

    public function indexAction()
    {
        $this->view->messages = $this->messages;
        $this->_helper->conditionalGet()->sendFreshWhen(['etag' => $this->messages]);
    }

    public function newAction()
    {
        $this->view->message = $this->message;
        $this->_helper->conditionalGet()->sendFreshWhen(['etag' => $this->message]);
    }

    public function createAction()
    {
        if ($this->message->isValid()) {

            $task = Task::findOrInit($this->message->task_id);

            $sms = new Sms;
            $sms->sendMessage($task->assigned_phone, $this->message->text);
            
            if ($sms->isError()) {
                $this->view->errors = ['Unable to send message. ' . $sms->getErrorMessage()];
                $this->getResponse()->setHttpResponseCode(422);
            }
            
            $this->message->sid = $sms->getMessageSid();
            $this->message->direction = 'to';
            $this->message->recipeint_number = $sms->getRecipientPhoneNumber();
            
            if ($this->message->save()) {
                $this->view->message = $this->message;
            } else {
                $this->view->errors = ['create' => 'Unable to save message'];
                $this->getResponse()->setHttpResponseCode(422);
            }
        } else {
            $this->view->errors = $this->message->errors();
            $this->getResponse()->setHttpResponseCode(422);
        }
    }

    public function showAction()
    {
        $this->view->messages = $this->messages;
        $this->_helper->conditionalGet()->sendFreshWhen(
                ['etag' => [$this->messages]]
        );
    }

    public function updateAction()
    {
        Message::hydrate($this->message, $this->_getParam('text'));
        if ($this->message->isValid() && $this->message->save()) {
            $this->view->message = $this->message;
        } else {
            $this->view->errors = $this->message->errors();
            $this->getResponse()->setHttpResponseCode(422);
        }
    }

    public function destroyAction()
    {
        if ($this->message->destroy()) {
            $this->view->message = $this->message;
        } else {
            $this->view->errors = ['delete' => 'Unable to delete resource'];
        }
    }

}
