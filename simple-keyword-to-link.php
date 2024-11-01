<?php
/*
Plugin Name: Simple Keyword to Link
Description: Really Simple "Keyword to Link" Converter. Automatically create links for specific words in your content
Version: 1.5
Author: Alessandro Piconi
Author URI: https://invernomuto.net
*/

define('plugin_slug', 'simple-keyword-to-link');

class Sablab_Linkify_Words {

    private $customSlug = 'linksSk2L';
    private $subs_done = 0;

    public function __construct() {
        add_filter( 'the_content', array( $this, 'linkify_words' ) );
        add_action( 'admin_menu', array( $this, 'build_menu' ) );
        add_action( 'init', array($this, 'load_languages') );

        add_action( 'init', array($this, 'add_custom_rewrite_rule'));
        add_action('template_redirect', array($this, 'save_link_stats'));
        add_filter('query_vars', array($this, 'my_custom_vars'));
    }

    function add_custom_rewrite_rule()
    {
        //* SE NON FUNZIONA, POTREBBE ESSERE LA CACHE DELLE REWRITE URL, ABILITARE E DISABILITARE IL TIPO DI PERMANLINK
        add_rewrite_rule('^'.$this->customSlug.'/([^/]+)/?', 'index.php?slugSk2L=$matches[1]', 'top');
    }

    function my_custom_vars($vars)
    {
        $vars[] = 'slugSk2L';
        return $vars;
    }

    function save_link_stats()
    {
        if (get_query_var('slugSk2L')) {
            //* ottengo i dati associati allo slug
            $words_to_links = get_option( plugin_slug.'_data' );
            if ( ! empty( $words_to_links ) ) 
            {
                foreach( $words_to_links AS &$word_to_link )
                {
                    if( sanitize_title($word_to_link['keyword']) == get_query_var('slugSk2L') )
                    { 
                        // echo "<pre>";
                        // print_r($word_to_link);
                        //* SCRIVO LA STATS SOLO SE NON È UN BOT
                        if( !$this->is_crawler() ){
                            //* scrivo un log di testo csv con le info - che poi diventerà qualcosa di più sofisticato o la base di dati per mostrare i dati
                            $filename = plugin_dir_path( __FILE__ ) . 'stats.csv';
                            $info = [
                                'date' => date('Y-m-d H:i:s'),
                                'keyword' => $word_to_link['keyword'],
                                'url' => $word_to_link['link'],
                                'ip' => $_SERVER['REMOTE_ADDR'],
                                'referer' => $_SERVER['HTTP_REFERER'],
                                'agent' => $_SERVER['HTTP_USER_AGENT'],
                                'lang' => $_SERVER['HTTP_ACCEPT_LANGUAGE'],
                            ];
                            $handle = fopen($filename, 'a');
                            fputcsv($handle, $info);
                            fclose($handle);

                        }

                        //* faccio il redirect;
                        wp_redirect($word_to_link['link'], 302);
                        exit;
                    }
                }
            }
            //* se arrivo qui, qualcosa è andato storto e sparo il 404
            global $wp_query;
            // Imposta lo status della query a 404
            $wp_query->set_404(); 
            // Forza l'uscita
            status_header( 404 );
            get_template_part( 404 ); 
            exit();
        }
    }

    function build_menu() 
    {   
        add_menu_page( 'Simple Keyword To Link', 'Simple Keyword To Link', 'manage_options', 'simple-keyword-to-link-words', 
            array( $this, 'render_settings_page' ), 'dashicons-admin-links', 4 );
        //TODO PORCATELLA PER AGGIUNGERE UN LINK CHE SIA CHIAMABILE PER ESEGUIRE UNA FUNZIONE: TROVARE IL MODO CORRETTO
        add_submenu_page( 'simple-keyword-to-link-words-unbind', 'Delete', 'delete', 'manage_options', 'simple-keyword-to-link-delete-element', 
            array( $this, 'delete_element' ));
    }

    function load_languages()
    {
        load_plugin_textdomain( plugin_slug, false, basename( dirname( __FILE__ ) ) . '/languages/' );
    }

