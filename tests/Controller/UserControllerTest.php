<?php

declare(strict_types=1);

namespace Tests\Controller;

use App\Entity\User;
use Tests\Security\SecurityTrait;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Security\Csrf\CsrfToken;


class UserControllerTest extends WebTestCase
{

    private KernelBrowser $client;
    private ObjectManager $em;
    private EntityRepository $userRepository;

    use SecurityTrait;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = $this->client->getContainer()->get('doctrine')->getManager();
        $this->userRepository = $this->em->getRepository(User::class);
        //we can asume we are always connected for our Tests and tests the firewall in SecurityController
        $this->loginInAsUser($this->em);
    }
    /**
     * 
     */
    public function testListAction()
    {
        $crawler = $this->client->request('get', '/users');
        $this->assertResponseStatusCodeSame(200);
        //count the number of row it should be equal to the number of entity in our DB
        $this->assertEquals(count($this->userRepository->findAll()), $crawler->filter('tbody tr')->count());
        $this->assertSame('Liste des utilisateurs', $crawler->filter('h1')->text());
    }
    /**
     * @dataProvider fieldProvider
     * 
     */
    public function testCreateAction(array $fieldForm, bool $isValid, string $csrfToken = null)
    {
        $expectedEntityCount = $this->userRepository->count([]);
        $crawler = $this->client->request('get', '/users/create');
        $this->assertResponseStatusCodeSame(200);
        $this->checkFormSubmission($fieldForm, $isValid, 'Ajouter', $crawler);
        $this->assertSame($isValid ? ++$expectedEntityCount : $expectedEntityCount, $this->userRepository->count([]));
    }
    /**
     * @dataProvider fieldProvider
     * 
     */
    public function testEditAction(array $fieldForm, bool $isValid, string $csrfToken = null)
    {

        $user = $this->userRepository->findOneByUsername('test');
        $crawler = $this->client->request('get', 'users/' . $user->getId() . '/edit');
        $this->assertResponseStatusCodeSame(200);
        $this->checkFormSubmission($fieldForm, $isValid, 'Modifier', $crawler);
        $user->setUsername('test');
    }
    public function checkFormSubmission(array $fieldForm, bool $isValid, string $button, Crawler $crawler)
    {
        $form = $crawler->selectButton($button)->form();
        $this->assertNotNull($form);
        $form->setValues($fieldForm);
        $crawlerSubmit = $this->client->submit($form);
        if ($isValid) {
            $this->assertResponseStatusCodeSame(302);
            $crawler = $this->client->followRedirect();
            $this->assertResponseStatusCodeSame(200);
            $user = $this->userRepository?->findOneByUsername($fieldForm['user']['username']);
            $this->assertNotNull($user);
            $this->assertSame($fieldForm['user']['email'], $user->getEmail());
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertSame($crawler->getUri(), $crawlerSubmit->getUri());
        }
    }
    public function fieldProvider()
    {
        return [
            [$this->createForm('test2', 'mdp', 'vraimail@g.com'), true],
            [$this->createForm(';DROP table task', 'mdp', 'vraimail@g.com'), true],
            [$this->createForm('test2', 'mdp', 'fauxmail.com'), false],
            [$this->createForm('test2', 'mdp', 'vraimail@g.com', 'paslemêmemdp'), false],
            /* The line above throw an Exception we should can and will catch this (we just need to set it to unique)
            By creating a new \** @uniqueEntity(username) in our entity*/
            [$this->createForm('seb', 'mdp', 'vraimail@g.com'), false], //same name as another fixtures*/
            [$this->createForm('test2', 'mdp', 'email@g.com'), false] //same mail as another fixtures

        ];
    }
    public function createForm(string $username, string $password, string $mail, string $secondPassword = null)
    {
        return [
            "user" => [
                "username" => $username,
                "password" => [
                    "first" => $password,
                    "second" => $secondPassword ?? $password,
                ],
                "email" => $mail
            ]

        ];
    }
}
