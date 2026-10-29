<?php
declare(strict_types=1);

// Valores padrão. Tudo aqui pode ser alterado pelo painel; o que for salvo no banco
// substitui o padrão chave a chave.
function settings_defaults(): array
{
    return [
        'site' => [
            'title'       => 'Arquivo Sapucaí',
            'tagline'     => 'O seu acervo no YouTube para reviver os grandes momentos do carnaval na Marquês de Sapucaí.',
            'description' => 'Acervo histórico do carnaval carioca: desfiles do Grupo Especial ano a ano, crônicas e estudos de caso.',
            'email'       => 'contato@arquivosapucai.com.br',
            'logo'        => 'assets/img/logo.webp',
            'header_bg'   => 'assets/img/fundo-cabecalho.webp',
            'share_image' => 'assets/img/banner-guia-2027.webp',
            'socials'     => [
                'youtube'   => 'https://www.youtube.com/@ArquivoSapucai',
                'instagram' => '',
                'facebook'  => '',
                'x'         => '',
                'tiktok'    => '',
                'linkedin'  => '',
            ],
        ],
        'home' => [
            'split_gap'       => 40, // espaço entre a imagem e o texto (O Arquivo, Playlists e Texto + imagem)
            'ticker_speed'    => 4,      // 1 (bem devagar) a 10 (rápida)
            'ticker_sep'      => 'flag', // flag | star | dot | diamond | dash | bar | none | text | image
            'ticker_sep_text' => '',
            'ticker_sep_image'=> '',
            'ticker_gap'      => 22,     // espaço entre aviso e separador (px)
            'ticker_pad'      => 9, // espaço em cima e embaixo do texto da faixa de avisos (px)
            'ticker'          => 'O seu acervo no YouTube para reviver os grandes momentos do carnaval na Marquês de Sapucaí.',
            'blog_title'      => 'Blog - Crônicas e Estudos de Caso',
            'arquivo_title'   => 'O Arquivo',
            'arquivo_text'    => "Nosso objetivo é transformar o Canal Arquivo Sapucaí em um acervo mesmo, uma espécie de arquivo histórico, onde os apaixonados podem experienciar de novo os desfiles que marcaram épocas. Planejamos enriquecer este acervo aos poucos, garantindo a preservação e a celebração da rica história do carnaval do Rio de Janeiro.\n\nQuer participar? Se inscreva no nosso canal no YouTube e acompanhe a história do carnaval sendo contada desfile após desfile.",
            'arquivo_image'   => 'assets/img/print-youtube.webp',
            'arquivo_button'  => 'VEM VER! É DE GRAÇA!',
            'arquivo_link'    => 'https://www.youtube.com/@ArquivoSapucai',
            'playlists_title' => 'Playlists no YouTube',
            'playlists_intro' => "Nosso objetivo é manter um acervo histórico, onde os sambistas e apaixonados podem rever os desfiles que marcaram épocas a hora que quiser. O acervo cresce aos poucos, garantindo a preservação e a celebração da rica história do carnaval do Rio de Janeiro. Inscreva-se, acompanhe e fique à vontade para participar. O \"Arquivo\" é de todos nós.",
            'playlists_outro' => "A história do carnaval se mistura com a história de cada um de nós. E reviver os desfiles traz na memória do sambista aquela sensação única, aquele frio na barriga. Então embarque no \"Arquivo\", pra eternizar esse sentimento.",
            'playlists_layout'  => 'grid', // grid = grade de playlists | feature = imagem + texto + botão
            'playlists_image'   => 'assets/img/sample/desfile-1.webp',
            'playlists_button'  => 'VER AS PLAYLISTS',
            'playlists_link'    => '/playlists',
            'playlists_new_tab' => false,
            'playlists_align'   => 'left', // left | center | right | justify
            'playlists'       => [
                ['title' => 'Grupo Especial', 'image' => 'assets/img/sample/desfile-1.webp', 'link' => 'https://www.youtube.com/@ArquivoSapucai/playlists'],
                ['title' => 'Baterias', 'image' => 'assets/img/sample/desfile-2.webp', 'link' => 'https://www.youtube.com/@ArquivoSapucai/playlists'],
                ['title' => 'Sapucaí', 'image' => 'assets/img/sample/desfile-3.webp', 'link' => 'https://www.youtube.com/@ArquivoSapucai/playlists'],
            ],
            'acervo_show'     => true,
            'acervo_image'    => 'assets/img/sample/acervo.webp',
            'acervo_title'    => 'O Acervo',
            'acervo_text'     => 'Preservamos a memória vibrante do Carnaval da Sapucaí, unindo a tradição oral e os registros visuais para garantir que a história dos nossos desfiles seja eternamente celebrada.',
            'acervo_button'   => 'SAIBA MAIS',
            'acervo_link'     => '/blog',
            'historia_show'   => true,
            'historia_image'  => 'assets/img/sample/desfile-3.webp',
            'historia_title'  => 'História',
            'historia_text'   => 'Explore milhares de registros históricos, desde croquis de fantasias até vídeos inéditos que capturam a essência da Sapucaí ao longo das décadas.',
            'historia_button' => 'SAIBA MAIS',
            'historia_link'   => '/blog',
            'blog_button'     => 'VER TODAS',
            'contact_title'   => 'Contato',
            'contact_text'    => 'Tem uma história, uma foto antiga ou uma correção pra mandar? Escreve pra gente.',
            'contact_labels'  => ['nome' => 'Nome', 'sobrenome' => 'Sobrenome', 'email' => 'E-mail', 'telefone' => 'Telefone', 'mensagem' => 'Mensagem'],
            'contact_button'  => 'Enviar',
        ],
        'blog' => [
            'per_page'          => 6,      // posts por vez: página do blog e seção Blog da home
            'comments_enabled'  => true,
            'comments_moderate' => true,
            'share_buttons'     => true,
            'cards_per_row'      => 3,       // 2 a 5 (no celular sempre 1)
            'cards_layout'       => 'grid',  // grid = grade reta | masonry = mosaico
            'card_ratio'         => 'wide',  // original | banner | wide | landscape | square | portrait
            'card_title_lines'   => 3, // 0 = sem limite
            'card_excerpt_lines' => 3,
            'post_sidebar'       => true,  // coluna com os últimos posts ao lado do post
            'sidebar_title'      => 'Últimos posts',
            'sidebar_count'      => 5,
            'related_title'      => 'Posts relacionados',
        ],
        'playlist_page' => [
            'page_slug'   => 'playlists', // página (de Páginas) onde as décadas aparecem, depois do texto
            'count_label' => 'playlists', // "8 playlists" ao lado do nome da década
            'decades'     => [],          // [{name, items: [{year, title, subtitle, link}]}]
        ],
        'footer' => [
            'word'         => 'ARQUIVO SAPUCAÍ', // texto grande ao fundo do rodapé
            'show_word'    => true,
            'nav_title'    => 'Navegue',
            'rss_label'    => 'RSS',
            'copyright'    => '© 2024–{ano} Arquivo Sapucaí. Todos os direitos reservados.',
            'author_label' => 'Área do autor',
            'email_label'  => 'Contato:',
        ],
        'newsletter' => [
            'title'       => 'Receba as novidades',
            'text'        => 'Novos posts, playlists e estudos de caso direto no seu e-mail.',
            'placeholder' => 'Seu melhor e-mail',
            'button'      => 'Quero receber',
        ],
        // Para onde vão os avisos por e-mail (null = e-mail informado na instalação; vazio = sem aviso)
        'notify' => [
            'contact'    => null,
            'newsletter' => null,
            'comments'   => null,
        ],
        // Medição de visitas e anúncios: só carrega depois que o leitor aceita os cookies
        'tracking' => [
            'meta_pixel'  => '',
            'ga4'         => '',
            'cookie_text' => 'Usamos cookies para contar as visitas e melhorar o site. Você aceita?',
        ],
        // Fonte, tamanho e negrito escolhidos para cada parte (o que não está aqui usa o padrão do site)
        'type' => [],
        'appearance' => [
            'font_sizes' => [
                'heading' => 44, // títulos das seções
                'body'    => 16, // texto corrido
                'ui'      => 15, // botões e detalhes
            ],
            'font_weights' => [
                'heading' => 800,
                'body'    => 400,
            ],
            'menu' => [
                'align'  => 'right', // logo = colado no logo | center | right
                'gap'    => 30,      // espaço entre os itens (px)
                'effect' => 'line',  // line = risco | grow = risco que cresce | pill = fundo arredondado | color = só a cor
            ],
            'header' => [
                'overlay'  => 100,  // força da cor por cima da imagem (0 = só imagem, 100 = só cor)
                'gradient' => true, // cor mais forte à esquerda
            ],
            'sizes' => [
                'logo'   => 96,  // tamanho do logo no cabeçalho (px)
                'header' => 120, // altura do cabeçalho (px)
            ],
            'fonts' => [
                'heading' => 'Syne',
                'body'    => 'Bitter',
                'ui'      => 'Instrument Sans',
            ],
            'colors' => [
                'header_bg'     => '#20063A',
                'header_text'   => '#FFFFFF',
                'header_accent' => '#CDF50D',
                'menu_hover'    => '#FFFFFF',
                'menu_line'     => '#CDF50D',
                'menu_current'  => '#CDF50D',
                'header_social' => '#CDF50D',
                'ticker_bg'     => '#E31E24',
                'ticker_text'   => '#FFFFFF',
                'blog_bg'       => '#20063A',
                'blog_title'    => '#CDF50D',
                'card_bg'       => '#F1F0EC',
                'card_title'    => '#1E0D06',
                'card_text'     => '#4A3F3A',
                'pl_bar_bg'         => '#FBFAFD', // página de Playlists
                'pl_bar_text'       => '#20063A',
                'pl_card_bg'        => '#20063A',
                'pl_year'           => '#CDF50D',
                'pl_text'           => '#FFFFFF',
                'pl_accent'         => '#CDF50D',
                'pager_bg'          => '#3A2352', // números das páginas do blog
                'pager_text'        => '#FFFFFF',
                'pager_active_bg'   => '#CDF50D', // página aberta e mouse por cima
                'pager_active_text' => '#1E0D06',
                'arquivo_bg'    => '#E9E6D3',
                'arquivo_title' => '#1E0D06',
                'arquivo_text'  => '#1E0D06',
                'playlists_bg'  => '#20063A',
                'playlists_title' => '#FFFFFF',
                'playlists_text'  => '#FFFFFF',
                'acervo_bg'     => '#E9E6D3',
                'acervo_title'  => '#1E0D06',
                'acervo_text'   => '#1E0D06',
                'contact_bg'    => '#FDFCF0',
                'contact_title' => '#1E0D06',
                'contact_text'  => '#4A3F3A',
                'footer_bg'     => '#1E0D06',
                'footer_text'   => '#E9E6D3',
                'footer_accent' => '#CDF50D',
                'button_bg'     => '#CDF50D',
                'button_text'   => '#1E0D06',
                'button_hover_bg'   => '#E31E24',
                'button_hover_text' => '#FFFFFF',
                'button_all_bg'         => '#E31E24',
                'button_all_text'       => '#FFFFFF',
                'button_all_hover_bg'   => '#CDF50D',
                'button_all_hover_text' => '#1E0D06',
                'button_dark_bg'         => '#1E0D06',
                'button_dark_text'       => '#E9E6D3',
                'button_dark_hover_bg'   => '#CDF50D',
                'button_dark_hover_text' => '#1E0D06',
                'post_page_bg'  => '#20063A',
                'post_box_bg'   => '#FFFFFF',
                'post_title'    => '#1E0D06',
                'post_text'     => '#2B211D',
                'post_link'     => '#B3161B',
                'post_nav_bg'   => '#FDFCF0',
                'post_nav_text' => '#1E0D06',
            ],
        ],
    ];
}

