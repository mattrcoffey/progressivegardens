<?php
     
    class Webgility_Ecc_Model_Mysql4_Ecc extends Mage_Core_Model_Mysql4_Abstract
    {
        public function _construct()
        {   
            $this->_init('ecc/ecc', 'id');
        }
    }