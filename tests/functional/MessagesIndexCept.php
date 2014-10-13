<?php
$I = new TestGuy($scenario);
$I->wantTo('Create a task assigned to Jane and send message to Jane');
$task = $I->haveTask(['assigned_name' => 'Jane Doe']);

$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendGET("/messages/?format=json&task_id=" . $task->id);
$I->seeResponseCodeIs(202);
$I->canSeeResponseContainsJson();
