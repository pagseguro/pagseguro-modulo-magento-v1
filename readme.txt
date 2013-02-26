***********
Módulo de integração PagSeguro para Magento
v.1.1
***********


= Descrição =

Este módulo tem por finalidade integrar o PagSeguro como meio de pagamento dentro da plataforma Magento.


= Requisitos =

Disponível para a versão 1.7.0.2 do Magento.


= Instalação =

1. Certifique-se de que não há instalação de outros módulos para o PagSeguro em seu sistema;
2. Descompacte o conteúdo do arquivo zip dentro da pasta app em sua instalação Magento. Caso seja informado da sobrescrita de alguns arquivos, você pode confirmar o procedimento sem problemas. Esta instalação não afetará nenhum arquivo do seu sistema, somente adicionará os arquivos do módulo PagSeguro;
3. Após copiar os arquivos, no administrador do Magento vá até a opcão Gerenciador de Cache na aba Sistema e clique na opção Flush Magento Cache.

Pronto, o módulo de integração PagSeguro para Magento já está instalado.


= Configuração =

Após instalar o módulo, é necessário que se faça algumas configurações para que efetivamente seja possível utilizar-se dele. Essas configurações estão disponíveis na opção Métodos de pagamento nas configurações do módulo.

	- email: E-mail cadastrado no PagSeguro
	- token: Token cadastrado no PagSeguro
	- url de redirecionamento: url utilizada para se fazer redirecionamento após o cliente realizar a efetivação da compra no ambiente do PagSeguro. Pode ser uma url do próprio sistema ou uma outra qualquer de interesse do vendedor.
	- charset: codificação do sistema (ISO-8859-1 ou UTF-8)
	- log: diretório a partir da raíz do sistema, onde se deseja criar o arquivo de log . Ex.: /logs/log_pagseguro.log

= Changelog =

v1.1
Modificação na estrutura de pastas para facilitar a intalação.

v1.0
Versão inicial. Integração com API de checkout do PagSeguro.


= NOTAS =
	
	- Certifique-se que o email e o token informados estejam relacionados a uma conta que possua o perfil de vendedor ou empresarial;
	- Certifique-se que tenha definido corretamente o charset de acordo com a codificação (ISO-8859-1 ou UTF-8) do seu sistema. Isso irá prevenir que as transações gerem possíveis erros ou quebras ou ainda que caracteres especiais possam ser apresentados de maneira diferente do habitual.	