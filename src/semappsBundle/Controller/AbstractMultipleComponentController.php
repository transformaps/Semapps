<?php

namespace semappsBundle\Controller;


use Symfony\Component\HttpFoundation\Request;

abstract class AbstractMultipleComponentController extends AbstractComponentController
{
    var $sfLink;

    public function componentList($componentConf,$componentType){
        /** @var  $sfClient \VirtualAssembly\SemanticFormsBundle\Services\SemanticFormsClient  */
        $sfClient = $this->container->get('semantic_forms.client');
        /** @var \VirtualAssembly\SparqlBundle\Services\SparqlClient $sparqlClient */
        $sparqlClient   = $this->container->get('sparqlbundle.client');

        if(array_key_exists('graphuri',$componentConf) && $componentConf['graphuri'] != null)
            $graphURI = $componentConf['graphuri'];
        else
            $graphURI = $this->getGraph(null);

        /** @var \VirtualAssembly\SparqlBundle\Sparql\sparqlSelect $sparql */
        $sparql = $sparqlClient->newQuery($sparqlClient::SPARQL_SELECT);
        $graphURI = $sparql->formatValue($graphURI,$sparql::VALUE_TYPE_URL);
        $componentType = $sparql->formatValue($componentType,$sparql::VALUE_TYPE_URL);

        $sparql->addPrefixes($sparql->prefixes)
            ->addSelect('?URI')
            ->addWhere('?URI','rdf:type',$componentType,$graphURI);
        foreach ($componentConf['label'] as $field ){
            $label = $componentConf['fields'][$field]['value'];
            $fieldFormatted = $sparql->formatValue($field,$sparql::VALUE_TYPE_URL);
            $sparql->addSelect('?'.$label)
                ->addWhere('?URI',$fieldFormatted,'?'.$label,$graphURI);
        }

        $results = $sfClient->sparql($sparql->getQuery());

        $listContent = [];
        if (isset($results["results"]["bindings"])) {
            foreach ($results["results"]["bindings"] as $item) {
                $title = '';
                foreach ($componentConf['label'] as $field ){
                    $label = $componentConf['fields'][$field]['value'];
                    $title .= $item[$label]['value'] .' ';
                }
                $listContent[] = [
                    'uri'   => $item['URI']['value'],
                    'title' => $title,
                ];

            }
        }
        return $listContent;
    }

    public function listAction($componentName,Request $request)
    {
        $bundleName = $this->getBundleNameFromRequest($request);
        $componentList = $this->getParameter('semantic_forms.component');
        $componentConf = $this->getParameter($componentName.'Conf');
        $listContent = $this->componentList($componentConf,$componentList[$componentName]);
        return $this->render(
            $bundleName.':'.ucfirst($componentName).':'.$componentName.'List.html.twig',
            array(
                'componentName' => $componentName,
                'plural'        => $componentName.'(s)',
                'listContent'   => $listContent,
            )
        );
    }

    public function removeComponent($uri){
        /** @var  $sfClient \VirtualAssembly\SemanticFormsBundle\Services\SemanticFormsClient  */
        $sfClient = $this->container->get('semantic_forms.client');
        /** @var \VirtualAssembly\SparqlBundle\Services\SparqlClient $sparqlClient */
        $sparqlClient   = $this->container->get('sparqlbundle.client');

        $sparql = $sparqlClient->newQuery($sparqlClient::SPARQL_DELETE);
        $sparqlDeux = clone $sparql;

        $uri = $sparql->formatValue($uri,$sparql::VALUE_TYPE_URL);

        $sparql->addDelete($uri,'?P','?O','?gr')
            ->addWhere($uri,'?P','?O','?gr');
        $sparqlDeux->addDelete('?s','?PP',$uri,'?gr')
            ->addWhere('?s','?PP',$uri,'?gr');

        $sfClient->update($sparql->getQuery());
        $sfClient->update($sparqlDeux->getQuery());
    }

    public function removeAction($componentName,Request $request){

        self::removeComponent($request->get('uri'));
        return $this->redirectToRoute(
            'componentList', ["componentName" => $componentName]
        );

    }

    function getSfLink($id = null)
    {
        return $this->sfLink;
    }
    public function setSfLink($sfLink){
        $this->sfLink = $sfLink;
    }
    function getElement($id = null)
    {
        return null;
    }
}
