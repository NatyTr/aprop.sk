<?php
/**
 * Testy page ACF — one field group for the whole /testy/ page.
 */

function aprop_get_testy_acf_location_page_id() {
    $page = get_page_by_path( 'testy' );
    if ( $page && ! is_wp_error( $page ) ) {
        return (string) $page->ID;
    }
    return '14618';
}

function aprop_get_testy_acf_field_group() {
    return array(
        'key'                   => 'group_aprop_testy_page',
        'title'                 => 'Testy — obsah stránky',
        'fields'                => array(
            array(
                'key' => 'field_aprop_testy_tab_hero',
                'label' => 'Hero banner',
                'type' => 'tab',
                'placement' => 'top',
            ),
            array(
                'key' => 'field_aprop_testy_hero_banner_image',
                'label' => 'Hlavný obrázok',
                'name' => 'testy_hero_banner_image',
                'type' => 'image',
                'return_format' => 'array',
                'preview_size' => 'medium',
                'library' => 'all',
            ),
            array(
                'key' => 'field_aprop_testy_hero_eyebrow',
                'label' => 'Eyebrow',
                'name' => 'testy_hero_eyebrow',
                'type' => 'text',
                'default_value' => 'APROP / ONLINE TESTY',
            ),
            array(
                'key' => 'field_aprop_testy_hero_title_line_1',
                'label' => 'Nadpis - 1. riadok',
                'name' => 'testy_hero_title_line_1',
                'type' => 'text',
                'default_value' => 'Pripravte sa na skúšku pilota dronu.',
            ),
            array(
                'key' => 'field_aprop_testy_hero_title_line_2',
                'label' => 'Nadpis - 2. riadok',
                'name' => 'testy_hero_title_line_2',
                'type' => 'text',
            ),
            array(
                'key' => 'field_aprop_testy_hero_description',
                'label' => 'Popis',
                'name' => 'testy_hero_description',
                'type' => 'textarea',
                'rows' => 3,
                'default_value' => 'Precvičujte otázky pre A1/A3, A2 a STS. Sledujte výsledky a pripravujte sa vlastným tempom.',
            ),
            array(
                'key' => 'field_aprop_testy_hero_primary_button_text',
                'label' => 'Primárne tlačidlo - text',
                'name' => 'testy_hero_primary_button_text',
                'type' => 'text',
                'default_value' => 'Vybrať prípravu',
            ),
            array(
                'key' => 'field_aprop_testy_hero_primary_button_url',
                'label' => 'Primárne tlačidlo - URL',
                'name' => 'testy_hero_primary_button_url',
                'type' => 'url',
                'default_value' => '/testy/',
            ),
            array(
                'key' => 'field_aprop_testy_hero_secondary_button_text',
                'label' => 'Sekundárne tlačidlo - text',
                'name' => 'testy_hero_secondary_button_text',
                'type' => 'text',
                'default_value' => 'Vyskúšať A1/A3 zadarmo',
            ),
            array(
                'key' => 'field_aprop_testy_hero_secondary_button_url',
                'label' => 'Sekundárne tlačidlo - URL',
                'name' => 'testy_hero_secondary_button_url',
                'type' => 'url',
                'default_value' => '/produkt/hobby-kurz-drony/',
            ),
            array(
                'key' => 'field_aprop_testy_hero_stats_label',
                'label' => 'Zelený box - horný text',
                'name' => 'testy_hero_stats_label',
                'type' => 'text',
                'default_value' => 'Váš najlepší výsledok',
            ),
            array(
                'key' => 'field_aprop_testy_hero_stats_value',
                'label' => 'Zelený box - hodnota',
                'name' => 'testy_hero_stats_value',
                'type' => 'text',
                'default_value' => '93 %',
            ),
            array(
                'key' => 'field_aprop_testy_hero_stats_caption',
                'label' => 'Zelený box - dolný text',
                'name' => 'testy_hero_stats_caption',
                'type' => 'text',
                'default_value' => 'A2 · Ukážkové údaje',
            ),
            array(
                'key' => 'field_aprop_testy_tab_process',
                'label' => 'Ako to prebieha',
                'type' => 'tab',
                'placement' => 'top',
            ),
            array(
                'key' => 'field_aprop_testy_process_header_tab',
                'label' => 'Hlavička',
                'type' => 'tab',
                'placement' => 'left',
            ),
            array(
                'key' => 'field_aprop_testy_process_eyebrow',
                'label' => 'Eyebrow',
                'name' => 'testy_process_eyebrow',
                'type' => 'text',
                'default_value' => 'AKO TO PREBIEHA',
            ),
            array(
                'key' => 'field_aprop_testy_process_title',
                'label' => 'Nadpis',
                'name' => 'testy_process_title',
                'type' => 'textarea',
                'rows' => 2,
                'default_value' => "Od prvého kliknutia\nk lepšej príprave.",
            ),
            array(
                'key' => 'field_aprop_testy_process_intro',
                'label' => 'Úvodný text',
                'name' => 'testy_process_intro',
                'type' => 'textarea',
                'rows' => 3,
                'default_value' => 'Jasný postup. Vlastné tempo. Všetko, čo potrebujete na online prípravu, na jednom mieste.',
            ),
            array(
                'key' => 'field_aprop_testy_process_card_1_tab',
                'label' => 'Karta 1',
                'type' => 'tab',
                'placement' => 'left',
            ),
            array(
                'key' => 'field_aprop_testy_process_card_1_number',
                'label' => 'Číslo',
                'name' => 'testy_process_card_1_number',
                'type' => 'text',
                'default_value' => '01',
            ),
            array(
                'key' => 'field_aprop_testy_process_card_1_icon',
                'label' => 'Ikona',
                'name' => 'testy_process_card_1_icon',
                'type' => 'image',
                'return_format' => 'array',
                'preview_size' => 'thumbnail',
                'library' => 'all',
                'instructions' => 'Ak nevyplníš, použije sa predvolená ikona z theme.',
            ),
            array(
                'key' => 'field_aprop_testy_process_card_1_label',
                'label' => 'Label',
                'name' => 'testy_process_card_1_label',
                'type' => 'text',
                'default_value' => 'VÝBER PRÍPRAVY',
            ),
            array(
                'key' => 'field_aprop_testy_process_card_1_title',
                'label' => 'Nadpis',
                'name' => 'testy_process_card_1_title',
                'type' => 'text',
                'default_value' => 'Nájdite svoj smer.',
            ),
            array(
                'key' => 'field_aprop_testy_process_card_1_description',
                'label' => 'Popis',
                'name' => 'testy_process_card_1_description',
                'type' => 'textarea',
                'rows' => 3,
                'default_value' => 'Začínate, rozširujete si vedomosti alebo sa pripravujete na STS? Vyberte si prípravu podľa svojho cieľa.',
            ),
            array(
                'key' => 'field_aprop_testy_process_card_1_tags',
                'label' => 'Tagy (oddelené čiarkou)',
                'name' => 'testy_process_card_1_tags',
                'type' => 'text',
                'default_value' => 'A1/A3, A2, STS',
            ),
            array(
                'key' => 'field_aprop_testy_process_card_2_tab',
                'label' => 'Karta 2',
                'type' => 'tab',
                'placement' => 'left',
            ),
            array(
                'key' => 'field_aprop_testy_process_card_2_number',
                'label' => 'Číslo',
                'name' => 'testy_process_card_2_number',
                'type' => 'text',
                'default_value' => '02',
            ),
            array(
                'key' => 'field_aprop_testy_process_card_2_icon',
                'label' => 'Ikona',
                'name' => 'testy_process_card_2_icon',
                'type' => 'image',
                'return_format' => 'array',
                'preview_size' => 'thumbnail',
                'library' => 'all',
                'instructions' => 'Ak nevyplníš, použije sa predvolená ikona z theme.',
            ),
            array(
                'key' => 'field_aprop_testy_process_card_2_label',
                'label' => 'Label',
                'name' => 'testy_process_card_2_label',
                'type' => 'text',
                'default_value' => 'VÁŠ PRÍSTUP',
            ),
            array(
                'key' => 'field_aprop_testy_process_card_2_title',
                'label' => 'Nadpis',
                'name' => 'testy_process_card_2_title',
                'type' => 'text',
                'default_value' => 'Všetko vo vašom účte.',
            ),
            array(
                'key' => 'field_aprop_testy_process_card_2_description',
                'label' => 'Popis',
                'name' => 'testy_process_card_2_description',
                'type' => 'textarea',
                'rows' => 3,
                'default_value' => 'Zaregistrujte sa a otvorte si vybranú prípravu. A1/A3 si môžete najprv vyskúšať úplne zadarmo.',
            ),
            array(
                'key' => 'field_aprop_testy_process_card_2_footer',
                'label' => 'Dolný text',
                'name' => 'testy_process_card_2_footer',
                'type' => 'text',
                'default_value' => 'Vaša príprava na jednom mieste',
            ),
            array(
                'key' => 'field_aprop_testy_process_card_3_tab',
                'label' => 'Karta 3',
                'type' => 'tab',
                'placement' => 'left',
            ),
            array(
                'key' => 'field_aprop_testy_process_card_3_number',
                'label' => 'Číslo',
                'name' => 'testy_process_card_3_number',
                'type' => 'text',
                'default_value' => '03',
            ),
            array(
                'key' => 'field_aprop_testy_process_card_3_icon',
                'label' => 'Ikona',
                'name' => 'testy_process_card_3_icon',
                'type' => 'image',
                'return_format' => 'array',
                'preview_size' => 'thumbnail',
                'library' => 'all',
                'instructions' => 'Ak nevyplníš, použije sa predvolená ikona z theme.',
            ),
            array(
                'key' => 'field_aprop_testy_process_card_3_label',
                'label' => 'Label',
                'name' => 'testy_process_card_3_label',
                'type' => 'text',
                'default_value' => 'TRÉNING A VÝSLEDKY',
            ),
            array(
                'key' => 'field_aprop_testy_process_card_3_title',
                'label' => 'Nadpis',
                'name' => 'testy_process_card_3_title',
                'type' => 'text',
                'default_value' => 'Každý pokus vás posúva.',
            ),
            array(
                'key' => 'field_aprop_testy_process_card_3_description',
                'label' => 'Popis',
                'name' => 'testy_process_card_3_description',
                'type' => 'textarea',
                'rows' => 3,
                'default_value' => 'Precvičujte otázky vlastným tempom. Pozrite si výsledok a sledujte, ako sa vám darí v ďalších pokusoch.',
            ),
            array(
                'key' => 'field_aprop_testy_process_card_3_footer',
                'label' => 'Dolný text',
                'name' => 'testy_process_card_3_footer',
                'type' => 'text',
                'default_value' => 'Vlastné tempo',
            ),
            array(
                'key' => 'field_aprop_testy_tab_try',
                'label' => 'Vyskúšať zadarmo',
                'type' => 'tab',
                'placement' => 'top',
            ),
            array(
                'key' => 'field_aprop_testy_try_eyebrow',
                'label' => 'Eyebrow',
                'name' => 'testy_try_eyebrow',
                'type' => 'text',
                'default_value' => 'A1/A3 ZADARMO',
            ),
            array(
                'key' => 'field_aprop_testy_try_title',
                'label' => 'Nadpis',
                'name' => 'testy_try_title',
                'type' => 'textarea',
                'rows' => 2,
                'default_value' => "Najprv si prípravu\nvyskúšajte.",
            ),
            array(
                'key' => 'field_aprop_testy_try_description',
                'label' => 'Popis',
                'name' => 'testy_try_description',
                'type' => 'textarea',
                'rows' => 3,
                'default_value' => 'Zaregistrujte sa a začnite s A1/A3 zadarmo. Precvičte si otázky a pozrite si svoje výsledky.',
            ),
            array(
                'key' => 'field_aprop_testy_try_button_text',
                'label' => 'Tlačidlo - text',
                'name' => 'testy_try_button_text',
                'type' => 'text',
                'default_value' => 'Vyskúšať A1/A3 zadarmo',
            ),
            array(
                'key' => 'field_aprop_testy_try_button_url',
                'label' => 'Tlačidlo - URL',
                'name' => 'testy_try_button_url',
                'type' => 'url',
                'default_value' => '/moj-ucet/',
            ),
            array(
                'key' => 'field_aprop_testy_try_note',
                'label' => 'Poznámka pod tlačidlom',
                'name' => 'testy_try_note',
                'type' => 'text',
                'default_value' => 'Príprava A1/A3 · Po registrácii',
            ),
            array(
                'key' => 'field_aprop_testy_try_image',
                'label' => 'Obrázok',
                'name' => 'testy_try_image',
                'type' => 'image',
                'return_format' => 'array',
                'preview_size' => 'medium',
                'library' => 'all',
                'instructions' => 'Ak nevyplníš, použije sa predvolený obrázok z theme.',
            ),
            array(
                'key' => 'field_aprop_testy_try_image_badge',
                'label' => 'Badge na obrázku (mobile)',
                'name' => 'testy_try_image_badge',
                'type' => 'text',
                'default_value' => 'OD TEÓRIE K PRAXI',
            ),
            array(
                'key' => 'field_aprop_testy_tab_overview',
                'label' => 'Vaša online príprava',
                'type' => 'tab',
                'placement' => 'top',
            ),
            array(
                'key' => 'field_aprop_testy_overview_header_tab',
                'label' => 'Hlavička',
                'type' => 'tab',
                'placement' => 'left',
            ),
            array(
                'key' => 'field_aprop_testy_overview_eyebrow',
                'label' => 'Eyebrow',
                'name' => 'testy_overview_eyebrow',
                'type' => 'text',
                'default_value' => 'VAŠA ONLINE PRÍPRAVA',
            ),
            array(
                'key' => 'field_aprop_testy_overview_title',
                'label' => 'Nadpis',
                'name' => 'testy_overview_title',
                'type' => 'textarea',
                'rows' => 2,
                'default_value' => "Každý pokus.\nLepší prehľad.",
            ),
            array(
                'key' => 'field_aprop_testy_overview_description',
                'label' => 'Popis',
                'name' => 'testy_overview_description',
                'type' => 'textarea',
                'rows' => 2,
                'default_value' => 'Otázky, výsledky aj história prípravy. Všetko na jednom mieste.',
            ),
            array(
                'key' => 'field_aprop_testy_overview_disclaimer',
                'label' => 'Disclaimer',
                'name' => 'testy_overview_disclaimer',
                'type' => 'text',
                'default_value' => 'Náhľady rozhrania a výsledky sú ilustračné.',
            ),
            array(
                'key' => 'field_aprop_testy_overview_card_1_tab',
                'label' => 'Karta 1',
                'type' => 'tab',
                'placement' => 'left',
            ),
            array(
                'key' => 'field_aprop_testy_overview_card_1_number',
                'label' => 'Číslo',
                'name' => 'testy_overview_card_1_number',
                'type' => 'text',
                'default_value' => '01',
            ),
            array(
                'key' => 'field_aprop_testy_overview_card_1_title',
                'label' => 'Nadpis',
                'name' => 'testy_overview_card_1_title',
                'type' => 'text',
                'default_value' => 'Precvičujte otázky',
            ),
            array(
                'key' => 'field_aprop_testy_overview_card_1_description',
                'label' => 'Popis',
                'name' => 'testy_overview_card_1_description',
                'type' => 'textarea',
                'rows' => 2,
                'default_value' => 'Prehľadné testovanie vás prevedie prípravou krok za krokom.',
            ),
            array(
                'key' => 'field_aprop_testy_overview_card_2_tab',
                'label' => 'Karta 2',
                'type' => 'tab',
                'placement' => 'left',
            ),
            array(
                'key' => 'field_aprop_testy_overview_card_2_number',
                'label' => 'Číslo',
                'name' => 'testy_overview_card_2_number',
                'type' => 'text',
                'default_value' => '02',
            ),
            array(
                'key' => 'field_aprop_testy_overview_card_2_title',
                'label' => 'Nadpis',
                'name' => 'testy_overview_card_2_title',
                'type' => 'text',
                'default_value' => 'Pozrite si výsledok',
            ),
            array(
                'key' => 'field_aprop_testy_overview_card_2_description',
                'label' => 'Popis',
                'name' => 'testy_overview_card_2_description',
                'type' => 'textarea',
                'rows' => 2,
                'default_value' => 'Po dokončení testu uvidíte, ako sa vám darilo.',
            ),
            array(
                'key' => 'field_aprop_testy_overview_card_3_tab',
                'label' => 'Karta 3',
                'type' => 'tab',
                'placement' => 'left',
            ),
            array(
                'key' => 'field_aprop_testy_overview_card_3_number',
                'label' => 'Číslo',
                'name' => 'testy_overview_card_3_number',
                'type' => 'text',
                'default_value' => '03',
            ),
            array(
                'key' => 'field_aprop_testy_overview_card_3_title',
                'label' => 'Nadpis',
                'name' => 'testy_overview_card_3_title',
                'type' => 'text',
                'default_value' => 'Sledujte svoj pokrok',
            ),
            array(
                'key' => 'field_aprop_testy_overview_card_3_description',
                'label' => 'Popis',
                'name' => 'testy_overview_card_3_description',
                'type' => 'textarea',
                'rows' => 2,
                'default_value' => 'Vráťte sa k výsledkom a porovnajte svoje pokusy.',
            ),
            array(
                'key' => 'field_aprop_testy_tab_faq',
                'label' => 'FAQ',
                'type' => 'tab',
                'placement' => 'top',
            ),
            array(
                'key' => 'field_aprop_testy_faq_header_tab',
                'label' => 'Hlavička',
                'type' => 'tab',
                'placement' => 'left',
            ),
            array(
                'key' => 'field_aprop_testy_faq_eyebrow',
                'label' => 'Eyebrow',
                'name' => 'testy_faq_eyebrow',
                'type' => 'text',
                'default_value' => 'DOBRÉ VEDIEŤ',
            ),
            array(
                'key' => 'field_aprop_testy_faq_title',
                'label' => 'Nadpis',
                'name' => 'testy_faq_title',
                'type' => 'text',
                'default_value' => "Ešte predtým,\nnež začnete.",
            ),
            array(
                'key' => 'field_aprop_testy_faq_description',
                'label' => 'Popis',
                'name' => 'testy_faq_description',
                'type' => 'textarea',
                'rows' => 2,
                'default_value' => 'Odpovede na otázky o prístupe a online príprave.',
            ),
            array(
                'key' => 'field_aprop_testy_faq_item_1_tab',
                'label' => 'Otázka 1',
                'type' => 'tab',
                'placement' => 'left',
            ),
            array(
                'key' => 'field_aprop_testy_faq_q_1',
                'label' => 'Otázka',
                'name' => 'testy_faq_q_1',
                'type' => 'text',
                'default_value' => 'Čo si môžem vyskúšať zadarmo?',
            ),
            array(
                'key' => 'field_aprop_testy_faq_a_1',
                'label' => 'Odpoveď',
                'name' => 'testy_faq_a_1',
                'type' => 'textarea',
                'rows' => 3,
                'default_value' => 'Po registrácii získate prístup k príprave A1/A3. Môžete si vyskúšať testovanie a pozrieť svoje výsledky.',
            ),
            array(
                'key' => 'field_aprop_testy_faq_item_2_tab',
                'label' => 'Otázka 2',
                'type' => 'tab',
                'placement' => 'left',
            ),
            array(
                'key' => 'field_aprop_testy_faq_q_2',
                'label' => 'Otázka',
                'name' => 'testy_faq_q_2',
                'type' => 'text',
                'default_value' => 'Kedy získam platený prístup?',
            ),
            array(
                'key' => 'field_aprop_testy_faq_a_2',
                'label' => 'Odpoveď',
                'name' => 'testy_faq_a_2',
                'type' => 'textarea',
                'rows' => 3,
                'default_value' => 'Prihlasovacie údaje k testom posielame do 24 hodín od prijatia úhrady.',
            ),
            array(
                'key' => 'field_aprop_testy_faq_item_3_tab',
                'label' => 'Otázka 3',
                'type' => 'tab',
                'placement' => 'left',
            ),
            array(
                'key' => 'field_aprop_testy_faq_q_3',
                'label' => 'Otázka',
                'name' => 'testy_faq_q_3',
                'type' => 'text',
                'default_value' => 'Ako dlho platí zakúpený prístup?',
            ),
            array(
                'key' => 'field_aprop_testy_faq_a_3',
                'label' => 'Odpoveď',
                'name' => 'testy_faq_a_3',
                'type' => 'textarea',
                'rows' => 3,
                'default_value' => 'Prístup k plateným testom A2 alebo STS je na 1 mesiac. Pri zakúpení balíka testov A2 + STS, máte prístup až na 3 mesiace.',
            ),
            array(
                'key' => 'field_aprop_testy_faq_item_4_tab',
                'label' => 'Otázka 4',
                'type' => 'tab',
                'placement' => 'left',
            ),
            array(
                'key' => 'field_aprop_testy_faq_q_4',
                'label' => 'Otázka',
                'name' => 'testy_faq_q_4',
                'type' => 'text',
                'default_value' => 'Získam absolvovaním testov osvedčenie?',
            ),
            array(
                'key' => 'field_aprop_testy_faq_a_4',
                'label' => 'Odpoveď',
                'name' => 'testy_faq_a_4',
                'type' => 'textarea',
                'rows' => 3,
                'default_value' => 'Nie. Naše testy slúžia iba na prípravu na skúšky na Dopravnom úrade, ktorý Vám po ich úspešnom zvládnutí vydá platné osvedčenie pilota na diaľku.',
            ),
            array(
                'key' => 'field_aprop_testy_faq_item_5_tab',
                'label' => 'Otázka 5',
                'type' => 'tab',
                'placement' => 'left',
            ),
            array(
                'key' => 'field_aprop_testy_faq_q_5',
                'label' => 'Otázka',
                'name' => 'testy_faq_q_5',
                'type' => 'text',
                'default_value' => '',
            ),
            array(
                'key' => 'field_aprop_testy_faq_a_5',
                'label' => 'Odpoveď',
                'name' => 'testy_faq_a_5',
                'type' => 'textarea',
                'rows' => 3,
                'default_value' => '',
            ),
            array(
                'key' => 'field_aprop_testy_faq_item_6_tab',
                'label' => 'Otázka 6',
                'type' => 'tab',
                'placement' => 'left',
            ),
            array(
                'key' => 'field_aprop_testy_faq_q_6',
                'label' => 'Otázka',
                'name' => 'testy_faq_q_6',
                'type' => 'text',
                'default_value' => 'Potrebujem aj výklad. Čo si mám vybrať?',
            ),
            array(
                'key' => 'field_aprop_testy_faq_a_6',
                'label' => 'Odpoveď',
                'name' => 'testy_faq_a_6',
                'type' => 'textarea',
                'rows' => 3,
                'default_value' => 'V prípade potreby výkladu odporúčame zakúpiť jeden z našich kurzov, kde okrem prístupu k samotným testom, dostanete kvalitné online školenie od nášho certifikovaného inštruktora a zároveň aj prístup k študijným materiálom a aktuálnej mape zemepisných oblastí UAS.',
            ),
            array(
                'key' => 'field_aprop_testy_tab_packages',
                'label' => 'Balíčky príprav',
                'type' => 'tab',
                'placement' => 'top',
            ),
            array(
                'key' => 'field_aprop_testy_packages_eyebrow',
                'label' => 'Eyebrow',
                'name' => 'testy_packages_eyebrow',
                'type' => 'text',
                'default_value' => 'VYBERTE SI PRÍPRAVU',
            ),
            array(
                'key' => 'field_aprop_testy_packages_title',
                'label' => 'Nadpis',
                'name' => 'testy_packages_title',
                'type' => 'textarea',
                'rows' => 2,
                'default_value' => "Vaša skúška.\nVáš ďalší krok.",
            ),
            array(
                'key' => 'field_aprop_testy_packages_description',
                'label' => 'Popis',
                'name' => 'testy_packages_description',
                'type' => 'textarea',
                'rows' => 3,
                'default_value' => 'Začínate? Spoznajte základy s A1/A3. Máte konkrétny cieľ? Vyberte A2 alebo STS. Viac príprav nájdete v balíkoch nižšie.',
            ),
            array(
                'key' => 'field_aprop_testy_packages_bundles_title',
                'label' => 'Nadpis balíkov',
                'name' => 'testy_packages_bundles_title',
                'type' => 'text',
                'default_value' => 'Chcete toho zvládnuť viac?',
            ),
            array(
                'key' => 'field_aprop_testy_packages_bundles_description',
                'label' => 'Popis balíkov',
                'name' => 'testy_packages_bundles_description',
                'type' => 'textarea',
                'rows' => 2,
                'default_value' => 'Spojte prípravy do jedného prístupu a ušetrite čas aj peniaze.',
            ),
        ),
        'location'              => array(
            array(
                array(
                    'param'    => 'page',
                    'operator' => '==',
                    'value'    => aprop_get_testy_acf_location_page_id(),
                ),
            ),
        ),
        'menu_order'            => 0,
        'position'              => 'normal',
        'style'                 => 'default',
        'label_placement'       => 'top',
        'instruction_placement' => 'label',
        'active'                => true,
    );
}

