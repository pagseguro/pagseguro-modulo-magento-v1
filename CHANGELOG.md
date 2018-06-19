Changelog
---------
3.16.6
- Fix: Na listagem de bancos na tela de pagamento por débito online nas versões 1.7 e 1.8 do Magento
- Fix: Na máscara de dinheiro nos campos parcelas e total na tela de pagamento

3.16.4
- Fix: ajuste na regra css do botão "btn-pagseguro"
- Fix: ajuste na exibição dos meios de pagamento do checkout transparente

3.16.2
- Fix: exibição das opções de banco no método de pagamento Débito Online

3.16.1
- Fix: redirecionamento para tela de erro caso ocorra algum problema no checkout padrão ou lightbox

3.16.0
- Inserir configuração de Status do PagSeguro por Notificação (admin) em relação ao states do Magento
- Retentativa de pagamento ao finalizar um checkout com erro
- Compatibilidade com IWD OnePageCheckout versão 4.3.6
- Opção de definir a URL de sucesso do checkout transparente com cartão de crédito
- Fixes: lightbox não estava carregando o ambiente corretamente e removido botão de retentativa de pagamento ao finalizar checkout como visitante 


3.12.1
- Fix: corrigido erro do datatable após um estorno parcial quando não há mais transações para serem estornadas

3.12.0
- Melhorias na tela de Listar Transações (admin) referentes ao estorno parcial

3.11.0
- Checkout sem endereço (para produtos do tipo 'virtual' e 'downloadable')
- Valida se o telefone do comprador foi configurado antes de tentar usar o telefone do endereço de entrega
- Habilitar/desabilitar recuperação de carrinho do PagSeguro via admin
- Atualizada versão da biblioteca PHP do PagSeguro usada no módulo
- Tela de listar transações no admin, permitindo ver detalhes da transação
- Estorno parcial
- Fix: Corrigido id dos itens do pedido (carrinho) enviados para o PagSeguro

3.5.1
- Corrigido bug de incompatibilidade com PHP 5.4 no checkout

3.5.0
- Não exibe meio de pagamento transparente na tela de checkout caso o mesmo não esteja habilitado ou configurado corretamente na conta do vendedor ou aplicação do PagSeguro configurada no módulo
- Exibe na interface administrativa o estado (ativo, inativo) dos meios de pagamento do checkout transparente da conta do PagSeguro configurada no módulo


3.4.0
- Atualizada biblioteca PHP do PagSeguro para versão 3.3.0
- Fix: erro no "credit card holder phone" (não estava sendo enviado o telefone do usuário)
- Melhoria: adicionada validação para, caso os dados do cartão de crédito não tenham sido inseridos corretamente (como senha errada, validada errada), exibir mensagem na tela de checkout (antes a compra era finalizada com erro)
- Fix: erro ao exibir o parcelamento máximo na tela do produto

3.3.0
- Adicionada compatibilidade com One Step Checkout Brasil 6 Pro (Inovarti)

3.2.2
- Corrigido bug que não deixava visível o uso do checkout padrão/lightbox em alguns ambientes linux.

3.2.1
- Corrigido bug ao exibir o formulário de checkout quando as credencias do PagSeguro eram inválidas.
  - Adicionada mensagem de credenciais inválidas ao salvar as configurações no admin e ocultando os meios de pagamento do PagSeguro na tela de pagamento caso as credenciais salvas sejam inválidas.

3.2.0
- Alterado fluxo do checkout transparente para o fluxo padrão do Magento (onepage)
- Atualizada tela de cofiguração do módulo (admin), adicionando categorias e possibilitando habilitar métodos transparentes (boleto, débito online e cartão de crédito) individualmente

3.1.0
- Adicionada biblioteca de máscaras (Vannila-Masker)
- Corrigidos bugs no checkout transparente
- Corrigidos bugs nas transações do admin (Conciliação, Cancelamento e Estorno)
- Adicionada bandeira do Brasil no checkout transparente
- Correções e melhorias gerais


3.0.0
- Adicionado checkout transparente (pagamento via boleto, debito online e cartão de crédito)
- Atualizada versão da biblioteca php do pagseguro usada pelo módulo
- Mudança das versões suportadas do magento, agora aceita da versão do magento 1.6 até a versão 1.9.3.3
- Versão do php agora deve ser >= 5.4
- Refatoração e melhorias em geral

2.8.0
- Possibilidade de exibir uma lista com o maior parcelamento disponível de acordo com o preço do produto visualizado;

2.7.0
 - Possibilidade de consultar e solicitar o cancelamento de transações;
 - Possibilidade de consultar e solicitar o estorno de transações;
 - Ajustes em geral;
 - Obs.: As funcionalidades descritas acima ainda não estão disponíveis comercialmente para todos os vendedores. Em caso de dúvidas acesse nosso [fórum].

2.6.0
 - Possibilidade de definir descontos com base no meio de pagamento escolhido durante o checkout PagSeguro;

2.5.0
 - Atualização do layout da tela de configuração;
 - Integração com Sandbox;
 - Ajustes em geral;

2.4
 - Correção dos js e css que carregavam por HTTP quando o site era acessado em HTTPS;
 - Alterando estrutura do módulo de PagSeguro_PagSeguro para Uol_PagSeguro;

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
