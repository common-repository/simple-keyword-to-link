# RIFERIMENTI
Per il processo di trasformazione si può prendere a riferimento il plugin https://wordpress.org/plugins/links-auto-replacer/  

Nel file wp-content/plugins/links-auto-replacer/public/class-lar-public.php  
La funzione **lar_auto_replace_links** genera il DOM che viene passato a **showDOMNode** che si occupa dello scorrere del DOM

## TO DO LIST  
COMPLETARE LA TRADUZIONE

FARE UN CHECK PERCHÉ SI TRATTI DI UN URL VALIDO  
linkify.php - riga 86   

METTERE FUNZIONE CHE CONTROLLA CHE TUTTI GLI INDIRIZZI RESTITUISCANO UN 200 COME CODICE IN UNA PAGINA AD HOC  

modo di vedere in quali post compaiono le parole, magari anche con ricerca che manda al sito pubblico all'inizio

nuova finestra no/si

evitare l'indicizzazione delle pagine con i link per le statistiche in qualche modo, anche inserendo le istruzioni nel link stesso "rel = 'nofollow'"
vedere altre opzioni di ThirstyAffiliates Settings e Pretty Links

impostazioni per mettere i valori di default delle varie select e lo slug da intercettare (e trovare modo di fare wipe della cache per far prendere il nuovo slug) 

pubblicare tutte queste modifica sul plugin di base oppure fare che questa è la versione per l'affiliazione? decidere