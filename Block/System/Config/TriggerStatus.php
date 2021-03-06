<?php

namespace ReesSolutions\ElasticStatus\Block\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class TriggerStatus extends Field
{
    /**
     * @var string
     */
    protected $_template = 'ReesSolutions_ElasticStatus::system/config/trigger.phtml';

    /**
     * Remove scope label
     *
     * @param  AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Return element html
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param  AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * Return ajax url for collect button
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('rselastic/system_config/status');
    }

    /**
     * getSuffix
     *
     * @return string
     */
    public function getSuffix()
    {
        return '_status';
    }

    /**
     * Generate collect button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData(
            [
                'id' => 'trigger_button_status',
                'label' => __('Show Current Status'),
            ]
        );

        return $button->toHtml();
    }
}
