<?php
/**
 * Created by PhpStorm.
 * User: LaFaucheuse
 * Date: 22/03/2017
 * Time: 10:36
 */

namespace GrandsVoisinsBundle\Services;


class FormattingForm
{
    public function format($formBySf){
        $formTransformed["url"] = $formTransformed["uri"] = $formBySf["subject"];
        $formTransformed["thumbnail"] = $formBySf["thumbnail"];
        $formTransformed["fields"] = array();
        foreach ($formBySf["fields"] as $field){
            if(array_key_exists($field["property"],$formTransformed["fields"])){
                array_push($formTransformed["fields"][$field["property"]]["value"],$field["value"]);
                array_push($formTransformed["fields"][$field["property"]]["htmlName"],$field["htmlName"]);
                dump($formTransformed);
                if(array_key_exists('valueLabel',$formTransformed["fields"][$field["property"]]) && $formTransformed["fields"][$field["property"]]['valueLabel'] != null )
                    array_push($formTransformed["fields"][$field["property"]]["valueLabel"],$field["valueLabel"]);
            }
            else{
                $formTransformed["fields"][$field["property"]]["label"] = $field["label"];
                $formTransformed["fields"][$field["property"]]["comment"] = $field["comment"];
                $formTransformed["fields"][$field["property"]]["value"] = array($field["value"]);
                $formTransformed["fields"][$field["property"]]["widgetType"] = $field["widgetType"];
                $formTransformed["fields"][$field["property"]]["htmlName"] = array($field["htmlName"]);
                $formTransformed["fields"][$field["property"]]["cardinality"] = $field["cardinality"];
                $formTransformed["fields"][$field["property"]]["valueLabel"] = (array_key_exists('valueLabel',$field))? array($field["valueLabel"]) : null;
            }
        }

        return $formTransformed;
    }

}