<?php
$I = new TestGuy($scenario);
$I->wantTo('Emulate HTTP Request from Twilio');
$task = $I->haveTask(['assigned_name' => 'John Doe']);

$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendGET("incoming-message", 
            [
            'MessageSid' => 'SM083df28652884bb4a12d57c66b7dd291',
            'SmsSid' => 'SM083df28652884bb4a12d57c66b7dd291',
            'AccountSid' => TWILLIO_SID,
            'From' => $task->assigned_phone,
            'To' => TWILLIO_PHONE_NUM,
            'Body' => 'Yeps',
            'NumMedia' => 0
            ]);
$I->seeResponseCodeIs(200);
//$I->seeResponseContains('"assigned_name":"Jane Doe"');
