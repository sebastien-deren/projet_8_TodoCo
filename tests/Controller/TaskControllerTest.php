<?php

declare(strict_types=1);

namespace Tests\Controller;

use App\Entity\Task;
use Doctrine\ORM\EntityRepository;
use Tests\Security\SecurityTrait;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskControllerTest extends WebTestCase
{

    private KernelBrowser $client;
    private ObjectManager $em;
    private EntityRepository $taskRepository;


    use SecurityTrait;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = $this->client->getContainer()->get('doctrine')->getManager();
        $this->taskRepository = $this->em->getRepository(Task::class);
    }
    /**
     * @covers TaskController::listAction
     */
    public function testIndex()
    {
        $this->loginInAsUser($this->em);
        //Hardcoding the request URLs is a best practice for application tests. 
        $crawler = $this->client->request('GET', "tasks");
        //check that the page is loaded correctly
        $this->assertResponseStatusCodeSame(200);
        //might rewrite this to not break on site reworking
        //check that the templates is loaded corretly
        $this->assertSame('To Do List app', $crawler->filter('title')->text());
        //check that we have the good number of task (will changed 10 to $task->findAll->count() )
        $this->assertCount(count($this->taskRepository->findAll()), $crawler->filter('div.row > div.col-lg-4'));
    }
    /**
     * @dataProvider formProvider
     * @covers TaskController::editAction
     */
    public function testCreate(string $title,string $content, bool $isValid)
    {
        $expectedEntityCount = $this->taskRepository->count([]);
        $this->loginInAsUser($this->em);
        $crawler = $this->client->request('GET', 'tasks/create');
        $this->assertResponseStatusCodeSame(200);
        $form = $crawler->selectButton('Ajouter')->form();
        $this->assertNotNull($form);
        $form->setValues(['task' => [
            'title' => $title,
            'content' => $content,
            '_token' => $form->getPhpValues()['task']['_token']
        ]]);


        $crawler = $this->client->submit($form);
        if($isValid){
            $expectedEntityCount++;
            $this->assertResponseStatusCodeSame(302);
            $crawler = $this->client->followRedirect();
            $this->assertResponseStatusCodeSame(200);
            $this->assertNotEmpty($this->taskRepository->findByTitle($title));
        }
        else{
            $this->assertStringContainsString('devez saisir', $crawler->filter('form')->text());
            $this->assertGreaterThanOrEqual(1, $crawler->filter('ul li')->count());
        }
        $this->assertSame($expectedEntityCount,$this->taskRepository->count([]));
    }

    /**
     * @dataProvider formProvider
     * @covers TaskController::editAction
     */
    public function testEdit(string $title, string $content, bool $validity)
    {
        $task = $this->taskRepository->findAll()[0];
        $this->loginInAsUser($this->em);
        $crawler = $this->client->request('GET', 'tasks/' . $task->getId() . '/edit');
        $this->assertResponseStatusCodeSame(200);
        $form = $crawler->selectButton('Modifier')->form();
        $this->assertNotNull($form);
        $this->assertSame($task->getContent(), $form->getPhpValues()["task"]["content"]);
        $this->assertSame($task->getTitle(), $form->getPhpValues()['task']['title']);


        $form->setValues(['task' => [
            'title' => $title,
            'content' => $content,
            '_token' => $form->getPhpValues()['task']['_token']
        ]]);

        $crawler = $this->client->submit($form->disableValidation());
        if ($validity) {
            $this->assertResponseStatusCodeSame(302);
            $crawler = $this->client->followRedirect();
            $this->assertResponseStatusCodeSame(200);
            $this->assertSelectorTextContains('.alert', 'La tâche a bien été modifiée.');
            $this->assertNotEmpty($this->taskRepository->findByTitle($title));
        } else {
            $this->assertStringContainsString('devez saisir', $crawler->filter('form')->text());
            $this->assertGreaterThanOrEqual(1, $crawler->filter('ul li')->count());
        }
    }
    /**
     * @covers TaskController::toggleTaskAction
     */
    public function testToggle(){
        $this->loginInAsUser($this->em);
        $task= $this->taskRepository->findAll()[0];
        $isDone= $task->isDone();
        $this->client->request('GET','tasks/'.$task->getId().'/toggle');
        $this->assertResponseStatusCodeSame(302);
        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);
        $this->assertEquals(!$isDone,$task->isDone()); 
        $this->assertSelectorTextContains('.alert','a bien été marquée comme faite');
        
        $task = $this->taskRepository->find($task->getId());

    }
    /**
     * @covers TaskController:deleteTaskAction
     */
    public function testDeleteTask()
    {
        $expectedEntityCount = $this->taskRepository->count([]);
        $this->loginInAsUser($this->em);
        $task=$this->taskRepository->findAll()[0];
        $this->client->request('GET','tasks/'.$task->getId().'/delete');
        $this->assertResponseStatusCodeSame(302);
        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);
        $this->assertEquals($expectedEntityCount-1,$this->taskRepository->count([]));
    }
    public function formProvider()
    {
        return [
            'data-set-Valid' => ['testValid', 'we have change the content', true],
            'data-set-Null-Title' => ['', 'we have change the content', false],
            'data-set-Null-Content' => ['testValid', '', false],
            'data-set-Null-Both' => ['', '', false],
            'data-set-SQL-injection' => ['DROP TABLE;', 'SELECT * FROM user;', true],
        ];
    }
}
