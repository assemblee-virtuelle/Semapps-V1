<?php
namespace  AV\SparqlBundle\Sparql;
/**
 * Created by PhpStorm.
 * User: LaFaucheuse
 * Date: 15/05/2017
 * Time: 11:40
 */
class sparql
{
    CONST VALUE_TYPE_URL = 0;
    CONST VALUE_TYPE_TEXT = 1;
    CONST ORDER_ASC = 0;
    CONST ORDER_DESC = 1;
    CONST ORDER = 2;

    public $prefixes = [
      'xsd'   => 'http://www.w3.org/2001/XMLSchema#',
      'fn'    => 'http://www.w3.org/2005/xpath-functions#',
      'text'  => 'http://jena.apache.org/text#',
      'rdf'   => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
      'rdfs'  => 'http://www.w3.org/2000/01/rdf-schema#',
      'foaf'  => 'http://xmlns.com/foaf/0.1/',
      'purl'  => 'http://purl.org/dc/elements/1.1/',
      'event' => 'http://purl.org/NET/c4dm/event.owl#',
      'fipa'  => 'http://www.fipa.org/schemas#',
      'skos'  => 'http://www.w3.org/2004/02/skos/core#',
      'gvoi'  => 'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#',
      'org'   => 'http://www.w3.org/ns/org#'
    ];

    private $prefix;

    private $where = [];

    private $union= [];

    private $group =[];

    private $filter =[];

    private $order ='';

    private $limit;

    public function getLimit(){
        return $this->limit;
    }

    public function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    public function groupBy($val){
        $this->group[] = $val;
        return $this;
    }

    public function orderBy($type,$val){
        switch ($type){
            case sparql::ORDER_ASC :
                $this->order = ' ORDER BY ASC('.$val.")\n";
                break;
            case sparql::ORDER_DESC :
                $this->order = ' ORDER BY DESC('.$val.")\n";
                break;
            case sparql::ORDER :
                $this->order = ' ORDER BY '.$val."\n";
                break;

        }
        return $this;
    }


    public function addPrefixes(Array $prefix ){
        foreach ($prefix as $key => $value)
            $this->addPrefix($key,$value);
        return $this;
    }

    public function addPrefix($key,$value){
        if(is_string($key) && is_string($value))
            $this->prefix[$key] = $value;
        return $this;
    }

    public function getPrefix(){
        return $this->prefix;
    }
    public function addUnion(array $head,array $union){
        $this->union[]['head'] = $head;
        $this->union[sizeof($this->union)-1]['union'] = $union;
        return $this;

    }
    public function addWhere($subject,$predicate,$object,$graph =null){
        $this->formatTriple($this->where,$this->createTriple($subject,$predicate,$object),$graph);
        return $this;
    }
    public function addFilter($filter){
        $this->filter[] =$filter;
        return $this;
    }
    public function addOptional($subject,$predicate,$object,$graph =null){
        if(!$graph)
            $this->where[] = "OPTIONAL { ".$this->createTriple($subject,$predicate,$object).' }';
        else
            $this->where[$graph][] = "OPTIONAL { ".$this->createTriple($subject,$predicate,$object).' }';

        return $this;
    }

    public function formatValue($val,$type = sparql::VALUE_TYPE_TEXT){
        return ($type == sparql::VALUE_TYPE_TEXT) ? '"'.$val.'"' : '<'.$val.'>';
    }

    public function getQuery(){
        $query['prefix'] = $this->prefixToString();
        $query['where'] = $this->whereToString();
        $query['limit'] = $this->limitToString();
        $query['order'] = $this->order;
        $query['group'] = $this->groupToString();
        return $query;
    }

    public function prefixToString(){
        $prefixString ='';
        if(sizeof($this->prefix) > 0){
            foreach ($this->prefix as $key => $url){
                $prefixString .= 'PREFIX '.$key.': <'.$url.'>'."\n";
            }
        }
        return $prefixString;
    }

    public function whereToString(){
        $whereString = '';
        if(sizeof($this->where) > 0 || sizeof($this->union) > 0){
        $whereString ='WHERE {';
        $whereString .= $this->formatTab($this->where);
        $whereString .= $this->unionToString();
        $whereString .= $this->filterToString();
        $whereString .= '}';
        }
        return $whereString;
    }

    public function unionToString()
    {
        $unionString = '';

        foreach ($this->union as $unions){
            $unionString.='{ ';
            //head
            foreach ($unions['head'] as $graph =>$head){
                if(is_array($unions['head'][$graph])){
                    $unionString .= ' GRAPH '.$graph. ' {';
                    foreach ($head as $string){
                        $unionString .=$string;
                    }
                    $unionString.= " }\n";
                }
                else{
                    $unionString.=$head;
                }
            }
            $unionString.='} UNION {';
            //union
            foreach ($unions['union'] as $graph =>$union){
                if(is_array($union)){
                    $unionString .= ' GRAPH '.$graph. ' {';
                    foreach ($union as $string){
                        $unionString .=$string;
                    }
                    $unionString.= " }\n";
                }
                else{
                    $unionString.=$union;
                }
            }
            $unionString.="}\n";
        }
        return $unionString;
    }
    public function filterToString(){
        $filterString = "";
        foreach ($this->filter as $filter){
            $filterString .= "FILTER ( ".$filter." ).\n";
        }
        return $filterString;
    }
    public function limitToString(){
        if ($this->limit)
            return 'LIMIT '.$this->limit;
        else
            return '';
    }

    public function groupToString(){
        if (empty($this->group) )
            return '';
        $string = 'GROUP BY ';
        foreach ($this->group as $elem){
            $string.=$elem.' ';
        }
        return $string."\n";
    }

    protected function formatTab(Array $tab){
        $query ="";
        foreach ($tab as $graph =>$string){
            if (!is_array($string))
                $query .=  $string."\n";
            else{
                $query .='GRAPH '.$graph.' {'."\n";
                foreach ($string as $triple){
                    $query .=  $triple."\n";
                }
                $query .= '}'."\n";
            }
        }
        return $query;
    }

    protected function formatTriple(&$tab,$triple,$graph){
        if(!$graph)
            $tab[] = $triple;
        else
            $tab[$graph][] = $triple;
    }

    public function createTriple($subject,$predicat,$object){
        return $subject.' '.$predicat.' '.$object.'.';
    }
}