function aprop_get_testy_acf_field_groups() {
    return array( aprop_get_testy_acf_field_group() );
}

function aprop_get_acf_field_group_db_id( $key ) {
    $posts = get_posts(
        array(
            'post_type'              => 'acf-field-group',
            'name'                   => $key,
            'post_status'            => array( 'publish', 'acf-disabled', 'draft', 'private' ),
            'posts_per_page'         => 1,
            'fields'                 => 'ids',
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        )
    );

    return $posts ? (int) $posts[0] : 0;
}

function aprop_cleanup_legacy_testy_acf_field_groups() {
    $legacy_keys = array(
        'group_aprop_testy_hero',
        'group_aprop_testy_process',
        'group_aprop_testy_try',
        'group_aprop_testy_overview',
        'group_aprop_testy_faq',
        'group_aprop_testy_packages',
    );

    foreach ( $legacy_keys as $key ) {
        $posts = get_posts(
            array(
                'post_type'      => 'acf-field-group',
                'name'           => $key,
                'post_status'    => array( 'publish', 'acf-disabled', 'draft', 'private', 'trash' ),
                'posts_per_page' => -1,
                'fields'         => 'ids',
            )
        );
        foreach ( $posts as $id ) {
            wp_delete_post( (int) $id, true );
        }
        $json = get_stylesheet_directory() . '/acf-json/' . $key . '.json';
        if ( file_exists( $json ) ) {
            unlink( $json );
        }
    }
}

