<?php

namespace semappsBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;


abstract class AbstractComponentController extends Controller
{


		protected function getBundleNameFromRequest($request){
				return explode("\\",$request->attributes->get('_controller'))[0];
		}
}
