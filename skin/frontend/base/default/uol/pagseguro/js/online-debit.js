function validateDebitBankName(self) {
  document.getElementById('onlineDebitBankName').value = self.value;
  if (!self.validity.valid) {
    displayError(self)
    return false
  } else {
    displayError(self, false)
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