// Memória das configurações durante a página (atualizada a cada save_setting)
function &settings_cache(): ?array
{
    static $cache = null;
    return $cache;
}

function setting(string $name)
{
    $cache = &settings_cache();
    if ($cache === null) {
        $cache = settings_defaults();
        try {
            foreach (db_all('SELECT name, value FROM settings') as $row) {
                $saved = json_decode($row['value'], true);
                if (is_array($saved) && isset($cache[$row['name']]) && is_array($cache[$row['name']])) {
                    $cache[$row['name']] = settings_merge($cache[$row['name']], $saved);
                } elseif ($saved !== null) {
                    $cache[$row['name']] = $saved;
                }
            }
        } catch (PDOException $e) {
            // Banco ainda não instalado: usa só os padrões
        }
    }
    return $cache[$name] ?? null;
}

// O que foi salvo substitui o padrão chave a chave; listas (playlists, avisos) são trocadas inteiras,
// senão uma playlist apagada voltaria do padrão
function settings_merge(array $defaults, array $saved): array
{
    foreach ($saved as $key => $value) {
        if (is_array($value) && !array_is_list($value) && is_array($defaults[$key] ?? null) && !array_is_list($defaults[$key])) {
            $defaults[$key] = settings_merge($defaults[$key], $value);
        } else {
            $defaults[$key] = $value;
        }
    }
    return $defaults;
}

