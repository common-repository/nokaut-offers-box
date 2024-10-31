<?php
namespace NokautOffersBox;

class UrlHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider urlProvider
     */
    public function testFilterWhiteLabelNokautUrl($url, $expected)
    {
        $this->assertEquals($expected, UrlHelper::filterWhiteLabelNokautUrl($url));
    }

    public function urlProvider()
    {
        return array(
            array('/laptopy/', '/laptopy/'),
            array('/sluchawki/sony-mdr-7506.html', '/sluchawki/sony-mdr-7506.html'),
            array('/sluchawki/sony-mdr-7506', '/sluchawki/sony-mdr-7506'),
            array('http://www.nokaut.pl/sluchawki/sony-mdr-7506.html', 'http://www.nokaut.pl/sluchawki/sony-mdr-7506.html'),
            array('https://www.nokaut.pl/sluchawki/', 'https://www.nokaut.pl/sluchawki/'),
            array('http://somewl.pl/zakupy/kategoria-hi-tech/', '/hi-tech/'),
            array('http://zakupy.somewl.pl/kategoria-hi-tech/', '/hi-tech/'),
            array('http://zakupy.somewl.pl/kategoria-hi-tech', '/hi-tech/'),
            array('http://zakupy.somewl.pl/kategoria-##hi-tech/', '/hi-tech/'),
            array('http://somewl.pl/zakupy/okazje-hi-tech/', '/hi-tech/'),
            array('http://somewl.pl/zakupy/okazja-dnia-##hi-tech/', '/hi-tech/'),
            array('http://somewl.pl/zakupy/okazja-dnia-##hi-tech', '/hi-tech/'),
            array('http://zakupy.somewl.pl/kategoria-##laptopy/', '/laptopy/'),
            array('http://somewl.pl/zakupy/kategoria-laptopy/', '/laptopy/'),
            array('http://zakupy.somewl.pl/kategoria-laptopy/', '/laptopy/'),
            array('http://zakupy.somewl.pl/kategoria-laptopy', '/laptopy/'),
            array('http://somewl.pl/zakupy/okazje-laptopy/', '/laptopy/'),
            array('https://zakupy.somewl.pl/okazje-laptopy/', '/laptopy/'),
            array('http://somewl.pl/zakupy/okazje-laptopy', '/laptopy/'),
            array('https://zakupy.somewl.pl/okazje-##laptopy/', '/laptopy/'),
            array('http://zakupy.somewl.pl/okazje-laptopy', '/laptopy/'),
            array('http://somewl.pl/zakupy/okazja-dnia-##laptopy/', '/laptopy/'),
            array('http://somewl.pl/zakupy/okazja-dnia-##laptopy', '/laptopy/'),
            array('http://zakupy.somewl.pl/okazja-dnia-##laptopy', '/laptopy/'),
            array(
                'http://zakupy.somewl.pl/produkt-narozniki/stylowa-sofa-kanapa-z-czarnej-skory-naturalnej-naroznik-stockholm-e846f6d7d17570d87db47b3e6e5cb99b.html',
                '/narozniki/stylowa-sofa-kanapa-z-czarnej-skory-naturalnej-naroznik-stockholm-e846f6d7d17570d87db47b3e6e5cb99b.html'
            ),
            array(
                'http://somewl.pl/zakupy/produkt-narozniki/stylowa-sofa-kanapa-z-czarnej-skory-naturalnej-naroznik-stockholm-e846f6d7d17570d87db47b3e6e5cb99b.html',
                '/narozniki/stylowa-sofa-kanapa-z-czarnej-skory-naturalnej-naroznik-stockholm-e846f6d7d17570d87db47b3e6e5cb99b.html'
            ),
            array(
                'http://somewl.pl/zakupy/produkt-##narozniki/stylowa-sofa-kanapa-z-czarnej-skory-naturalnej-naroznik-stockholm-e846f6d7d17570d87db47b3e6e5cb99b.html',
                '/narozniki/stylowa-sofa-kanapa-z-czarnej-skory-naturalnej-naroznik-stockholm-e846f6d7d17570d87db47b3e6e5cb99b.html'
            ),
            array(
                'http://somewl.pl/zakupy/produkt-narozniki/stylowa-sofa-kanapa-z-czarnej-skory-naturalnej-naroznik-stockholm-e846f6d7d17570d87db47b3e6e5cb99b',
                '/narozniki/stylowa-sofa-kanapa-z-czarnej-skory-naturalnej-naroznik-stockholm-e846f6d7d17570d87db47b3e6e5cb99b'
            ),
            array(
                'http://somewl.pl/zakupy/produkt-##narozniki/stylowa-sofa-kanapa-z-czarnej-skory-naturalnej-naroznik-stockholm-e846f6d7d17570d87db47b3e6e5cb99b',
                '/narozniki/stylowa-sofa-kanapa-z-czarnej-skory-naturalnej-naroznik-stockholm-e846f6d7d17570d87db47b3e6e5cb99b'
            ),
            array(
                'http://zakupy.somewl.pl/kategoria-dnia-##meble-do-salonu/producent:meble-laski-kaczorowski-sp-k,cena:373.00~817.00.html#box',
                '/meble-do-salonu/producent:meble-laski-kaczorowski-sp-k,cena:373.00~817.00.html#box'
            ),
            array(
                'http://somewl.pl/zakupy/kategoria-dnia-##meble-do-salonu/producent:meble-laski-kaczorowski-sp-k,cena:373.00~817.00.html#box',
                '/meble-do-salonu/producent:meble-laski-kaczorowski-sp-k,cena:373.00~817.00.html#box'
            ),
            array(
                'http://somewl.pl/zakupy/kategoria-dnia-##meble-do-salonu/producent:meble-laski-kaczorowski-sp-k,cena:373.00~817.00.html#box',
                '/meble-do-salonu/producent:meble-laski-kaczorowski-sp-k,cena:373.00~817.00.html#box'
            ),
        );
    }
}