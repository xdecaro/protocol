# Protocol by xdecaro

Protocol by xdecaro è il componente Joomla per registrazione amministrativa, numerazione di protocollo, registri, assegnazioni, classificazione, fascicoli e tracciabilità.

## Identità

- Componente: `com_decaroprotocol`
- Pacchetto: `pkg_decaroprotocol`
- Repository: `xdecaro/Protocol`
- Versione corrente: `1.4.0`
- Obiettivo: Joomla 4, 5 e 6 quando tecnicamente possibile

## Confini

Protocol gestisce l'identità amministrativa del documento: numero, anno, registro, direzione, oggetto, assegnazioni, relazioni e audit.

Documents resta il sistema documentale opzionale condiviso per storage, versioni, ACL documentali e download protetti. Protocol deve funzionare anche senza Documents installato.

## Core by xdecaro

Dalla versione 1.2.0 Protocol integra opzionalmente Core by xdecaro `1.3.0+` tramite il namespace canonico `xdecaro\Core` e le sole API pubbliche.

Quando Core è disponibile e compatibile, Protocol può usare:

- design token e primitive UI condivise;
- Web Asset Manager di Core;
- `EntityReference` e `RelationReference` per integrazioni cross-product;
- rilevamento versione e compatibilità nella pagina Informazioni.

Core non è una dipendenza obbligatoria: se manca, è precedente a `1.3.0` o non è compatibile, Protocol usa il proprio fallback locale senza perdere le funzioni di protocollo.

Il namespace deprecato `Xdecaro\Core` non viene consumato dal runtime Protocol. La logica di numerazione, registri, protocolli e audit resta esclusivamente in Protocol.

## Documents

Dalla versione 1.3.0 Protocol dispone del contratto opzionale per collegare documenti gestiti da Documents `1.2.1+` ai propri record. Dalla versione 1.4.0 questo contratto è utilizzabile direttamente nella scheda Protocol: una bozza già salvata può collegare o scollegare documenti esistenti, mentre un protocollo definitivo mostra e scarica gli allegati in sola lettura.

Il contratto è intenzionalmente stretto:

- l'entità pubblica Protocol è `record`;
- il documento resta proprietà di `com_decarodocuments` con entità `document`;
- il tipo relazione predefinito è `attachment`;
- Protocol verifica ACL, token Joomla per le modifiche e stato del proprio record;
- Documents verifica ACL documentali, presenza del documento e persistenza della relazione;
- Protocol ottiene il servizio tramite `bootComponent('com_decarodocuments')->getRelationService()`;
- il download continua a passare dal controller protetto di Documents;
- Protocol non legge né scrive direttamente tabelle `#__decarodocuments_*` e non accede ai percorsi storage privati;
- dopo la protocollazione attach e detach vengono rifiutati anche lato server; eventuali correzioni devono usare un futuro flusso di rettifica tracciato.

Documents `1.2.1+` resta il minimo del contratto pubblico; i test runtime della linea Protocol 1.4.0 usano la release riparata Documents 1.2.2. Se Core o Documents non sono disponibili, l'integrazione documentale risulta non disponibile senza compromettere numerazione, registri, record o audit Protocol.

## Nucleo

La base corrente comprende:

1. registri configurabili;
2. contatori separati per registro e anno;
3. bozze di protocollo;
4. protocollazione atomica con transazione database;
5. blocco della normale modifica dopo la protocollazione;
6. audit dell'assegnazione del numero;
7. dashboard e ricerca amministrativa di base;
8. pagina Informazioni e diagnostica;
9. integrazione opzionale Core;
10. integrazione opzionale Documents tramite API pubblica e pannello allegati;
11. build ZIP del componente e del package.

Un numero assegnato non viene mai riutilizzato. Un protocollo protocollato non torna bozza tramite normale modifica.
