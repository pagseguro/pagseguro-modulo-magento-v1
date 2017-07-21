# Módulo de integração PagSeguro para Magento

## Descrição

Com o módulo instalado e configurado, você pode pode oferecer o PagSeguro como opção de pagamento em sua loja. O módulo utiliza as seguintes funcionalidades que o PagSeguro oferece na forma de APIs:

- Integração com a [API de Pagamentos](https://dev.pagseguro.uol.com.br/documentacao/pagamentos)
- Integração com a [API de Notificações](https://pagseguro.uol.com.br/v2/guia-de-integracao/api-de-notificacoes.html#!rmcl)

## Requisitos
- [Magento](https://www.magentocommerce.com/) Community 1.9.0 até 1.9.3.3 (ainda em fase de testes nas versões 1.6.x, 1.7.x, 1.8.x)
- [PHP](http://www.php.net/) 5.4.x+, 5.5.x+, 5.6.x+ (ainda em fase de testes nas versões 7.x)
- [SPL](http://php.net/manual/en/book.spl.php)
- [cURL](http://php.net/manual/en/book.curl.php)
- [SimpleXML](http://php.net/manual/en/book.simplexml.php)

## Instalação

- Certifique-se de que não há instalação de outros módulos para o PagSeguro em seu sistema;
- Baixe o repositório como arquivo zip ou [clique aqui](https://github.com/pagseguro/magento/raw/master/UOL_PagSeguro-3.0.0.tgz);
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
 - **nome de exibição**: define o nome que será utilizado para o meio de pagamento na tela de checkout.
 - **ambiente**: especifica em que ambiente as transações serão feitas.
 - **e-mail**: e-mail cadastrado no PagSeguro.
 - **token**: token cadastrado no PagSeguro.
 - **url de redirecionamento**: ao final do fluxo de pagamento no PagSeguro, seu cliente será redirecionado automaticamente para a página de confirmação em sua loja ou então para a URL que você informar neste campo. Para ativar o redirecionamento ao final do pagamento é preciso ativar o serviço de [Pagamentos via API]. Obs.: Esta URL é informada automaticamente e você só deve alterá-la caso deseje que seus clientes sejam redirecionados para outro local.
 - **url de notificação**: sempre que uma transação mudar de status, o PagSeguro envia uma notificação para sua loja ou para a URL que você informar neste campo. Por padrão deve-se utilizar a seguinte URL, substituindo `minhaloja` pelo endereço da sua loja: http://minhaloja/index.php/pagseguro/notification/send/  Obs.: Esta URL só deve ser alterada caso você deseje receber as notificações em outro local.
 - **charset**: codificação do seu sistema (ISO-8859-1 ou UTF-8).
 - **ativar log**: ativa/desativa a geração de logs.
 - **diretório**: informe o local e nome do arquivo a partir da raíz de instalação do Magento onde se deseja criar o arquivo de log. Ex.: /pagseguro.log. 
 - **checkout**: especifica o modelo de checkout que será utilizado. É possível escolher entre checkout padrão, checkout lightbox e checkout transparente.
 - **listar transações abandonadas?**: ativa/desativa a pesquisa de transações que foram abandonadas no checkout do PagSeguro.
 - **template de e-mail**: define qual o template de email sua loja usuará para o envio do email de recuperação de venda.
 - **oferecer desconto para ...**: ativa/desativa desconto para checkouts utilizando este meio de pagamento
 - **percentual de desconto**: define o percentual de desconto a ser concedido para o meio de pagamento escolhido
 - **transações -> abandonadas**: permite consultar as transações que foram abandonadas nos últimos 10 dias, desta forma você pode enviar email-s de recuperação de venda. O e-mail conterá um link que redirecionará o comprador para o fluxo de pagamento, exatamente no ponto onde ele parou.
 - **transações -> abandonadas -> dias**: defina a quantidade máxima de dias em que a transação foi abandonada. Ex.: se você definir 8, então somente as transações abandonadas nos últimos 8 dias serão exibidas.
 - **transações -> cancelamento**: esta pesquisa retornará todas as transações que estejam com status "em análise" e "aguardando pagamento", dentro da quantidade de dias definidos para a pesquisa. Desta forma você pode solicitar o cancelamento destas transações.
 - **transações -> cancelamento -> dias**: número de dias que devem ser considerados para a pesquisa.
 - **transações -> conciliação**: permite consultar as transações efetivadas no PagSeguro nos últimos 30 dias. A pesquisa retornará um comparativo com o status das transações em sua base local e o status atual da transação no PagSeguro, desta forma você pode identificar e atualizar transações com status divergentes.
 - **transações -> conciliação -> dias**: número de dias que devem ser considerados para a pesquisa.
 - **transações -> estorno**: esta pesquisa retornará todas as transações que estejam com status "paga", "disponível" e "em disputa", dentro da quantidade de dias definidos para a pesquisa. Desta forma você pode solicitar o estorno dos valores pagos para seus compradores.
 - **transações -> estorno -> dias**: número de dias que devem ser considerados para a pesquisa.
 - **requisitos**: exibe se os pré-requisitos básicos para o correto funcionamento do módulo estão sendo atendidos
 - **listar parcelamento**: Habilita a exibição de uma listagem de parcelas na tela de visualização do produto. (Irá exibir o maior parcelamento disponível para o produto na tela de exibição do mesmo)

## Dúvidas

Caso tenha dúvidas ou precise de suporte, acesse nosso [fórum](https://comunidade.pagseguro.uol.com.br/hc/pt-br/community/topics).</p>

## Changelog
Para consultar o log de alterações acesse o arquivo [CHANGELOG.md](CHANGELOG.md).

## Notas
- A versão 2.x deste respositório está depreciada en encontra-se [na branch 2.8](https://github.com/pagseguro/magento/tree/2.8) ou através da sua [última release](https://github.com/pagseguro/magento/releases/tag/2.8.0).
- O PagSeguro somente aceita pagamento utilizando a moeda Real brasileiro (BRL).
- Certifique-se que o email e o token informados estejam relacionados a uma conta que possua o perfil de vendedor ou empresarial.
- Certifique-se que tenha definido corretamente o charset de acordo com a codificação (ISO-8859-1 ou UTF-8) do seu sistema. Isso irá prevenir que as transações gerem possíveis erros ou quebras ou ainda que caracteres especiais possam ser apresentados de maneira diferente do habitual.
- Para que ocorra normalmente a geração de logs, certifique-se que o diretório e o arquivo de log tenham permissões de leitura e escrita.

## Contribuições
Todas as contribuições devem ser feitas via Pull Request na [branch de desenvolvimento](https://github.com/pagseguro/magento/tree/desenvolvimento), seguindo os passos:
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
