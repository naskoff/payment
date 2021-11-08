<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Post;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

class AppPostFixtures extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {

        $faker = Factory::create();

        /* @var $user User */
        $user = $this->getReference('POST_USER');

        for ($i = 0; $i <= 25; $i++) {

            $post = new Post();
            $post->setUser($user);
            $post->setTitle($faker->title);
            $post->setContent($faker->text);
            $post->setCreatedAt($faker->dateTime);

            $manager->persist($post);
        }

        $manager->flush();
    }

    public function getOrder(): int
    {
        return 2;
    }
}
