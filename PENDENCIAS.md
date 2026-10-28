# Pendências do Arquivo Sapucaí

Situação em 24/10/2026: o site está instalado e funcionando no endereço de teste
**http://novo.arquivosapucai.com.br** (Hostinger, Business Web Hosting, PHP 8.3).
O site atual (arquivosapucai.com.br) continua intocado.

## Rodada 4: feita e revisada (225 itens passando), falta aplicar na Hostinger
Feito: painel organizado em grupos com atalhos; tamanhos nas fontes base; fonte, tamanho e negrito em cada texto da home, do contato, do rodapé, do menu e dos avisos (com "usar o padrão do site"); Acervo e História em duas colunas com foto, botão, link e âncora próprios; textos fixos do contato e do rodapé editáveis; "© 2024–ano atual"; "Área do autor" em nova aba; card respeita o número de linhas; apagar playlist agora funciona.

- [ ] **Tabela de todos os textos do site** (lembrete do Igor): onde cada texto aparece e onde se edita no painel.
- [ ] Menu: colocar "Acervo" → /acervo e "História" → /historia quando as páginas forem publicadas.

## Rodada 5: feita e revisada (249 itens passando), falta aplicar na Hostinger
Feito: rodapé sem logo, com os 3 títulos alinhados e no mesmo estilo, links mais juntos, ícones das redes com o dobro do tamanho e "Contato:" antes do e-mail; páginas internas com faixa fina, botão Voltar e Contato no fim; peso das fontes (Fino a Extra negrito) em cada texto e em Aparência; imagem do cabeçalho e força da cor em Aparência; botão "Escolher da biblioteca" do editor não falha mais quando a janela demora a abrir.

## Rodada 6 (coletando; o Igor está usando o painel e anotando ajustes de uso)
1. Páginas (Acervo, História…): seletor de região da foto na faixa do topo (clicar no ponto que deve aparecer, como na capa do post).
2. Faixa vermelha de avisos: controlar a espessura (altura), para combinar com o tamanho da fonte.
3. Cabeçalho em Aparência: prévia ao vivo da barra enquanto mexe na força da cor e no degradê.
4. Esconder do Google automaticamente quando o site estiver em novo.arquivosapucai.com.br (libera sozinho no endereço oficial) + aviso amarelo no Início do painel.
5. Dados estruturados para o Google (artigo, autor, data, vídeo).
6. Campos no painel para o número do Pixel da Meta e do Google Analytics (vazios = desligados) + aviso de cookies (LGPD).
7. Avisos por e-mail escolhidos no painel (Conteúdo do site): um e-mail para mensagens do contato, um para novos cadastros da newsletter do rodapé (hoje não avisa) e um para comentários aguardando aprovação; vazio = sem aviso; botão "mandar e-mail de teste".
- Na mudança para o endereço oficial: cadeado (SSL), Search Console com o mapa do site, colar os números do Pixel e do Analytics.

## A resolver
- [ ] **SSL (cadeado) do subdomínio de teste.** Pedir ao suporte da Hostinger que ative o SSL de `novo.arquivosapucai.com.br`. Depois de ativo:
  - tirar o `#` das duas linhas de HTTPS no `.htaccess`;
  - trocar `base_url` para `https://` no `config.php` do servidor.
- [ ] **Trocar a senha do banco de dados.** A senha atual apareceu na conversa. Pedir à Hostinger uma senha nova e atualizar o campo `password` no `config.php` do servidor.
- [ ] **Forma de atualizar o site.** Decidir entre um zip novo a cada mudança ou ligar o GitHub direto na Hostinger (deploy automático).
- [ ] **Avisos por e-mail.** Confirmar se os avisos de nova mensagem e de comentário pendente chegam no e-mail. Se não chegarem, configurar o envio por SMTP da Hostinger.
- [ ] **Testes do Igor.** O Igor está testando e anotando o que mudar. Trazer a lista para a próxima conversa.
- [ ] **Links das playlists.** Trocar os links das 3 imagens de Playlists pelos links de cada playlist (Painel → Conteúdo do site → Textos da home).
- [ ] **Redes sociais.** Preencher Instagram e as outras redes (Painel → Conteúdo do site → Site e redes).
- [ ] **Carrossel do topo (banners):**
  - escolher no painel a velocidade da troca;
  - escolher o efeito da troca (sumir/aparecer ou deslizar para o lado);
  - título e botão por cima da imagem, escritos no painel (opcional por banner; o Igor ainda vai avaliar).
- [ ] **Animações GSAP.** Montar uma demonstração com efeitos na própria home para o Igor aprovar.
- [ ] **Publicação final.** Depois dos testes aprovados, apontar `arquivosapucai.com.br` para o site novo (substituindo o Builder).
