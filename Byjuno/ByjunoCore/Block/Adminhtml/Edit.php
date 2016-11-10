<?php

namespace Byjuno\ByjunoCore\Block\Adminhtml;


class Edit extends \Magento\Backend\Block\Widget\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_edit';
        $this->_blockGroup = 'Byjuno_ByjunoCore';
        parent::_construct();
    }

    protected function _toHtml()
    {
        try {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $logger = $objectManager->get('\Byjuno\ByjunoCore\Model\Logs');
            $logview = $logger->load($this->getRequest()->getParam('id'));
            /* @var $logview \Byjuno\ByjunoCore\Model\Logs */
            $domInput = new \DOMDocument();
            $domInput->preserveWhiteSpace = FALSE;
            $domInput->loadXML($logview->getData("request"));
            $elem = $domInput->getElementsByTagName('Request');
            $elem->item(0)->removeAttribute("UserID");
            $elem->item(0)->removeAttribute("Password");

            $domInput->formatOutput = TRUE;
            libxml_use_internal_errors(true);
            $testXml = simplexml_load_string($logview->getData("response"));
            $domOutput = new \DOMDocument();
            $domOutput->preserveWhiteSpace = FALSE;
            if ($testXml) {
                $domOutput->loadXML($logview->getData("response"));
                $domOutput->formatOutput = TRUE;
                $html = '
            <a href="javascript:history.go(-1)">Back to log</a>
            <h3>Input & output XML</h3>
            <table width="50%">
                <tr>
                    <td>Input (Attributes Login & password removed)</td>
                    <td>Response</td>
                </tr>
                <tr>
                    <td width="50%" style="border: 1px solid #CCCCCC; padding: 5px; vertical-align: top;"><code style="width: 100%; word-wrap: break-word; white-space: pre-wrap;">' . htmlspecialchars($domInput->saveXml()) . '</code></td>
                    <td width="50%" style="border: 1px solid #CCCCCC; padding: 5px; vertical-align: top;"><code style="width: 100%; word-wrap: break-word; white-space: pre-wrap;">' . htmlspecialchars($domOutput->saveXml()) . '</code></td>
                </tr>
            </table>';
            } else {
                $html = '
            <a href="javascript:history.go(-1)">Back to log</a>
            <h1>Input & output XML</h1>
            <table width="50%">
                <tr>
                    <td>Input (attributes Login & password removed)</td>
                    <td>Response</td>
                </tr>
                <tr>
                    <td width="50%" style="border: 1px solid #CCCCCC; padding: 5px; vertical-align: top;"><code style="width: 100%; word-wrap: break-word; white-space: pre-wrap;">' . htmlspecialchars($domInput->saveXml()) . '</code></td>
                    <td width="50%" style="border: 1px solid #CCCCCC; padding: 5px; vertical-align: top;"><code style="width: 100%; word-wrap: break-word; white-space: pre-wrap;">Raw data: ' . $logview->getData("response") . '</code></td>
                </tr>
            </table>';
            }
        } catch(\Exception $e)
        {
            $html = '
            <a href="javascript:history.go(-1)">Back to log</a><br /><br />
            Error with XML';
        }
        return $html;
    }

}