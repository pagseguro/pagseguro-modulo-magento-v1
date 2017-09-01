function validateDocument(self) {
    console.log(self.value);
    //document.getElementById('onlineDebitBankName').value = self.value;
}

function validateCreditCard(self) {
    console.log(self.value);
    //set block value to be used in transaction later
    //document.getElementById('onlineDebitBankName').value = self.value;
}

function setCreditCardSessionId (session) {
   return PagSeguroDirectPayment.setSessionId(session)
}

function getSenderHash () {
    return PagSeguroDirectPayment.getSenderHash()
}

function assignCreditCardHash () {
    setTimeout(function () {
        document.getElementById('creditCardHash').value = getSenderHash()
    }, 500)
}

function validateCreditCard (self) {
    if (self.validity.valid && (self.value.length >= 14 && self.value.length <= 22)) {
      displayError(self, false)
      return true
    } else {
      displayError(self)
      return false
    }
  }

function unmask (el) {
    return el.replace(/[/ -. ]+/g, '').trim()
  }

function cardInstallmentOnChange (data) {
    data = JSON.parse(data)
    document.getElementById('creditCardInstallment').value = data.quantity
    document.getElementById('creditCardInstallmentValue').value = data.installmentAmount
    document.getElementById('card_total').innerHTML = 'R$ ' + data.totalAmount
  }

function cardInstallment (data) {
    var select = document.getElementById('card_installment_option')
    data = data[Object.getOwnPropertyNames(data)[0]]
    data.forEach(function (item) {
      select.options[select.options.length] = new Option(item.quantity + 'x de R$ ' + item.installmentAmount,
        JSON.stringify(item))
    })
    if (data) {
      select.removeAttribute('disabled')
    }
  }

function getInstallments (brand) {
    PagSeguroDirectPayment.getInstallments({
      amount: document.getElementById('grand_total').value,
      brand: brand,
      success: function (response) {
        cardInstallment(response.installments)
      },
      error: {},
    })
 }

function getBrand (self) {
    if (validateCreditCard(self)) {
      PagSeguroDirectPayment.getBrand({
        cardBin: unmask(document.getElementById('creditCardNum').value),
        success: function (response) {
          document.getElementById('creditCardBrand').value = response.brand.name
          getInstallments(response.brand.name)
          displayError(document.getElementById('creditCardNum'), false)
        },
        error: function () {
          displayError(document.getElementById('creditCardNum'))
        },
      })
    } else {
      displayError(document.getElementById('creditCardNum'))
    }
}

// mandar pro geral
function displayError (target, error = true) {
    target = document.getElementsByClassName(target.id + '-error-message')[0]
    if (error && target.classList.contains('display-none')) {
      target.classList.remove('display-none')
    } else if (!error) {
      target.classList.add('display-none')
    }
  }


function createCardToken () {
//    if (
//      validateCreditCard(document.querySelector('#creditCardNum')) &&
//      validateDocument(document.querySelector('#creditCardDocument')) &&
//      validateCardHolder(document.querySelector('#creditCardHolder')) &&
//      validateCreditCardHolderBirthdate(document.querySelector('#creditCardHolderBirthdate')) &&
//      validateCreditCardMonth(document.querySelector('#creditCardExpirationMonth')) &&
//      validateCreditCardYear(document.querySelector('#creditCardExpirationYear')) &&
//      validateCreditCardCode(document.querySelector('#creditCardCode')) &&
//      validateCreditCardInstallment(document.querySelector('#card_installment_option'))
//    ) {
      var param = {
        cardNumber: unmask(document.getElementById('creditCardNum').value),
        brand: document.getElementById('creditCardBrand').value,
        cvv: document.getElementById('creditCardCode').value,
        expirationMonth: document.getElementById('creditCardExpirationMonth').value,
        expirationYear: document.getElementById('creditCardExpirationYear').value,
        success: function (response) {
          //formCreditCard(response)
          document.getElementById('creditCardToken').value = response.card.token;
          
        },
        error: function (error) {},
      }
      PagSeguroDirectPayment.createCardToken(param)
//    } else {
//      validateCreditCard(document.querySelector('#creditCardNum'))
//      validateDocument(document.querySelector('#creditCardDocument'))
//      validateCardHolder(document.querySelector('#creditCardHolder'))
//      validateCreditCardHolderBirthdate(document.querySelector('#creditCardHolderBirthdate'))
//      validateCreditCardMonth(document.querySelector('#creditCardExpirationMonth'))
//      validateCreditCardYear(document.querySelector('#creditCardExpirationYear'))
//      validateCreditCardCode(document.querySelector('#creditCardCode'))
//      validateCreditCardInstallment(document.querySelector('#card_installment_option'))
//    }
  }
  
  function validateCreditCardCode (self) {
    if (self.validity.tooLong || self.validity.tooShort || !self.validity.valid) {
      displayError(self)
      return false
    } else {
      displayError(self, false)
      createCardToken();
      return true
    }
  }
  
  