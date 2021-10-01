<?php
/**
 * Created by PhpStorm.
 * User: ankita
 * Date: 11/9/21
 * Time: 1:00 PM
 */

namespace Neosoft\CustomPayment\Model\Adminhtml\System\Config\Source\Customer;


class Group implements \Magento\Framework\Option\ArrayInterface
{

    protected $_options;

    public function __construct(\Magento\Customer\Model\ResourceModel\Group\CollectionFactory $groupCollectionFactory)
    {
        $this->_groupCollectionFactory = $groupCollectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->_options) {
            $this->_options = $this->_groupCollectionFactory->create()->loadData()->toOptionArray();
        }
        return $this->_options;
    }
}