function validateDocument(self) {
    console.log(self.value);
    //document.getElementById('onlineDebitBankName').value = self.value;
}

function validateDebitBankName(self) {
    console.log(self.value);
    //set block value to be used in transaction later
    document.getElementById('onlineDebitBankName').value = self.value;
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
