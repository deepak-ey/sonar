<?php
/**
 * Created by PhpStorm.
 * User: ankita
 * Date: 11/9/21
 * Time: 9:03 AM
 */

namespace Neosoft\CustomPayment\Model\Source;



class Customdropdown extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    const EMPTY_VALUE_LABEL = 'Null';

    public function getAllOptions() {
        if ($this->_options === null) {
            $this->_options = [
                ['label' => self::EMPTY_VALUE_LABEL , 'value' => NULL],
                ['label' => __('Net15'), 'value' => 1],
                ['label' => __('Net30'), 'value' => 2],
                ['label' => __('Net60'), 'value' => 3]
            ];
        }
        return $this->_options;
    }
}