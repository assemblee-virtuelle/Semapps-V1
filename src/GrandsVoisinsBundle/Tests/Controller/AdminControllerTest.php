<?php
/**
 * Created by PhpStorm.
 * User: LaFaucheuse
 * Date: 16/05/2017
 * Time: 16:17
 */

namespace GrandsVoisinsBundle\Tests\Controller;


class AdminControllerTest extends toolsFormTest
{

    public function testParameters(){
        $formButtonName = 'Enregistrer';
        $field = [
          'username' => ['test_php_new'],
          'password' => [$this->password],
          'passwordNew' => ['newPassword'],
          'passwordNewConfirm' => ['newPassword'],
        ];
        $fieldBack = [
          'username' => [$this->user],
          'password' => ['newPassword'],
          'passwordNew' => [$this->password],
          'passwordNewConfirm' => [$this->password],
        ];
        self::testLogin();
        $this->crawler = $this->client->request('GET','/parametres');
        self::assertGreaterThan(0,$this->crawler->filter('html:contains("'.$formButtonName.'")')->count());
        $this->getForm($formButtonName);
        $this->setForm('grandsvoisinsbundle_user',$field,0);
        $this->crawler = $this->client->submit($this->form);
        $this->getForm($formButtonName);
        self::assertGreaterThan(0,$this->crawler->filter('html:contains("les informations ont été correctement enregistés")')->count());
        self::assertEquals('test_php_new',$this->form["grandsvoisinsbundle_user[username]"]->getValue());
        $this->testLogout();
        $this->login('test_php_new','newPassword');
        $this->crawler = $this->client->request('GET', '/parametres');
        $this->getForm($formButtonName);
        $this->setForm('grandsvoisinsbundle_user',$fieldBack,0);
        $this->crawler = $this->client->submit($this->form);
        self::assertGreaterThan(0,$this->crawler->filter('html:contains("les informations ont été correctement enregistés")')->count());
        self::assertEquals('test_php',$this->form["grandsvoisinsbundle_user[username]"]->getValue());
        $this->testLogout();
        $this->testLogin();
    }
}