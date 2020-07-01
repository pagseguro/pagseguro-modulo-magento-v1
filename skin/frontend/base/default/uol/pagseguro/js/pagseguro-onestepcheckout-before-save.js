/**
 * Call events before magento OneSstepChekouPayment switchToMethod event - only call
 * when the type of payment selected is relative to PagSeguro methods, preventing to
 * save all the time a PagSeguro Method is selected
 * @type OnestepcheckoutShipment
 */
OnestepcheckoutShipment.prototype.switchToMethod = OnestepcheckoutShipment.prototype.switchToMethod.wrap(
  function(switchToMethod, methodCode, forced){
    if (isPagSeguroCurrentPaymentMethod() && forced === true) {
      return false; //do nothing
    }

    // normal flow
    return switchToMethod(methodCode, forced);
  });

/**
 * Observer for checkout price modifications, like changes in shipment price or taxes
 * to call the installments value with the updated value
 * @object OnestepcheckoutForm.hidePriceChangeProcess
 * 
 */
OnestepcheckoutForm.prototype.hidePriceChangeProcess = OnestepcheckoutForm.prototype.hidePriceChangeProcess.wrap(function(hidePriceChangeProcess){
    var granTotalAmountUpdated = convertPriceStringToFloat(this.granTotalAmount.textContent);
    
    if (document.getElementById('grand_total') !== null && parseFloat(document.getElementById('grand_total').value) !== granTotalAmountUpdated) {
      document.getElementById('grand_total').value = granTotalAmountUpdated;
      if (document.getElementById('creditCardNum') !== null && document.getElementById('creditCardNum').value.length > 6) {
        getInstallments(document.getElementById('creditCardBrand').value);
      }
    }
 
    return hidePriceChangeProcess();
});

//call pagseguro validation events before magento OneStepChekouPayment validate event, before finish checkout
OnestepcheckoutForm.prototype.validate = OnestepcheckoutForm.prototype.validate.wrap(function(validate){
    if (validatePagSeguroActiveMethod()) {
      return validate();
    }
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

/**
 * Converts an brazilian real price string, like R$9,99, or 9,99, to float (9.99)
 * @param {string} priceString
 * @returns {float}
 */
function convertPriceStringToFloat(priceString){    
  if(priceString === ""){
    priceString =  0;
  }else{
    priceString = priceString.replace("R$","");
    priceString = priceString.replace(".","");
    priceString = priceString.replace(",",".");
    priceString = parseFloat(priceString);
  }
  return priceString;
}

/**
 * Return if is selected an PagSeguro Payment Method as a current payment method
 * in the checkout payment section
 * @returns {bolean}
 */
function isPagSeguroCurrentPaymentMethod() {
  currentPaymentMethod = document.querySelector('input[name="payment[method]"]:checked').value;
  return (currentPaymentMethod === 'pagseguro_credit_card'
    || currentPaymentMethod === 'pagseguro_boleto'
    || currentPaymentMethod === 'pagseguro_online_debit'
    || currentPaymentMethod === 'pagseguro_default_lightbox');
}