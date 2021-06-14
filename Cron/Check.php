<?php

namespace ReesSolutions\ElasticStatus\Cron;

use ReesSolutions\ElasticStatus\Model\Email;
use ReesSolutions\ElasticStatus\Model\Request;
use \Magento\Framework\App\Config\ScopeConfigInterface;

class Check
{
    /**
     * @var Email
     */
    private $email;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param Email $scopeConfig
     */
    public function __construct(
        Email $email,
        Request $request,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->email = $email;
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
    }

    public function execute()
    {
        if ($this->scopeConfig->getValue('rs_es_config/email/enable')) {
            $status = false;
            try {
                $status = $this->request->execute();
            } catch (\Exception $e) {
                $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            }

            if ($status && stripos($status, 'error') !== false && stripos($status, 'No response') !== false) {
                $this->email->send($status);
            }
        }
    }
}
