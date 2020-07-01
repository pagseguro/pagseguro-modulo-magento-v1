function validateDebitBankName(self) {
  if (!self.validity.valid) {
    displayError(self)
    return false
  } else {
    displayError(self, false)
    
    var radioBankInputs =  document.querySelectorAll('#debitbankName');
    var i;
    for (i=0; i < radioBankInputs.length; i++) {
        if (radioBankInputs[i].checked) {
          document.getElementById('onlineDebitBankName').value = radioBankInputs[i].value;
        }
        // one time selected it will be already select, so they don't need more to be required entry
        radioBankInputs[i].classList.remove('required-entry');
        // remove One Step Checkout observer from calling everytime a bank is selected
        radioBankInputs[i].stopObserving('blur');
    }

    return true
  }
}

function setOnlineDebitSessionId(session) {
  return PagSeguroDirectPayment.setSessionId(session)
}

function getSenderHash() {
  return PagSeguroDirectPayment.getSenderHash()
}

function assignOnlineDebitHash() {
  setTimeout(function () {
    document.getElementById('onlineDebitHash').value = getSenderHash()
  }, 500)
}

function validateDebitForm() {
  if (validateDocument(document.getElementById('debitDocument')) &&
    validateDebitBankName(document.getElementById('debitbankName'))
  ) {
    document.getElementById('onlineDebitHash').value = getSenderHash()
    return true;
  }

  validateDocument(document.getElementById('debitDocument'));
  validateDebitBankName(document.getElementById('debitbankName'));
  return false;
}