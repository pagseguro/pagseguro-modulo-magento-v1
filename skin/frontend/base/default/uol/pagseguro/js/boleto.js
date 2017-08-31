function validateDocument(self) {
    console.log(self);
}

function setSessionId (session) {
   return PagSeguroDirectPayment.setSessionId(session)
}

function getSenderHash () {
    return PagSeguroDirectPayment.getSenderHash()
}

function assignSenderHash () {
    setTimeout(function () {
        document.getElementById('boletoHash').value = getSenderHash()
    }, 500)
}