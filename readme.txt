***********
Módulo PagSeguro para o Magento
Este módulo tem por finalidade realizar transações de pagamentos entre sistema Magento e o PagSeguro
Disponível para as versões 1.7.0.2 do Magento.
***********

- Instalação
	
	
Para instalar o módulo Magento do PagSeguro abra a pasta app localizada na raiz do magento.

Após entrar na pasta app, vá para a pasta etc e localize a pasta modules.

Após localizar a pasta modules, copie o arquivo PagSeguro_PagSeguro.xml dentro dela.

Após copiar o arquivo, volte para a pasta app e entre na pasta code.

Dentro de code, copie a pasta PagSeguro (módulo) dentro da pasta local.

Caso a pasta local não exista, basta criar.

Após copiar os arquivos, no administrador do Magento vá até a opcão Gerenciador de Cache na aba Sistema e clique na opção Flush Magento Cache.

Pronto, o magento já está instalado. 	

- Configurações

Após instalado o módulo, é necessário que se faça algumas configurações para que efetivamente seja possível utilizar-se dele. Essas configurações estão disponíveis na opção Métodos de pagamento nas configurações do módulo.

	- email: E-mail cadastrado no PagSeguro
	- token: Token cadastrado no PagSeguro
	- url de redirecionamento: url utilizada para se fazer redirecionamento após o cliente realizar a efetivação da compra no ambiente do PagSeguro. Pode ser uma url do próprio sistema ou uma outra qualquer de interesse do vendedor.
	- charset: codificação do sistema (ISO-8859-1 ou UTF-8)
	- log: diretório a partir da raíz do sistema, onde se deseja criar o arquivo de log . Ex.: /logs/log_pagseguro.log
			
* NOTAS:
	
	- Certifique-se que o email e o token informados estejam relacionados a uma conta que possua o perfil de vendedor ou empresarial;
	- Certifique-se que tenha definido corretamente o charset de acordo com a codificação (ISO8859-1 ou UTF8) do seu sistema. Isso irá prevenir que as transações gerem possíveis erros ou quebras ou ainda que caracteres especiais possam ser apresentados de maneira diferente do habitual.
	