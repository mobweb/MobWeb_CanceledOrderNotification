<?php

class MobWeb_CanceledOrderNotification_Helper_Notification extends Mage_Core_Helper_Abstract {

    /*
     *
     * 
     *
     */
    public function send($order, $recipient) {
        //$this->queueNewOrderEmail($order, $recipient);
        if($this->sendTransactional($order, $recipient)) {
            Mage::helper('canceledordernotification')->log('Notification email sent to ' . $recipient);
        }
    }

    /*
     *
     * Send the notification email using a custom transactional email template
     *
     */
    public function sendTransactional($order, $recipient) {

        // Load the transactional email template
        $templateId = Mage::getStoreConfig('sales_email/order/order_status_notification_template');
        $template = Mage::getModel('core/email_template')->load($templateId);

        // Check if the transactional email exists
        if($template->isObjectNew()) {
            // Create a log entry
            Mage::helper('canceledordernotification')->log('Unknown or invalid template selected for notification, not sending notification: ' . print_r(array($templateId, $template), true));
            return false;
        }

        Mage::getModel('core/email_template')->sendTransactional(
            $template->getId(),
            array(
                'name' => Mage::getStoreConfig('trans_email/ident_support/name'),
                'email' =>  Mage::getStoreConfig('trans_email/ident_support/email')
            ),
            $recipient,
            $recipient,
            array(
                'order' => $order,
                'status' => $order->getStatusLabel(),
                'payment' => $order->getPayment()->getMethodInstance()->getTitle()
            ),
            Mage::app()->getStore()->getId()
        );

        return true;
    }

    /*
     *
     * Send the notification email using Magento's default "New Order" email logic.
     * Copied from Mage_Sales_Model_Order::queueNewOrderEmail and slightly modified
     *
     */
    public function queueNewOrderEmail($order, $recipient) {
        $storeId = Mage::app()->getStore()->getStoreId();
        $forceMode = true;

        // Get the destination email addresses to send copies to
        $copyTo = Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_COPY_TO, $storeId);
        $copyTo = explode(',', $copyTo);
        // $copyMethod = Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_COPY_METHOD, $storeId);
        $copyMethod = 'copy';

        // Start store emulation process
        /** @var $appEmulation Mage_Core_Model_App_Emulation */
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

        try {
            // Retrieve specified view block from appropriate design package (depends on emulated store)
            $paymentBlock = Mage::helper('payment')->getInfoBlock($order->getPayment())
                ->setIsSecureMode(true);
            $paymentBlock->getMethod()->setStore($storeId);
            $paymentBlockHtml = $paymentBlock->toHtml();
        } catch (Exception $exception) {
            // Stop store emulation process
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
            throw $exception;
        }

        // Stop store emulation process
        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

        // Retrieve corresponding email template id and customer name
        if ($order->getCustomerIsGuest()) {
            $templateId = Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_GUEST_TEMPLATE, $storeId);
            $customerName = $order->getBillingAddress()->getName();
        } else {
            $templateId = Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_TEMPLATE, $storeId);
            $customerName = $order->getCustomerName();
        }

        /** @var $mailer Mage_Core_Model_Email_Template_Mailer */
        $mailer = Mage::getModel('core/email_template_mailer');
        /** @var $emailInfo Mage_Core_Model_Email_Info */
        $emailInfo = Mage::getModel('core/email_info');
        // $emailInfo->addTo($order->getCustomerEmail(), $customerName);
        if ($copyTo && $copyMethod == 'bcc') {
            // Add bcc to customer email
            foreach ($copyTo as $email) {
                $emailInfo->addBcc($email);
            }
        }
        $mailer->addEmailInfo($emailInfo);

        // Email copies are sent as separated emails if their copy method is 'copy'
        if ($copyTo && $copyMethod == 'copy') {
            foreach ($copyTo as $email) {
                $emailInfo = Mage::getModel('core/email_info');
                $emailInfo->addTo($email);
                $mailer->addEmailInfo($emailInfo);
            }
        }

        // Set all required params and send emails
        $mailer->setSender(Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_IDENTITY, $storeId));
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($templateId);
        $mailer->setTemplateParams(array(
            'order'        => $order,
            'billing'      => $order->getBillingAddress(),
            'payment_html' => $paymentBlockHtml
        ));

        /** @var $emailQueue Mage_Core_Model_Email_Queue */
        $emailQueue = Mage::getModel('core/email_queue');
        $emailQueue->setEntityId($order->getId())
            ->setEntityType(Mage_Sales_Model_Order::ENTITY)
            ->setEventType(Mage_Sales_Model_Order::EMAIL_EVENT_NAME_NEW_ORDER)
            ->setIsForceCheck(!$forceMode);

        $mailer->setQueue($emailQueue)->send();

        return true;
    }
}