# Protocol by xdecaro

Protocol by xdecaro è il componente Joomla per registrazione amministrativa, numerazione di protocollo, registri, assegnazioni, classificazione, fascicoli e tracciabilità.

## Identità

- Componente: `com_decaroprotocol`
- Pacchetto: `pkg_decaroprotocol`
- Repository: `xdecaro/Protocol`
- Versione iniziale: `1.0.0`
- Obiettivo: Joomla 4, 5 e 6 quando tecnicamente possibile

## Confini

Protocol gestisce l'identità amministrativa del documento: numero, anno, registro, direzione, oggetto, assegnazioni, relazioni e audit.

Documents resta il sistema documentale opzionale condiviso per storage, versioni, ACL documentali e download protetti. Protocol deve funzionare anche senza Documents installato.

## Nucleo 1.0.0

La prima implementazione copre:

1. registri configurabili;
2. contatori separati per registro e anno;
3. bozze di protocollo;
4. protocollazione atomica con transazione database;
5. blocco della normale modifica dopo la protocollazione;
6. audit dell'assegnazione del numero;
7. dashboard e ricerca amministrativa di base;
8. build ZIP del componente e del package.

Un numero assegnato non viene mai riutilizzato. Un protocollo protocollato non torna bozza tramite normale modifica.
