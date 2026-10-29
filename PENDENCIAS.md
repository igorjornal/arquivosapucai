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

## Rodada 7: feita, revisada (335 itens) e aplicada na Hostinger (29/10; a pasta templates precisou ser substituída de novo)
Feito: Playlists em dois formatos (grade ou imagem + texto + botão, com alinhamento); texto acompanha a imagem diminuída (espaço de 24px); cores próprias dos 3 tipos de botão; faixa de avisos com velocidade, separador à escolha e espaço; menu com cores ao passar o mouse, efeito (risco, risco que cresce, fundo arredondado, só cor), alinhamento e espaço entre itens; família Archivo inteira na lista de fontes; redes sociais e lupinha no cabeçalho no lugar da caixa de busca; uma busca só (saiu a da barra de categorias).

## Rodada 8: feita e revisada (369 itens passando), falta aplicar na Hostinger
Painel reorganizado pelo lugar onde a coisa aparece no site: Escrever (Início, Posts com Categorias e Tags, Páginas, Imagens), Leitores (Comentários, Mensagens, Newsletter), O site parte por parte (Cabeçalho e menu, Faixa de avisos, Página inicial com abas de seções/ordem/banners, Rodapé, Blog e posts), Geral (Padrão do site, Configurações, Autores). Cada tela salva só o que é dela. Fontes de cada texto ficam em "Fontes e tamanhos" (fechado). Prévia ao vivo da faixa. Ícones das redes no topo com destaque e cor própria. A revisão confere que as 48 cores, as 31 fontes de texto e os outros 109 ajustes estão em alguma tela. Endereços antigos do painel levam às telas novas.
- [ ] Ainda falta: envio dos avisos por SMTP (hoje caem no spam).

## Rodada 9 (coletando; só anotar, fazer quando o Igor mandar)
1. Prévias do painel iguais ao site: a prévia do cabeçalho passa a ser o próprio cabeçalho do site (mesmo código e visual: fonte e tamanho do menu, ícones das redes, lupinha, efeito ao passar o mouse, largura real), reagindo na hora a tudo que se mexe no painel, inclusive fonte, tamanho e peso escolhidos (já aparece com a fonte que está no site). O mesmo para a prévia da faixa de avisos.
2. Espaço entre os itens do menu: deixar chegar bem mais perto (régua começando em 0). Causa: no efeito "fundo arredondado" cada item tem 14px de folga de cada lado, que somam 28px ao espaço escolhido; por isso a régua parece não diminuir. Fazer o espaço da régua ser o espaço que se vê entre um texto e outro, em qualquer efeito.
3. Posts da página inicial e do blog com o mesmo número: um valor só ("quantos posts por vez"), que aparece em Página inicial → Blog e em Blog e posts; mudou num lugar, muda no outro. Embaixo dos posts da página inicial, link para a página 2 do blog (só aparece se houver mais posts), começando exatamente no post seguinte.
4. Espaço entre a imagem e o texto (O Arquivo, Playlists em "Imagem + texto" e seções "Texto + imagem"): ficou colado demais (24px fixo). Erro de interpretação: o pedido era reduzir pela metade o espaço total que sobrava, e não o espaço mínimo. Corrigir com UMA régua "Espaço entre a imagem e o texto" que vale igual para todas essas seções (para ficarem uniformes), no topo de Página inicial → Textos, imagens e cores das seções. Começa em 40px (só um pouco mais que hoje).
5. Busca do cabeçalho: ao abrir pela lupinha, o campo empurra o menu e quebra a linha (Contato desce). Abrir a busca numa caixinha flutuante logo abaixo da lupinha, por cima da faixa e do banner, sem mexer em nada do menu; fecha com X, com Esc ou clicando fora. No celular, continua dentro do menu aberto.
- Pendente de resposta: a fileira de atalhos ("Logo, Fundo, Itens do menu…") continua fixa no topo; o Igor ainda vai dizer se prefere que role junto.

## A resolver
- [ ] **SSL (cadeado) do subdomínio de teste.** Pedir ao suporte da Hostinger que ative o SSL de `novo.arquivosapucai.com.br`. Depois de ativo:
  - tirar o `#` das duas linhas de HTTPS no `.htaccess`;
  - trocar `base_url` para `https://` no `config.php` do servidor.
- [ ] **Trocar a senha do banco de dados.** A senha atual apareceu na conversa. Pedir à Hostinger uma senha nova e atualizar o campo `password` no `config.php` do servidor.
- [ ] **Forma de atualizar o site.** Decidir entre um zip novo a cada mudança ou ligar o GitHub direto na Hostinger (deploy automático).
- [ ] **Avisos por e-mail caem no spam.** O teste chegou no Gmail, mas na caixa de spam (29/10). Solução: enviar pelo e-mail de verdade da Hostinger (SMTP com usuário e senha), em vez do envio simples do PHP. A senha fica só no config.php do servidor, nunca na conversa. Enquanto isso: marcar "Não é spam" no Gmail.
- [ ] **Limpar as pastas com número no nome** em public_html (app.1533, templates.6108 etc.): são cópias antigas criadas pelo Gerenciador de Arquivos ao mover pastas. O site não usa; apagar com calma, uma vez só, quando o Igor quiser.
- [ ] **Testes do Igor.** O Igor está testando e anotando o que mudar. Trazer a lista para a próxima conversa.
- [ ] **Links das playlists.** Trocar os links das 3 imagens de Playlists pelos links de cada playlist (Painel → Conteúdo do site → Textos da home).
- [ ] **Redes sociais.** Preencher Instagram e as outras redes (Painel → Conteúdo do site → Site e redes).
- [ ] **Carrossel do topo (banners):**
  - escolher no painel a velocidade da troca;
  - escolher o efeito da troca (sumir/aparecer ou deslizar para o lado);
  - título e botão por cima da imagem, escritos no painel (opcional por banner; o Igor ainda vai avaliar).
- [ ] **Animações GSAP.** Montar uma demonstração com efeitos na própria home para o Igor aprovar.
- [ ] **Publicação final.** Depois dos testes aprovados, apontar `arquivosapucai.com.br` para o site novo (substituindo o Builder).
