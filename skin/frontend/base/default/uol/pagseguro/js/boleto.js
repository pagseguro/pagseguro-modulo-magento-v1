//function validateDocument(self) {
//    console.log(self);
//}

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
  console.log('dentro do form bilit');
  //activeLoading();
    if (validateDocument(document.querySelector('#bilitDocument'))) {
      return true;
    }
      //displayError(document.querySelector('#bilitDocument'),);
    return false;
    //}
  }