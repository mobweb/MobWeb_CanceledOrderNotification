<?php

class MobWeb_CanceledOrderNotification_Model_Observer {

    public function salesOrderSaveAfter(Varien_Event_Observer $observer) {
        $order = $observer->getOrder();
        $storeId = Mage::app()->getStore()->getStoreId();

        // Check if the order status has changed
        $originalOrderStatus = $order->getOrigData('status');
        $orderStatus = $order->getStatus();
        if($originalOrderStatus !== $orderStatus) {
            Mage::helper('canceledordernotification')->log('Order status has changed: ' . print_r($orderStatus, true));

            $notificationStatuses = Mage::getStoreConfig('sales_email/order/order_status_notification_statuses');
            $notificationStatuses = $notificationStatuses ? explode(',', $notificationStatuses) : array();

            // Check if the new order status should trigger a notification
            if(in_array($orderStatus, $notificationStatuses)) {
                Mage::helper('canceledordernotification')->log('Order status is in list of notification statuses: ' . print_r(array($orderStatus, $notificationStatuses), true));

                // Check if a recipient has been defined
                if($recipients = Mage::getStoreConfig('sales_email/order/copy_to', $storeId)) {

                    // Trigger the email(s)
                    foreach(explode(',', $recipients) AS $recipient) {
                        Mage::helper('canceledordernotification/notification')->send($order, $recipient);
                    }
                } else {
                    Mage::helper('canceledordernotification')->log('No order email copy recipient defined, not sending a notification email');
                }
            } else {
                Mage::helper('canceledordernotification')->log('Order status is not in list of notification statuses: ' . print_r(array($orderStatus, $notificationStatuses), true));
            }
        }

        return $observer;
    }
}