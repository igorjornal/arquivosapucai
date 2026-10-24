<?php
declare(strict_types=1);

// Conteúdo inicial: menu, banners, categorias e um post de demonstração.
// Só roda numa instalação nova (tabelas vazias).

function seed_initial_content(int $adminId): void
{
    $now = now();

    if (!db_val('SELECT COUNT(*) FROM menu_items')) {
        $menu = [
            ['Home', '/'],
            ['Blog', '/blog'],
            ['O Arquivo', '/#arquivo'],
            ['Playlists', '/#playlists'],
            ['Acervo e História', '/#acervo'],
            ['Contato', '/#contato'],
        ];
        foreach ($menu as $i => [$label, $url]) {
            db_insert('menu_items', ['label' => $label, 'url' => $url, 'new_tab' => 0, 'sort_order' => $i]);
        }
    }

    if (!db_val('SELECT COUNT(*) FROM slides')) {
        db_insert('slides', ['image' => 'assets/img/banner-guia-2027.webp', 'alt' => 'Guia dos Enredos 2027: enredos, sinopses e sambas do Grupo Especial', 'link' => '/blog', 'active' => 1, 'sort_order' => 0]);
        db_insert('slides', ['image' => 'assets/img/banner-serie-ouro-2027.webp', 'alt' => 'Série Ouro 2027', 'link' => '/blog', 'active' => 1, 'sort_order' => 1]);
    }

    if (!db_val('SELECT COUNT(*) FROM categories')) {
        $cats = ['Crônica', 'Estudo de Caso', 'Guia dos Enredos', 'Série Ouro'];
        foreach ($cats as $i => $name) {
            db_insert('categories', ['name' => $name, 'slug' => slugify($name), 'description' => null, 'sort_order' => $i]);
        }
    }

    if (!db_val('SELECT COUNT(*) FROM posts')) {
        $catId = (int) db_val('SELECT id FROM categories WHERE slug = ?', ['cronica']);
        db_insert('posts', [
            'title'        => 'Post de exemplo: tudo o que o editor faz',
            'slug'         => 'post-de-exemplo',
            'excerpt'      => 'Um post de demonstração com cores, tamanhos de fonte, imagens alinhadas, tabela colorida, botão e divisórias. Pode apagar quando quiser.',
            'content'      => sanitize_html(sample_post_html()),
            'cover_image'  => 'assets/img/sample/desfile-1.webp',
            'cover_alt'    => 'Desfile na Marquês de Sapucaí',
            'cover_ratio'  => 'banner',
            'category_id'  => $catId ?: null,
            'author_id'    => $adminId,
            'status'       => 'published',
            'published_at' => date('Y-m-d H:i:s', time() - 2 * 86400),
            'featured'     => 0,
            'allow_comments' => 1,
            'created_at'   => $now,
            'updated_at'   => $now,
        ]);
    }
}

function sample_post_html(): string
{
    return <<<'HTML'
<p><span style="font-size: 20px">Este é um <strong>post de demonstração</strong>. Ele existe só para mostrar, no visual do site, cada recurso do editor. Pode ser apagado pelo painel a qualquer momento.</span></p>
<h2>Texto com cor e marca-texto</h2>
<p>Dá para usar <span style="color: #e31e24">texto em vermelho</span>, <span style="background-color: #000624; color: #ffffff">&nbsp;faixa branca sobre azul-marinho&nbsp;</span>, <em>itálico</em>, <u>sublinhado</u>, <s>tachado</s> e <a href="/blog">links</a>.</p>
<p style="text-align: center"><span style="font-size: 26px">Tamanho livre, alinhado ao centro.</span></p>
<p style="line-height: 2.2">Este parágrafo tem espaçamento entre linhas maior, para mostrar o controle de respiro dentro de um mesmo bloco de texto.</p>
<div class="spacer" style="height: 40px"></div>
<figure class="image align-left" style="width: 42%"><img src="/assets/img/sample/desfile-2.webp" alt="Comissão de frente" style="border-radius: 18px"><figcaption>Imagem alinhada à esquerda, com cantos arredondados e legenda.</figcaption></figure>
<p>Quando a imagem fica à esquerda ou à direita, o texto contorna. A largura da imagem é ajustável, e o formato pode ser quadrado, retangular ou de banner.</p>
<p>No celular, as imagens laterais ocupam a largura toda, para não espremer o texto.</p>
<hr class="divider-dashed">
<h3>Tabela com cores</h3>
<table style="border-collapse: collapse; width: 100%">
<tbody>
<tr><td style="background-color: #20063a; color: #cdf50d; border: 1px solid #ffffff"><strong>Campo</strong></td><td style="background-color: #20063a; color: #cdf50d; border: 1px solid #ffffff"><strong>Exemplo</strong></td></tr>
<tr><td style="background-color: #ffdacd; border: 1px solid #ffffff">Células coloridas</td><td style="background-color: #ffdacd; border: 1px solid #ffffff">Fundo e borda de cada célula escolhidos no editor</td></tr>
<tr><td style="background-color: #fff6c8; border: 1px solid #ffffff">Uso no Arquivo</td><td style="background-color: #fff6c8; border: 1px solid #ffffff">Ficha técnica e notas por quesito</td></tr>
</tbody>
</table>
<h3>Vídeo do YouTube no meio do post</h3>
<div class="video"><iframe src="https://www.youtube.com/watch?v=SLrGaE65GSM" title="Vídeo do canal Arquivo Sapucaí"></iframe></div>
<hr class="divider-double divider-short">
<ul><li>Lista com marcadores</li><li>Fácil de ler</li></ul>
<ol><li>Lista numerada</li><li>Para passo a passo</li></ol>
<p style="text-align: center"><a class="btn-post" href="/blog" style="--btn-bg: #e31e24; --btn-color: #ffffff; --btn-hover-bg: #fffc00; --btn-hover-color: #000000">Botão vermelho que fica amarelo ao passar o mouse</a></p>
HTML;
}
