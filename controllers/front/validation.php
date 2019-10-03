<?php
/**
 *
 * Latpay - A Sample Payment Module for PrestaShop 1.7
 *
 * Order Validation Controller
 *
 * @author Latpay Team
 * @copyright 2007-2019 PrestaShop SA
 * @license https://opensource.org/licenses/afl-3.0.php */
class LatpayValidationModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        /**
         * Get current cart object from session
         */
        $cart = $this->context->cart;
        $authorized = false;
        /**
         * Verify if this module is enabled and if the cart has
         * a valid customer, delivery address and invoice address
         */
        if (!$this->module->active || $cart->id_customer == 0 || $cart->id_address_delivery == 0
            || $cart->id_address_invoice == 0) {
            Tools::redirect('index.php?controller=order&step=1');
        }
        /**
         * Verify if this payment module is authorized
         */
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'latpay') {
                $authorized = true;
                break;
            }
        }
        if (!$authorized) {
            die($this->l('This payment method is not available.'));
        }
        /** @var CustomerCore $customer */
        $customer = new Customer($cart->id_customer);
        /**
         * Check if this is a vlaid customer account
         */
        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }
        /**
         * Place the order
         */
        if ($_POST) {
            $Status = Tools::getValue("Status");
            if ($Status=='00') {
                $this->module->validateOrder(
                    (int) $this->context->cart->id,
                    Configuration::get('PS_OS_PAYMENT'),
                    (float) $this->context->cart->getOrderTotal(true, Cart::BOTH),
                    $this->module->displayName,
                    null,
                    null,
                    (int) $this->context->currency->id,
                    false,
                    $customer->secure_key
                );
                Tools::redirect('index.php?controller=order-confirmation&id_cart='.(int)$cart->id.'&id_module='.(int)$this->module->id.'&id_order='.$this->module->currentOrder.'&key='.$customer->secure_key);
            } else if ($Status=='05') {
                $this->module->validateOrder(
                    (int) $this->context->cart->id,
                    Configuration::get('PS_OS_ERROR'),
                    (float) $this->context->cart->getOrderTotal(true, Cart::BOTH),
                    $this->module->displayName,
                    null,
                    null,
                    (int) $this->context->currency->id,
                    false,
                    $customer->secure_key
                );
                      $cart_id = (int)$this->context->cart->id;   
                      $this->context->cart = new Cart($cart_id);
                      $duplicated_cart = $this->context->cart->duplicate();
                      $this->context->cart = $duplicated_cart['cart'];
                      $this->context->cookie->id_cart = (int)$this->context->cart->id;
                Tools::redirect('index.php?controller=order&step=1');
            } else if ($Status=='92') {
                // $this->module->validateOrder(
                //     (int) $this->context->cart->id,
                //     Configuration::get('PS_OS_CANCELED'),
                //     (float) $this->context->cart->getOrderTotal(true, Cart::BOTH),
                //     $this->module->displayName,
                //     null,
                //     null,
                //     (int) $this->context->currency->id,
                //     false,
                //     $customer->secure_key
                // );
                      $cart_id = (int)$this->context->cart->id;  
                      $this->context->cart = new Cart($cart_id);
                      $duplicated_cart = $this->context->cart->duplicate();
                      $this->context->cart = $duplicated_cart['cart'];
                      $this->context->cookie->id_cart = (int)$this->context->cart->id;
                Tools::redirect('index.php?controller=order&step=1');
            }
        }
    }
}