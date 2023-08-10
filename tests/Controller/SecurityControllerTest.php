<?php

declare(strict_types=1);

namespace Tests\Controller;

use Closure;
use App\Enum\Role;
use App\Entity\Task;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Tests\Security\SecurityTrait;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

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
    public function testLoginActionAdmin(string|Closure $route, Role $role, string $class = null): void
    {

        $user = $this->em->getRepository(User::class)->findOneByUsername('admin');
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
        $this->accessAuthorized($computedRoute);
    }
    /**
     * @dataProvider routesAuthorizedProvider
     *
     */
    public function testLoginActionUser(string|Closure $route, Role $role, string $class = null): void
    {

        $user = $this->em->getRepository(User::class)->findOneByUsername('user');
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
        if ($role === Role::Admin) {
            $this->accessDenied($computedRoute);
        } else {
            $this->accessAuthorized($computedRoute);
        }
    }
    public function accessAuthorized(string $computedRoute): void
    {
        $crawler = $this->client->request('GET', $computedRoute);
        $this->assertResponseStatusCodeSame(200);
    }
    public function accessDenied(string $computedRoute): void
    {
        $crawler = $this->client->request('GET', $computedRoute);
        $this->assertResponseStatusCodeSame(403);
    }
    /**
     *
     * @dataProvider routesForbiddenProvider
     */
    public function testLogoutCheck(string $route, string $method): void
    {
        $expectedCrawler = $this->client->request('GET', '/login');
        $this->loginInAsUser($this->em);
        $this->client->request('GET', '/logout');
        $this->client->request($method, $route);
        $crawler = $this->client->followRedirect();
        $this->assertEquals($expectedCrawler->text(), $crawler->text());
    }
    /**
     * @dataProvider routesForbiddenProvider
     */
    public function testFirewall(string $route, string $method): void
    {
        $expectedCrawler = $this->client->request('GET', '/login');
        $this->client->request($method, $route);
        $crawler = $this->client->followRedirect();
        $this->assertEquals($expectedCrawler->text(), $crawler->text());
    }
    //routes behind firewall
    /**
     * @return mixed[]
     */
    public function routesForbiddenProvider(): array
    {
        return array(
            array('/tasks', 'GET'),
            array('/tasks/create', 'GET'),
            array('/tasks/1/toggle', 'GET'),
            array('tasks/1/delete', 'GET'),
            array('/tasks/1/edit', 'GET'),
            array('/users/1/edit', 'GET'),
            array('/users', 'GET'),
        );
    }
    /**
     * @return mixed[]
     */
    public function routesAuthorizedProvider(): array
    {
        return array(
            array('/tasks', Role::User),
            array('/tasks/create', Role::User),
            array('/users', Role::Admin),
            array(fn ($id) => 'tasks/' . $id . '/edit', Role::User, Task::class),
            array(fn ($id) => 'users/' . $id . '/edit', Role::Admin, User::class),
        );
    }
    private function getId(string $class): int
    {
        $repository= $this->em->getRepository($class);
        return $repository->findAll()[0]->getId();
    }
}
