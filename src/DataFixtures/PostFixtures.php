<?php

namespace App\DataFixtures;

use App\Entity\Post;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpClient\HttpClient;

class PostFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Fetch dummy JSON API
        $client = HttpClient::create();
        $response = $client->request('GET', 'https://jsonplaceholder.typicode.com/posts');
        $postsData = $response->toArray();

        foreach ($postsData as $postData) {
            $post = new Post();
            $post->setApiId($postData['id']);
            $post->setTitle($postData['title']);
            $post->setBody($postData['body']);

            $manager->persist($post);
        }

        $manager->flush();
    }
}
