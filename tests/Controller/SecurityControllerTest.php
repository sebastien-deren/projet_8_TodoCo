<?php

declare(strict_types=1);

namespace Tests\Controller;

use App\Entity\Task;
use App\Entity\User;
use Closure;
use Doctrine\ORM\EntityManager;
use PhpParser\Node\Expr\Instanceof_;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\UriResolver;
use Tests\Security\SecurityTrait;

use function PHPUnit\Framework\isNull;

class SecurityControllerTest extends WebTestCase
{
    use SecurityTrait;

    private KernelBrowser $client;
    private EntityManager $em;
    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = $this->client->getContainer()->get('doctrine')->getManager();
    }
    /**
     * @dataProvider routesAuthorizedProvider
     *
     */
    public function testLoginAction(string|Closure $route, string $class = null)
    {

        $user = $this->em->getRepository(User::class)->findOneByUsername('seb');
        $crawler = $this->client->request('GET', '/login');
        $this->assertResponseStatusCodeSame(200);
        $form = $crawler->selectButton('Se connecter')->form();
        $this->assertNotNull($form);
        $form->setValues(["_username" => $user->getUsername(), "_password" => "password"]);
        $crawler = $this->client->submit($form);
        $crawler = $this->client->followRedirect();
        $expectedCrawler = $this->client->request('GET', '/');
        $this->assertSame($expectedCrawler->getUri(), $crawler->getUri());
        $computedRoute = ($route instanceof Closure) ? $route($this->getId($class)) : $route;
        $crawler = $this->client->request('GET', $computedRoute);
        $this->assertResponseStatusCodeSame(200);
    }
    /**
     *
     * @dataProvider routesForbiddenProvider
     */
    public function testLogoutCheck(string $route)
    {
        $expectedCrawler = $this->client->request('GET', '/login');
        $this->loginInAsUser($this->em);
        $this->client->request('GET','/logout');
        $this->client->request('GET', $route);
        $crawler = $this->client->followRedirect();
        $this->assertEquals($expectedCrawler->text(), $crawler->text());

    }
    /**
     * @dataProvider routesForbiddenProvider
     */
    public function testFirewall(string $route)
    {
        $expectedCrawler = $this->client->request('GET', '/login');
        $this->client->request('GET', $route);
        $crawler = $this->client->followRedirect();
        $this->assertEquals($expectedCrawler->text(), $crawler->text());
    }
    //routes behind firewall
    public function routesForbiddenProvider()
    {
        return array(
            array('/tasks'),
            array('/tasks/create'),
            array('/tasks/1/toggle'),
            array('tasks/1/delete'),
            array('/tasks/1/edit'),
            array('/users/1/edit'),
            array('/users'),
        );
    }
    public function routesAuthorizedProvider()
    {
        return array(
            array('/tasks'),
            array('/tasks/create'),
            array('/users'),
            array(fn ($id) => 'tasks/' . $id . '/edit', Task::class),
            array(fn ($id) => 'users/' . $id . '/edit', User::class),
        );
    }
    private function getId($class): int
    {
        return $this->em->getRepository($class)->findAll()[0]->getId();
    }
}
