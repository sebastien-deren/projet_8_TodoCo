<?php

declare(strict_types=1);

namespace Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Tests\Security\SecurityTrait;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;


class UserControllerTest extends WebTestCase
{

    private KernelBrowser $client;
    private ObjectManager $em;
    /**
     * @var UserRepository $userRepository
     */
    private EntityRepository $userRepository;
    private User $user;

    use SecurityTrait;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = $this->client->getContainer()->get('doctrine')->getManager();
        $this->userRepository = $this->em->getRepository(User::class);
        //we can asume we are always connected for our Tests and tests the firewall in SecurityController
        $this->user = $this->loginInAsUser($this->em,'admin');
        $this->assertContains('ROLE_ADMIN',$this->user->getRoles());
    }
    /**
     * 
     */
    public function testListAction():void
    {
        $crawler = $this->client->request('get', '/users');
        $this->assertResponseStatusCodeSame(200);
        //count the number of row it should be equal to the number of entity in our DB
        $this->assertEquals(count($this->userRepository->findAll()), $crawler->filter('tbody tr')->count());
        $this->assertSame('Liste des utilisateurs', $crawler->filter('h1')->text());
    }
    /**
     * @dataProvider fieldProvider
     * @param mixed[] $fieldForm
     * 
     */
    public function testCreateAction(array $fieldForm, bool $isValid, string $csrfToken = null):void
    {
        $expectedEntityCount = $this->userRepository->count([]);
        $crawler = $this->client->request('get', '/users/create');
        $this->assertResponseStatusCodeSame(200);
        $this->checkFormSubmission($fieldForm, $isValid, 'Ajouter', $crawler);
        $this->assertSame($isValid ? ++$expectedEntityCount : $expectedEntityCount, $this->userRepository->count([]));
    }
    /**
     * @dataProvider fieldProvider
     * @param mixed[] $fieldForm
     */
    public function testEditAction(array $fieldForm, bool $isValid, string $csrfToken = null):void
    {

        $user = $this->userRepository->findOneByUsername('test');
        $crawler = $this->client->request('get', 'users/' . $user->getId() . '/edit');
        $this->assertResponseStatusCodeSame(200);
        $this->checkFormSubmission($fieldForm, $isValid, 'Modifier', $crawler);
        $user->setUsername('test');
    }
    /**
     * @param mixed[] $fieldForm
     */
    public function checkFormSubmission(array $fieldForm, bool $isValid, string $button, Crawler $crawler):void
    {
        $form = $crawler->selectButton($button)->form();
        $this->assertNotNull($form);
        $form->setValues($fieldForm);
        $crawlerSubmit = $this->client->submit($form);
        if ($isValid) {
            $this->assertResponseStatusCodeSame(302);
            $crawler = $this->client->followRedirect();
            $this->assertResponseStatusCodeSame(200);
            $user = $this->userRepository->findOneByUsername($fieldForm['user']['username'])??null;
            $this->assertNotNull($user);
            $this->assertSame($fieldForm['user']['email'], $user->getEmail());
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertSame($crawler->getUri(), $crawlerSubmit->getUri());
        }
    }
    /**
     * @return array<mixed>
     */
    public function fieldProvider():array
    {
        return [
            [$this->createForm('test2', 'mdp', 'vraimail@g.com'), true],
            [$this->createForm(';DROP table task', 'mdp', 'vraimail@g.com'), true],
            [$this->createForm('test2', 'mdp', 'fauxmail.com'), false],
            [$this->createForm('test2', 'mdp', 'vraimail@g.com', 'paslemêmemdp'), false],
            /* The line above throw an Exception we should can and will catch this (we just need to set it to unique)
            By creating a new \** @uniqueEntity(username) in our entity*/
            [$this->createForm('admin', 'mdp', 'vraimail@g.com'), false], //same name as another fixtures*/
            [$this->createForm('test2', 'mdp', 'email@g.com'), false] //same mail as another fixtures

        ];
    }
    /**
     * @return array<mixed>
     */
    public function createForm(string $username, string $password, string $mail, string $secondPassword = null):array
    {
        return [
            "user" => [
                "username" => $username,
                "clear_password" => [
                    "first" => $password,
                    "second" => $secondPassword ?? $password,
                ],
                "email" => $mail
            ]

        ];
    }
}
