<?php

namespace Berkman\SlideshowBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testCompleteScenario()
    {
        // Create a new client to browse the application
        $client = $this->createClient();
        $client->followRedirects();

        $crawler = $client->request('GET', '/');
        $this->assertTrue(200 === $client->getResponse()->getStatusCode());

        // Fill in the form and submit it
        $form = $crawler->selectButton('Search')->form(array(
            'finder[keyword]'  => 'kitten',
            'finder[catalogs][VIA]' => true
        ));

        $client->submit($form);

        $this->assertTrue(200 === $client->getResponse()->getStatusCode());
        $crawler = $client->getCrawler();

        $this->assertTrue($crawler->filter('h2:contains("images for \\\"kitten\\\"")')->count() > 0);

        $form = $crawler->selectButton('Finish')->form(array(
            'images[0]' => true,
            'images[1]' => true,
            'images[2]' => true,
            'collections[6]' => true 
        ));

        $client->submit($form);

        $this->assertTrue(200 === $client->getResponse()->getStatusCode());
        $crawler = $client->getCrawler();
        $this->assertTrue($crawler->filter('h2:contains("Login")')->count() > 0);

        $form = $crawler->selectButton('Login')->form(array(
            '_username' => 'justin',
            '_password' => 'password'
        ));

        $client->submit($form);

        $this->assertTrue(200 === $client->getResponse()->getStatusCode());
        $crawler = $client->getCrawler();
        $this->assertTrue($crawler->filter('h2:contains("Your images")')->count() > 0);

        $form = $crawler->selectButton('Create')->form(array(
            'slideshow[name]' => 'Kittens',
        ));

        $client->submit($form);

        $this->assertTrue(200 === $client->getResponse()->getStatusCode());
        $crawler = $client->getCrawler();
        $this->assertTrue($crawler->filter('div:contains("New slideshow \\\"Kittens\\\" created")')->count() > 0);
        $this->assertTrue($crawler->filter('h2:contains("Kittens by justin")')->count() > 0);

        $crawler = $client->click($crawler->selectLink('Edit')->link());
        $this->assertTrue(200 === $client->getResponse()->getStatusCode());
        $this->assertTrue($crawler->filter('h2:contains("Edit Slideshow")')->count() > 0);

        $form = $crawler->selectButton('Update')->form(array(
            'slideshow[always_show_info]' => true
        ));

        $client->submit($form);
        $this->assertTrue(200 === $client->getResponse()->getStatusCode());
        $crawler = $client->getCrawler();
        $this->assertTrue($crawler->filter('div:contains("Slideshow \\\"Kittens\\\" successfully updated.")')->count() > 0);

        $crawler = $client->click($crawler->selectLink('Start Slideshow')->link());
        $this->assertTrue($crawler->filter('title:contains("Kittens")')->count() > 0);
        $crawler = $client->back();

        $form = $crawler->selectButton('Delete')->form();
        $client->submit($form);

        $crawler = $client->getCrawler();
        $this->assertTrue($crawler->filter('div:contains("Slideshow \\\"Kittens\\\" successfully deleted.")')->count() > 0);
    }
}
