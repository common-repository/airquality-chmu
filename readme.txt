=== AirQuality CHMU ===
Plugin Name: AirQuality CHMU
Author: Jakub Macíček
Tags: weather, pollution, monitor, widget, health
Requires at least: 4.7
Tested up to: 4.9.4
Requires PHP: 5.5
Stable tag: 0.11
Version: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Tento plugin slouží k zobrazení environmentálních dat z Českého hydrometeorologického ústavu.

== Description ==

Air Quality CHMU je plugin, který zobrazuje data o kvalitě ovzduší od nejbližší měřící stanice, který zadáte v nastavení pluginu. V nastavení pluginu musí být zadáné město, pro které se vám zobrazí environmentální data. Plugin využívá naměřená data, které nám nabízí Český hydrometeorologický ústav ve formě JSON.

V nastavení si administrátor zvolí město, pro které si chce zobrazit environmentální data a dále může uživatele informovat na základě indexu kvality ovzduší, co znamená ta daná kvalita ovzduší.

Data jsou čerpána z třetí strany a to z Českého hydrometeorologického úřadu ze souboru: http://portal.chmi.cz/files/portal/docs/uoco/web_generator/aqindex_cze.json

== Installation ==

1. Nahrejte plugin do složky /wp-content/plugins/airquality-chmu`
2. Aktivujte plugin prostřednictvím Wordpress
3. Zadejte v CHMU Nastavení stanici, kde chcete měřit kvalitu ovzduší a poté uložte nastavení tlačítkem "Uložit"
4. Vložte shortcode: "chmu_widget" aby se vám zobrazil plugin

== Changelog ==

= 0.12 =
* Vložení názvu pluginu

= 0.11 =
* Úprava skriptů

= 0.1 =
* První verze pluginu

== Upgrade Notice ==

= 0.11 =
Využití lokálních knihoven z Wordpressu css a js.

= 0.12 =
Vložení názvu pluginu

