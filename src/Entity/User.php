<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

use function PHPUnit\Framework\isNull;

#[ORM\Entity]
#[UniqueEntity('email')]
#[UniqueEntity('username')]
#[ORM\Table('user_app')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\Column(type: 'string', length: 25, unique: true)]
    #[Assert\NotBlank(message: "Vous devez saisir un nom d'utilisateur.")]
    private string $username;

    #[ORM\Column(type: 'string', length: 64)]
    private ?string $password = null;

    #[ORM\Column(type: 'string', length: 60, unique: true)]
    #[Assert\NotBlank(message: 'Vous devez saisir une adresse email.')]
    #[Assert\Email(message: "Le format de l'adresse n'est pas correcte.")]
    private string $email;

    /**
     * @var Collection<string|int,Task> $tasks
     */
    #[ORM\OneToMany(mappedBy: 'creator', targetEntity: Task::class, orphanRemoval: true)]
    private Collection $tasks;

    /**
     * @var array<string> $roles
     */
    #[ORM\Column(type: Types::SIMPLE_ARRAY, nullable: true)]
    private array $roles = [];

    public function __construct()
    {
        $this->tasks = new ArrayCollection();
    }

    public function getId():int
    {
        return $this->id;
    }
    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function getUsername():string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getSalt():null
    {
        return null;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getEmail():string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }
    /**
     * @param array<string> $roles
     */
    public function setRoles(array|string $roles): self
    {
        if (is_string($roles)) {
            $this->roles[] = $roles;
        } else {
            $this->roles = $roles;
        }
        return $this;
    }

    public function eraseCredentials(): void
    {
    }

    /**
     * @return Collection<int|string, Task>
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(Task $task): static
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks->add($task);
            $task->setCreator($this);
        }

        return $this;
    }

    public function removeTask(Task $task): static
    {
        if ($this->tasks->removeElement($task)) {
            // set the owning side to null (unless already changed)
            if ($task->getCreator() === $this) {
                $task->setCreator(null);
            }
        }

        return $this;
    }
}
