<?php
class Watermelon_CustomStatus_Model_Observer
{

			public function afterOrderPlaced(Varien_Event_Observer $observer)
			{
				$backorders = false;
				$order = $observer->getEvent()->getOrder();

			    foreach ($order->getAllItems() as $item) {
			    	$backorderedininventory = $this->checkItemInventory($item);
			    	if (strtolower($item->getStatus()) == "partial" || strtolower($item->getStatus()) == "backordered") {
			    		$backorders = true;
			    		break;
			    	}
			    }
			    if ($backorders && $order->getStatus() != 'backordered') {
			    	$order->setStatus("backordered");
			    	$order->save();
			    } 
			    if (!$backorders && $order->getStatus() == 'backordered' && $order->getGrandTotal() > 0) {
			    	$order->setStatus("processing");
			    	$order->save();
			    } 			    			    
			}
			public function beforeInvoiceSaved(Varien_Event_Observer $observer)
			{
				$invoice = $observer->getEvent()->getInvoice();
				$order = $invoice->getOrder();

				//Mage::log($order->getStatus(),null,'backordered.log');

			    $invoiceBackorders = Mage::getSingleton('adminhtml/session')->getData('invoice_backorder_shipping');
			    $backorderMethodTotal = $order->getBackorderTotal();
			    $backorderCarrierTitle = $order->getBackorderCarrierTitle();
			    $backorderMethodTitle = $order->getBackorderMethodTitle();
			    if ($invoiceBackorders == 'yes') {
			    	$invoice->addComment('Backordered shipping charge included in total. ' . (string)$backorderCarrierTitle . ' ' . (string)$backorderMethodTitle . ' ' . (string)$backorderMethodTotal);
			    }
			}		

			private function checkItemInventory($_item) {
      			$isbackordereditems = false;
            	$inventory = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_item->getProduct());
            	if((int)$inventory->getQty() < (int)$_item->getQty()) {
                	$isbackordereditems = true;
         		}
         		return $isbackordereditems;
     		}
}

