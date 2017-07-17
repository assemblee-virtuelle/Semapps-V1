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

    public function testTeam(){
        $field = [
            'username' => ['newUser'],
            'email' => ['newUser@newUser.fr'],
        ];
        $formButtonName = 'Créer';
        self::testLogin();
        $this->crawler = $this->client->request('GET','/mon-compte/invite');
        self::assertGreaterThan(0,$this->crawler->filter('html:contains("'.$formButtonName.'")')->count());
        $this->getForm($formButtonName);
        $this->setForm('grandsvoisinsbundle_user',$field,0);
        $this->crawler = $this->client->submit($this->form);
        $this->crawler = $this->client->followRedirect();
        $this->getForm($formButtonName);

        self::assertGreaterThan(0,$this->crawler->filter('html:contains("Un compte à bien été créé pour")')->count());
        self::assertGreaterThan(0,$this->crawler->filter('html:contains("newUser")')->count());
        self::assertGreaterThan(0,$this->crawler->filter('.team-user-delete')->count());
        //$this->crawler = $this->client->click($this->crawler->filter('a:contains("delete-'.$field['username'].'")')->eq(0)->link());
        self::assertEquals(1,$this->crawler->filter('#delete-'.$field['username'][0])->count());
        $userId = $this->crawler->filter('#delete-'.$field['username'][0])->attr('rel');

        $this->crawler = $this->client->request('GET','/mon-compte/user/delete/' . $userId);
        $this->crawler = $this->client->followRedirect();
        $this->debugContent();
        self::assertEquals(1,$this->crawler->filter('html:contains("a bien été supprimé")')->count());

    }

}