    public function linkify_words( $content ) 
    {
        $words_to_links = get_option( plugin_slug.'_data' );
        if ( ! empty( $words_to_links ) ) 
        {
            foreach( $words_to_links as $word_to_link ) 
            {
                //* AD OGNI CICLO RICARICO IL DOCUMENTO PER AVERE I NODI AGGIORNATI AL CICLO PRECEDENTE
                $doc = new DOMDocument();
                @$doc->loadHTML('<?xml encoding="UTF-8">'.$content);
                $doc->encoding = 'UTF-8';

                //* SE LA PAROLA HA ALMENO UN OCCORRENZA NEL CONTENUTO
                if( stripos($doc->textContent, $word_to_link['keyword']) !== false )
                {
                    //* INIZIALIZZO LE VARIABILI DI CONTEGGIO
                    $this->subs_done = 0;
                    $options['subs_max'] = $word_to_link['num'];
                    //* PROCEDO CON LA SOSTITUZIONE
                    $options = [
                        'caseSensitive' => ( $word_to_link['caseSensitive'] == 'si' ),
                        'fullWord' => ( $word_to_link['fullWord'] == 'si' ),
                        'enableStats' => ( $word_to_link['enableStats'] == 'si' ),
                        'subs_max'  => $word_to_link['num'],
                        'targetBlank' => ( isset($word_to_link['targetBlank']) AND $word_to_link['targetBlank'] == 'si' ),
                    ];
                    //* SE SONO ATTIVE LE STATISTICHE CAMBIO IL LINK CON LO SLUG ASSOCIATO
                    if( $options['enableStats'] )
                        $word_to_link['link'] = get_site_url().'/'.$this->customSlug.'/'.sanitize_title($word_to_link['keyword']);
                                  
                    $changed = $this->insertLink($doc, $word_to_link['keyword'], $word_to_link['link'], $options);
                }

                //* USO $DOC PERCHÉ ALTRIMENTI SE NON VIENE TORVATA NESSUNA OCCORRENZA, NON VIENE MOSTRATO IL CONTENUTO
                //* PRENDO IL CONTENUTO DEL BODY PERCHÉ ALTRIMENTI HO ANCHE TUTTE LE INTESTAZIONI RIPETUTE
                $body = $doc->documentElement->lastChild;
                $content = htmlspecialchars_decode($doc->saveHTML($body));

                $content = str_replace( ['<body>', '</body>'], ['', ''], $content);
            }
        }
        return $content;
    }

    //* FUNZIONE CHE CHIAMO PER OGNI KEYWORD, SE HA ALMENO UN OCCORRENZA NEL CONTENUTO
    function insertLink( DOMNode $domNode, $word, $replacement, $options ){
        //* SE HO CONCLUSO TUTTE LE SOSTITUZIONI DISPONIBILI NON PROCEDO OLTRE
        if( $options['subs_max'] != -1 AND $this->subs_done >= $options['subs_max'] )
            return $domNode;

        //* ACCETTO IN ENTRATA UN NODO E CICLO I FIGLI
        foreach ($domNode->childNodes as $node) 
        {
            //* SE È UN NODO TI TIPO LINK - PROCEDO OLTRE
            if($node->nodeName == 'a') continue;

            //* VERIFICO SE È UN NODO DI TIPO TEXT E ALLORA AGISCO
            if($node->nodeName == '#text')
            {
                $per_post_limit = $options['subs_max'] - $this->subs_done;

                //* SE È UN LINK AFFILIATO CHE USA LE STATISTICHE, APRO A PAGINA NUOVA E IMPOSTO DI NON SEGUIRLO
                $linkProperties = [];
                if( $options['enableStats'] ){
                    $linkProperties['rel'] = 'rel="nofollow"';
                    $linkProperties['target'] = 'target="_blank"';
                }
                if( $options['targetBlank'] )
                    $linkProperties['target'] = 'target="_blank"';

                //* CONTROLLO SE CI SONO ANCORA SISTUZIONI DA FARE
                if( $per_post_limit > 0 OR $options['subs_max'] == -1)
                {    
                    //* VERIFICO SE LA SOSTITUZIONE DEVE ESSERE FULL WORD, non sostituisco quindi parti di parole.
                    if( $options['fullWord'] )
                    {
                        //* VERIFICO SE LA SOSTITUZIONE DEVE ESSERE CASE SENSITIVE
                        $toReplace = ( $options['caseSensitive'] ) ? "/( )(".$word.")([ .,;:-])/" : "/( )(".$word.")([ .,;:-])/i";
                        $node->nodeValue = preg_replace( $toReplace, "\$1<a href=\"" . $replacement . "\" ".implode(' ', $linkProperties).">\$2</a>\$3", $node->nodeValue, $per_post_limit, $subs_done);
                    }
                    else
                    {
                        //* VERIFICO SE LA SOSTITUZIONE DEVE ESSERE CASE SENSITIVE
                        $toReplace = ( $options['caseSensitive'] ) ? "/".$word."/" : "/".$word."/i";
                        $node->nodeValue = preg_replace( $toReplace, "<a href=\"" . $replacement . "\" ".implode(' ', $linkProperties).">\$0</a>", $node->nodeValue, $per_post_limit, $subs_done);
                    }
                    
                }
                
                //* SE SONO STATE FATTE DELLE SOSTITUZIONI, AGGIORNO IL TOTALE DI QUELLE FATTE
                if( isset($subs_done) )
                    $this->subs_done += $subs_done;
            }
            
            //* VERIFICO SE IL NODO HA DEI FIGLI E IN CASO CHIAMO DI NUOVO QUESTA FUNZIONE
			if( $node->hasChildNodes() ) 
            {
				$this->insertLink( $node, $word, $replacement, $options );
			}            
        }
        return $domNode;
    }

