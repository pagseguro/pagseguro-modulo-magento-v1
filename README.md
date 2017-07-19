# Módulo de integração PagSeguro para Magento

## Descrição

Com o módulo instalado e configurado, você pode pode oferecer o PagSeguro como opção de pagamento em sua loja. O módulo utiliza as seguintes funcionalidades que o PagSeguro oferece na forma de APIs:

- Integração com a [API de Pagamentos](https://dev.pagseguro.uol.com.br/documentacao/pagamentos)
- Integração com a [API de Notificações](https://pagseguro.uol.com.br/v2/guia-de-integracao/api-de-notificacoes.html#!rmcl)

## Requisitos
- [Magento](https://www.magentocommerce.com/) Community 1.6, 1.7, 1.8, 1.9
- [PHP](http://www.php.net/) 5.4.0+
- [SPL](http://php.net/manual/en/book.spl.php)
- [cURL](http://php.net/manual/en/book.curl.php)
- [SimpleXML](http://php.net/manual/en/book.simplexml.php)

## Instalação

- Certifique-se de que não há instalação de outros módulos para o PagSeguro em seu sistema;
- Baixe o repositório como arquivo zip ou [clique aqui];
- Antes de seguir com a instalação você deve desativar e limpar a compilação do Magento;
- Na área administrativa do seu Magento, acesse o menu System -> Magento Connect -> Magento Connect Manager. Caso tenha uma versão anterior instalada faça a remoção agora;
- Clique em "choose file", aponte para o arquivo UOL_PagSeguro-x.x.x.tgz e faça upload;
- Caso utilize a compilação, ative-a e execute-a novamente;
- Caso tenha uma versão anterior a 2.3 instalada, copie o arquivo *remove-module-2.2.php* para a raíz de instalação do seu Magento e execute o arquivo no browser, ex.: www.meusite.com.br/magento/remove-module-2.2.php. Siga as instruções na tela para a remoção dos arquivos.

## Dúvidas

Caso tenha dúvidas ou precise de suporte, acesse nosso [fórum]([fórum]: https://comunidade.pagseguro.uol.com.br/hc/pt-br/community/topics).</p>

## Notas
- A versão 2.x deste respositório está depreciada en encontra-se em [link no repo]
- O PagSeguro somente aceita pagamento utilizando a moeda Real brasileiro (BRL).
- Certifique-se que o email e o token informados estejam relacionados a uma conta que possua o perfil de vendedor ou empresarial.
- Certifique-se que tenha definido corretamente o charset de acordo com a codificação (ISO-8859-1 ou UTF-8) do seu sistema. Isso irá prevenir que as transações gerem possíveis erros ou quebras ou ainda que caracteres especiais possam ser apresentados de maneira diferente do habitual.
- Para que ocorra normalmente a geração de logs, certifique-se que o diretório e o arquivo de log tenham permissões de leitura e escrita.

## Contribuições

- Faça um fork
- Adicione sua feature ou correção de bug
- Envie um pull request no [GitHub]
