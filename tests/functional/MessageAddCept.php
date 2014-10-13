<?php
$I = new TestGuy($scenario);
$I->wantTo('Create a task assigned to Jane and send message to Jane');
$task = $I->haveTask(['assigned_name' => 'Jane Doe']);

$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendPost("/messages/?format=json&task_id=" . $task->id, 
        [
            'task_id' => $task->id,
            'text '=> 'Do you accept a task?',
            'recipient_number' => $task->assigned_phone,
            'direction' => 'from',
            'created_at' => gmdate(DATE_FORMAT)
        ]);
$I->seeResponseCodeIs(200);
