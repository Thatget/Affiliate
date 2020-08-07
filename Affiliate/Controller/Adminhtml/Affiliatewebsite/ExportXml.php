<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliatewebsite;

use Magento\Framework\App\Filesystem\DirectoryList;

class ExportXml extends \MW\Affiliate\Controller\Adminhtml\Affiliatewebsite
{
    /**
     * Export affiliate website grid to XML format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $fileName = 'affiliate_website.xml';
        $content = $this->_view->getLayout()->createBlock(
            'MW\Affiliate\Block\Adminhtml\Affiliatewebsite\Grid'
        );
        $fileFactory = $this->_objectManager->get(
            'Magento\Framework\App\Response\Http\FileFactory'
        );

        return $fileFactory->create(
            $fileName,
            $content->getExcelFile($fileName),
            DirectoryList::VAR_DIR
        );
    }
}
