<?php
$I = new TestGuy($scenario);
$I->wantTo('List all the tasks');

//$I->haveHttpHeader('Content-Type','application/json');
$I->haveHttpHeader('Content-Type','application/x-www-form-urlencoded');
$I->sendGET('/task?format=json');
$I->seeResponseCodeIs(200);
