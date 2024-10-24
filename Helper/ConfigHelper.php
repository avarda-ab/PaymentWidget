<?php

namespace Avarda\PaymentWidget\Helper;

use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\FlagManager;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\Module\Manager;
use Magento\Store\Model\ScopeInterface;

class ConfigHelper
{
    const TEST_URL = 'https://stage.checkout-api.avarda.com/';
    const PROD_URL = 'https://checkout-api.avarda.com/';
    const STAGE_WIDGET_JS_URL = 'https://payment-widget.stage.avarda.com/cdn/payment-widget.js';
    const PROD_WIDGET_JS_URL = 'https://payment-widget.avarda.com/cdn/payment-widget.js';

    const TOKEN_PATH = 'api/partner/tokens';
    const KEY_TOKEN_FLAG = 'avarda_payment_widget_api_token';
    const MODE_CHECKOUT = 'checkout';
    const MODE_PAYMENTS = 'payments';

    const PAYMENT_METHOD_LOAN = 'Loan';
    const PAYMENT_METHOD_DIRECT_INVOICE = 'direct-invoice';

    protected string $parentModule = '';

    protected ScopeConfigInterface $config;
    protected EncryptorInterface $encryptor;
    protected FlagManager $flagManager;
    protected Resolver $localeResolver;

    /**
     * @throws Exception
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface $encryptor,
        FlagManager $flagManager,
        Manager $moduleManager,
        Resolver $localeResolver,
    ) {
        $this->config = $scopeConfig;
        $this->encryptor = $encryptor;
        $this->flagManager = $flagManager;
        $this->localeResolver = $localeResolver;

        if ($moduleManager->isEnabled('Avarda_Checkout3') && $this->isCheckoutActive()) {
            $this->parentModule = self::MODE_CHECKOUT;
        } elseif ($moduleManager->isEnabled('Avarda_Payments') && $this->isPaymentsActive()) {
            $this->parentModule = self::MODE_PAYMENTS;
        } else {
            throw new Exception('You must have either avarda/checkout3 or avarda/payments module installed and enabled');
        }
    }

    /**
     * @return bool
     */
    public function isCheckoutActive(): bool
    {
        return (bool) $this->getConfigValue('payment/avarda_checkout3_checkout/active');
    }

    /**
     * @return bool
     */
    public function isPaymentsActive(): bool
    {
        return (bool) $this->getConfigValue('avarda_payments/api/active');
    }

    /**
     * @return string|false
     */
    public function getMode()
    {
        if ($this->parentModule == self::MODE_CHECKOUT) {
            return self::MODE_CHECKOUT;
        } elseif ($this->parentModule == self::MODE_PAYMENTS) {
            return self::MODE_PAYMENTS;
        } else {
            return false;
        }
    }

    /**
     * Get My Pages configuration status
     *
     * @return bool
     */
    public function isActive()
    {
        if ($this->getMode() == self::MODE_CHECKOUT) {
            return (bool) $this->getConfigValue('avarda/payment_widget/checkout_active');
        } elseif ($this->getMode() == self::MODE_PAYMENTS) {
            return (bool) $this->getConfigValue('avarda/payment_widget/invoice_active');
        } else {
            return false;
        }
    }

    /**
     * @return bool|null
     */
    public function getTestMode()
    {
        if ($this->getMode() == self::MODE_CHECKOUT) {
            return (bool) $this->getConfigValue('payment/avarda_checkout3_checkout/test_mode');
        } elseif ($this->getMode() == self::MODE_PAYMENTS) {
            return (bool) $this->getConfigValue('avarda_payments/api/test_mode');
        } else {
            return false;
        }
    }

    /**
     * @return string
     */
    public function getClientSecret()
    {
        $secret = null;
        if ($this->getMode() == self::MODE_CHECKOUT) {
            $secret = $this->getConfigValue('payment/avarda_checkout3_checkout/client_secret');
        } elseif ($this->getMode() == self::MODE_PAYMENTS) {
            $secret = $this->getConfigValue('avarda_payments/api/client_secret');
        }
        return $secret ? $this->encryptor->decrypt($secret) : '';
    }

    /**
     * @return string
     */
    public function getClientId()
    {
        if ($this->getMode() == self::MODE_CHECKOUT) {
            return $this->getConfigValue('payment/avarda_checkout3_checkout/client_id');
        } elseif ($this->getMode() == self::MODE_PAYMENTS) {
            return $this->getConfigValue('avarda_payments/api/client_id');
        } else {
            return '';
        }
    }

