//call events before magento payment.save() event
Payment.prototype.save = Payment.prototype.save.wrap(function(save) {
  var validator = new Validation(this.form);
  if (this.validate() && validator.validate()) {
    // Do form validations
    if (validatePagSeguroActiveMethod()) {
      save();
    }
  }
});

/**
 * Validate the active payment method before magento save payment
 * @returns {Boolean}
 */
function validatePagSeguroActiveMethod() {
  switch (payment.currentMethod) {
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