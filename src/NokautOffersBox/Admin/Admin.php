<?php
namespace NokautOffersBox\Admin;

use NokautOffersBox\ApiKitFactory;
use NokautOffersBox\View\OffersBox;

class Admin
{
    const NOKAUT_OFFERS_BOX_CONFIG_PAGE_UNIQUE_KEY = 'nokaut-offers-box-config';

    private static $initiated = false;

    public static function init()
    {
        if (!self::$initiated) {
            self::initHooks();
        }
    }

    public static function initHooks()
    {
        self::$initiated = true;

        add_action('admin_init', array(__CLASS__, 'adminInit'));
        add_action('admin_menu', array(__CLASS__, 'adminMenu'), 1);
        add_action('admin_menu', array('NokautOffersBox\\Admin\\Options', 'redirectAfterResetOptions'));

        add_action('admin_enqueue_scripts', array(__CLASS__, 'initNokautOffersBoxAdminJs'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'initNokautOffersBoxAdminCss'));
    }

    public static function adminInit()
    {
        Options::init();
    }

    public static function adminMenu()
    {
        $hook = add_options_page(__('Nokaut Offers Box', 'Nokaut Offers Box'), __('Nokaut Offers Box', 'Nokaut Offers Box'), 'manage_options', self::NOKAUT_OFFERS_BOX_CONFIG_PAGE_UNIQUE_KEY, array(__CLASS__, 'displayPage'));

        // top right corner help tabs
        if (version_compare($GLOBALS['wp_version'], '3.3', '>=')) {
            add_action("load-$hook", array(__CLASS__, 'adminHelp'));
        }
    }

    public static function initNokautOffersBoxAdminJs()
    {
        wp_register_script('jquery.range-min.js', NOKAUT_OFFERS_BOX_PLUGIN_URL . 'assets/vendor/jrange/jquery.range-min.js', array('jquery'), NOKAUT_OFFERS_BOX_VERSION);
        wp_enqueue_script('jquery.range-min.js');

        wp_register_script('nokaut-offers-box-admin.js', NOKAUT_OFFERS_BOX_PLUGIN_URL . 'assets/js/nokaut-offers-box-admin.js', array('jquery'), NOKAUT_OFFERS_BOX_VERSION);
        wp_localize_script('nokaut-offers-box-admin.js', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
        wp_enqueue_script('nokaut-offers-box-admin.js');
    }

    public static function initNokautOffersBoxAdminCss()
    {
        wp_register_style('jquery.range.css', NOKAUT_OFFERS_BOX_PLUGIN_URL . 'assets/vendor/jrange/jquery.range.css', array(), NOKAUT_OFFERS_BOX_VERSION);
        wp_enqueue_style('jquery.range.css');
    }

    public static function displayPage()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        echo '<div class="wrap">';
        echo '<h2>Nokaut Offers Box - konfiguracja wtyczki Nokaut.pl</h2>';
        Options::form();
        echo '</div>';
    }

