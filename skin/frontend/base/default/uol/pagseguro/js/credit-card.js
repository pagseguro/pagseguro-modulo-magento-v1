function setCreditCardSessionId(session) {
  return PagSeguroDirectPayment.setSessionId(session)
}

function getSenderHash() {
  return PagSeguroDirectPayment.getSenderHash()
}

function assignCreditCardHash() {
  setTimeout(function () {
    document.getElementById('creditCardHash').value = getSenderHash()
  }, 500)
}

function validateCreditCard(self) {
  selfNumbers = removeLetters(unmask(self.value));
  if (self.validity.valid && selfNumbers !== "" && (self.value.length >= 14 && self.value.length <= 22)) {
    displayError(self, false)
    return true
  } else {
    displayError(self)
    return false
  }
}

function validateCardHolder (self) {
    selfLetters = removeNumbers(unmask(self.value));
    if (self.validity.tooShort || !self.validity.valid || selfLetters === "") {
      displayError(self)
      return false
    } else {
      displayError(self, false)
      return true
    }
  }
  
  function validateCreditCardHolderBirthdate (self) {
    var val = self.value
    var date_regex = /^(0[1-9]|1\d|2\d|3[01])\/(0[1-9]|1[0-2])\/(19|20)\d{2}$/
    if (!(date_regex.test(val))) {
      displayError(self)
      return false
    } else {
      displayError(self, false)
      return true
    }
  }
  
  function validateCreditCardMonth (self) {
    if (self.validity.valid) {
      displayError(self, false)
      return true
    } else {
      displayError(self)
      return false
    }
  }
  
  function validateCreditCardYear (self) {
    if (self.validity.valid) {
      displayError(self, false)
      return true
    } else {
      displayError(self)
      return false
    }
  }

function cardInstallmentOnChange(data) {
  data = JSON.parse(data)
  document.getElementById('creditCardInstallment').value = data.quantity
  document.getElementById('creditCardInstallmentValue').value = data.installmentAmount
  document.getElementById('card_total').innerHTML = 'R$ ' + data.totalAmount
}

function cardInstallment(data) {
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

function validateCreditCardInstallment (self) {
    if (self.validity.valid) {
      displayError(self, false)
      return true
    } else {
      displayError(self)
      return false
    }
  }

function getInstallments(brand) {
  PagSeguroDirectPayment.getInstallments({
    amount: document.getElementById('grand_total').value,
    brand: brand,
    success: function (response) {
      cardInstallment(response.installments)
    },
    error: {},
  })
}

function getBrand(self) {
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

function createCardToken() {
    if (validateCreateToken()) {
      var param = {
        cardNumber: unmask(document.getElementById('creditCardNum').value),
        brand: document.getElementById('creditCardBrand').value,
        cvv: document.getElementById('creditCardCode').value,
        expirationMonth: document.getElementById('creditCardExpirationMonth').value,
        expirationYear: document.getElementById('creditCardExpirationYear').value,
        success: function (response) {
          document.getElementById('creditCardToken').value = response.card.token;
        },
        error: function (error) {
          console.log(error);
        },
    }

    PagSeguroDirectPayment.createCardToken(param)
  }
}

function validateCreditCardCode(self, createToken) {
  if (self.validity.tooLong || self.validity.tooShort || !self.validity.valid) {
    displayError(self)
    return false
  } else {
    displayError(self, false)
    if (createToken === true && validateCreateToken()) {
      createCardToken();
    }
    return true
  }
}

function validateCreditCardForm() {
  if (
   validateCreditCard(document.querySelector('#creditCardNum')) &&
   validateDocument(document.querySelector('#creditCardDocument')) &&
   validateCardHolder(document.querySelector('#creditCardHolder')) &&
   validateCreditCardHolderBirthdate(document.querySelector('#creditCardHolderBirthdate')) &&
   validateCreditCardMonth(document.querySelector('#creditCardExpirationMonth')) &&
   validateCreditCardYear(document.querySelector('#creditCardExpirationYear')) &&
   validateCreditCardCode(document.querySelector('#creditCardCode'), false) &&
   validateCreditCardInstallment(document.querySelector('#card_installment_option'))
  ) {

   if (document.getElementById('creditCardToken').value === "") {
     createCardToken();
   }
   return true;
  }
  
  validateCreditCard(document.querySelector('#creditCardNum'))
  validateDocument(document.querySelector('#creditCardDocument'))
  validateCardHolder(document.querySelector('#creditCardHolder'))
  validateCreditCardHolderBirthdate(document.querySelector('#creditCardHolderBirthdate'))
  validateCreditCardMonth(document.querySelector('#creditCardExpirationMonth'))
  validateCreditCardYear(document.querySelector('#creditCardExpirationYear'))
  validateCreditCardCode(document.querySelector('#creditCardCode'), false)
  validateCreditCardInstallment(document.querySelector('#card_installment_option'))
  return false;
}

function validateCreateToken() {
  if(validateCreditCard(document.querySelector('#creditCardNum')) 
    && validateCreditCardMonth(document.querySelector('#creditCardExpirationMonth'))
    && validateCreditCardYear(document.querySelector('#creditCardExpirationYear'))
    && validateCreditCardCode(document.querySelector('#creditCardCode'), false)
    && document.getElementById('creditCardBrand').value !== ""
    ) {
      return true
  }

  validateCreditCard(document.querySelector('#creditCardNum'));
  validateCreditCardMonth(document.querySelector('#creditCardExpirationMonth'));
  validateCreditCardYear(document.querySelector('#creditCardExpirationYear'));
  validateCreditCardCode(document.querySelector('#creditCardCode'), false);

  return false;
}

/**
 * Return the value of 'el' without letters
 * @param {string} el
 * @returns {string}
 */
function removeLetters(el) {
  return el.replace(/[a-zA-Z]+/, '');

}

/**
 * Return the value of 'el' without numbers
 * @param {string} el
 * @returns {string}
 */
function removeNumbers(el) {
  return el.replace(/[0-9]+/, '');
}