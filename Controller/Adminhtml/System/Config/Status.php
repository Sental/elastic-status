<?php

namespace ReesSolutions\ElasticStatus\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use ReesSolutions\ElasticStatus\Model\Request;
use Magento\Framework\App\Action\HttpPostActionInterface;
 
class Status extends Action implements HttpPostActionInterface
{
 
    protected $resultJsonFactory;
 
    /**
     * @var Data
     */
    protected $helper;

    protected $request;
 
    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Request $request
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->request = $request;
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
        } catch (\Exception $e) {
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
        }
 
        $d = new \DateTime('NOW');
        $lastCollectTime = "Sent attempted at: " . $d->format('Y-m-d\TH:i:sP');
        if (!empty($status)) {
            $status = '<span style="color: blue;">' . $status . '</span>';
            $lastCollectTime .= "&#13;" . $status;
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
