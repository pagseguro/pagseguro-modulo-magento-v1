Módulo de integração PagSeguro para Magento
===========================================
---
Descrição
---------
---
Com o módulo instalado e configurado, você pode pode oferecer o PagSeguro como opção de pagamento em sua loja. O módulo utiliza as seguintes funcionalidades que o PagSeguro oferece na forma de APIs:

 - Integração com a [API de Pagamentos]
 - Integração com a [API de Notificações]


Requisitos
----------
---
 - [Magento] Community 1.5.x, 1.6.x, 1.7.x, 1.8.x, 1.9.1.0
 - [PHP] 5.3.3+
 - [SPL]
 - [cURL]
 - [DOM]


Instalação
----------
---
 - Certifique-se de que não há instalação de outros módulos para o PagSeguro em seu sistema;
 - Baixe o repositório como arquivo zip ou faça um fork do projeto;
 - Antes de seguir com a instalação você deve desativar e limpar a compilação do Magento;
 - Na área administrativa do seu Magento, acesse o menu System -> Magento Connect -> Magento Connect Manager. Caso tenha uma versão anterior instalada faça a remoção agora;
 - Clique em "choose file", aponte para o arquivo UOL_PagSeguro-x.x.x.tgz e faça upload;
 - Caso utilize a compilação, ative-a e execute-a novamente;
 - Caso tenha uma versão anterior a 2.3 instalada, copie o arquivo *remove-module-2.2.php* para a raíz de instalação do seu Magento e execute o arquivo no browser, ex.: www.meusite.com.br/magento/remove-module-2.2.php. Siga as instruções na tela para a remoção dos arquivos.


Configuração
------------
---
Para acessar e configurar o módulo acesse o menu PagSeguro -> Configurações. As opções disponíveis estão descritas abaixo.

 - **ativar módulo**: ativa/desativa o módulo.
 - **nome de exibição**: define o nome que será utilizado para o meio de pagamento.
 - **e-mail**: e-mail cadastrado no PagSeguro.
 - **token**: token cadastrado no PagSeguro.
 - **url de redirecionamento**: ao final do fluxo de pagamento no PagSeguro, seu cliente será redirecionado automaticamente para a página de confirmação em sua loja ou então para a URL que você informar neste campo. Para ativar o redirecionamento ao final do pagamento é preciso ativar o serviço de [Pagamentos via API]. Obs.: Esta URL é informada automaticamente e você só deve alterá-la caso deseje que seus clientes sejam redirecionados para outro local.
 - **url de notificação**: sempre que uma transação mudar de status, o PagSeguro envia uma notificação para sua loja ou para a URL que você informar neste campo. Obs.: Esta URL é informada automaticamente e você só deve alterá-la caso deseje receber as notificações em outro local.
 - **charset**: codificação do seu sistema (ISO-8859-1 ou UTF-8).
 - **log**: ativa/desativa a geração de logs.
 - **diretório**: informe o local a partir da raíz de instalação do Magento onde se deseja criar o arquivo de log. Ex.: /logs/ps.log. Caso não informe nada, o log será gravado dentro da pasta ../PagSeguroLibrary/PagSeguro.log.
 - **checkout**: especifica o modelo de checkout que será utilizado. É possível escolher entre checkout padrão e checkout lightbox.
 - **listar transações abandonadas?**: ativa/desativa a pesquisa de transações que foram abandonadas no checkout do PagSeguro.
 - **transações iniciadas há no máximo (dias)**: defina a quantidade máxima de dias em que a transação foi abandonada. Ex.: se você definir 8, então somente as transações abandonadas há até 8 dias, a contar da data da compra, serão exibidas.
 - **template de e-mail**: define qual o template de email sua loja usuará para o envio do email de recuperação de venda.
 - **conciliação**: retorna todas as transações efetivadas no PagSeguro em um período de até 30 dias anteriores a data em que a consulta for realizada. A pesquisa retornará um comparativo com o status das transações em sua base local e o status atual da transação no PagSeguro, desta forma você pode identificar e atualizar transações com status divergentes.
 - **dias**: número de dias que devem ser considerados para a pesquisa de conciliação.
 - **abandonadas**: retorna uma lista com todas as transações que não foram efetivadas em um determinado espaço de tempo (ver *transações iniciadas há no máximo (dias)*). Ao ativar esta funcionalidade você pode disparar e-mail's de recuperação de venda. O e-mail conterá um link que redirecionará o comprador para o fluxo de pagamento, exatamente no ponto onde ele parou.


