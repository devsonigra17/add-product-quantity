<?php
namespace Dev\QuantityAdd\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\ScopeInterface;

class AddQuantity implements ObserverInterface
{
    const ENABLE = "add_product_quantity/add_quantity/enable";
    const NUMBER = "add_product_quantity/add_quantity/number";

    protected $stockState;
    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockState
    )
    {
        $this->stockState = $stockState;
        $this->scopeConfig = $scopeConfig;
    }
    public function getStatus()
    {
        $status = $this->scopeConfig->getValue(self::ENABLE,ScopeInterface::SCOPE_STORE);
        return $status;
    }
    public function getNumber()
    {
        $number = $this->scopeConfig->getValue(self::NUMBER,ScopeInterface::SCOPE_STORE);
        return $number;
    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $items = $order->getAllItems();
        foreach($items as $item)
        {
            $productId = $item->getProductId();
            $product = $this->stockState->getStockItem($productId);
            // echo "<pre>";print_r($product);exit("=============");
            $productQty = $product->getty();
            $status = $this->getStatus();
            $number = $this->getNumber();
            if($status == 1)
            {
                $addQty = $productQty + $number;
            }
            else{
                $addQty = $productQty;
            }
            // $addQty = $productQty + 100;
            $product->setQty($addQty);
            $product->save();
        }
        $order->save();
    }
}