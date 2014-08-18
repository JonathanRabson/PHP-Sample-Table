<?php
	//Class html is a wrapper for generating HTML tags.
	//When an instance is released, it will automatically render the HTML;
	//or one can call the render method manually.
	//[[NOTE: Ideally, elements should be implemented using an "element" class that counts children
	//while permitting non-element content (text) mixed with nested elements;
	//currently, we just use a simple flat model of the HTML document which is then rendered sequentially.
	//Indenting of HTML source is only partially implemented.]]
	class html {
		public $strDoctype = '!DOCTYPE html';
		public $strBodyAttributes;
		public $strBody;
		public $bRendered = false;
		public $arrHead;
		public $arrHeadAttributes;
		public $arrNoEnd;
		private $intIndentLevel = 0;
				
		//When calling the class, specify the title tag contents.
		function __construct($strTitle){
			$this->arrHead['title'] = $strTitle;
			$this->addTagInHead('link', '');
			$this->addAttributesInHead('link', 'rel="stylesheet" type="text/css" href="styles.css"');
			
			//[[Hard-coded: Assumes HTML, not XHTML]]
			//[[Not a complete list]]
			$this->arrNoEnd = array('link','br','img');
			$this->intIndentLevel = 1; //Start out with indent for body.		
		}
	
		//Use this to add elements to head element (add attributes separately at addAttributesInHead)
		public function addTagInHead($name, $value)
		{
			$this->arrHead[$name] = $value;
		
		}
		//To add attributes, match the name to an element in the head section of the HTML.
		public function addAttributesInHead($name, $value)
		{
			$this->arrHeadAttributes[$name] = $value;
		
		}
		
		//Add HTML table from database result object to body
		public function addResultsTable($result) {
			$this->strBody .= $this->makeResultsTable($result);
		}
		
		//Make HTML table from database result object
		//(returns a string containing the table)
		public function makeResultsTable($result) {
			$bHeadDone = false;
			$strRes = $this->newline() . $this->makeTag('table',false);
			$strHead = $this->makeTag('thead',false)
				. $this->makeTag('tr',false);
			$strBody = $this->makeTag('tbody',false);
			
			while ($row = $result->fetch_assoc()) {
				$strBody .= $this->makeTag('tr',false);	
				foreach ($row as $key => $value) {
					if(!$bHeadDone) {
						$strHead .= $this->makeElement('th','',$key);
					}
					$strBody .= $this->makeElement('td','',$value);
					
				}
				$strBody .= $this->makeTag('tr',true);
				$bHeadDone = true; //After 1st row, the head is done.
			}
			if($bHeadDone == true) {
				$strHead .= $this->makeTag('tr',true)
					. $this->makeTag('thead',true);
				$strRes .= $strHead . $strBody;
				$strRes .= $this->makeTag('tbody',true);
				$strRes .= $this->makeTag('table',true);
				return $strRes;
			} else {
				return $this->makeElement('mark','','No results found');
			}
			
		}
		
		//To add a new line and indent as needed
		//[[Includes nesting functionality but not fully implemented yet because
		//a more heirarchical model is needed to adequately deal with nesting when the 
		//creation of elements may not occur in order.]]
		private function newline($intChangeIndentBefore = 0, $bUseIndent = true, $intChangeIndentAfter = 0) {	
			$strRes = "\n";
			if($intChangeIndentBefore !=0) {
				$this->incrementIndent($intChangeIndentBefore);
			}
			if($bUseIndent) {
				$strRes = str_pad($strRes,$this->intIndentLevel * 4," ",STR_PAD_RIGHT);
			}
			//Affects next call.
			if($intChangeIndentAfter !=0) {
				$this->incrementIndent($intChangeIndentAfter);
			}

			return $strRes;
		}
		//Internal function to change indenting level
		private function incrementIndent($intChange) {
			$this->intIndentLevel += $intChange;
			//For safety:  disallow negative or excessing indent.
			if($this->intIndentLevel < 0) {
				$this->intIndentLevel = 0;
			} else if($this->intIndentLevel > 10) {
				$this->intIndentLevel = 10;
			}
		}
						
		//To make a tag [[does not currenly support XHTML-style self-closing tags]]
		private function makeTag($strContents, $bEnd) {
			$strTag = '<';
			if($bEnd) {
				$strTag .= '/';
			}
			$strTag .= $strContents . '>';
			return $strTag;
		}
		//Make sure there's a space before something; useful for attributes
		private function leftPad($strAttributes) {
			if((strlen($strAttributes) > 0) && (substr($strAttributes,0,1) != ' ')) {
				return " $strAttributes";
			} else {
				return $strAttributes;
			}
		}
		
		//For calling from outside to make an element.  This appends to body.
		//[[This adds sequentially only and does not nest or add children to the DOM.]]
		public function addBodyElement($strElementName, $strAttributes, $strValue, $bBlock = true) {
			$this->strBody .= $this->makeElement($strElementName, $strAttributes, $strValue, $bBlock = true);
		}
		
		
		//Make full element with start and closing
		private function makeElement($strElementName, $strAttributes, $strValue, $bBlock = true) {
			$strRes = '';
			if($bBlock) {
				$strRes = $this->newline();
			}
						
			$strRes .= $this->makeTag($strElementName . $this->leftPad($strAttributes), false);
			$strRes .= $strValue;
			
			if(!in_array($strElementName,$this->arrNoEnd)) {
				//Close the tag unless it shouldn't be closed per HTML5 spec.
				$strRes .= $this->makeTag($strElementName, true);
			}
			
			return $strRes;
		}
		
		//Allows one to render the HTML and continue processing.
		//If not called from outside the function, it will happen on object destruction.
		public function render() {
			global $bRendered;
			$strHTMLText = $this->makeTag($this->strDoctype, false);
			$strHTMLText .= $this->newline(0,false) . $this->makeTag('html',false);
			
			$strHTMLText .= $this->newline(0,false) . $this->makeTag('head',false);
			$strHeadAttribute = '';
			
			if (is_array($this->arrHead)) {
				foreach ($this->arrHead as $key => $value) {
					if(isset($this->arrHeadAttributes[$key])) {
						$strHeadAttribute = $this->arrHeadAttributes[$key];
					} else {
						$strHeadAttribute = '';
					}
					if(isset($key)) {
						$strHTMLText .= $this->newline(0,true) . $this->makeElement($key, $strHeadAttribute, $value, false);
					}
				}
			}
			$strHTMLText .= $this->newline(0,false) . $this->makeTag('head',true);
			
			$strHTMLText .= $this->newline(0,false) . $this->makeElement('body',$this->strBodyAttributes,$this->strBody . $this->newline(0,false), false);
			$strHTMLText .= $this->newline(0,false) . $this->makeTag('html',true);
			
			echo $strHTMLText;
			
			$bRendered = true;
		}
		
		public function __destruct( ) {
			global $bRendered;
			if(!$bRendered) {
				$this->render();
			}
		}
	
	} //End HTML Class
	
?>
