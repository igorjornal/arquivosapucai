# Como instalar o Arquivo Sapucaí na Hostinger

Instale primeiro num **endereço de teste** (ex.: `novo.arquivosapucai.com.br`). O site atual continua no ar.

> Os nomes dos menus da Hostinger mudam de vez em quando. Se algum não bater com o que você vê, tire um print e mande na conversa.

## 1. Criar o endereço de teste (subdomínio)
No painel da Hostinger, crie o subdomínio **novo** para `arquivosapucai.com.br`.
A Hostinger cria uma pasta para ele (algo como `public_html/novo` ou `domains/novo.arquivosapucai.com.br/public_html`). **Anote qual é a pasta.**

## 2. Criar o banco de dados
Na área de **Bancos de Dados MySQL**, crie um banco novo. Anote estes 4 dados:
- nome do banco (ex.: `u123456789_arquivo`)
- usuário (ex.: `u123456789_arquivo`)
- senha
- servidor (quase sempre `localhost`)

## 3. Subir os arquivos
1. Abra o **Gerenciador de Arquivos** e entre na pasta do subdomínio (passo 1).
2. Se houver um `default.php` ou `index.html` de exemplo lá dentro, apague.
3. Envie o arquivo `arquivo-sapucai-site.zip` e use **Extrair** (Extract) ali mesmo.
4. Confira que `index.php`, `install.php` e as pastas `app`, `assets` e `templates` ficaram **direto na pasta do subdomínio**, e não dentro de outra subpasta.
5. Pode apagar o zip depois.

## 4. Rodar o instalador
1. Abra no navegador: `https://novo.arquivosapucai.com.br/install.php`
2. Confira se todos os itens da lista estão com ✅.
3. Preencha os dados do banco (passo 2), o endereço do site e o **seu** e-mail e senha do painel.
4. Clique em **Instalar**. Você cai direto na tela de login do painel.

Depois de instalado, o instalador se bloqueia sozinho.

## 5. Entrar no painel
`https://novo.arquivosapucai.com.br/admin`. Também dá para entrar pelo link “Área do autor”, no rodapé.

## Se algo der errado
- **Página com “muitos redirecionamentos” ou erro de segurança (SSL):** o certificado do subdomínio ainda não foi ativado. Ative o SSL para o subdomínio no painel da Hostinger e espere alguns minutos.
- **Erro 500:** confira a versão do PHP do subdomínio (precisa ser 8.1 ou mais nova).
- **Qualquer outra coisa:** mande um print na conversa.

## Quando o teste for aprovado
Aí a gente aponta o domínio principal para o site novo. Esse passo a gente faz junto.
