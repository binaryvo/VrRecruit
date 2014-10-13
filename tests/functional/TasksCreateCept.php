<?php
$I = new TestGuy($scenario);
$I->wantTo('Create a task assigned to John');

$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendPOST("/task?format=json", json_encode(array(
                'deadline' => gmdate(DATE_FORMAT),
                'assigned_phone' => '+31234567890',
                'assigned_name' => 'John Doe',
            )));
//$I->seeResponseCodeIs(200);
//$I->seeResponseContains('"assigned_name":"Jane Doe"');
