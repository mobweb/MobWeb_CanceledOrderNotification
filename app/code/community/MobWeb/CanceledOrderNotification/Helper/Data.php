<?php

class MobWeb_CanceledOrderNotification_Helper_Data extends Mage_Core_Helper_Abstract {

    public function getIsDebug() {
        return false;
    }

    public function log($msg) {
        if(Mage::helper('canceledordernotification')->getIsDebug()) {
            Mage::log($msg, NULL, 'MobWeb_CanceledOrderNotification.log');
        }
    }
}