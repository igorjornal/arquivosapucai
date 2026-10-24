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
                'youtube'   => '',
                'instagram' => '',
                'facebook'  => '',
                'x'         => '',
                'tiktok'    => '',
                'linkedin'  => '',
            ],
        ],
        'home' => [
            'ticker'          => 'O seu acervo no YouTube para reviver os grandes momentos do carnaval na Marquês de Sapucaí.',
            'blog_title'      => 'Blog - Crônicas e Estudos de Caso',
            'blog_count'      => 6,
            'arquivo_title'   => 'O Arquivo',
            'arquivo_text'    => "Nosso objetivo é transformar o Canal Arquivo Sapucaí em um acervo mesmo, uma espécie de arquivo histórico, onde os apaixonados podem experienciar de novo os desfiles que marcaram épocas. Planejamos enriquecer este acervo aos poucos, garantindo a preservação e a celebração da rica história do carnaval do Rio de Janeiro.\n\nQuer participar? Se inscreva no nosso canal no YouTube e acompanhe a história do carnaval sendo contada desfile após desfile.",
            'arquivo_image'   => 'assets/img/print-youtube.webp',
            'arquivo_button'  => 'VEM VER! É DE GRAÇA!',
            'arquivo_link'    => '',
            'playlists_title' => 'Playlists no YouTube',
            'playlists_intro' => "Nosso objetivo é manter um acervo histórico, onde os sambistas e apaixonados podem rever os desfiles que marcaram épocas a hora que quiser. O acervo cresce aos poucos, garantindo a preservação e a celebração da rica história do carnaval do Rio de Janeiro. Inscreva-se, acompanhe e fique à vontade para participar. O \"Arquivo\" é de todos nós.",
            'playlists_outro' => "A história do carnaval se mistura com a história de cada um de nós. E reviver os desfiles traz na memória do sambista aquela sensação única, aquele frio na barriga. Então embarque no \"Arquivo\", pra eternizar esse sentimento.",
            'playlists'       => [
                ['title' => 'Grupo Especial', 'image' => 'assets/img/sample/desfile-1.webp', 'link' => ''],
                ['title' => 'Baterias', 'image' => 'assets/img/sample/desfile-2.webp', 'link' => ''],
                ['title' => 'Sapucaí', 'image' => 'assets/img/sample/desfile-3.webp', 'link' => ''],
            ],
            'acervo_image'    => 'assets/img/sample/acervo.webp',
            'acervo_title'    => 'O Acervo',
            'acervo_text'     => 'Preservamos a memória vibrante do Carnaval da Sapucaí, unindo a tradição oral e os registros visuais para garantir que a história dos nossos desfiles seja eternamente celebrada.',
            'historia_title'  => 'História',
            'historia_text'   => 'Explore milhares de registros históricos, desde croquis de fantasias até vídeos inéditos que capturam a essência da Sapucaí ao longo das décadas.',
            'acervo_button'   => 'SAIBA MAIS',
            'acervo_link'     => '/blog',
            'contact_title'   => 'Contato',
            'contact_text'    => 'Tem uma história, uma foto antiga ou uma correção pra mandar? Escreve pra gente.',
        ],
        'blog' => [
            'per_page'          => 9,
            'comments_enabled'  => true,
            'comments_moderate' => true,
            'share_buttons'     => true,
        ],
        'newsletter' => [
            'title' => 'Receba as novidades',
            'text'  => 'Novos posts, playlists e estudos de caso direto no seu e-mail.',
        ],
        'appearance' => [
            'fonts' => [
                'heading' => 'Syne',
                'body'    => 'Bitter',
                'ui'      => 'Instrument Sans',
            ],
            'colors' => [
                'header_bg'     => '#20063A',
                'header_text'   => '#FFFFFF',
                'header_accent' => '#CDF50D',
                'ticker_bg'     => '#E31E24',
                'ticker_text'   => '#FFFFFF',
                'blog_bg'       => '#20063A',
                'blog_title'    => '#CDF50D',
                'card_bg'       => '#F1F0EC',
                'card_title'    => '#1E0D06',
                'card_text'     => '#4A3F3A',
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

function setting(string $name)
{
    static $cache = null;
    if ($cache === null) {
        $cache = settings_defaults();
        try {
            foreach (db_all('SELECT name, value FROM settings') as $row) {
                $saved = json_decode($row['value'], true);
                if (is_array($saved) && isset($cache[$row['name']]) && is_array($cache[$row['name']])) {
                    $cache[$row['name']] = array_replace_recursive($cache[$row['name']], $saved);
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

function save_setting(string $name, array $value): void
{
    $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    db_exec(
        'INSERT INTO settings (name, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)',
        [$name, $json]
    );
}

// Fontes que já vêm junto com o site (não dependem do Google)
function bundled_fonts(): array
{
    return ['Syne', 'Bitter', 'Instrument Sans'];
}

// Lista oferecida no painel. Qualquer uma fora das embutidas é carregada do Google Fonts.
function font_choices(): array
{
    return [
        'Syne', 'Bitter', 'Instrument Sans', 'Anton', 'Archivo', 'Archivo Black', 'Bebas Neue',
        'Barlow', 'Barlow Condensed', 'Cormorant Garamond', 'Crimson Pro', 'DM Sans', 'DM Serif Display',
        'EB Garamond', 'Fraunces', 'Inter', 'Lato', 'Libre Baskerville', 'Lora', 'Merriweather',
        'Montserrat', 'Noticia Text', 'Nunito', 'Open Sans', 'Oswald', 'Playfair Display', 'Poppins',
        'PT Serif', 'Raleway', 'Roboto', 'Roboto Slab', 'Source Serif 4', 'Space Grotesk', 'Unbounded',
        'Work Sans',
    ];
}
