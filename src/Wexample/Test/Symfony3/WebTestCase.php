<?php

namespace Wexample\Test\Symfony3;

use GuzzleHttp\Client;

abstract class WebTestCase extends \Symfony\Bundle\FrameworkBundle\Test\WebTestCase
{
    protected $preserveGlobalState = false;
    protected $runTestInSeparateProcess = true;
    /**
     * @var \Symfony\Component\HttpKernel\Client
     */
    var $client;
    var $applicationPath = __DIR__."/../../../web/app.php";
    var $localePath = '';

    public function createApplication()
    {
        putenv('APP_ENV=local');
        $app          = require $this->applicationPath;
        $app['debug'] = true;

        return $app;
    }

    public function log($message)
    {
        echo "\n".$message;
    }

    public function content(Client $client)
    {
        echo $client->getResponse()->getContent();
    }

    public function goToLocate($path)
    {
        return $this->client->request('GET', $this->localePath.$path);
    }

    public function newClient()
    {
        // Needed to be able to log user.
        $this->app['session.test'] = true;
        $this->client              = static::createClient();

        return $this->client;
    }

    public function checkPath($expectedPath)
    {
        $realPath = $this->client->getRequest()->getPathInfo();
        $this->assertTrue(
          $realPath == $expectedPath,
          $realPath.' should be equal to '.$expectedPath
        );
    }

    public function checkPathLocale($expectedPath) {
        return $this->checkPath($this->localePath . $expectedPath);
    }

    public function checkStatusCode($expectedStatusCode)
    {
        $statusCode = $this->client->getResponse()->getStatusCode();
        $this->assertTrue(
          $statusCode == $expectedStatusCode,
          $statusCode.' should be equal to '.$expectedStatusCode
        );
    }

    public function logUser($login, $password)
    {

        $crawler = $this->client->request('GET', $this->localePath.'/login');

        // Page is displayed.
        $this->assertTrue(
          $this->client->getResponse()->isOk(),
          'Login is working'
        );
        $button = $crawler->filter('button[type=submit]');

        $this->log('Login submit button : '.$button->count());

        $this->assertEquals(
          1,
          $button->count()
        );

        $form = $button->form();

        $form['_username'] = $login;
        $form['_password'] = $password;

        // Submit the form
        $this->client->submit($form);

        $this->assertTrue(
          $this->client->getResponse()->getStatusCode() === 302,
          'Logged'
        );

        $this->checkPath('/login-check');

        $this->client->followRedirect();

    }
}
