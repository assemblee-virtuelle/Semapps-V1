<?php
/**
 * Created by PhpStorm.
 * User: LaFaucheuse
 * Date: 22/03/2017
 * Time: 10:36
 */

namespace semappsBundle\Services;


class FormattingForm
{
    public function format($formBySf)
    {

        $formTransformed["url"]       = $formTransformed["uri"] = $formBySf["subject"];
        $formTransformed["thumbnail"] = $formBySf["thumbnail"];
        $formTransformed["fields"]    = array();
        foreach ($formBySf["fields"] as $field) {
            $property = $field["property"];

            if (array_key_exists($property, $formTransformed["fields"])) {
                $fieldContent["value"][]    = $field["value"];
                $fieldContent["htmlName"][] = $field["htmlName"];

                if (array_key_exists(
                        'valueLabel',
                        $formTransformed["fields"][$property]
                    ) && $formTransformed["fields"][$property]['valueLabel'] != null
                ) {
                    $fieldContent["valueLabel"][] = $field["valueLabel"];
                }
            } else {
                $fieldContent = [
                    "label"       => $field["label"],
                    "comment"     => $field["comment"],
                    "value"       => array($field["value"]),
                    "widgetType"  => $field["widgetType"],
                    "htmlName"    => array($field["htmlName"]),
                    "cardinality" => $field["cardinality"],
                    "valueLabel"  => (array_key_exists(
                        'valueLabel',
                        $field
                    )) ? array($field["valueLabel"]) : null,
                ];
            }
            $formTransformed["fields"][$property] = $fieldContent;
        }
        return $formTransformed;
    }
}
