<?php

namespace Vreasy\Models;

use Services_Twilio;
use Services_Twilio_RestException;

class Sms 
{
    protected $twillioService = null;
    protected $messages = [];
    protected $messageSid = null;
    protected $messageFrom = null;
    protected $messageTo = null;
    protected $isError = false;
    protected $errorMessage;


    public function __construct()
    {
        $this->twillioService = new Services_Twilio(TWILLIO_SID, TWILLIO_AUTH_TOKEN);
    }
    
    public function sendMessage($phoneTo, $message)
    {
        try {
            $message = $this->twillioService->account->messages->sendMessage(TWILLIO_PHONE_NUM, $phoneTo, $message);
            $this->messageSid = $message->sid;
            $this->messageFrom = $message->from;
            $this->messageTo = $message->to;
        } catch (Services_Twilio_RestException $e) {
            $this->setError($e->getMessage());
        }
    }

    public function getMessages()
    {
        return $this->messages;
    }
    
    public function getMessageSid()
    {
        return $this->messageSid;
    }
    
    public function getSenderPhoneNumber()
    {
        return $this->messageFrom;
    }
    
    public function getRecipientPhoneNumber()
    {
        return $this->messageSid;
    }
    
    public function setError($errorMessage)
    {
        $this->isError = true;
        $this->errorMessage = $errorMessage;
    }
    
    public function isError()
    {
        return $this->isError;
    }
    
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }
    
}