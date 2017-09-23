<?php
/**
 * Created by PhpStorm.
 * User: FMbugua
 * Date: 9/23/2017
 * Time: 3:00 PM
 */

namespace Discount\Code\Observer;

use Magento\Framework\Event\ObserverInterface;

class DiscountPrice implements ObserverInterface
{
    /**
     * Handler for 'checkout_cart_after_product_add_after' event
     *@param Observer $observer
     *@return voir
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        // TODO: Implement execute() method.
        $item = $observer->getEvent()->getData('quote_item');
        $item = ($item->getParentItem()? $item->getParentItem(): $item);
        $price = $item->getProduct()->getPriceInfo()->getPrice('final_price')->getValue();
        $new_price = $price - ($price * 75 / 100); //discount the price by 75%
        $item-> setCustomPrice($new_price);
        $item->SetOriginalCustomPrice($new_price);
        $item->getProduct->setIsSuperMode(true);

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/testObserver.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($observer);
    }
}