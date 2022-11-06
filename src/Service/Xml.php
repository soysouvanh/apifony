<?php 
namespace App\Service;

/**
 * Class extending DomDocument to convert an associative array to strong in XML format.
 * @uses (new \App\Service\Xml($assocArray))->saveXML();
 * @link https://github.com/digitickets/lalit/blob/master/src/Array2XML.php
 */
class Xml extends \DomDocument
{
	/**
	 * Instanciate Xml class instance.
	 * @param array $associativeArray Tableau associatif à convertir au format XML.
	 * @param string $nodeRootName (optionnel) Nom du noeud racine. "root" par défaut.
	 * @param array $docType (optionnel) Propriétés du document XML. [] par défaut.
	 * @param string $version (optionnel) Version du document. 1.0 par défaut.
	 * @param string $encoding (optionnel) Codage des caractères. utf-8 par défaut.
	 * @param bool $standalone (optionnel) Référence à une DTD externe ou non. false par défaut indiquant que la DTD se trouve dans un fichier séparé.
	 * @param bool $formatOutput (optionnel) Formate la sortie avec indentation et espace supplémentaire. false par défaut.
	 */
	public function __construct(array $associativeArray, string $nodeRootName = 'root', array $docType = [], string $version = '1.0', string $encoding = 'utf-8', bool $standalone = false, bool $formatOutput = false)
	{
		// Initialise l'instance
		parent::__construct($version, $encoding);
		
		$this->xmlStandalone = $standalone;
		$this->formatOutput = $formatOutput;
		$this->encoding = $encoding;
		
		// BUG 008 - Support <!DOCTYPE>
		if($docType) {
			$this->appendChild((new \DOMImplementation())->createDocumentType(
				$docType['name'] ?? '',
				$docType['publicId'] ?? '',
				$docType['systemId'] ?? ''
			));
		}
		
		// Crée le noeud racine
		$this->appendChild($this->arrayToXml($nodeRootName, $associativeArray));
	}
	
	/**
	 * Convertit un tableau associatif en XML en utilisant la récursivité.
	 * @param string $nodeName Nom du noeud.
	 * @param mixed $nodeValue (optionnel) Valeur du noeud à convertir en XML. Tableau vide [] par défaut.
	 * @return DOMNode
	 */
	private function arrayToXml(string $nodeName, $nodeValue = [])
	{
		// Crée le noeud
		$node = $this->createElement($nodeName);

		// Cas contenu du noeud de type tableau
		if(is_array($nodeValue)) {
			// Cas attribut
			if(isset($nodeValue['@attributes']) && is_array($nodeValue['@attributes'])) {
				// Renseigne les attributs du noeud
				foreach ($nodeValue['@attributes'] as $key => &$value) {
					$node->setAttribute($key, $value === true ? 'true' : ($value === false ? 'false' : $value));
				}
				
				// Supprime les attributs du tableau pour qu'ils ne soient pas traités comme noeud
				unset($nodeValue['@attributes']);
			}

			// Cas texte (sans noeud)
			if(isset($nodeValue['@value'])) {
				// Ajoute le texte
				$node->appendChild($this->createTextNode($nodeValue['@value'] === true ? 'true' : ($nodeValue['@value'] === false ? 'false' : $nodeValue['@value'])));
				return $node;
			}
			
			// Cas CDATA
			if(isset($nodeValue['@cdata'])) {
				// Ajoute le CDATA
				$node->appendChild($this->createCDATASection($nodeValue['@cdata'] === true ? 'true' : ($nodeValue['@cdata'] === false ? 'false' : $nodeValue['@cdata'])));
				return $node;
			}
			
			// Boucle sur les éléments du noeud
			foreach($nodeValue as $key => &$value) {
				// Cas élément de type tableau indexé
				if(is_array($value) && is_numeric(key($value))) {
					// Crée et ajoute le noeud parent
					$parentNode = $this->createElement($key);
					$node->appendChild($parentNode);
					
					// Ajoute les noeuds enfants "row" au noeud parent
					foreach($value as &$v) {
						$parentNode->appendChild($this->arrayToXml('row', $v));
					}
				}
				
				// Cas élément de type non tableau indexé
				else {
					// Ajoute le noeud enfant
					$node->appendChild($this->arrayToXml($key, $value));
				}
			}
		}

		// Cas contenu du noeud de type non tableau
		else {
			// Ajoute le texte
			$node->appendChild($this->createTextNode($nodeValue === true ? 'true' : ($nodeValue === false ? 'false' : $nodeValue)));
		}
		
		// Retourne le noeud créé
		return $node;
	}
}