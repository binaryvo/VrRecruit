<?php
$I = new TestGuy($scenario);
$I->wantTo('Show a list of task statuses');
$task = $I->haveTask(['assigned_name' => 'Jane Doe']);

$I->haveHttpHeader('Content-Type','application/json');
$I->sendGET("/task-status-history/?format=json&task_id={$task->id}");
$I->seeResponseCodeIs(200);
$I->canSeeResponseContainsJson();
