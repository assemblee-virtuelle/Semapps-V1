<?php
/**
 * Created by PhpStorm.
 * User: LaFaucheuse
 * Date: 12/05/2017
 * Time: 09:37
 */

namespace mmmfestBundle\Tests\Controller;


class toolsFormTest extends toolsTest
{
    const TEST_CREATE = 0;
    const TEST_UPDATE = 1;
    const TEST_DELETE = 2;
    /** @var \Symfony\Component\DomCrawler\Form null  */
    protected $form = null;

    public function testHomeAction(){
        $this->login($this->user,$this->password);
        $this->crawler = $this->client->request('GET', '/mon-compte/');
        self::assertEquals(true,$this->client->getResponse()->isRedirect());
        $this->crawler = $this->client->followRedirect();
        self::assertGreaterThan(0,$this->crawler->filter('html:contains("Déconnexion")')->count());
    }


    public function getForm($buttonName){
        $this->form = $this->crawler->selectButton($buttonName)->form();
    }

    public function setForm($nameForm, Array $fields, $key = toolsFormTest::TEST_DELETE ){
        foreach ($fields as $field => $values){
            $this->form[$nameForm.'['.$field.']']->setValue(!isset($values[$key]) ? '' : $values[$key])  ;

        }
    }

    public function formTest($nameForm, Array $fields, $key = toolsFormTest::TEST_DELETE ){
        foreach ($fields as $field => $values){
            self::assertEquals(!isset($values[$key]) ? '' : $values[$key],$this->form[$nameForm.'['.$field.']']->getValue());
        }
    }

}