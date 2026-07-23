=== Woo Comgate Logistic ===
Requires at least: 5.0
Tested up to: 5.7.3
Stable tag: 1.4.3
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Version: 1.4.3

WooCommerce integrační plugin pro napojení na služby platební brány Comgate.

== Description ==
* Plugin umožňuje napojení platební brány Comgate

== Instalace ==
* Plugin je možné nainstalovat pomocí FTP, nebo nahráním Zip souboru v administraci

= Minimální požadavky =
* WordPress 5.4 nebo vyšší
* PHP version 5.6 nebo vyšší
* MySQL version 5.0 nebo vyšší

== Changelog ==
<a href="https://toret.cz/produkt/woo-comgate/" target="_blank">Changelog</a>

= 1.4.3 =
* oprava stránky toret plugins

= 1.4.2 =
* přidána kompatibilita s toret order numbers

= 1.4.1 =
* odstranění volání ajax_url

= 1.4.0 =
* přidána kompatibilita s Toret Order Number

= 1.3.2 =
* oprava virtuálních produktů

= 1.3.1 =
* doplnění SE a NO jazyku

= 1.3.0 =
* změna načítání platebních metod
* doplnění css a textů v administraci

= 1.2.29 =
* oprava překlepu

= 1.2.28 =
* oprava načítání refID

= 1.2.27 =
* přidány filtry pro vlastní číslování objednávky a pro nastavení vlastních stavů objednávky po zpracování

= 1.2.26 =
* oprava zobrazení platební metody

= 1.2.25 =
* oprava překladové domény

= 1.2.24 =
* změna kontroly licence

= 1.2.23 =
* Oprava zpomalení webu při přidání produktu do košíku

= 1.2.22 =
* Zlepšení podpory produktu s variantami

= 1.2.21 =
* přidány filtry ‘toret_comgate_send_account’ a ‘toret_comgate_send_account_bank’

= 1.2.20 =
* doplněny překladové řetězce

= 1.2.19 =
* změna způsobu načítání obrázků povolených plateb

= 1.2.18 =
* změna způsobu načítání povolených plateb

= 1.2.17 =
* drobná změna textu

= 1.2.16 =
* přidána kontrola platební metody při zrušení objednávky

= 1.2.15 =
* přidán filtr na kontrolu ID objednávky ‘comgate_custom_order_id’

= 1.2.14 =
* změna zpracování čekání na platbu

= 1.2.13 =
* změna zpracování plateb převodem

= 1.2.12 =
* drobná úprava textu

= 1.2.11 =
* Odstranění zapomenutého výpisu v kódu

= 1.2.10 =
* Přidání kontroly platební metody na změnu statusu

= 1.2.9 =
* Přidání pole blokovaných stavů a filtru comgate_blocked_statuses
* https://gist.github.com/Musilda/b5feb020d0525214b3cfc4c0ece45b27

= 1.2.8 =
* Upravy informacnich textu v pluginu

= 1.2.7 =
* Upraveny kódy pro bankovní tlačítka

= 1.2.6 =
* Doplněna kompatibilita pro Sequential Number plugin
* Upravena platební metoda pro bankovní tlačítka
* Doplněno logování komunikace s Comgate do interního logu
* Odstraněny soubory motify a response log.

= 1.2.5 =
* Oprava definování třídy logu

= 1.2.4 =
* Zrušení souborů notify_log a response_log
* Nová tabulka pro logování komunikace s Comgate
* Výpis logu v administraci WordPressu
* Možnost vypsat log pouze pro jedu objednávku
* Odstranění zapomenutého výpisu proměnné v nastavení pluginu

= 1.2.3 =
* Doplnění parametru lang do dat při vytvoření platby
* Přidán filtr toret_comgate_gateway_language umožňující manipulaci s jazykem brány

= 1.2.2 =
* Odstranění kontrolního výpisu uložení licence v administraci

= 1.2.1 =
* Do notifikace přidána kontrola získání objednávky

= 1.2.0 =
* Kontrola neexistující objednávky / smazaná, nebo testovací objednávka vyhazovala chybu

= 1.1.9 =
* Úprava odpovědi při neplatné notifikaci

= 1.1.8 =
* Úprava stavu objednávky při notifikaci

= 1.1.7 =
* Změna url volání Comgate API

= 1.1.6 =
* Odstranění výpisu nastavení v administraci

= 1.1.5 =
* Oprava ukládání id transakce

= 1.1.4 =
* Odstranění metod pro zjištění obsahu košíku z třídy platební brány
* Přidání výběru platebních metod v pokladně
* Nastavení povolených platebních metod
* Opravy drobných chyb

= 1.1.3 =
* Oprava kompatibility získání url pokladny
* Oprava kompatibility získání id objednávky
* Přidána kompatibilita pro snížení skladu

= 1.1.2 =
* Oprava volání objednávky v souboru notify.php

= 1.1.1 =
* Oprava kontroly zaplacené, selhané a zrušené platby
* Oprava několik překlepů
* Aktualizace knihovny pro Comgate

= 1.1.0 =
* Kompatibilita s WooCommerce 3.0

= 1.0.0 =
* Vydání pluginu