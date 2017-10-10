/** 
 * @TODO: ver de usar essa pra não salvar em cada campo do cartão de crédito 
 */
//call events before magento OneSstepChekouPayment forcePayment.save() event
//OnestepcheckoutPayment.prototype.forcesavePayment = OnestepcheckoutPayment.prototype.forcesavePayment.wrap(function(forcesavePayment){
//  var validator = new Validation(this.form);
//  if (validator.validate()) {
//    // Do form validations
//    if (validatePagSeguroActiveMethod()) {
//      //posso usar isso aqui pra gerar o hash, ver se é chamado mesmo quando vem por padrão um pagamento selecionado
//      forcesavePayment();
//    }
//  }
//});
//call pagseguro validation events before magento OneStepChekouPayment validate event, before finish checkout
OnestepcheckoutForm.prototype.validate = OnestepcheckoutForm.prototype.validate.wrap(function(validate){
  //var validator = new Validation(this.form);
  //if (validator.validate()) {
    // Do form validations
    if (validatePagSeguroActiveMethod()) {
      return validate();
    }
  //}
});

/**
 * Validate the active payment method before magento save payment
 * @returns {Boolean}
 */
function validatePagSeguroActiveMethod() {
  //OSCPayment.currentMethod
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