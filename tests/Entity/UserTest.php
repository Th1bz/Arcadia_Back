<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Entity\Role;
use App\Entity\RapportVeterinaire;
use PHPUnit\Framework\TestCase;
use Doctrine\Common\Collections\Collection;

class UserTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = new User();
    }

    public function testUserCreation(): void
    {
        $this->assertInstanceOf(User::class, $this->user);
        $this->assertNull($this->user->getId());
        $this->assertInstanceOf(Collection::class, $this->user->getRapportVeterinaires());
        $this->assertTrue($this->user->getRapportVeterinaires()->isEmpty());
    }

    public function testUsername(): void
    {
        $username = 'johndoe';
        $this->user->setUsername($username);
        $this->assertEquals($username, $this->user->getUsername());
    }

    public function testPassword(): void
    {
        $password = 'password123';
        $this->user->setPassword($password);
        $this->assertEquals($password, $this->user->getPassword());
    }

    public function testName(): void
    {
        $name = 'Doe';
        $this->user->setName($name);
        $this->assertEquals($name, $this->user->getName());
    }

    public function testFirstName(): void
    {
        $firstName = 'John';
        $this->user->setFirstName($firstName);
        $this->assertEquals($firstName, $this->user->getFirstName());
    }

    public function testRole(): void
    {
        $role = new Role();
        $this->user->setRole($role);
        $this->assertSame($role, $this->user->getRole());
    }

    public function testRapportVeterinaire(): void
    {
        $rapport = new RapportVeterinaire();
        
        // Test ajout d'un rapport
        $this->user->addRapportVeterinaire($rapport);
        $this->assertTrue($this->user->getRapportVeterinaires()->contains($rapport));
        $this->assertSame($this->user, $rapport->getUser());

        // Test ajout du même rapport (ne devrait pas dupliquer)
        $this->user->addRapportVeterinaire($rapport);
        $this->assertEquals(1, $this->user->getRapportVeterinaires()->count());

        // Test suppression d'un rapport
        $this->user->removeRapportVeterinaire($rapport);
        $this->assertFalse($this->user->getRapportVeterinaires()->contains($rapport));
        $this->assertNull($rapport->getUser());
    }

    public function testFluentInterfaces(): void
    {
        // Test que les setters retournent l'instance de l'objet
        $this->assertSame($this->user, $this->user->setUsername('test'));
        $this->assertSame($this->user, $this->user->setPassword('test'));
        $this->assertSame($this->user, $this->user->setName('test'));
        $this->assertSame($this->user, $this->user->setFirstName('test'));
        $this->assertSame($this->user, $this->user->setRole(new Role()));
    }
}