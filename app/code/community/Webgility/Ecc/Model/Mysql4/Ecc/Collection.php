<?php
     
    class Webgility_Ecc_Model_Mysql4_Ecc_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
    {
        public function _construct()
        {
            //parent::__construct();
            $this->_init('ecc/ecc');
        }
    }