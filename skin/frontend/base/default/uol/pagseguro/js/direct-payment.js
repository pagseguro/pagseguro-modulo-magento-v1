//   
//function validateDocument(self) {
//    console.log(self.value);
//    //document.getElementById('onlineDebitBankName').value = self.value;
//}
//
//function validateCreditCard(self) {
//    console.log(self.value);
//    //set block value to be used in transaction later
//    //document.getElementById('onlineDebitBankName').value = self.value;
//}
//
//function setSessionId (session) {
//   return PagSeguroDirectPayment.setSessionId(session)
//}
//
//function getSenderHash () {
//    return PagSeguroDirectPayment.getSenderHash()
//}
//
//function assignSenderHash () {
//    if (document.getElementById('creditCardHash').value === ""
//        || document.getElementById('boletoHash').value === ""
//        || document.getElementById('onlieDebitHash').value === "") 
//    {
//        setTimeout(function () {
//            var hash = getSenderHash();
//            document.getElementById('creditCardHash').value = hash;
//            document.getElementById('onlieDebitHash').value = hash;
//            document.getElementById('onlieDebitHash').value = hash;
//        }, 500)
//    }
//}