Dúvidas?
----------
---
Caso tenha dúvidas ou precise de suporte, acesse nosso [fórum].


Changelog
---------
---
2.4
 - Correção dos js e css que carregavam por HTTP quando o site era acessado em HTTPS;
 - Alterando estrutura do módulo de PagSeguro_PagSeguro para Uol_PagSeguro

2.3
 - Possibilidade de consultar transações no PagSeguro para conciliar os status com a base local;
 - Adicionado opção para visualização de transações abandonadas, permitindo o envio de email com um link para que o comprador possa continuar o processo de compra de onde ele parou;
 - Compatibilidade com Magento 1.9.0.1;
 - Criação de pacote instalável;
 - Ajustes em geral;

2.2
 - Ajustes no tratamento das notificações, entre outros;

2.1
 - Correção de bugs;

2.0
 - Correção de bug ao finalizar compra quando a instalação possui mais de uma store;

1.9
 - Correção de bug ao finalizar compra quando o compilador do Magento está ativado;

1.8
 - Adicionado opção para utilização do Checkout Lightbox;

1.7
 - Ajustes no tratamento de endereços;

1.6
 - Code cleanup e correção de bug;

1.5
 - Armazenar no Magento o ID da transação feita no PagSeguro;

1.4
 - Verificar se o ambiente atende os requisitos;
 - Não utilizar URLs de localhost para notificação/redirecionamento;
 - Compatibilidade com OSC-Magento-Brasil;
 - Atualização da lib PagSeguro PHP;
 - Compatibilidade com Magento 1.5.x e 1.6.x;


1.3
 - Remoção da janela intermediária de redirecionamento para o PagSeguro;
 - Agora é exibido uma mensagem amigável ao comprador caso ocorra algum erro com a compra;
 - Melhorando tratamento dos dados de endereço que são enviados ao PagSeguro.

1.2
 - Correção: Erro ao finalizar compra.

1.1

 - Adicionado: Integração com API de Notificação do PagSeguro.
 - Adicionado: Links para criação de conta e token.
 - Adicionado: Url padrão de retorno caso não seja informada.
 - Correção: Redefinição de envio do frete.
 - Correção: Ajuste no envio de taxas.

1.0

 - Versão inicial. Integração com API de Pagamento do PagSeguro.


Licença
-------
---
Copyright 2013 PagSeguro Internet LTDA.

Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except in compliance with the License. You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software distributed under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the License for the specific language governing permissions and limitations under the License.


Notas
---------
---
 - O PagSeguro somente aceita pagamento utilizando a moeda Real brasileiro (BRL).
 - Certifique-se que o email e o token informados estejam relacionados a uma conta que possua o perfil de vendedor ou empresarial.
 - Certifique-se que tenha definido corretamente o charset de acordo com a codificação (ISO-8859-1 ou UTF-8) do seu sistema. Isso irá prevenir que as transações gerem possíveis erros ou quebras ou ainda que caracteres especiais possam ser apresentados de maneira diferente do habitual.
 - Para que ocorra normalmente a geração de logs, certifique-se que o diretório e o arquivo de log tenham permissões de leitura e escrita.


Contribuições
-------------
---
Achou e corrigiu um bug ou tem alguma feature em mente e deseja contribuir?

* Faça um fork.
* Adicione sua feature ou correção de bug.
* Envie um pull request no [GitHub].


  [API de Pagamentos]: https://pagseguro.uol.com.br/v2/guia-de-integracao/api-de-pagamentos.html
  [API de Notificações]: https://pagseguro.uol.com.br/v2/guia-de-integracao/api-de-notificacoes.html
  [fórum]: http://forum.pagseguro.uol.com.br/
  [Pagamentos via API]: https://pagseguro.uol.com.br/integracao/pagamentos-via-api.jhtml
  [Notificação de Transações]: https://pagseguro.uol.com.br/integracao/notificacao-de-transacoes.jhtml
  [Magento]: https://www.magentocommerce.com/
  [PHP]: http://www.php.net/
  [SPL]: http://php.net/manual/en/book.spl.php
  [cURL]: http://php.net/manual/en/book.curl.php
  [DOM]: http://php.net/manual/en/book.dom.php
  [GitHub]: https://github.com/pagseguro/magento