    public function delete_element()
    {
        if( isset($_GET['pos']) )
        {
            $pos2delete = intval($_GET['pos']);
            $words_to_links = get_option( plugin_slug.'_data' );
            if ( ! empty( $words_to_links ) ) 
            {
                unset( $words_to_links[$pos2delete] );
                $words_to_links = array_values($words_to_links);
            }
            update_option( plugin_slug.'_data', $words_to_links );
        }
        die('<script>location.href="admin.php?page=simple-keyword-to-link-words"</script>');
    }

    public function render_settings_page() 
    {
        //* SE C'È IL POST ALLORA INSERISCO I DATI NEL FILE
		if( count( $_POST ) AND !empty($_POST['word_to_link_word']) AND !empty($_POST['word_to_link_link']) )
        {
            $word = sanitize_text_field($_POST['word_to_link_word']);
            $link = sanitize_url($_POST['word_to_link_link']);
            $num = trim(intval($_POST['word_to_link_num']));
            $caseSensitive = sanitize_text_field($_POST['word_to_link_casesensitive']);
            $fullWord = sanitize_text_field($_POST['word_to_link_fullword']);
            $targetBlank = sanitize_text_field($_POST['word_to_link_targetblank']);
            $enableStats = sanitize_text_field($_POST['word_to_link_enable_stats']);
            
            //* CICLO TUTTE LE PAROLE CHIAVI PER SOVRASCRIVERE SE GIÀ PRESENTE, COSÌ DA INCLUDERE ANCHE L'EDIT
            $isPresente = false;
            $words_to_links = get_option( plugin_slug.'_data' );
            if ( ! empty( $words_to_links ) ) 
            {
                foreach( $words_to_links AS &$word_to_link )
                {
                    if( $word_to_link['keyword'] == $word )
                    {
                        $isPresente = true;
                        $word_to_link['link'] = $link;
                        $word_to_link['num'] = $num;
                        $word_to_link['caseSensitive'] = $caseSensitive;
                        $word_to_link['fullWord'] = $fullWord;
                        $word_to_link['targetBlank'] = $targetBlank;
                        $word_to_link['enableStats'] = $enableStats;
                    }
                }
            }

            if( !$isPresente )
                $words_to_links[] = [
                    'keyword' => $word, 
                    'link' => $link, 
                    'num' => $num, 
                    'caseSensitive' => $caseSensitive, 
                    'fullWord' => $fullWord,
                    'targetBlank' => $targetBlank,
                    'enableStats' => $enableStats,
                ];
            //* ORDINO L'ARRAY IN ORDINE ALFABETICO PER LA KEYWORD
            usort($words_to_links, array($this, 'compareByKeyword'));      
			update_option( plugin_slug.'_data', $words_to_links );
		}

        $words_to_links = get_option( plugin_slug.'_data' );
        include( 'views/settings.php' );
        include( 'views/credits.php' );
    }

    function compareByKeyword($a, $b) 
    {
        return strcmp($a["keyword"], $b["keyword"]);
    }

    function is_crawler() 
    {
        $ips = ['217.113.43.215','195.81.226.146'];
        $bots = [
            'Googlebot',
            'bingbot',
            'YandexBot',
            'DuckDuckBot',
            'Baiduspider',
            'ia_archiver',
            'R6_FeedFetcher',
            'NetcraftSurveyAgent',
            'Sogou web spider',
            'Exabot',
            'MJ12bot',
            'AhrefsBot',
            'DataForSeoBot',
            'SemrushBot',
            'BLEXBot',
            'Bytespider',
            'ubermetrics',
        ];

        foreach( $ips as $ip ) 
        { 
            if ( stripos( $_SERVER['REMOTE_ADDR'], $ip ) !== false )
            {
                return true;
            } 
                
        }        

        foreach($bots as $bot) 
        {
            if( stripos( $_SERVER['HTTP_USER_AGENT'], $bot) !== false ) 
            {
                return true;
            }
        }

        return false;
    }
}

new Sablab_Linkify_Words();
