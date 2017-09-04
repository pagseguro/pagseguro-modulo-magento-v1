//function validateDebitBankName(self) {
//    console.log(self.value);
//    //set block value to be used in transaction later
//    document.getElementById('onlineDebitBankName').value = self.value;
//}

function validateDebitBankName (self) {
  console.log('validate debit bank name');
  document.getElementById('onlineDebitBankName').value = self.value;
    if (!self.validity.valid) {
      displayError(self)
      return false
    } else {
      displayError(self, false)
      return true
    }
  }

function setOnlineDebitSessionId (session) {
   return PagSeguroDirectPayment.setSessionId(session)
}

function getSenderHash () {
    return PagSeguroDirectPayment.getSenderHash()
}

function assignOnlineDebitHash () {
    setTimeout(function () {
        document.getElementById('onlineDebitHash').value = getSenderHash()
    }, 500)
}

function validateDebitForm  () {
    if (validateDocument(document.getElementById('debitDocument')) &&
      validateDebitBankName(document.getElementById('debitbankName'))) {
        return true;
    }

    validateDocument(document.getElementById('debitDocument'));
    validateDebitBankName(document.getElementById('debitbankName'));
    return false;
  }