function aprop_persist_testy_acf_field_groups_to_db() {
    if ( ! function_exists( 'acf_import_field_group' ) ) {
        return;
    }

    aprop_cleanup_legacy_testy_acf_field_groups();

    $json_dir = get_stylesheet_directory() . '/acf-json';
    if ( ! is_dir( $json_dir ) ) {
        wp_mkdir_p( $json_dir );
    }

    foreach ( aprop_get_testy_acf_field_groups() as $field_group ) {
        if ( empty( $field_group['key'] ) ) {
            continue;
        }

        if ( aprop_get_acf_field_group_db_id( $field_group['key'] ) ) {
            continue;
        }

        $imported = acf_import_field_group( $field_group );
        if ( $imported && function_exists( 'acf_write_json_field_group' ) ) {
            acf_write_json_field_group( $imported );
        }
    }

    update_option( 'aprop_testy_acf_persisted', time(), false );
}

add_action( 'acf/init', 'aprop_persist_testy_acf_field_groups_to_db', 20 );

add_filter( 'acf/settings/save_json', function ( $path ) {
    return get_stylesheet_directory() . '/acf-json';
} );

add_filter( 'acf/settings/load_json', function ( $paths ) {
    $paths[] = get_stylesheet_directory() . '/acf-json';
    return $paths;
} );
