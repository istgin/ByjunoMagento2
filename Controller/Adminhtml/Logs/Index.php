<?php

namespace Byjuno\ByjunoCore\Controller\Adminhtml\Logs;
use Magento\Backend\App\Action;

class Index extends Action
{
    protected $_resultPageFactory = false;
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Byjuno_ByjunoCore::manage_logs');
    }

    public function execute()
    {
        if ($this->getRequest()->getQuery('ajax')) {
            $this->_forward('grid');
            return false;
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Byjuno_ByjunoCore::main_menu');
        $resultPage->getConfig()->getTitle()->prepend(__('Byjuno transaction log'));
        return $resultPage;
    }

}