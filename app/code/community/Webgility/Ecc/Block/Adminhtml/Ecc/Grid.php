<?php
     
    class Webgility_Ecc_Block_Adminhtml_Ecc_Grid extends Mage_Adminhtml_Block_Widget_Grid
    {
	
        public function __construct()
        {
            parent::__construct();
            $this->setId('eccGrid');
			
            // This is the primary key of the database
            $this->setDefaultSort('id');
            $this->setDefaultDir('ASC');
            $this->setSaveParametersInSession(true);
            $this->setUseAjax(true);
        }
     
        protected function _prepareCollection()
        {
		
            $collection = Mage::getModel('ecc/ecc')->getCollection();
            $this->setCollection($collection);
            return parent::_prepareCollection();
        }
     
        protected function _prepareColumns()
        {
            $this->addColumn('id', array(
                'header'    => Mage::helper('ecc')->__('ID'),
                'align'     =>'right',
                'width'     => '50px',
                'index'     => 'id',
            ));
     
            $this->addColumn('store_id', array(
                'header'    => Mage::helper('ecc')->__('Store ID'),
                'align'     =>'left',
                'index'     => 'store_id',
            ));
			$this->addColumn('email', array(
                'header'    => Mage::helper('ecc')->__('Email'),
                'align'     =>'left',
                'index'     => 'email',
            ));
			
			$this->addColumn('wcstoremodule', array(
                'header'    => Mage::helper('ecc')->__('Store Module'),
                'align'     =>'left',
                'index'     => 'wcstoremodule',
            ));
     
            /*
            $this->addColumn('content', array(
                'header'    => Mage::helper('<module>')->__('Item Content'),
                'width'     => '150px',
                'index'     => 'content',
            ));
            */
     
            $this->addColumn('created_time', array(
                'header'    => Mage::helper('ecc')->__('Creation Time'),
                'align'     => 'left',
                'width'     => '120px',
                'type'      => 'date',
                'default'   => '--',
                'index'     => 'created_time',
            ));
     
            $this->addColumn('update_time', array(
                'header'    => Mage::helper('ecc')->__('Update Time'),
                'align'     => 'left',
                'width'     => '120px',
                'type'      => 'date',
                'default'   => '--',
                'index'     => 'update_time',
            ));   
     
     
            $this->addColumn('status', array(
     
                'header'    => Mage::helper('ecc')->__('Status'),
                'align'     => 'left',
                'width'     => '80px',
                'index'     => 'status',
                'type'      => 'options',
                'options'   => array(
                    1 => 'Active',
                    0 => 'Inactive',
                ),
            ));
     
            return parent::_prepareColumns();
        }
     
        public function getRowUrl($row)
        {
            return $this->getUrl('*/*/edit', array('id' => $row->getId()));
        }
     
        public function getGridUrl()
        {
          return $this->getUrl('*/*/grid', array('_current'=>true));
        }
     
     
    }