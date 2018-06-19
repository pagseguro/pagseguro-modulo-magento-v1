# Módulo de integração PagSeguro para Magento

## Descrição

Com o módulo instalado e configurado, você pode pode oferecer o PagSeguro como opção de pagamento em sua loja. O módulo utiliza as seguintes funcionalidades que o PagSeguro oferece na forma de APIs:

- Integração com a [API de Pagamentos](https://dev.pagseguro.uol.com.br/documentacao/pagamentos)
- Integração com a [API de Notificações](https://pagseguro.uol.com.br/v2/guia-de-integracao/api-de-notificacoes.html#!rmcl)

## Índice
**[Requisitos](#requisitos)**<br>
**[Instalação](#instalação)**<br>
**[Configuração](#configuração)**<br>
**[Transações](#transações)**<br>
**[Dúvidas](#dúvidas)**<br>
**[Changelog](#changelog)**<br>
**[Notas](#notas)**<br>
**[Contribuições](#contribuições)**<br>
**[Licença](#licença)**<br>

## Requisitos
- [Magento](https://www.magentocommerce.com/) Community 1.9.0 até 1.9.3.8
- [PHP](http://www.php.net/) 5.4.27+, 5.5.x+, 5.6.x+
- [SPL](http://php.net/manual/en/book.spl.php)
- [cURL](http://php.net/manual/en/book.curl.php)
- [SimpleXML](http://php.net/manual/en/book.simplexml.php)

### Compatibilidade com plugins de Checkout
- IWD OnePageCheckout: versão 4.3.6

## Instalação
> **ATENÇÃO** Recomendamos que seja feito backup da sua loja Magento antes de realizar qualquer instalação ou atualização do módulo.

- Certifique-se de que não há instalação de outros módulos para o PagSeguro em seu sistema;
- Caso utilize a compilação do Magento, desative-a e limpe-a *(Sistema -> Ferramentas -> Compilação)*;
- Baixe a última versão do módulo **[nesse link](https://github.com/pagseguro/magento/raw/master/UOL_PagSeguro-3.16.6.tgz)** ou então baixe o repositório como arquivo zip através do botão do GitHub;
- Na área administrativa do seu Magento, acesse o menu *Sistema/System -> Magento Connect -> Magento Connect Manager*. Caso tenha uma versão anterior do módulo instalada faça a remoção agora;
- No Magento Connect Manger, dentro da seção Direct package file upload, clique em **Escolher arquivo/Choose file**, selecione o arquivo UOL_PagSeguro-x.x.x.tgz (baixado anteriormente), clique no botão de upload e acompanhe a instalação do módulo no console da página;
- Caso utilize a compilação, volte para a área administrativa do Magento, ative-a e execute-a novamente;
- Pronto, ao finalizar o processo o módulo do PagSeguro estará instalando no seu Magento! Siga para a [próxima seção](#configuração) para configurar e começar a usar o módulo.

> Caso tenha uma versão do módulo do PagSeguro anterior à 2.3 instalada, copie o arquivo *remove-module-2.2.php* para a raíz de instalação do seu Magento e execute o arquivo no browser, ex.: www.meusite.com.br/magento/remove-module-2.2.php. Siga as instruções na tela para a remoção dos arquivos.

Configuração
------------
---
Para acessar e configurar o módulo acesse o menu PagSeguro -> Configurações. As opções disponíveis estão descritas abaixo.

 -------------------------
 **Configurações Gerais**
 
 - **ambiente**: especifica em que ambiente as transações serão feitas *(produção/sandbox)*.
 - **e-mail**: e-mail cadastrado no PagSeguro.
 - **token**: token cadastrado no PagSeguro.
 - **url de redirecionamento**: ao final do fluxo de pagamento no PagSeguro, seu cliente será redirecionado automaticamente para a página de confirmação em sua loja ou então para a URL que você informar neste campo. Para ativar o redirecionamento ao final do pagamento é preciso ativar o serviço de [Pagamentos via API]. Obs.: Esta URL é informada automaticamente e você só deve alterá-la caso deseje que seus clientes sejam redirecionados para outro local.
 - **url de notificação**: sempre que uma transação mudar de status, o PagSeguro envia uma notificação para sua loja. 
     - *Observação: Esta URL só deve ser alterada caso você deseje receber as notificações em outro local.*
 - **charset**: codificação do seu sistema (ISO-8859-1 ou UTF-8).
 - **ativar log**: ativa/desativa a geração de logs.
 - **diretório**: informe o local e nome do arquivo a partir da raíz de instalação do Magento onde se deseja criar o arquivo de log. Ex.: /pagseguro.log. 
     - *Por padrão o módulo virá configurado para salvar o arquivo de log em /var/pagseguro.log*.
 - **listar transações abandonadas?**: ativa/desativa a pesquisa de transações que foram abandonadas no checkout do PagSeguro.
 - **template de e-mail**: define qual o template de email sua loja usuará para o envio do email de recuperação de venda.
 - **oferecer desconto para ...**: ativa/desativa desconto para checkouts utilizando este meio de pagamento
 - **percentual de desconto**: define o percentual de desconto a ser concedido para o meio de pagamento escolhido
 - **transações -> abandonadas**: permite consultar as transações que foram abandonadas nos últimos 10 dias, desta forma você pode enviar emails de recuperação de venda. O e-mail conterá um link que redirecionará o comprador para o fluxo de pagamento, exatamente no ponto onde ele parou.
 - **habilitar recuperação de carrinho**: Habilita a recuperação de carrinho do PagSeguro. (por padrão está desabilitada)
 - **listar parcelamento**: Habilita a exibição de uma listagem de parcelas na tela de visualização do produto. (Irá exibir o maior parcelamento disponível para o produto na tela de exibição do mesmo)
 
 -------------------------
  **Configurar Status**

  Não é necessário alterar essa configuração. O módulo já vem com uma configuração padrão de status mas, caso deseje personalizar, esta seção permite configurar para cada status do Pagseguro o respectivo status do Magento (opcional).
  - **pendente**: define qual state do Magento será associado ao status Pendente do PagSeguro.
  - **aguardando pagamento**: define qual state do Magento será associado ao status Aguardando pagamento do PagSeguro.
  - **em análise**: define qual state do Magento será associado ao status Em análise do PagSeguro.
  - **paga**: define qual state do Magento será associado ao status Paga do PagSeguro.
  - **disponível**: define qual state do Magento será associado ao status Disponível do PagSeguro.
  - **em disputa**: define qual state do Magento será associado ao status Em disputa do PagSeguro.
  - **devolvida**: define qual state do Magento será associado ao status Devolvida do PagSeguro.
  - **cancelada**: define qual state do Magento será associado ao status Cancelada do PagSeguro.
  - **chargeback debitado**: define qual state do Magento será associado ao status Chargeback Debitado do PagSeguro.
  - **em contestação**: define qual state do Magento será associado ao status Em Contestação do PagSeguro.
  
 -------------------------
 **Configurar Tipos de Checkout**
 
 - *PagSeguro (Padrão ou Lightbox)*
   - **ativar**: ativa/desativa o meio de pagamento PagSeguro (padrão ou lightbox).
   - **nome de exibição**: define o nome que será utilizado para o meio de pagamento na tela de checkout.
   - **checkout**: especifica o modelo de checkout que será utilizado. É possível escolher entre checkout padrão ou checkout lightbox.
 
 
 - *Checkout Transparente - Cartão de Crédito*
   - **ativar**: ativa/desativa o meio de pagamento Checkout Transparente - Cartão de Crédito.
   - **nome de exibição**: define o nome que será utilizado para esse meio de pagamento na tela de checkout.
 
 
 - *Checkout Transparente - Boleto Bancário*
   - **ativar**: ativa/desativa o meio de pagamento Checkout Transparente - Boleto Bancário.
   - **nome de exibição**: define o nome que será utilizado para esse meio de pagamento na tela de checkout.
 
 
 - *Checkout Transparente - Débito Online*
   - **ativar**: ativa/desativa o meio de pagamento Checkout Transparente - Débito Online.
   - **nome de exibição**: define o nome que será utilizado para esse meio de pagamento na tela de checkout.
 
 
 Transações
------------
---
 Para realizar consultas e outras operações acesse o menu PagSeguro -> Transações. . As opções disponíveis estão descritas abaixo:
 
 - **transações -> abandonadas**: permite pesquisar as transações que foram abandonadas dentro da quantidade de dias definidos para a pesquisa.
 - **transações -> cancelamento**: esta pesquisa retornará todas as transações que estejam com status "em análise" e "aguardando pagamento", dentro da quantidade de dias definidos para a pesquisa. Desta forma você pode solicitar o cancelamento destas transações.
 - **transações -> conciliação**: permite consultar as transações efetivadas no PagSeguro nos últimos 30 dias. A pesquisa retornará um comparativo com o status das transações em sua base local e o status atual da transação no PagSeguro, desta forma você pode identificar e atualizar transações com status divergentes.
 - **transações -> estorno**: esta pesquisa retornará todas as transações que estejam com status "paga", "disponível" e "em disputa", dentro da quantidade de dias definidos para a pesquisa. Desta forma você pode solicitar o estorno dos valores pagos para seus compradores.
 - **transações -> listar transações**: esta pesquisa retorna as últimas transações realizadas pela sua loja no PagSeguro, permitindo utilizar diversos filtros (data, id do pedido, do pagseguro, status) ao realizar uma consulta. A partir do resultado dessa consulta é possível ver os detalhes de cada pedido no PagSeguro através da ação "Ver detalhes transação".
 - **requisitos**: exibe se os pré-requisitos básicos para o correto funcionamento do módulo estão sendo atendidos
 >  É aconselhável que antes de usar as funcionalidades de **estorno** ou **cancelamento** você faça a **conciliação** de suas transações para obter os status mais atuais.

## Dúvidas

Caso tenha dúvidas ou precise de suporte, acesse nosso [fórum](https://comunidade.pagseguro.uol.com.br/hc/pt-br/community/topics).</p>

## Changelog
Para consultar o log de alterações acesse o arquivo [CHANGELOG.md](CHANGELOG.md).

## Notas
- A versão 2.x deste respositório está depreciada en encontra-se [na branch 2.8](https://github.com/pagseguro/magento/tree/2.8) ou através da sua [release](https://github.com/pagseguro/magento/releases/tag/2.8.0).
- O PagSeguro somente aceita pagamento utilizando a moeda Real brasileiro (BRL).
- Certifique-se que o email e o token informados estejam relacionados a uma conta que possua o perfil de vendedor ou empresarial.
- Certifique-se que tenha definido corretamente o charset de acordo com a codificação (ISO-8859-1 ou UTF-8) do seu sistema. Isso irá prevenir que as transações gerem possíveis erros ou quebras ou ainda que caracteres especiais possam ser apresentados de maneira diferente do habitual.
- Para que ocorra normalmente a geração de logs, certifique-se que o diretório e o arquivo de log tenham permissões de leitura e escrita.

## Contribuições
Todas as contribuições devem ser feitas via Pull Request na **[branch de desenvolvimento](https://github.com/pagseguro/magento/tree/desenvolvimento)**, seguindo os passos:
- Faça um fork
- Adicione sua feature ou correção de bug
- Envie um pull request no [GitHub](https://github.com/pagseguro/magento/tree/desenvolvimento)

Licença
-------
---
Copyright 2013 PagSeguro Internet LTDA.

Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except in compliance with the License. You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software distributed under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the License for the specific language governing permissions and limitations under the License.
