<?php

namespace Berkman\SlideshowBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FeedbackTest extends WebTestCase
{
    public function testCompleteScenario()
    {
        // Create a new client to browse the application
        $client = $this->createClient();
        $client->followRedirects();

        $crawler = $client->request('GET', '/feedback');
        $this->assertTrue(200 === $client->getResponse()->getStatusCode());

        // Fill in the form and submit it
        $form = $crawler->selectButton('Send')->form(array(
            'feedback[email]'  => 'jclark@cyber.law.harvard.edu',
            'feedback[message]'  => 'This is my feedback.',
            'feedback[cc]'  => true
        ));

        $client->submit($form);

        $this->assertTrue(200 === $client->getResponse()->getStatusCode());
        $crawler = $client->getCrawler();
        $this->assertTrue($crawler->filter('div:contains("Your feedback has been sent")')->count() > 0);
    }
}
