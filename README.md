# Protocol by xdecaro

Protocol by xdecaro è il componente Joomla per registrazione amministrativa, numerazione di protocollo, registri, classificazione essenziale, collegamenti documentali e tracciabilità.

## Identità

- Componente: `com_decaroprotocol`
- Pacchetto: `pkg_decaroprotocol`
- Repository: `xdecaro/protocol`
- Versione corrente: `1.5.0`
- Obiettivo: Joomla 4, 5 e 6 quando tecnicamente possibile

## Confini

Protocol gestisce l'identità amministrativa del documento: registro, direzione, numero, anno, oggetto, mittente/destinatari, riferimenti e audit.

Protocol non sostituisce:

- **Documents**, che resta proprietario di storage, versioni, ACL documentali e download protetti;
- **Communications**, che gestirà composizione e invio di email/PEC/comunicazioni umane quando disponibile;
- **Notifications**, per gli avvisi automatici di sistema;
- **Tasks**, per le attività operative assegnabili.

Le integrazioni opzionali non sono dipendenze obbligatorie e Protocol non accede alle tabelle private degli altri prodotti.

## Core by xdecaro

Dalla versione 1.5.0 Protocol usa opzionalmente Core by xdecaro `1.4.0+` tramite il namespace canonico `xdecaro\Core`.

Quando Core è disponibile, Protocol può usare:

- design token e primitive UI condivise;
- Web Asset Manager condiviso;
- `EntityReference` e `RelationReference`;
- `CapabilityRegistry`.

Capability dichiarate:

- `protocol.records`
- `protocol.protocolize`
- `protocol.query`
- `protocol.documents`
- `protocol.analytics.provider`
- `protocol.notifications.bridge`
- `protocol.tasks.bridge`

La direzione resta `Protocol -> Core`; Core non conosce il dominio Protocol.

## API pubblica del componente

`bootComponent('com_decaroprotocol')` restituisce la facade `ProtocolComponent`, che espone servizi pubblici senza obbligare altri prodotti a istanziare classi interne o leggere tabelle Protocol:

- `getProtocolService()`;
- `getCoreIntegrationService()`;
- `getDocumentsIntegrationService()`;
- `getAnalyticsSourceService()`;
- `getCrossProductIntegrationService()`.

La numerazione e il passaggio Draft → Protocol restano proprietà esclusiva di `ProtocolService` e sono transazionali.

## Documents

Protocol collega opzionalmente documenti esistenti attraverso l'API pubblica Documents.

Regole:

- entità Protocol: `record`;
- entità Documents: `document`;
- relazione predefinita: `attachment`;
- Protocol verifica ACL e stato del record;
- Documents verifica ACL, persistenza e storage;
- nessuna query Protocol verso `#__decarodocuments_*`;
- i protocolli definitivi mantengono gli allegati leggibili ma rifiutano attach/detach ordinari.

## Notifications e Tasks

La 1.5.0 introduce un bridge API opzionale verso Notifications e Tasks.

Protocol non genera automaticamente notifiche o task perché il modello corrente non possiede un assegnatario strutturato. I consumer possono usare il bridge quando dispongono già di un destinatario autorizzato e semanticamente corretto.

Questa scelta evita di introdurre in Protocol una logica di workflow/assegnazione non ancora definita dal dominio.

## Analytics

Il package 1.5.0 include il plugin opzionale `xdecaroanalytics/decaroprotocol`.

Il provider pubblica dati esclusivamente attraverso `AnalyticsSourceService`, applicando ACL Protocol:

Metriche:

- totale record;
- record protocollati;
- bozze;
- registri attivi.

Dataset:

- record per direzione;
- record per registro;
- protocolli per mese.

Analytics non legge direttamente le tabelle Protocol.

## Nucleo

La base corrente comprende:

1. registri configurabili;
2. contatori separati per registro e anno;
3. bozze di protocollo;
4. protocollazione atomica con transazione e lock;
5. blocco della normale modifica dopo la protocollazione;
6. audit dell'assegnazione del numero;
7. dashboard e ricerca amministrativa;
8. pagina Informazioni e diagnostica;
9. Core 1.4 opzionale;
10. Documents opzionale tramite API pubblica;
11. Notifications/Tasks opzionali tramite API pubblica;
12. provider Analytics opzionale;
13. build deterministica e update server Joomla.

Un numero assegnato non viene mai riutilizzato. Un protocollo definitivo non torna bozza tramite la normale modifica.
