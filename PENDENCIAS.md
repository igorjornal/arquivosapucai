# Pendências do Arquivo Sapucaí

Situação em 24/10/2026: o site está instalado e funcionando no endereço de teste
**http://novo.arquivosapucai.com.br** (Hostinger, Business Web Hosting, PHP 8.3).
O site atual (arquivosapucai.com.br) continua intocado.

## Rodada 4 (pedidos coletados; antes de programar, entregar o mapa do painel para o Igor aprovar)
1. Organizar o painel sem informação repetida: o padrão do site fica em Aparência, e o ajuste de cada parte fica na tela dela.
2. Aparência: as fontes base (Títulos, Texto corrido, Menus/botões) ganham também o tamanho.
3. Fonte e tamanho da faixa de avisos saem de Aparência e vão para Conteúdo do site, junto com os textos dos avisos.
4. Card do blog: tirar o corte de 140 letras, para o número de linhas valer (e o card crescer).
5. Todos os textos da home ganham fonte, tamanho e negrito, com a opção "usar o padrão do site" já marcada.
6. Páginas grandes do painel: cabeçalho forte por assunto, fundo levemente bege por grupo e índice de atalhos no topo.
7. Acervo e História: duas colunas lado a lado na home (no celular, uma embaixo da outra), cada uma com foto, título, texto, botão e link próprios, e cada uma pode ser escondida sem esconder a outra. No menu: "Acervo" leva a /acervo e "História" leva a /historia, mas só quando as páginas forem publicadas (hoje são rascunho).
8. Contato e rodapé: cada item ganha texto, fonte e tamanho. Os textos que hoje estão fixos passam a ser editáveis: nomes dos campos, "Enviar", "Navegue", "RSS", "Seu melhor e-mail", "Quero receber", copyright e "Área do autor". Nome do site, descrição e e-mail continuam em Conteúdo → Site, e no rodapé só se ajustam a fonte e o tamanho deles. Copyright: "© 2024–ano atual" (o segundo ano muda sozinho).
9. **Lembrete:** entregar uma tabela com todos os textos do site, dizendo onde cada um aparece e onde se edita no painel.

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
