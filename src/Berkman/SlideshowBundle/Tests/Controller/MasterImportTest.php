<?php

namespace Berkman\SlideshowBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MasterImportTest extends WebTestCase
{
    public function testCompleteScenario()
    {
        // Create a new client to browse the application
        $client = $this->createClient();
        $client->followRedirects();

        $crawler = $client->request('GET', '/');
        $this->assertTrue(200 === $client->getResponse()->getStatusCode());

        $file = new UploadedFile(
            realpath(__DIR__ . '/../MasterImport.csv'),
            'MasterImport.csv',
            'text/csv',
            1033
        );

        // Fill in the form and submit it
        $form = $crawler->selectButton('Import')->form(array(
            'master_import[file]'  => $file
        ));

        $client->submit($form);

        $this->assertTrue(200 === $client->getResponse()->getStatusCode());
        $crawler = $client->getCrawler();
        $this->assertTrue($crawler->filter('legend:contains("Login")')->count() > 0);

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
        $this->assertTrue($crawler->filter('h2:contains("Kittens")')->count() > 0);

        $crawler = $client->click($crawler->selectLink('Edit')->link());
        $this->assertTrue(200 === $client->getResponse()->getStatusCode());
        $this->assertTrue($crawler->filter('a:contains("EDIT SLIDESHOW")')->count() > 0);

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
