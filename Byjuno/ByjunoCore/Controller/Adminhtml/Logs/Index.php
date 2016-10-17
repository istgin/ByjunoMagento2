<?php

namespace Byjuno\ByjunoCore\Controller\Adminhtml\Logs;
use Magento\Backend\App\Action;

class Index extends Action
{
    protected $resultPageFactory = false;
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        //Call page factory to render layout and page content
        $resultPage = $this->resultPageFactory->create();
/*
        //Set the menu which will be active for this page
        $resultPage->setActiveMenu('Mageplaza_Example::blog_manage');

        //Set the header title of grid
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Blogs'));

        //Add bread crumb
        $resultPage->addBreadcrumb(__('Mageplaza'), __('Mageplaza'));
        $resultPage->addBreadcrumb(__('Hello World'), __('Manage Blogs'));
*/
        return $resultPage;
    }

}