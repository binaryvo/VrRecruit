<?php

use Vreasy\Models\Task;
use Vreasy\Models\Status;
use Vreasy\Models\Message;
use Vreasy\Utils\Str;

class Vreasy_IncomingMessageController extends Vreasy_Rest_Controller {

    protected $message, $messages;

    public function preDispatch()
    {
        parent::preDispatch();
        $req = $this->getRequest();
        $action = $req->getActionName();
        $contentType = $req->getHeader('Content-Type');

        switch ($action) {
            case 'index':
            case 'create':
                $this->message = Message::findLastByRecipient($req->getParam('From'));
                break;
        }

        if (!in_array($action, [
                    'index',
                    'create',
                ]) || !$req->getParam('MessageSid')) {
            throw new Zend_Controller_Action_Exception('Resource not found', 404);
        }
    }

    public function indexAction()
    {
        $req = $this->getRequest();
//        echo "................" . $this->message->task_id . "................"; die;
        if ($this->message->task_id) {
           
           // add message to database
           Message::instanceWith([
               'sid' => $req->getParam('MessageSid'),
               'task_id' => $this->message->task_id,
               'text' => $req->getParam('Body'),
               'recipient_number' => $req->getParam('From'),
               'direction' => 'from',
               'created_at' => date('Y-m-d H:i:s')
            ])->save();
           
            $task = Task::findOrInit($this->message->task_id);
            
            // can change status only for task in 'pending' status
            if ($task->status_id->name == 'pending') {
                $incomingMessage = $this->getRequest()->getParam('Body');
                list($firstWord) = explode(' ', trim($incomingMessage));

                if (Str::checkIsConfirmWord($firstWord)) {
                    $task->status_id = Status::where(['name' => 'accepted']);
                } else {
                    $task->status_id = Status::where(['name' => 'refused']);
                }
                $task->save();
                $this->view->message = "ok!";
                $this->getResponse()->setHttpResponseCode(202);
                return;
            }
        }
        
        $this->_helper->json(['error' => 'appropriate task not found']);
    }

    public function newAction()
    {
    }

    public function createAction()
    {
        $this->indexAction();
    }
    
    public function showAction()
    {
    }

    public function updateAction()
    {
    }

    public function destroyAction()
    {
    }

}
