/*
 * This file have all the pagseguro direct payment common functions, like
 * form input masks and  validations and calls to the pagseguro js api
 */

/**
 * Validate document (cpf or cnpj) according with it's length
 * @param {type} self
 * @returns {Boolean}
 */
function validateDocument(self) {
  var value = unmask(self.value)
  if (value.length === 11) {
    return validateCpf(self)
  } else if (value.length === 14) {
    return validateCnpj(self)
  } else {
    displayError(self)
    return false
  }
}

/**
 * Remove special characters, spaces
 * @param {type} el
 * @returns {unresolved}
 */
function unmask(el) {
  return el.replace(/[/ -. ]+/g, '').trim()
}

/**
 * Validate CPF
 * @param {object} self
 * @returns {Boolean}
 */
function validateCpf(self) {
  var cpf = unmask(self.value)
  var numeros, digitos, soma, i, resultado, digitos_iguais
  digitos_iguais = 1
  if (cpf.length < 11) {
    displayError(self)
    return false
  }
  for (i = 0; i < cpf.length - 1; i++)
    if (cpf.charAt(i) != cpf.charAt(i + 1)) {
      digitos_iguais = 0
      break
    }
  if (!digitos_iguais) {
    numeros = cpf.substring(0, 9)
    digitos = cpf.substring(9)
    soma = 0
    for (i = 10; i > 1; i--) {
      soma += numeros.charAt(10 - i) * i
    }
    resultado = soma % 11 < 2 ? 0 : 11 - soma % 11
    if (resultado != digitos.charAt(0)) {
      displayError(self)
      return false
    }
    numeros = cpf.substring(0, 10)
    soma = 0
    for (i = 11; i > 1; i--) {
      soma += numeros.charAt(11 - i) * i
    }
    resultado = soma % 11 < 2 ? 0 : 11 - soma % 11
    if (resultado != digitos.charAt(1)) {
      displayError(self)
      return false
    }
    displayError(self, false)
    return true
  } else {
    displayError(self)
    return false
  }
}

/**
 * Validates CNPJ
 * @param {object} self
 * @returns {Boolean}
 */
function validateCnpj(self) {
  var cnpj = unmask(self.value)
  var numbersVal
  var digits
  var sum
  var i
  var result
  var pos
  var size
  var equal_digits
  equal_digits = 1
  if (cnpj.length < 14 && cnpj.length < 15) {
    displayError(self)
    return false
  }
  for (i = 0; i < cnpj.length - 1; i++) {
    if (cnpj.charAt(i) != cnpj.charAt(i + 1)) {
      equal_digits = 0
      break
    }
  }
  if (!equal_digits) {
    size = cnpj.length - 2
    numbersVal = cnpj.substring(0, size)
    digits = cnpj.substring(size)
    sum = 0
    pos = size - 7
    for (i = size; i >= 1; i--) {
      sum += numbersVal.charAt(size - i) * pos--
      if (pos < 2) {
        pos = 9
      }
    }
    result = sum % 11 < 2 ? 0 : 11 - sum % 11
    if (result != digits.charAt(0)) {
      displayError(self)
      return false
    }
    size = size + 1
    numbersVal = cnpj.substring(0, size)
    sum = 0
    pos = size - 7
    for (i = size; i >= 1; i--) {
      sum += numbersVal.charAt(size - i) * pos--
      if (pos < 2) {
        pos = 9
      }
    }
    result = sum % 11 < 2 ? 0 : 11 - sum % 11
    if (result != digits.charAt(1)) {
      displayError(self)
      return false
    }
    displayError(self, false)
    return true
  } else {
    displayError(self)
    return false
  }
}

/**
 * Show input error
 * @param {type} target
 * @param {type} error
 * @returns {undefined}
 */
function displayError(target, error = true) {
  target = document.getElementsByClassName(target.id + '-error-message')[0]
  if (error && target.classList.contains('display-none')) {
    target.classList.remove('display-none')
  } else if (!error) {
    target.classList.add('display-none')
}
}

/**
 * Add mask for document (cpf or cnpj)
 * Important: Called on keyup event
 * @param {this} document
 * @returns {bool}
 */
function documentMask(document) {
  if (document.value.length < 14 
          || (document.value.length == 14 && (event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 46))) {
    MascaraCPF(document);
  } else {
    MascaraCNPJ(document);
  }
}

/*
 * Mask functions below adapted from
 * http://www.fabiobmed.com.br/excelente-codigo-para-mascara-e-validacao-de-cnpj-cpf-cep-data-e-telefone/
 */

