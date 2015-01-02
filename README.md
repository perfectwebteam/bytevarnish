# Snelle instructies

1. Download de [Byte Varnish extensie voor Joomla!](https://raw.githubusercontent.com/perfectwebteam/bytevarnish/master/pkg_bytevarnish.zip)
2. Installeer de extensie via het extensiebeheer van Joomla
3. Klaar! Controleer of alles goed werkt.

# Achtergrond

Byte heeft in samenwerking met [Perfect Web Team](http://perfectwebteam.nl) een eigen extensie ontwikkeld voor Joomla. Met deze extensie kunnen Joomla sites op het supersnelle Byte Varnish Cluster draaien. De extensie zorgt ervoor dat Joomla de juiste caching headers genereert met TTL (time to live) informatie en dat specifieke pagina's en/of extensies niet worden gecached. Ook zorgt de extensie voor het automatisch legen van de cache voor specifieke pagina's na het aanpassen van een artikel in Joomla en wordt de volledige site cache geleegd na het klikken op de speciale knop daarvoor.

# Installatie

De Byte Varnish extensie bestaat uit twee delen:

1. Module; `mod_bytevarnish`
2. Plugin; `plg_system_bytevarnish`

Beide zitten verwerkt in de extensie `pkg_bytevarnish.zip` dat [hier](https://raw.githubusercontent.com/perfectwebteam/bytevarnish/master/pkg_bytevarnish.zip) te downloaden is. Dit zip bestand installeer je in Joomla via `Extensies` -> `Extensiebeheer`, tabblad `Installeren`. Selecteer het zip bestand en klik op `Uploaden & installeren`. De module en plugin worden nu geïnstalleerd, automatisch geactiveerd en gepubliceerd op de juiste positie. De extensie is nu in werking en de site maakt gebruik van het Byte Varnish Cluster. Je kan de extensie verder naar wens configureren.

# Configuratie

De extensie is verder naar wens te configureren. Dit kan door de plugin instellingen aan te passen. Hiervoor ga je naar `Extensies` -> `Pluginbeheer` waar je zoekt naar de `Byte Varnish` plugin van type `system`. Na het openen van de plugin beschik je over de volgende configuratie opties.

## Algemene opties

#### Schakel Byte.nl Varnish in
Hiermee wordt de Byte Varnish caching in- en uitgeschakeld vanuit Joomla. Als de caching is ingeschakeld is er een `Cache-control: public` HTTP-header actief op de site. Als de caching is uitgeschakeld is dit een `Cache-control: no-cache` HTTP-header waardoor er geen pagina's meer aan de Varnish cache worden toegevoegd. Pagina's die reeds in de Varnish cache zitten blijven actief totdat deze geleegd worden.

Standaard waarde: `Ja`

#### Automatisch cache legen
Hiermee wordt het automatisch legen van pagina's uit de Varnish cache na aanpassing van een artikel in- en uitgeschakeld. Als deze functie is ingeschakeld worden de pagina's in de Varnish cache waarop het volledige Joomla artikel en de introtekst zichtbaar is geleegd. De plugin detecteert automatisch om welke pagina's het gaat en zal deze legen in de Varnish cache.

Standaard waarde: `Ja`

#### TTL (max-age) in minuten
Hier voer je de tijd in minuten in die gebruikt wordt voor de 'houdbaarheid' van een pagina in de Varnish cache. Bij een waarde van `60` wordt na 60 minuten bij een nieuwe pagina request de pagina opnieuw in de Varnish cache geplaatst. De ingevoerde tijd in minuten wordt door de plugin omgezet in seconden en wordt gebruikt voor de `max-age` HTTP-header van de site.

Standaard waarde: `60`

## Opties voor uitsluiten pagina caching
Standaard zorgt de plugin er voor dat als de bezoeker inlogt er een `NO_CACHE` cookie wordt geplaatst. Hierdoor zal een ingelogde bezoeker geen pagina's uit de Varnish cache krijgen maar direct van de webserver. Het is goed mogelijk dat daarnaast bepaalde pagina's uitgesloten moeten worden van caching voor alle bezoekers van de website. Denk aan pagina's met dynamische content, waar ingelogd wordt of die beveiligd zijn. De plugin heeft hiervoor diverse opties.

#### Menu-items uitsluiten
Hiermee kan je specifieke menu-items uitsluiten van caching. Selecteer uit de dropdown één of meerdere menu-items die moeten worden uitgesloten. Op deze pagina's wordt dan de `Cache-control: no-cache` HTTP-header actief. 

Standaard waarde: `-`

#### Componenten uitsluiten
Hiermee kan je specifieke componenten compleet uitsluiten van caching. Selecteer uit de dropdown één of meerdere componenten die moeten worden uitgesloten. Op deze pagina's wordt dan de `Cache-control: no-cache` HTTP-header actief. Standaard is `com_users` hier ingesteld, maar bijvoorbeeld formulier extensies moeten vaak ook voor de juiste werking worden uitgesloten. 

Standaard waarde: `com_users`

# Tips & Tricks

## Testen
Na het inschakelen van de Varnish cache is het erg belangrijk om je site goed te testen. Werkt het inloggen voor de bezoekers nog? Worden contactformulieren juist verwerkt? Worden reacties geplaatst? Kortom, doorloop alle functionaliteiten van je site en sluit waar nodig pagina's en/of componenten uit van caching. 

## Varnish cache legen
Naast de mogelijkheid om de Varnish cache voor de gehele site te legen via het controlepaneel van Byte is dit ook mogelijk vanuit de beheer van Joomla. Klik daarvoor op `Purge Varnish Cache` in de grijze statusbalk onderaan iedere pagina. Alle pagina's in de Varnish cache worden door deze actie verwijderd.    

## Joomla caching
De Byte Varnish Cache staat los van de caching instellingen van Joomla. Deze kan je ingesteld laten, maar dit kan wellicht tot verwarring zorgen. Na het maken van een site aanpassing kan er een oudere gecachde pagina van Joomla worden opgenomen in de Varnish cache. Voor het juist cachen moet dus eerst de Joomla cache geleegd worden en daarna de Varnish cache, dan weet je zeker dat het juiste wordt opgenomen in de Varnish cache. Door de Joomla caching uit te schakelen weet je zeker dat dit goed gaat en is het legen van zowel de Joomla als de Varnish cache niet nodig. Dit is sowieso raadzaam tijdens het testen van de Varnish cache.

## .htaccess expire headers
Vergeet niet je `.htaccess` bestand na te lopen op eventuele `mod_expires` regels voor de HTTP-headers. Deze zouden voor conflicten kunnen zorgen met de HTTP-headers die de Byte Varnish extensie genereert. Verwijder of pas deze aan als de plugin na installatie niet werkt en je `mod_expires` regels actief hebt in je `.htaccess` bestand.
