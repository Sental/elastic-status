<?php

namespace ReesSolutions\ElasticStatus\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use ReesSolutions\ElasticStatus\Model\Request;
use ReesSolutions\ElasticStatus\Model\Email as Sender;
use Magento\Framework\App\Action\HttpPostActionInterface;
 
class Email extends Action implements HttpPostActionInterface
{
 
    protected $resultJsonFactory;
 
    /**
     * @var Data
     */
    protected $helper;

    protected $request;

    protected $email;
 
    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Request $request,
        Sender $email
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->request = $request;
        $this->email = $email;
        parent::__construct($context);
    }
 
    /**
     * Trigger review notification email
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $boolResult = false;
        $status = false;
        try {
            $boolResult = true;
            $status = $this->request->execute();
            $this->email->send("The Full Elasticsearch Status is displayed because this status email was manually sent.\n" . $status);
        } catch (\Exception $e) {
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
        }
 
        $d = new \DateTime('NOW');
        $lastCollectTime = "Sent attempted at: " . $d->format('Y-m-d\TH:i:sP');
        if (!empty($status)) {
            $lastCollectTime .= "\n" . $status;
        }
        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultJsonFactory->create();
 
        return $result->setData(['success' => $boolResult, 'time' => $lastCollectTime]);
    }
 
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('ReesSolutions_ElasticStatus::es_config');
    }
}