    /**
     * Add help to the Nokaut Offers Box page
     *
     * @return false if not the Nokaut Offers Box page
     */
    public static function adminHelp()
    {
        $current_screen = get_current_screen();

        $current_screen->add_help_tab(
            array(
                'id' => 'overview',
                'title' => "Wprowadzenie",
                'content' =>
                    '<p><strong>Nokaut Offers Box - wtyczka Nokaut.pl - dostawcy treści  e-commerce</strong></p>
                    <p><ul>Wtyczka umożliwia:
                            <li>tworzenie prostych boksów ofertowych umieszczanych w treści postów (short codes)</li>
                            <li>kierowanie użytkowników Twojego bloga bezpośrednio do sklepów internetowych związanych z tematyką Twojego bloga</li>
                        </ul>
                    </p>
                    <p>Wygląd poszczególnych elementów można całkowicie zmieniać i dostosowywać do swoich potrzeb w edytorze stylów CSS,
                    startowa wizualizacja jest tylko przykładem wykorzystania dostępnych danych.</p>'
            )
        );

        $current_screen->add_help_tab(
            array(
                'id' => 'configuration',
                'title' => 'Konfiguracja',
                'content' =>
                    '<p><strong>Nokaut Offers Box - konfiguracja wtyczki</strong></p>
                    <p>
                        <ul>Aby uzyskać dostęp do Nokaut Search API:
                            <li>zarejestruj się w Programie Partnerskim Nokaut.pl:
                            <a href="https://partner.nokaut.pl/rejestracja" target="_blank">https://partner.nokaut.pl/rejestracja</a></li>
                            <li>napisz na adres partnerzy@nokaut.pl i poproś o unikalny API KEY, podając w treści wiadomości swój PID (unikalny numer Partnera,
                            który otrzymasz po rejestracji) oraz adres WWW Twojej strony</li>
                        </ul>
                    </p>
                    <p>
                        <ul>Jeśli posiadasz już klucz dostępowy do Nokaut Search API, skonfiguruj wtyczkę:</p>
                            <li><b>Pole API KEY:</b> tu wklej swój uniklany klucz API</li>
                            <li><b>Pole API URL:</b> tu wklej adres serwera: http://nokaut.io/api/v2/</li>
                            <li>Suwaki <b>Minimalna ilość ofert do wyświetlenia</b> i <b>Limit ofert do wyświetlenia:</b> określ domyślną minimalną i
                            maksymalną ilość ofert w boxie. W każdej chwili będzie można zmienić te wartości lub ustawić indywidualną ilość ofert w wybranym boxie.</li>
                            <li><b>Nazwa domyślnego szablonu:</b> domyślnie jest to \'list\'. W każdej chwili będzie można go zmienić lub ustawić
                            indywidualny szablon w wybranym boxie. Szablony są dokładniej opisane w zakładce \'Integracja z blogiem\' - opcja \'template\'.</li>
                        </ul>
                    </p>
                    <p>Po ustawieniu klucza dostępowego i API URL, poniżej zostanie wyświetlona informacja o statusie połączenia do api.</p>
                    ',
            )
        );

        $current_screen->add_help_tab(
            array(
                'id' => 'features',
                'title' => 'Integracja z blogiem',
                'content' =>
                    '<p><strong>Nokaut Offers Box - integracja wtyczki z blogiem</strong></p>
                    <p>Po włączeniu wtyczki i konfiguracji, możliwe jest osadzanie short code\'ów Wordpress we wpisach.</p>

                    <h3>Nokaut Offers Box - kontener z ofertami z Nokaut.pl [nokaut-offers-box]</h3>
                    <p>
                    <ul>Opcje:
                    <li><b>url</b> - adres do produktów lub produktu na http://www.nokaut.pl, bez domeny, ze znakiem / na początku, parametr wymagany
                        <ul>
                            <li>w przypadku podania adresu produktu http://www.nokaut.pl nie ma gwarancji, że zostanie zwrócony dokładnie ten produkt, adres przechodzi przez proces wyszukiwania i zwracane są najbardziej dopasowane produkty</li>
                            <li>w przypadku podania adresu produktu http://www.nokaut.pl można wymusić restrykcyjne zachowanie, tzn. pokazać produkt, lub nic nie pokazywać jeśli produktu nie ma, dodając przed adresem prefiks "p:" (wymusza traktowanie adresu tylko jako adres produktowy)</li>
                            <li>w atrybucie url można podać kilka adresów z http://www.nokaut.pl oddzielonych znakiem "|" (pipe, kreska pionowa), w tym przypadku dla każdego z adresów składowych zwracany jest dokładnie jedna oferta, pozwala to zestawić obok siebie po jednej ofercie różnych produktów, różnych wyników wyszukiwania</li>
                            <li>szablony z grup produkt i mall operują tylko na jednym adresie, odpowienio produktu lub kategorii / wyszukiwarki</li>
                            <li>w url można podać także bezwzględny adres do produktu lub kategorii pasażu opartego na WhiteLabel Demo, w razie problemów z poprawnym rozpoznawaniem części
                            url opisującej kategorię lub produkt, należy oddzielić ją dwoma znakami #, np. [nokaut-offers-box url=\'http://luxlife.pl/zakupy/kategoria-##odziez-meska/material:jeans.html\']</li>
                        </ul>
                    </li>
                    <li><b>cid</b> - ID kampanii, ułatwia analizę źródeł ruchu w Panelu Partnera Nokaut.pl, parametr opcjonalny, domyślnie przyjmuje ID artykułu</li>
                    <li><b>template</b> - nazwa pliku szablonu z katalogu view/templates użytego do przedstawienia ofert, bez rozszerzenia, parametr opcjonalny, domyślnie: ' . Options::getOption(Options::OPTION_DEFAULT_TEMPLATE_NAME) . ' (ustawienie: Nazwa domyślnego szablonu), predefiniowane szablony to:
                        <ul>
                            <li>box - produkty w formie kwadratowych kafelek z ramką
                                <br/><img height="200px" src="' . NOKAUT_OFFERS_BOX_PLUGIN_URL . 'screenshot-2.png' . '"/>
                            </li>
                            <li>boxes - produkty obok siebie po kilka w wierszu
                                <br/><img height="300px" src="' . NOKAUT_OFFERS_BOX_PLUGIN_URL . 'screenshot-4.png' . '"/>
                            </li>
                            <li>carrusel - karuzela produktowa, produkty obok siebie, animowane przesuwanie produktów
                                <br/><img height="200px" src="' . NOKAUT_OFFERS_BOX_PLUGIN_URL . 'screenshot-8.png' . '"/>
                            </li>
                            <li>fullbox - boks produktowy z dodatkowymi danymi, takimi jak opis i cechy
                                <br/><img height="300px" src="' . NOKAUT_OFFERS_BOX_PLUGIN_URL . 'screenshot-5.png' . '"/>
                            </li>
                            <li>list - lista produktów, jeden pod drugim
                                <br/><img height="200px" src="' . NOKAUT_OFFERS_BOX_PLUGIN_URL . 'screenshot-6.png' . '"/>
                            </li>
                            <li>product/default - produkt z najlepszą ofertą, oraz możliwością rozwinięcia większej ilości ofert
                                <br/><img height="320px" src="' . NOKAUT_OFFERS_BOX_PLUGIN_URL . 'screenshot-9.png' . '"/>
                            </li>
                            <li>mall/default - produkty z możliwością filtrowania, sortowania i rozwiajania większej ilości produktów
                                <br/><img height="400px" src="' . NOKAUT_OFFERS_BOX_PLUGIN_URL . 'screenshot-10.png' . '"/>
                            </li>
                        </ul>
                    </li>
                    <li><b>render_type</b> - sposób generowania treści:
                        <ul>
                            <li>ajax - domyślna wartość, treść ofert jest generowana w tle (AJAX), artykuł ładuje się bez opóźnień, a oferty pojawiają się chwilę po załadowaniu</li>
                            <li>inline - treść ofert jest generowana w momencie ładowania strony, oferty od razu są wkomponowane w treść artykułu,
                            ale może opóźniać wyświetlenie artykułu</li>
                        </ul>
                    </li>
                    <li><b>limit</b> - limit ofert pobieranych z API do wyświetlenia (nadpisanie domyślnej wartości z konfiguracji wtyczki)
                        <br/>Przykład - zastosowanie  limit=\'3\':
                        <br/><img height="200px" src="' . NOKAUT_OFFERS_BOX_PLUGIN_URL . 'screenshot-8.png' . '"/>
                    </li>
                    <li><b>limit_min</b> - minimalna ilość ofert do wyświetlenia, jeśli znaleziono mniej ofert, nie są pokazywane wyniki (nadpisanie domyślnej wartości z konfiguracji wtyczki)</li>
                    <li><b>classes</b> - klasy CSS do ustawienia na elemencie głównym szablonu ofert, dowolne wartości rozdzielone znakiem "|" (pipe, kreska pionowa), predefiniowane nazwy klas używane przez wbudowane szablony to:
                        <ul>
                            <li>big - zwiększa rozmiar zdjęcia prezentowanych ofert, dostępna dla szablonów: box, boxes, carrusel, list
                            <br/>Przykład - zastosowanie classes=\'big\':
                            <br/><img height="200px" src="' . NOKAUT_OFFERS_BOX_PLUGIN_URL . 'screenshot-2.png' . '"/>
                            </li>
                            <li>row1, row2, row3, row4, row5 - określają odpowiednio ilość ofert w wierszu, dostępne dla szablonów: boxes
                             <br/>Przykład - zastosowanie classes=\'row2\' limit=\'4\':
                             <br/><img height="300px" src="' . NOKAUT_OFFERS_BOX_PLUGIN_URL . 'screenshot-4.png' . '"/>
                            </li>
                        </ul>
                    </li>
                    </ul>
                    <ul>Pozostałe przykłady użycia:
                        <li><b>[nokaut-offers-box url=\'/laptopy/\']</b> - wyświetla oferty z kategorii Laptopy z <a href="http://www.nokaut.pl/laptopy/" target="_blank">http://www.nokaut.pl/laptopy/</a>, generowanie treści w tle, ID kampanii to ID artykułu, domyślny szablon</li>
                        <li><b>[nokaut-offers-box url=\'/laptopy/\' cid=\'213\' template=\'carrusel\' render_type=\'inline\']</b> - wyświetla oferty z kategorii Laptopy, generowanie podczas ładowania strony, wymuszone ID kampanii 213, szablon view/templates/carrusel.twig</li>
                        <li><b>[nokaut-offers-box url=\'/produkt:wyciskarka.html\' limit=\'4\']</b> - wyświetla oferty z wyszukiwaniem na słowo "wyciskarka", ograniczenie wyników do 4 ofert</li>
                        <li><b>[nokaut-offers-box url=\'/telefony-komorkowe/samsung-galaxy-s20-fe-sm-g780.html\' limit_min=\'1\']</b> - wyświetla ofertę telefonu, wymuszenie wyświetlania jednej oferty, adres produktu wpadnie do wyszukiwarki, zostanie zwrócony dany produkt, lub podobny</li>
                        <li><b>[nokaut-offers-box url=\'p:/telefony-komorkowe/samsung-galaxy-s20-fe-sm-g780.html\']</b> - wyświetla ofertę telefonu, wymuszenie traktowanie adresu jako adresu produktu, jeśli produkt nie istnieje, nic nie zostanie pokazane</li>
                        <li><b>[nokaut-offers-box url=\'/telefony-komorkowe/samsung-galaxy-s20-fe-sm-g780.html|/tablety/\']</b> - wyświetla po jednej ofercie telefonu oraz tabletu</li>
                        <li><b>[nokaut-offers-box url=\'/laptopy--najpopularniejsze.html\' template=\'mall/default\']</b> - wyświetla wiele laptopów posortowanych wg. popularości, z możliwością filrtowania, sortowania i rozwijania większej ilości produktów</li>
                        <li><b>[nokaut-offers-box url=\'/lustrzanki-cyfrowe/canon-eos-2000d.html\' template=\'product/default\' limit=\'5\']</b> - wyświetla aparat cyfrowy z najlepszą ofertą, z możliwością rozwinięcia większej ilości ofert</li>
                    </ul>
                    </p>
                    <ul>Uwagi:
                      <li>Jeśli po osadzeniu short code w artykule nie wyświetlają się oferty, w źródle strony można znaleźć zakomentowane komunikaty błędu. Dodatkowo krytyczne błędy są wysyłane do error loga serwera www.</li>
                    </ul>
                    '
            )
        );

        $current_screen->add_help_tab(
            array(
                'id' => 'customize',
                'title' => 'Dostosowywanie wyglądu',
                'content' =>
                    '<p><strong>Nokaut Offers Box - dostosowanie wyglądu elementów wtyczki</strong></p>
                    <p>Wtyczka pozwala na dostosowanie wyglądu każdego elementu do swoich potrzeb.<p>
                    <p>Wtyczka domyślnie wykorzystuje system szablonów <a href="https://twig.symfony.com/" target="_blank">Twig 3.x</a></p>
                    <p>Nie należy modyfikować kodu wtyczki, gdyż uniemożliwi to bezproblemowe wykonywanie jej aktualizacji
                    w przyszłości. Należy skopiować odpowiednie pliki z wtyczki (wp-content/plugins/nokaut-offers-box/view/)
                    do utworzonego katalogu aktywnego motywu (wp-content/themes/AKTYWNY_MOTYW/nokaut-offers-box/view/) Wordpress,
                    dopiero skopiowane pliki szablonów są bazą do indywidualnych zmian.</p>

                    <ul><b>Kolejność wczytywania plików szablonów wtyczki:</b>
                    <li>wp-content/themes/AKTYWNY_MOTYW/nokaut-offers-box/view/</li>
                    <li>wp-content/plugins/nokaut-offers-box/view/</li>
                    </ul>

                    <ul><b>Kolejność wczytywania plików CSS wtyczki:</b>
                    <li>wp-content/themes/AKTYWNY_MOTYW/nokaut-offers-box/assets/css/nokaut-offers-box.css</li>
                    <li>wp-content/plugins/nokaut-offers-box/assets/css/nokaut-offers-box.css</li>
                    </ul>

                    <ul><b>Kolejność wczytywania plików javascript wtyczki:</b>
                    <li>wp-content/themes/AKTYWNY_MOTYW/nokaut-offers-box/assets/js/nokaut-offers-box.js</li>
                    <li>wp-content/plugins/nokaut-offers-box/assets/js/nokaut-offers-box.js</li>
                    </ul>

                    <p>Alternatywą dla kopiowania pliów CSS jest stosowanie bardziej szczegółowych selektorów dla elementów HTML we własnych plikach CSS.</p>
                    ',
            )
        );

        // Help Sidebar
        $current_screen->set_help_sidebar(
            '<p><strong>Więcej informacji</strong></p>' .
            '<p><a href="http://www.nokaut.pl/" target="_blank">www.nokaut.pl</a></p>' .
            '<p><a href="mailto:partnerzy@nokaut.pl" target="_blank">partnerzy@nokaut.pl</a></p>'
        );
    }
}
