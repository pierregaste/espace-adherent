<?php

namespace Tests\App\Controller\EnMarche;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractEnMarcheWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
class PageControllerTest extends AbstractEnMarcheWebTestCase
{
    use ControllerTestTrait;

    #[DataProvider('providePages')]
    public function testPages(string $path)
    {
        $this->client->request(Request::METHOD_GET, $path);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
    }

    #[Depends('testPages')]
    public function testEmmanuelMacronVideos()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/emmanuel-macron/videos');

        $this->assertSame(12, $crawler->filter('.fb-video')->count());

        $videoGridText = $crawler->filter('.videos__grid')->text();

        $this->assertStringContainsString('Laurence Haïm a un message pour vous. Inscrivez-vous ➜ en-marche.fr/bercy', $videoGridText);
        $this->assertStringContainsString('#MacronPau avec les helpers en coulisses. Allez allez ! Cette révolution nous allons la porter.', $videoGridText);
        $this->assertStringNotContainsString('Découvrez le teaser', $videoGridText);
    }

    public function testMouvementLegislativesAction()
    {
        $this->client->request(Request::METHOD_GET, '/le-mouvement/legislatives');

        $this->assertClientIsRedirectedTo('https://legislatives.en-marche.fr', $this->client, false, true);
    }

    public static function providePages(): \Generator
    {
        yield ['/emmanuel-macron'];
        yield ['/emmanuel-macron/revolution'];
        yield ['/emmanuel-macron/videos'];
        yield ['/le-mouvement'];
        yield ['/le-mouvement/les-comites'];
        yield ['/le-mouvement/devenez-benevole'];
        yield ['/mentions-legales'];
        yield ['/elles-marchent'];
        yield ['/formation'];
        yield ['/formation/difficultes-internet'];
        yield ['/cestduconcret'];
        yield ['/action-talents'];
        yield ['/action-talents/candidater'];
        yield ['/nos-offres'];
        yield ['/candidatures-delegue-general-et-bureau-executif'];
        yield ['/emmanuel-macron/test'];
    }
}