/**
 * Add CNPJ mask to input
 * @param {type} cnpj
 * @returns {Boolean}
 */
function MascaraCNPJ(cnpj) {
  if (mascaraInteiro(cnpj) == false) {
    event.returnValue = false;
  }
  return formataCampo(cnpj, '00.000.000/0000-00', event);
}

/**
 * Add date mask to input
 * @param {type} cnpj
 * @returns {Boolean}
 */
function MascaraData(data) {
  if (mascaraInteiro(data) == false) {
    event.returnValue = false;
  }
  return formataCampo(data, '00/00/0000', event);
}

/**
 * Add CPF mask to input
 * @param {type} cnpj
 * @returns {Boolean}
 */
function MascaraCPF(cpf) {
  if (mascaraInteiro(cpf) == false) {
    event.returnValue = false;
  }
  return formataCampo(cpf, '000.000.000-00', event);
}

/**
 * Add credit card mask to input
 * @param {type} cnpj
 * @returns {Boolean}
 */
function creditCardMask(cc) {
  if (mascaraInteiro(cc) == false) {
    event.returnValue = false;
  }
  return formataCampo(cc, '0000 0000 0000 0000', event);
}

/**
 * Add not number mask to input
 * @param {type} cnpj
 * @returns {Boolean}
 */
function notNumberMask(someString) {
  if (maskNotNumber(someString) == false) {
    event.returnValue = false;
  }
  return true;
}

/**
 * Validate and prevent key typed event if it is a numbers
 * @returns {Boolean}
 */
function maskNotNumber() {
  if (event.keyCode == 8
          || event.keyCode == 9
          || event.keyCode == 46
          || event.keyCode < 48
          || (event.keyCode > 57 && event.keyCode < 96)
          || (event.keyCode > 105)) {

    return true;
  }
  event.returnValue = false;
  return false;
}

/**
 * Validate and prevent key typed event if it is not an integer([48,57] || [96, 105]), 
 * backspace(8), tab(9), or del(46)
 * @returns {Boolean}
 */
function mascaraInteiro() {
  if (event.keyCode == 8
          || event.keyCode == 9
          || event.keyCode == 46
          || (event.keyCode > 47 && event.keyCode < 58)
          || (event.keyCode > 95 && event.keyCode < 106)) {

    return true;
  }
  event.returnValue = false;
  return false;
}

/**
 * Format fields, according with the mask pattern
 * @param {type} campo
 * @param {type} Mascara
 * @param {type} evento
 * @returns {Boolean}
 */
function formataCampo(campo, Mascara, evento) {
  var boleanoMascara;

  var Digitato = evento.keyCode;
  exp = /\-|\.|\/|\(|\)| /g
  campoSoNumeros = campo.value.toString().replace(exp, "");

  var posicaoCampo = 0;
  var NovoValorCampo = "";
  var TamanhoMascara = campoSoNumeros.length;
  ;

  if (Digitato != 8) { // backspace 
    for (i = 0; i <= TamanhoMascara; i++) {
      boleanoMascara = ((Mascara.charAt(i) == "-") || (Mascara.charAt(i) == ".")
              || (Mascara.charAt(i) == "/"))
      boleanoMascara = boleanoMascara || ((Mascara.charAt(i) == "(")
              || (Mascara.charAt(i) == ")") || (Mascara.charAt(i) == " "))
      if (boleanoMascara) {
        NovoValorCampo += Mascara.charAt(i);
        TamanhoMascara++;
      } else {
        NovoValorCampo += campoSoNumeros.charAt(posicaoCampo);
        posicaoCampo++;
      }
    }
    campo.value = NovoValorCampo;
    return true;
  } else {
    return true;
  }
}

/**
 * Add credit card code mask to input
 * @param {type} cnpj
 * @returns {Boolean}
 */
function creditCardCodeMask(code) {
  if (mascaraInteiro(code) == false) {
    event.returnValue = false;
  }
  return true;
}

/**
 * @type {boolean}
 */
var alreadyGetPaymentMethods = false

/**
 *
 *
 */
function paymentMethods () {
  if (!alreadyGetPaymentMethods) {
    PagSeguroDirectPayment.getPaymentMethods({
      success: function (res) {
        pagseguroBoletoOptions(res)
        pagseguroCreditcardOptions(res)
        pagseguroOnlinedebitOptions(res)
      }
    })
  }
}