<?php

/**
 * Created by PhpStorm.
 * User: ankita
 * Date: 11/9/21
 * Time: 8:40 AM
 */

namespace Neosoft\CustomPayment\Model\Payment;

use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Payment\Model\Method\Logger;
use Neosoft\CustomPayment\Model\Source\CustomDropdown;

/**
 * Pay In Store payment method model
 */
class Simple extends \Magento\Payment\Model\Method\AbstractMethod {

    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = 'simple';

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Neosoft\CustomPayment\Model\Source\CustomDropdown
     */
    protected $customDropdown;

    const XML_PATH_ALLOWED_CUSTOMER_GROUP = 'payment/simple/customergroup';
    const DROPDOWN_ATTRIBUTE_CODE = 'custom_dropdown';

    public function __construct(\Magento\Framework\Model\Context $context,
            \Magento\Framework\Registry $registry,
            \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
            \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
            \Magento\Payment\Helper\Data $paymentData,
            \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
            Logger $logger,
            \Magento\Customer\Model\Session $customerSession,
            \Magento\Checkout\Model\Session $checkoutSession,
            \Magento\Catalog\Model\ProductFactory $productFactory,
            CustomDropdown $customDropdown,
            \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
            \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
            array $data = [],
            DirectoryHelper $directory = null
    ) {
        $this->_customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->productFactory = $productFactory;
        $this->customDropdown = $customDropdown;
        parent::__construct($context, $registry, $extensionFactory, $customAttributeFactory, $paymentData, $scopeConfig, $logger, $resource, $resourceCollection, $data, $directory);
    }

    /**
     * Is active
     *
     * @param int|null $storeId
     * @return bool
     *
     */
    public function isActive($storeId = null) {
        return (bool) (int) $this->isCustomerAllowed($storeId);
    }

    /**
     * Retrieve payment method title
     *
     * @return string
     *
     */
    public function getTitle() {
        if ($this->getPaymentMethodCustomTitle() && $this->getPaymentMethodCustomTitle() != CustomDropdown::EMPTY_VALUE_LABEL) {
            return $this->getPaymentMethodCustomTitle();
        }
        return $this->getConfigData('title');
    }

    /**
     *  Get Current Customer Group Id
     *
     * @return int
     */
    public function getCustomerGroupId() {
        if ($this->_customerSession->isLoggedIn()):
            return $this->_customerSession->getCustomer()->getGroupId();
        endif;
        return 0;
    }

    /**
     * Is Customer Allowed for payment method
     *
     * @param int|null $storeId
     * @return bool
     *
     */
    public function isCustomerAllowed($storeId = null) {
        $customerGroupId = $this->getCustomerGroupId();
        $allowedCustomer = $this->_scopeConfig->getValue(self::XML_PATH_ALLOWED_CUSTOMER_GROUP, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        $allowedCustomer = explode(',', $allowedCustomer);
        if (in_array($customerGroupId, $allowedCustomer)) {
            return true;
        }
        return false;
    }

    /**
     * Get Payment Method Title According to Product Attribute value
     *
     * @return string
     *
     */
    public function getPaymentMethodCustomTitle() {
        $attrValue = $this->getHigestAttributeValue();
        $options = $this->getAllOptions();
        foreach ($options as $option) {
            if ($option['value'] == $attrValue) {
                return $option['label'];
            }
        }
        return false;
    }

    /**
     * Get quote object associated with cart. By default it is current customer session quote
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuoteData() {
        return $this->checkoutSession->getQuote();
    }

    /**
     * Get product ids added in cart
     *
     * @return array
     */
    public function getAllQuoteProducts() {
        $quote = $this->getQuoteData();
        $allItems = $quote->getAllVisibleItems();
        $productIds = [];
        foreach ($allItems as $item) {
            $productIds[] = $item->getProductId();
        }
        return $productIds;
    }

    /**
     * Get Highest Product Attribute Value if multiple products are added in cart
     *
     * @return int
     *
     */
    public function getHigestAttributeValue() {
        $productIds = $this->getAllQuoteProducts();
        $attrValue = [];
        foreach ($productIds as $productId) {
            $product = $this->productFactory->create()->load($productId);
            $attrValue[] = $product->getData(self::DROPDOWN_ATTRIBUTE_CODE);
        }
        return max($attrValue);
    }

    /**
     * Get All options of Custom Dropdown attribute
     *
     * @return array
     *
     */
    public function getAllOptions() {
        return $this->customDropdown->getAllOptions();
    }

}