function save_setting(string $name, array $value): void
{
    $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    db_exec(
        'INSERT INTO settings (name, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)',
        [$name, $json]
    );
    // A próxima leitura na mesma página já vê o valor novo
    $cache = &settings_cache();
    if ($cache !== null) {
        $defaults = settings_defaults()[$name] ?? null;
        $cache[$name] = is_array($defaults) ? settings_merge($defaults, $value) : $value;
    }
}

// Fontes que já vêm junto com o site (não dependem do Google)
function bundled_fonts(): array
{
    return ['Syne', 'Bitter', 'Instrument Sans'];
}

// Larguras da Archivo: no Google Fonts são a mesma Archivo, só muda o quanto a letra é apertada ou esticada
function font_widths(): array
{
    return ['Archivo Extra Condensed' => '62.5%', 'Archivo Condensed' => '75%', 'Archivo Semi Condensed' => '87.5%', 'Archivo Semi Expanded' => '112.5%', 'Archivo Expanded' => '125%'];
}

// Nome escolhido no painel → família de verdade e largura
function font_parts(string $name): array
{
    $w = font_widths()[$name] ?? null;
    return $w ? ['family' => 'Archivo', 'stretch' => $w] : ['family' => $name, 'stretch' => '100%'];
}

// Lista oferecida no painel. Qualquer uma fora das embutidas é carregada do Google Fonts.
function font_choices(): array
{
    return [
        'Syne', 'Bitter', 'Instrument Sans', 'Anton', 'Archivo', 'Archivo Black', 'Bebas Neue',
        'Barlow', 'Barlow Condensed', 'Cormorant Garamond', 'Crimson Pro', 'DM Sans', 'DM Serif Display',
        'EB Garamond', 'Fraunces', 'Inter', 'Lato', 'Libre Baskerville', 'Lora', 'Merriweather',
        'Archivo Extra Condensed', 'Archivo Condensed', 'Archivo Semi Condensed', 'Archivo Semi Expanded', 'Archivo Expanded', 'Archivo Narrow',
        'Montserrat', 'Noticia Text', 'Nunito', 'Open Sans', 'Oswald', 'Playfair Display', 'Poppins',
        'PT Serif', 'Raleway', 'Roboto', 'Roboto Slab', 'Source Serif 4', 'Space Grotesk', 'Unbounded',
        'Work Sans',
    ];
}
