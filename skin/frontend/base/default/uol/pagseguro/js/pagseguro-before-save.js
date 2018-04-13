if (typeof IWD !== typeof undefined && IWD.OPC.savePayment) {
  IWD.OPC.savePayment = IWD.OPC.savePayment.wrap(function(savePayment) {
    if (validatePagSeguroActiveMethod()) {
      if (document.querySelector('#checkout-payment-method-load .radio:checked').value == 'pagseguro_credit_card') {
        var param = {
          cardNumber: unmask(document.getElementById('creditCardNum').value),
          brand: document.getElementById('creditCardBrand').value,
          cvv: document.getElementById('creditCardCode').value,
          expirationMonth: document.getElementById('creditCardExpirationMonth').value,
          expirationYear: document.getElementById('creditCardExpirationYear').value,
          success: function (response) {
            document.getElementById('creditCardToken').value = response.card.token;
            return savePayment();
          },
          error: function (error) {
            displayError(document.getElementById('creditCardToken'));
            IWD.OPC.Checkout.hideLoader();
            IWD.OPC.Checkout.unlockPlaceOrder();
            IWD.OPC.saveOrderStatus = false;
            return false;
          },
        }
        PagSeguroDirectPayment.createCardToken(param);
      } else {
        return savePayment();
      }
    } else {
      IWD.OPC.Checkout.hideLoader();
      IWD.OPC.Checkout.unlockPlaceOrder();
      IWD.OPC.saveOrderStatus = false;
      return false;
    }
  });
}

//call events before magento payment.save() event
Payment.prototype.save = Payment.prototype.save.wrap(function(save) {
  var validator = new Validation(this.form);
  if (this.validate() && validator.validate()) {
    // Do form validations
    if (validatePagSeguroActiveMethod()) {
      if (document.querySelector('#checkout-payment-method-load .radio:checked').value == 'pagseguro_credit_card') {
        var param = {
          cardNumber: unmask(document.getElementById('creditCardNum').value),
          brand: document.getElementById('creditCardBrand').value,
          cvv: document.getElementById('creditCardCode').value,
          expirationMonth: document.getElementById('creditCardExpirationMonth').value,
          expirationYear: document.getElementById('creditCardExpirationYear').value,
          success: function (response) {
            document.getElementById('creditCardToken').value = response.card.token;
            return save();
          },
          error: function (error) {
            displayError(document.getElementById('creditCardToken'));
          },
        }
        PagSeguroDirectPayment.createCardToken(param);
      } else {
        return save();
      }
    }
  }
});

/**
 * Validate the active payment method before magento save payment
 * @returns {Boolean}
 */
function validatePagSeguroActiveMethod() {
  //payment.currentMethod
  switch (document.querySelector('#checkout-payment-method-load .radio:checked').value) {
    case "pagseguro_credit_card":
      return validateCreditCardForm();
      break;
    case "pagseguro_boleto":
      return validateBoletoForm();
      break;
    case "pagseguro_online_debit":
      return validateDebitForm();
      break;
    case "pagseguro_default_lightbox":
      return true;
      break;
    default:
      return true;
      break;
  }
}