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
    ];
    private $prefix;

    private $where = [];

    private $union= [];

    private $group =[];

    private $order ='';

    private $limit;

    public function getLimit(){
        return $this->limit;
    }

    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    public function groupBy($val){
        $this->group[] = $val;
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
    }


    public function addPrefixes(Array $prefix ){
        foreach ($prefix as $key => $value)
            $this->addPrefix($key,$value);
    }

    public function addPrefix($key,$value){
        if(is_string($key) && is_string($value))
            $this->prefix[$key] = $value;
    }

    public function getPrefix(){
        return $this->prefix;
    }

    public function addWhere($subject,$predicate,$object,$graph =null){
        $this->formatTriple($this->where,$subject.' '.$predicate.' '.$object.'.',$graph);
    }

    public function addOptional($subject,$predicate,$object,$graph =null){
        if(!$graph)
            $this->where[] = "OPTIONAL { ".$subject.' '.$predicate.' '.$object.'. }';
        else
            $this->where[$graph][] = "OPTIONAL { ".$subject.' '.$predicate.' '.$object.'. }';
    }

    public function formatValue($val,$type = sparql::VALUE_TYPE_TEXT){
        return ($type == sparql::VALUE_TYPE_TEXT) ? '"'.$val.'"' : '<'.$val.'>';
    }

    public function getQuery(){
        $query['prefix'] = $this->formatPrefix();
        $query['where'] = $this->formatWhere();
        $query['limit'] = $this->formatLimit();
        $query['order'] = $this->order;
        $query['group'] = $this->formatGroup();
        return $query;
    }

    public function formatPrefix(){
        $prefixString ='';
        if(sizeof($this->prefix) > 0){
            foreach ($this->prefix as $key => $url){
                $prefixString .= 'PREFIX '.$key.': <'.$url.'>'."\n";
            }
        }
        return $prefixString;
    }

    public function formatWhere(){
        //if (empty($this->group) )
        //    return '';
        $whereString = '';
        if(sizeof($this->where) > 0){
        $whereString ='WHERE {';
        $whereString .= $this->formatTab($this->where);
        $whereString .= $this->formatUnion();
        $whereString .= '}';
        }
        return $whereString;
    }

    public function formatUnion()
    {
        $unionString = '';
        if($this->union){
            $unionString = 'UNION {';
            foreach ($this->union as $string){
                $unionString .= $string.".\n";
            }
            $unionString .= '}';
        }
        return $unionString;
    }

    public function formatLimit(){
        if ($this->limit)
            return 'LIMIT '.$this->limit;
        else
            return '';
    }

    public function formatGroup(){
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
}