<?php

namespace ReesSolutions\ElasticStatus\Model;

use \Laminas\Mail\Message;
use \Laminas\Mime\Message as MimeMessage;
use \Laminas\Mime\Mime;
use \Laminas\Mime\Part as MimePart;
use \Laminas\Mail\Transport\Sendmail as SendEmail;
use \Magento\Framework\App\Config\ScopeConfigInterface;

class Email
{
    /**
     * Module list
     *
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function send($data)
    {
        //Get the general email sender
        $from = $this->scopeConfig->getValue('rs_es_config/email/send_email');

        $storeName = $this->scopeConfig->getValue(
            'general/store_information/name',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0
        ) ?: 'Magento';
        //Set the From name to be the website
        $nameFrom = "Elastic Status " . $storeName
        . $this->scopeConfig->getValue('web/unsecure/base_url', ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);

        //Get the recipient email address
        $to = $this->scopeConfig->getValue('rs_es_config/email/email');

        //Get the recipient name
        $nameTo = $this->scopeConfig->getValue('rs_es_config/email/name');

        if (!empty($data) && !empty($from) && !empty($nameFrom) && !empty($to) && !empty($nameTo)) {
            $data = $storeName . " (" .
            $this->scopeConfig->getValue('web/unsecure/base_url', ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0) .
            ")\n" . $data;
            $body = $this->getBodyParts($data);
            $message = new Message();
            $message->setBody($body);
            $message->setFrom($from, $nameFrom);
            $message->setTo($to, $nameTo);
            $message->setSubject("ElasticStatus Alert");

            $contentTypeHeader = $message->getHeaders()->get('Content-Type');
            $contentTypeHeader->setType('multipart/alternative');

            $transport = new SendEmail();
            $transport->send($message);
        }
    }

    private function getBodyParts($content)
    {
        $htmlContent = str_replace("&#13;", "<br/>", $content);
        $textContent = str_replace("&#13;", "\n", $content);

        $text = new MimePart($textContent);
        $text->type = Mime::TYPE_TEXT;
        $text->charset = 'utf-8';
        $text->encoding = Mime::ENCODING_QUOTEDPRINTABLE;

        $html = new MimePart($htmlContent);
        $html->type = Mime::TYPE_HTML;
        $html->charset = 'utf-8';
        $html->encoding = Mime::ENCODING_QUOTEDPRINTABLE;

        $body = new MimeMessage();
        $body->setParts([$text, $html]);

        return $body;
    }
}
