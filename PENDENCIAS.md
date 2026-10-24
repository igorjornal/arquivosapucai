# Pendências do Arquivo Sapucaí

Situação em 24/10/2026: o site está instalado e funcionando no endereço de teste
**http://novo.arquivosapucai.com.br** (Hostinger, Business Web Hosting, PHP 8.3).
O site atual (arquivosapucai.com.br) continua intocado.

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
