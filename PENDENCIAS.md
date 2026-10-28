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

## Rodada 6: feita e revisada (296 itens passando), falta aplicar na Hostinger
Feito: ponto da foto na faixa das páginas; espessura da faixa de avisos; prévia ao vivo do cabeçalho; site de teste escondido do Google sozinho (aviso no Início do painel); dados estruturados para o Google; campos do Pixel da Meta e do Google Analytics com aviso de cookies (só mede quem aceita); e-mails de aviso escolhidos no painel com botão de teste (newsletter agora também avisa); quadro com um controle só sem título repetido; vídeos e playlists do YouTube com tamanho, vários lado a lado, capa opcional, título e botão "Ver no YouTube".

- [ ] Na mudança para o endereço oficial: cadeado (SSL), Search Console com o mapa do site, colar os números do Pixel e do Analytics.

## Rodada 7 (coletando; o Igor vai mandando os itens)
1. Seção Playlists da home com dois formatos, escolhidos no painel (o que não está em uso fica guardado):
   - "Grade de playlists": o formato de hoje.
   - "Imagem + texto": imagem à esquerda, clicável; à direita, título, texto e um botão. A imagem e o botão levam para a página interna com a lista de playlists. Alinhamento do título e do texto: esquerda, centro, direita ou justificado. O "texto depois das imagens" não aparece nesse formato. No celular, a imagem fica em cima.
2. Seções com imagem ao lado do texto (O Arquivo, "Texto + imagem" e o formato novo de Playlists): quando a imagem é diminuída, o texto vem junto para perto dela, em vez de ficar parado longe. O espaço entre a imagem e o texto cai pela metade. A imagem continua alinhada com a margem do blog.
3. Aparência → Botões com os 3 tipos, cada um com o nome de onde aparece e 4 cores (fundo, texto, fundo e texto ao passar o mouse): botão principal (O Arquivo, newsletter, Texto + imagem, busca, cookies); botão "Ver todas" (blog e Posts de uma categoria; hoje usa a cor da faixa e letra branca fixa); botão escuro (Acervo/História, Enviar do contato, Publicar comentário; hoje fixo). As cores de hoje continuam até o Igor mudar.
4. Faixa de avisos (Conteúdo do site → Faixa de avisos): régua de velocidade (de "Bem devagar" a "Rápida", igual com 1 ou 10 avisos; continua parando com o mouse em cima) e separador à escolha: bandeirinha (a de hoje), estrela, ponto, losango, traço, barra, nenhum, texto/emoji próprio ou imagem da biblioteca; os desenhos seguem a cor do texto da faixa; régua do espaço entre aviso e separador.
5. Menu do topo: cores em Aparência → Cores → Cabeçalho para o link ao passar o mouse, o risco embaixo e a página atual; efeito à escolha: risco embaixo (o de hoje), risco que cresce do meio, fundo arredondado (pílula) ou só troca de cor.

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
