<?php

namespace ReesSolutions\ElasticStatus\Cron;

use ReesSolutions\ElasticStatus\Model\Email;
use ReesSolutions\ElasticStatus\Model\Request;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\ObjectManagerInterface;

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
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param Email $email
     * @param Request $request
     * @param ScopeConfigInterface $scopeConfig
     * @param ObjectManagerInterface $_objectManager
     */
    public function __construct(
        Email $email,
        Request $request,
        ScopeConfigInterface $scopeConfig,
        ObjectManagerInterface $_objectManager
    ) {
        $this->email = $email;
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
        $this->_objectManager = $_objectManager;
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
