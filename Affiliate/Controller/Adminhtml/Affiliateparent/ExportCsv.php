<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliateparent;

use Magento\Framework\App\Filesystem\DirectoryList;

class ExportCsv extends \MW\Affiliate\Controller\Adminhtml\Affiliateparent
{
    /**
     * Export affiliate customers grid to CSV format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $fileName = 'customer.csv';
        $content = $this->_view->getLayout()->createBlock(
            'MW\Affiliate\Block\Adminhtml\Affiliateparent\Grid'
        );
        $fileFactory = $this->_objectManager->get(
            'Magento\Framework\App\Response\Http\FileFactory'
        );

        return $fileFactory->create(
            $fileName,
            $content->getCsvFile($fileName),
            DirectoryList::VAR_DIR
        );
    }
}
