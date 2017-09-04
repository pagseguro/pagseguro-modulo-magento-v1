Payment.prototype.save = Payment.prototype.save.wrap(function(save) {
  var validator = new Validation(this.form);
  if (this.validate() && validator.validate()) {
      // DO WHAT YOU WANT TO DO BEFORE SAVE
      console.log('antes de chamar o save do magento');
      
      //@TODO: tentar por o hash e o session aqui, se funcionar posso fazer isso uma vez só, pelo menos o hash o/
      
      if (validatePagSeguroActiveMethod()) {
        save(); // THIS WILL CALL CORE SAVE THAT WAS WRAPPED
      }
  }
});

function validatePagSeguroActiveMethod() {
  switch (payment.currentMethod) {
    case "pagseguro_credit_card":
      console.log('validando form CC');
      return validateCreditCardForm();
      break;
    
    case "pagseguro_boleto":
      console.log('validando form boleto');
      return validateBoletoForm();
      break;
    
    case "pagseguro_online_debit":
      console.log('validando form debito');
      validateDebitForm();
      break;

    default:
      return false;
      break;
  }

  
  //if (document.getElementById('payment_form_pagseguro_online_debit').style.display === "") {
//    console.log('validando form debito');
//    return validateDebitForm();
//  }
//  
//  //if (document.getElementById('payment_form_pagseguro_boleto').style.display === "") {
//    return validateBoletoForm();
//  }
//  
//  //if (document.getElementById('payment_form_pagseguro_credit_card').style.display === "") {
//    console.log('validando form cartao de credito');
//    return validateCreditCardForm();
//  }
}