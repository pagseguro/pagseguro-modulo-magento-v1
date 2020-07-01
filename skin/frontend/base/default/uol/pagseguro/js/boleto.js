function setBoletoSessionId (session) {
   return PagSeguroDirectPayment.setSessionId(session)
}

function getSenderHash () {
    return PagSeguroDirectPayment.getSenderHash()
}

function assignBoletoHash () {
  setTimeout(function () {
    document.getElementById('boletoHash').value = getSenderHash()
  }, 500)
}

function validateBoletoForm () {
  if (validateDocument(document.querySelector('#bilitDocument'))) {
    document.getElementById('boletoHash').value = getSenderHash();
    return true;
  }

  return false;
}