    /**
     * @return string
     */
    public function getApiUrl()
    {
        if ($this->getTestMode()) {
            return self::TEST_URL;
        }
        return self::PROD_URL;
    }

    /**
     * @return string
     */
    public function getTokenUrl(): string
    {
        return $this->getApiUrl() . self::TOKEN_PATH;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->encryptor->decrypt($this->flagManager->getFlagData(self::KEY_TOKEN_FLAG));
    }

    /**
     * @param $token string
     * @return bool
     */
    public function saveNewToken(string $token)
    {
        return $this->flagManager->saveFlag(self::KEY_TOKEN_FLAG, $this->encryptor->encrypt($token));
    }

    /**
     * @param $key
     * @return mixed
     */
    protected function getConfigValue($key)
    {
        return $this->config->getValue($key, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getWidgetJsUrl()
    {
        if ($this->getTestMode()) {
            return self::STAGE_WIDGET_JS_URL;
        } else {
            return self::PROD_WIDGET_JS_URL;
        }
    }

    public function getLanguage(): string
    {
        $localeCode = $this->localeResolver->getLocale();
        $parts = explode('_', $localeCode);
        return reset($parts);
    }

    /**
     * @return string
     */
    public function getPaymentMethod($price)
    {
        if ($this->getMode() == self::MODE_CHECKOUT) {
            $threshold = $this->getConfigValue('avarda/payment_widget/checkout_widget_threshold');
        } elseif ($this->getMode() == self::MODE_PAYMENTS) {
            $threshold = $this->getConfigValue('avarda/payment_widget/invoice_widget_threshold');
        }
        if (!$threshold) {
            return '';
        } elseif ($price > $threshold) {
            return self::PAYMENT_METHOD_LOAN;
        } else {
            return self::PAYMENT_METHOD_DIRECT_INVOICE;
        }
    }

    /**
     * @return string
     */
    public function getAccountClass($paymentMethod)
    {
        if ($paymentMethod == self::PAYMENT_METHOD_LOAN) {
            $suffix = 'loan';
        } elseif ($paymentMethod == self::PAYMENT_METHOD_DIRECT_INVOICE) {
            $suffix = 'directinvoice';
        } else {
            $suffix = '';
        }
        if ($this->getMode() == self::MODE_CHECKOUT) {
            return $this->getConfigValue('avarda/payment_widget/checkout_account_class_' . $suffix);
        } elseif ($this->getMode() == self::MODE_PAYMENTS) {
            return $this->getConfigValue('avarda/payment_widget/invoice_account_class_' . $suffix);
        } else {
            return '';
        }
    }

    /**
     * Takes from config rows and parses them to json to be used in init
     * buttons.primary.fontSize='22'
     * buttons.primary.base.backgroundColor='#fff'
     *
     * @return string
     */
    public function getStyles()
    {
        if ($this->getMode() == self::MODE_CHECKOUT) {
            $customCss = $this->getConfigValue('avarda/payment_widget/checkout_custom_css');
        } elseif ($this->getMode() == self::MODE_PAYMENTS) {
            $customCss = $this->getConfigValue('avarda/payment_widget/invoice_custom_css');
        } else {
            $customCss = '';
        }
        $styles = [];
        if ($customCss && count(explode("\n", $customCss)) > 0) {
            foreach (explode("\n", $customCss) as $row) {
                if (!trim($row) && strpos($row, '=') === false) {
                    continue;
                }
                [$path, $value] = explode('=', $row);
                $value = trim($value, " \t\n\r\0\x0B;'" . '"');

                if (!$value || !$path) {
                    continue;
                }

                $pathParts = explode('.', $path);
                $prevKey = false;
                foreach ($pathParts as $part) {
                    if ($prevKey === false) {
                        if (!isset($styles[$part])) {
                            $styles[$part] = [];
                        }
                        $prevKey = &$styles[$part];
                    } else {
                        if (!isset($prevKey[$part])) {
                            $prevKey[$part] = [];
                        }
                        $prevKey = &$prevKey[$part];
                    }
                }
                $prevKey = is_numeric($value) ? floatval($value) : $value;
                unset($prevKey);
            }
        }
        $stylesJson = json_encode($styles);
        if (!$stylesJson) {
            $stylesJson = '[]';
        }

        return $stylesJson;
    }
}
